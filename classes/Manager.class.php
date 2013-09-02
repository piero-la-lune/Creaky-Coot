<?php

# Links model :
# 	type => (string) 'unread|read|archived'
#	title => escaped (string)
#	content => (string)
#	date => (timestamp)
#	link => (string)
#	comment => (string)
#	tags => (array)
#
# Feeds model :
#	type => (string) 'rss|twitter'
#	title => escaped (string)
#	url => (string)
#	params => (array)
#	link => (string)
#	unread => (array)
#	read => (array)
#	archived => (array)
#	deleted => (array)
#	content => (const) T_RSS|T_DLOA
#	comment => (const) T_EMPTY|T_RSS|T_DLOAD
#	filter_html => (string)

class Manager {

	private static $instance;
	protected $feeds = array();
	protected $links = array();
	protected $tags = array();
	protected $done = array();
	protected $last_insert;

	const T_EMPTY = 0;
	const T_RSS = 1;
	const T_DLOAD = 2;

	protected $curl_opts = array(
		CURLOPT_AUTOREFERER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 4,
		CURLOPT_CONNECTTIMEOUT => 8,
		CURLOPT_TIMEOUT => 8,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) Gecko/20100101 Firefox/19.0',
		CURLOPT_SSL_VERIFYPEER => false
	);

	public function __construct() {
		global $config;
		$this->feeds = Text::unhash(get_file(FILE_FEEDS));
		$this->links = Text::unhash(get_file(FILE_LINKS));
		$this->tags = Text::unhash(get_file(FILE_TAGS));
	}

	public static function getInstance($project = NULL) {
		if (!isset(self::$instance)) {
			self::$instance = new Manager();
		}
		return self::$instance;
	}

	protected function save() {
		update_file(FILE_FEEDS, Text::hash($this->feeds));
		update_file(FILE_LINKS, Text::hash($this->links));
		update_file(FILE_TAGS, Text::hash($this->tags));
	}

	protected function createNewFeed($url = '', $type = 'rss', $params = array()) {
		foreach ($this->feeds as $f) {
			if ($f['url'] == $url && $f['params'] == $params) { return false; }
		}
		$id = Manager::newKey($this->feeds);
		$this->feeds[$id] = array(
			'type' => $type,
			'title' => '',
			'url' => $url,
			'params' => $params,
			'link' => '',
			'unread' => array(),
			'read' => array(),
			'archived' => array(),
			'deleted' => array(),
			'content' => self::T_RSS,
			'comment' => self::T_EMPTY,
			'filter_html' => ''
		);
		return $id;
	}

	public function getFeeds() {
		return $this->feeds;
	}

	public function getFeed($id) {
		if (isset($this->feeds[$id])) { return $this->feeds[$id]; }
		return false;
	}

	public function getLinks($filter = array(), $idStart = NULL, $limit = NULL) {
		global $config;
		if (!$limit) { $limit = $config['links_per_page']; }
		if (isset($filter['feed']) && isset($this->feeds[$filter['feed']])) {
			$links = array();
			$feed = $this->feeds[$filter['feed']];
			foreach ($feed['unread'] as $v) { $links[$v] = $this->links[$v]; }
			foreach ($feed['read'] as $v) { $links[$v] = $this->links[$v]; }
			foreach ($feed['archived'] as $v) { $links[$v] = $this->links[$v]; }
		}
		else {
			$links = $this->links;
		}
		if (isset($filter['tag'])) {
			# On doit pouvoir faire plus efficace...
			foreach ($links as $id => $l) {
				if (!in_array($filter['tag'], $l['tags'])) {
					unset($links[$id]);
				}
			}
		}
		if (isset($filter['q'])) {
			foreach ($links as $id => $l) {
				foreach ($filter['q'] as $q) {
					if (stripos($l['title'], $q) === false
						&& stripos($l['content'], $q) === false
						&& stripos($l['comment'], $q) === false
					) {
						unset($links[$id]);
					}
				}
			}
		}
		if (isset($filter['type'])) {
			foreach ($links as $id => $l) {
				if ($l['type'] != $filter['type']) { unset($links[$id]); }
			}
		}
		$links = Manager::sort($links);
		if ($idStart && isset($links[$idStart])) {
			$keys = array_keys($links);
			$start = array_search($idStart, $keys)+1;
		}
		else {
			$start = 0;
		}
		return array_slice($links, $start, $limit);
	}

	public function getLink($id) {
		if (isset($this->links[$id])) { return $this->links[$id]; }
		return false;
	}

	public function getTags() {
		return array_keys($this->tags);
	}
	public function addTags($id, $tags) {
		foreach ($tags as $t) {
			$this->tags[$t][] = $id;
		}
	}
	public function removeTags($id, $tags) {
		foreach ($tags as $t) {
			$key = array_search($id, $this->tags[$t]);
			if ($key !== false) {
				array_splice($this->tags[$t], $key, 1);
				if (empty($this->tags[$t])) { unset($this->tags[$t]); }
			}
		}
	}

	public function lastInsert() {
		return $this->last_insert;
	}

	public function addFeed($post) {
		$check = $this->rss_or_twitter($post);
		if ($check === false) {
			return Trad::$settings['validate_url'];
		}
		list($type, $url, $params) = $check;
		if (($id = $this->createNewFeed($url, $type, $params)) === false) {
			return Trad::A_ERROR_EXISTING_FEED;
		}
		$this->update(array($id));
		if (!isset($this->done[$id]) || $this->done[$id] === false) {
			unset($this->feeds[$id]);
			$this->done = array();
			return Trad::A_ERROR_BAD_FEED;
		}
		$this->feeds[$id]['title'] = $this->done[$id]['title'];
		$this->feeds[$id]['url'] = $this->done[$id]['url'];
		$this->feeds[$id]['link'] = $this->done[$id]['link'];
		$this->save();
		$this->done = array();
		return true;
	}

	public function editFeed($post, $id) {
		if (!isset($this->feeds[$id])
			|| !isset($post['title'])
			|| !isset($post['link'])
			|| !isset($post['content'])
			|| !isset($post['comment'])
			|| !isset($post['filter_html'])
		) {
			return Trad::A_ERROR_FORM;
		}
		$check = $this->rss_or_twitter($post);
		if ($check === false) {
			return Trad::$settings['validate_url'];
		}
		list(, $url, $params) = $check;
		$oldfeed = $this->feeds[$id];
		$this->feeds[$id]['title'] =
			Text::chars($post['title'], ENT_QUOTES, 'UTF-8');
		$this->feeds[$id]['url'] = $url;
		$this->feeds[$id]['params'] = $params;
		$this->feeds[$id]['link'] = $post['link'];
		$this->feeds[$id]['content'] = Text::compare(
			$post['content'],
			array(self::T_EMPTY, self::T_RSS, self::T_DLOAD),
			$this->feeds[$id]['content']
		);
		$this->feeds[$id]['comment'] = Text::compare(
			$post['comment'],
			array(self::T_EMPTY, self::T_RSS),
			$this->feeds[$id]['comment']
		);
		$this->feeds[$id]['filter_html'] = $post['filter_html'];
		$this->update(array($id));
		if (!isset($this->done[$id]) || $this->done[$id] === false) {
			$this->feeds[$id] = $oldfeed;
			$this->done = array();
			return Trad::A_ERROR_BAD_FEED;
		}
		$this->save();
		$this->done = array();
		return true;
	}

	protected function rss_or_twitter($post) {
		if (isset($post['twitter_url']) && isset($post['params'])) {
			$type = 'twitter';
			$url = $post['twitter_url'];
			$params = Text::params_arr($post['params']);
		}
		elseif (isset($post['url'])) {
			if (!filter_var($post['url'], FILTER_VALIDATE_URL)) {
				return false;
			}
			$type = 'rss';
			$url = $post['url'];
			$params = array();
		}
		else {
			return false;
		}
		return array($type, $url, $params);
	}

	public function refreshFeed($feed = NULL) {
		$done = array();
		$added = array();
		$ids = array($feed);
		if ($feed === NULL || !isset($this->feeds[$feed])) {
			$ids = array_keys($this->feeds);
		}
		$this->update($ids);
		foreach ($this->done as $d) {
			if ($d !== false) {
				$nb = 0;
				foreach ($d['added'] as $id) {
					$added[$id] = $this->links[$id];
					$nb++;
				}
				$done[] = array('title' => $d['title'], 'nb' => $nb);
			}
		}
		$this->save();
		$this->done = array();
		return array($done, $added);
	}

	public function clearFeed($id) {
		if (!isset($this->feeds[$id])) { return Trad::A_ERROR_UNKNOWN_FEED; }
		foreach ($this->feeds[$id]['unread'] as $l) {
			unset($this->links[$l]);
			$this->feeds[$id]['deleted'][] = $l;
		}
		$this->feeds[$id]['unread'] = array();
		foreach ($this->feeds[$id]['read'] as $l) {
			unset($this->links[$l]);
			$this->feeds[$id]['deleted'][] = $l;
		}
		$this->feeds[$id]['read'] = array();
		$this->save();
		return true;
	}

	public function deleteFeed($id) {
		if (!isset($this->feeds[$id])) { return Trad::A_ERROR_UNKNOWN_FEED; }
		foreach ($this->feeds[$id]['unread'] as $l) {
			unset($this->links[$l]);
		}
		foreach ($this->feeds[$id]['read'] as $l) {
			unset($this->links[$l]);
		}
		unset($this->feeds[$id]);
		$this->save();
		return true;
	}

	public function callback_update($header, $content, $id) {
		global $config;
		if (!isset($header['http_code']) || $header['http_code'] !== 200) {
			$this->done[$id] = false;
			return false;
		}
		$filter = new Filter($this->feeds[$id]['filter_html']);
		$parser = new RssParser($filter);
		$ans = $parser->readFeed($content, $header['url']);
		if ($ans === false) {
			$this->done[$id] = false;
			return false;
		}
		$added = array();
		foreach ($ans['items'] as $i) {
			$id2 = md5($i['link']);
			if (!isset($this->links[$id2])
				&& !in_array($id2, $this->feeds[$id]['deleted'])
			) {
				if ($config['auto_tag']) {
					if (empty($this->feeds[$id]['title'])) {
						$tags = array(Text::purge(
							Text::unchars($ans['title'])));
					}
					else {
						$tags = array(Text::purge(
							Text::unchars($this->feeds[$id]['title'])));
					}
				}
				else {
					$tags = array();
				}
				$this->feeds[$id]['unread'][] = $id2;
				$content = '';
				if ($this->feeds[$id]['content'] == self::T_RSS) {
					$content = $i['content'];
				}
				if ($this->feeds[$id]['content'] == self::T_DLOAD) {
					$curlManager = new Curl_Multi();
					$request = curl_init($i['link']);
					curl_setopt_array($request, $this->curl_opts);
					$curlManager->addHandle(
						$request,
						array($this, 'callback_add'),
						array(
							'id' => $id2,
							'filter_html' => $this->feeds[$id]['filter_html']
						)
					);
					$curlManager->finish();
					if (!isset($this->done[$id2]) || !$this->done[$id2]) {
						$content = '';
					}
					else {
						$content = $this->done[$id2]['content'];
					}
					unset($this->done[$id2]);
				}
				$comment = '';
				if ($this->feeds[$id]['comment'] == self::T_RSS) {
					$comment = $i['content'];
				}
				$this->links[$id2] = array(
					'type' => 'unread',
					'title' => $i['title'],
					'content' => $content,
					'date' => $i['date'],
					'link' => $i['link'],
					'comment' => $comment,
					'tags' => $tags
				);
				$this->addTags($id2, $tags);
				$added[] = $id2;
			}
		}
		$this->done[$id] = array(
			'title' => $ans['title'],
			'url' => $ans['url'],
			'link' => $ans['link'],
			'added' => $added
		);
		return true;
	}

	public function update($ids) {
		$curlManager = new Curl_Multi();
		foreach ($ids as $id) {
			# We assume all given ids correspond to existing feeds
			$feed = $this->feeds[$id];
			if ($feed['type'] == 'rss') {
				$request = curl_init($this->feeds[$id]['url']);
				curl_setopt_array($request, $this->curl_opts);
				$curlManager->addHandle(
					$request,
					array($this, 'callback_update'),
					$id
				);
			}
			else {
				$this->update_twitter($id);
			}
		}
		$curlManager->finish();
	}

	public function update_twitter($id) {
		global $config;
		$twitter = new Twitter();
		$ans = $twitter->get(
			$this->feeds[$id]['url'],
			$this->feeds[$id]['params'],
			$this->feeds[$id]['deleted']
		);
		if ($ans === false) {
			$this->done[$id] = false;
			return false;
		}
		$added = array();
		foreach ($ans as $id2 => $t) {
			$tags = array();
			if ($config['auto_tag'] && !empty($this->feeds[$id]['title'])) {
				$tags = array(Text::purge(
					Text::unchars($this->feeds[$id]['title'])));
			}
			$this->feeds[$id]['unread'][] = $id2;
			$this->links[$id2] = array(
				'type' => 'unread',
				'title' => Text::chars('@'.$t['user']),
				'content' => $t['tweet'],
				'date' => $t['date'],
				'link' => $t['url'],
				'comment' => NULL,
				'tags' => $tags,
				'tweet' => array('user_img' => $t['user_img'])
			);
			$this->addTags($id2, $tags);
			$added[] = $id2;
		}
		$this->done[$id] = array(
			'title' => Text::chars($this->feeds[$id]['url']),
			'url' => $this->feeds[$id]['url'],
			'link' => 'http://twitter.com',
			'added' => $added
		);
		return true;
	}

	public function callback_add($header, $content, $params) {
		$id = $params['id'];
		if (!isset($header['http_code'])
			|| $header['http_code'] !== 200
			|| !isset($header['content_type'])
			|| strpos($header['content_type'], 'text/html') === false
		) {
			$this->done[$id] = false;
			return false;
		}
		$filter = new Filter($params['filter_html']);
		$ans = $filter->execute($content, $header['url']);
		if (!$ans || empty($ans)) {
			$this->done[$id] = false;
			return false;
		}
		$this->done[$id] = array(
			'title' => $filter->getTitle(),
			'link' => $header['url'],
			'content' => $ans
		);
		return true;
	}

	public function add($post) {
		global $config;
		if (!isset($post['url'])
			|| !isset($post['title'])
			|| !isset($post['comment'])
			|| !isset($post['tags'])
		) {
			return Trad::A_ERROR_FORM;
		}

		if (empty($post['url'])) {
			if (empty($post['title'])) {
				return Trad::A_ERROR_FORM;
			}
			$id = Text::randomKey(32);
			$title = Text::chars($post['title']);
			$content = '<p>&nbsp;</p>';
			$link = Url::parse('links/'.$id);
		}
		else {
			if (!filter_var($post['url'], FILTER_VALIDATE_URL)) {
				return Trad::$settings['validate_url'];
			}
			$id = md5($post['url']);

			$curlManager = new Curl_Multi();
			$request = curl_init($post['url']);
			curl_setopt_array($request, $this->curl_opts);
			$curlManager->addHandle(
				$request,
				array($this, 'callback_add'),
				array('id' => $id, 'filter_html' => '')
			);
			$curlManager->finish();

			if (!isset($this->done[$id]) || !$this->done[$id]) {
				unset($this->done[$id]);
				return Trad::A_ERROR_BAD_LINK;
			}
			$title = $this->done[$id]['title'];
			$content = $this->done[$id]['content'];
			$link = $this->done[$id]['link'];
		}

		if (isset($this->links[$id])) {
			return Trad::A_ERROR_EXISTING_LINK;
		}

		$tags = array();
		foreach (explode(',', $post['tags']) as $t) {
			$t = Text::purge($t);
			if (!empty($t)) { $tags[] = $t; }
		}

		$filter = new Filter();
		$this->links[$id] = array(
			'type' => 'archived',
			'title' => $title,
			'content' => $content,
			'date' => time(),
			'link' => $link,
			'comment' => $filter->execute($post['comment'], $config['url']),
			'tags' => $tags
		);
		$this->addTags($id, $tags);
		$this->last_insert = $id;
		$this->save();
		return true;
	}

	public function edit($post) {
		global $config;
		if (!isset($post['id'])
			|| !isset($post['comment'])
			|| !isset($post['tags'])
			|| !isset($post['content'])
			|| !isset($post['title'])
			|| !isset($this->links[$post['id']])
		) {
			return Trad::A_ERROR_FORM;
		}
		$id = $post['id'];
		$parser = new Filter();
		$this->links[$id]['comment'] = $parser->execute(
			$post['comment'],
			$config['url']
		);
		$old_tags = $this->links[$id]['tags'];
		$added = array();
		$this->links[$id]['tags'] = array();
		foreach (explode(',', $post['tags']) as $t) {
			$t = Text::purge($t);
			if (!empty($t)) {
				$this->links[$id]['tags'][] = $t;
				if (!in_array($t, $old_tags)) { $added[] = $t; }
			}
		}
		$this->addTags($id, $added);
		$this->removeTags($id, array_diff($old_tags, $this->links[$id]['tags']));
		$this->links[$id]['content'] = $parser->execute(
			$post['content'],
			$this->links[$id]['link']
		);
		$this->links[$id]['title'] = Text::chars($post['title']);
		$this->save();
		return true;
	}

	public function markRead($ids) {
		if (!is_array($ids)) { $ids = array($ids); }
		$done = array();
		foreach ($ids as $id) {
			if (!isset($this->links[$id])
				|| $this->links[$id]['type'] != 'unread'
			) { continue; }
			foreach ($this->feeds as $k => $f) {
				if (($key = array_search($id, $f['unread'])) !== false) {
					unset($this->feeds[$k]['unread'][$key]);
					$this->feeds[$k]['read'][] = $id;
				}
			}
			$this->links[$id]['type'] = 'read';
			$done[$id] = true;
		}
		$this->save();
		return $done;
	}

	public function markUnread($ids) {
		if (!is_array($ids)) { $ids = array($ids); }
		$done = array();
		foreach ($ids as $id) {
			if (!isset($this->links[$id])
				|| $this->links[$id]['type'] != 'read'
			) { continue; }
			foreach ($this->feeds as $k => $f) {
				if (($key = array_search($id, $f['read'])) !== false) {
					unset($this->feeds[$k]['read'][$key]);
					$this->feeds[$k]['unread'][] = $id;
				}
			}
			$this->links[$id]['type'] = 'unread';
			$done[$id] = true;
		}
		$this->save();
		return $done;
	}

	public function archive($ids) {
		if (!is_array($ids)) { $ids = array($ids); }
		$done = array();
		foreach ($ids as $id) {
			if (!isset($this->links[$id])) { continue; }
			foreach ($this->feeds as $k => $f) {
				if (($key = array_search($id, $f['unread'])) !== false) {
					unset($this->feeds[$k]['unread'][$key]);
					$this->feeds[$k]['archived'][] = $id;
				}
				if (($key = array_search($id, $f['read'])) !== false) {
					unset($this->feeds[$k]['read'][$key]);
					$this->feeds[$k]['archived'][] = $id;
				}
			}
			$this->links[$id]['type'] = 'archived';
			$done[$id] = true;
		}
		$this->save();
		return $done;
	}

	public function clear($ids) {
		if (!is_array($ids)) { $ids = array($ids); }
		$done = array();
		foreach ($ids as $id) {
			if (!isset($this->links[$id])
				|| $this->links[$id]['type'] == 'archived'
			) { continue; }
			foreach ($this->feeds as $k => $f) {
				if (($key = array_search($id, $f['unread'])) !== false) {
					unset($this->feeds[$k]['unread'][$key]);
					$this->feeds[$k]['deleted'][] = $id;
				}
				if (($key = array_search($id, $f['read'])) !== false) {
					unset($this->feeds[$k]['read'][$key]);
					$this->feeds[$k]['deleted'][] = $id;
				}
			}
			unset($this->links[$id]);
			$done[$id] = true;
		}
		$this->save();
		return $done;
	}

	public function delete($ids) {
		if (!is_array($ids)) { $ids = array($ids); }
		$done = array();
		foreach ($ids as $id) {
			if (!isset($this->links[$id])) { continue; }
			foreach ($this->feeds as $k => $f) {
				if (($key = array_search($id, $f['unread'])) !== false) {
					unset($this->feeds[$k]['unread'][$key]);
					$this->feeds[$k]['deleted'][] = $id;
				}
				if (($key = array_search($id, $f['read'])) !== false) {
					unset($this->feeds[$k]['read'][$key]);
					$this->feeds[$k]['deleted'][] = $id;
				}
				if (($key = array_search($id, $f['archived'])) !== false) {
					unset($this->feeds[$k]['archived'][$key]);
					$this->feeds[$k]['deleted'][] = $id;
				}
			}
			unset($this->links[$id]);
			$done[$id] = true;
		}
		$this->save();
		return $done;
	}

	public function autoDelete($duration) {
		$duration = intval($duration);
		if ($duration === false) { return false; }
		$date = time()-$duration;
		foreach ($this->feeds as $k => $f) {
			$nb = 0;
			foreach ($f['read'] as $key => $id) {
				if ($this->links[$id]['date'] < $date) {
					unset($this->feeds[$k]['read'][$key]);
					unset($this->links[$id]);
					$this->feeds[$k]['deleted'][] = $id;
					$nb++;
				}
			}
			$done[] = array('title' => $f['title'], 'nb' => $nb);
		}
		$this->save();
		return $done;
	}

	public function autoClean($nb) {
		$nb = intval($nb);
		if ($nb === false) { return false; }
		$done = array();
		foreach ($this->feeds as $k => $f) {
			$start = count($f['deleted'])-$nb;
			if ($start > 0) {
				$this->feeds[$k]['deleted'] = array_slice($f['deleted'], $start);
				$done[] = array('title' => $f['title'], 'nb' => $start);
			}
			else {
				$done[] = array('title' => $f['title'], 'nb' => 0);
			}
		}
		$this->save();
		return $done;
	}

	public function import($file) {
		if (!isset($file['error'])
			|| $file['error'] > 0
			|| !isset($file['tmp_name'])
			|| !isset($file['name'])
		) {
			return Trad::A_ERROR_UPLOAD;
		}
		$feeds = RssParser::importOPML(file_get_contents($file['tmp_name']));
		if ($feeds === false) {
			return Trad::A_ERROR_IMPORT;
		}
		$ids = array();
		foreach ($feeds as $k => $f) {
			$id = $this->createNewFeed($f['url'], $f['type'], $f['params']);
			if ($id !== false) {
				$ids[$k] = $id;
			}
			else {
				unset($feeds[$k]);
			}
		}
		$this->update($ids);
		foreach ($feeds as $k => $f) {
			if (!isset($this->done[$ids[$k]])
				|| $this->done[$ids[$k]] === false
			) {
				unset($this->feeds[$ids[$k]]);
				continue;
			}
			$this->feeds[$ids[$k]]['url'] = $this->done[$ids[$k]]['url'];
			if (!empty($f['title'])) {
				$this->feeds[$ids[$k]]['title'] = Text::chars($f['title'], false);
			}
			else {
				$this->feeds[$ids[$k]]['title'] = $this->done[$ids[$k]]['title'];
			}
			if (!empty($f['link'])
				&& filter_var($f['link'], FILTER_VALIDATE_URL)
			) {
				$this->feeds[$ids[$k]]['link'] = $f['link'];
			}
			else {
				$this->feeds[$ids[$k]]['title'] = $this->done[$ids[$k]]['link'];
			}
		}
		$this->save();
		$this->done = array();
		return true;
	}

	public function export() {
		$urls = array();
		foreach ($this->feeds as $f) {
			$urls[] = array(
				'title' => Text::unchars($f['title']),
				'url' => $f['url'],
				'link' => $f['link'],
				'type' => $f['type'],
				'params' => Text::params_str($f['params'])
			);
		}
		$xml = RssParser::exportOPML($urls);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="export.opml"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: '.mb_strlen($xml));
		echo $xml;
		exit;
	}

	public static function previewList($links, $page = 'links') {
		$html = '';
		foreach ($links as $id => $l) {
			$html .= self::preview($id, $l, $page);
		}
		return $html;
	}
	public static function preview($id, $l, $page = 'links') {
		global $config;
		$unread = ' style="display:none"';
		$read = ' style="display:none"';
		$archived = ' style="display:none"';
		$tags = Manager::tagsList($l['tags'], false);
		if (!empty($tags)) { $tags = '<p>'.$tags.'</p>'; }
		if ($l['type'] == 'unread') { $read = ''; $archived = ''; }
		if ($l['type'] == 'read') { $unread = ''; $archived = ''; }
		if (isset($l['tweet'])) {
			$text = '<div class="div-table">'
				.'<div class="div-cell" style="width:54px;min-width:54px">'
					.'<img src="'.$l['tweet']['user_img'].'" class="img-tweet" />'
				.'</div>'
				.'<div class="div-cell">'
					.'<p>'.$l['content'].'</p>'
					.$tags
				.'</div>'
			.'</div>';
		}
		else {
			if (empty($l['comment'])) {
				$text = Text::intro($l['content'], 400, false).$tags;
			}
			else {
				$text = Text::intro($l['comment'], 400, false).$tags;
			}
		}
		$new_tab = ($config['open_new_tab']) ? ' target="_blank"' : '';
		return ''
.'<div class="div-link" id="link-'.$id.'">'
	.'<h2'.(($l['type'] == 'unread') ? ' class="unread"' : '').'>'
		.'<a href="'.Url::parse('links/'.$id).'"'.$new_tab.'>'
			.$l['title']
		.'</a>'
	.'</h2>'
	.$text
	.'<div class="div-actions">'
		.'<a href="'.$l['link'].'"'.$new_tab.'>'
			.mb_strtolower(Trad::V_LINK)
		.'</a>'
		.'<a href="#" '.Text::click('read', array('id' => $id)).$read.'>'
			.mb_strtolower(Trad::V_MARK_READ)
		.'</a>'
		.'<a href="#" '.Text::click('unread', array('id' => $id)).$unread.'>'
			.mb_strtolower(Trad::V_MARK_UNREAD)
		.'</a>'
		.'<a href="#" '.Text::click('archive', array('id' => $id)).$archived.'>'
			.mb_strtolower(Trad::V_ARCHIVE)
		.'</a>'
		.'<a href="#" '.Text::click('delete', array('id' => $id)).'>'
			.mb_strtolower(Trad::V_DELETE)
		.'</a>'
	.'</div>'
.'</div>';
	}

	public static function tagsList($tags, $empty = true) {
		$html = '';
		foreach ($tags as $t) {
			$html .= '<a href="'.Url::parse('tags/'.$t).'" class="tag">'.$t.'</a>';
		}
		if ($empty && empty($html)) { $html = '<i>'.Trad::W_EMPTY.'</i>'; }
		return $html;
	}

	public static function tagsPick($tags) {
		$list = '';
		sort($tags);
		foreach ($tags as $t) {
			$list .= '<span class="visible">'.$t.'</span>';
		}
		return '<div class="pick-tag">'.$list.'</div>';
	}

	public static function sort($a) {
		uasort($a, function($a, $b) {
			if ($a['date'] > $b['date']) { return -1; }
			else if ($a['date'] == $b['date']) { return 0; }
			return 1;
		});
		return $a;
	}

	public static function newKey($a) {
		if (empty($a)) { return 0; }
		return max(array_keys($a))+1;
	}

}

?>
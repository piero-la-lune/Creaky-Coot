<?php

class Manager {

	private static $instance;
	protected $feeds = array();
	protected $links = array();
	protected $done = array();

	protected $curl_opts = array(
		CURLOPT_AUTOREFERER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 4,
		CURLOPT_CONNECTTIMEOUT => 8,
		CURLOPT_TIMEOUT => 8,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) Gecko/20100101 Firefox/19.0'
	);

	public function __construct() {
		global $config;
		$this->feeds = Text::unhash(get_file(FILE_FEEDS));
		$this->links = Text::unhash(get_file(FILE_LINKS));
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
			'deleted' => array()
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
		$tags = array();
		foreach ($this->links as $l) {
			foreach ($l['tags'] as $t) {
				if (!in_array($t, $tags)) { $tags[] = $t; }
			}
		}
		return $tags;
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
			$params = array();
			foreach (explode(',', $post['params']) as $p) {
				$p = explode('=', $p);
				if (isset($p[0]) && isset($p[1])
					&& !empty($p[0]) && !empty($p[1])
				) {
					$params[$p[0]] = $p[1];
				}
			}
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
		$added = array();
		$ids = array($feed);
		if ($feed === NULL || !isset($this->feeds[$feed])) {
			$ids = array_keys($this->feeds);
		}
		$this->update($ids);
		foreach ($this->done as $d) {
			if ($d !== false) {
				foreach ($d['added'] as $id) {
					$added[$id] = $this->links[$id];
				}
			}
		}
		$this->save();
		$this->done = array();
		return $added;
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
		$parser = new RssParser();
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
				$this->links[$id2] = array(
					'type' => 'unread',
					'title' => $i['title'],
					'content' => $i['content'],
					'date' => $i['date'],
					'link' => $i['link'],
					'comment' => NULL,
					'tags' => $tags
				);
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

	public function callback_add($header, $content, $id) {
		if (!isset($header['http_code'])
			|| $header['http_code'] !== 200
			|| !isset($header['content_type'])
			|| strpos($header['content_type'], 'text/html') === false
		) {
			$this->done[$id] = false;
			return false;
		}
		$filter = new Filter();
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
		if (!filter_var($post['url'], FILTER_VALIDATE_URL)) {
			return Trad::$settings['validate_url'];
		}

		$id = md5($post['url']);
		if (isset($this->links[$id])) {
			return Trad::A_ERROR_EXISTING_LINK;
		}

		$curlManager = new Curl_Multi();
		$request = curl_init($post['url']);
		curl_setopt_array($request, $this->curl_opts);
		$curlManager->addHandle($request, array($this, 'callback_add'), $id);
		$curlManager->finish();

		if (!isset($this->done[$id]) || !$this->done[$id]) {
			unset($this->done[$id]);
			return Trad::A_ERROR_BAD_LINK;
		}

		$tags = array();
		foreach (explode(',', $post['tags']) as $t) {
			$t = Text::purge($t);
			if (!empty($t)) { $tags[] = $t; }
		}

		$filter = new Filter();
		$this->links[$id] = array(
			'type' => 'archived',
			'title' => $this->done[$id]['title'],
			'content' => $this->done[$id]['content'],
			'date' => time(),
			'link' => $this->done[$id]['link'],
			'comment' => $filter->execute($post['comment'], $config['url']),
			'tags' => $tags
		);
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
		$this->links[$id]['tags'] = array();
		foreach (explode(',', $post['tags']) as $t) {
			$t = Text::purge($t);
			if (!empty($t)) { $this->links[$id]['tags'][] = $t; }
		}
		$this->links[$id]['content'] = $parser->execute(
			$post['content'],
			$this->links[$id]['link']
		);
		$this->links[$id]['title'] = Text::chars($post['title']);
		$this->save();
		return true;
	}

	public function markRead($id) {
		if (!isset($this->links[$id])
			|| $this->links[$id]['type'] != 'unread'
		) { return false; }
		foreach ($this->feeds as $k => $f) {
			if (($key = array_search($id, $f['unread'])) !== false) {
				unset($this->feeds[$k]['unread'][$key]);
				$this->feeds[$k]['read'][] = $id;
			}
		}
		$this->links[$id]['type'] = 'read';
		$this->save();
		return true;
	}

	public function markUnread($id) {
		if (!isset($this->links[$id])
			|| $this->links[$id]['type'] != 'read'
		) { return false; }
		foreach ($this->feeds as $k => $f) {
			if (($key = array_search($id, $f['read'])) !== false) {
				unset($this->feeds[$k]['read'][$key]);
				$this->feeds[$k]['unread'][] = $id;
			}
		}
		$this->links[$id]['type'] = 'unread';
		$this->save();
		return true;
	}

	public function archive($id) {
		if (!isset($this->links[$id])) { return false; }
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
		$this->save();
		return true;
	}

	public function delete($id) {
		if (!isset($this->links[$id])) { return false; }
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
		$this->save();
		return true;
	}

	public function import($file) {
		if (!isset($file['error'])
			|| $file['error'] > 0
			|| !isset($file['tmp_name'])
			|| !isset($file['name'])
		) {
			return Trad::A_ERROR_UPLOAD;
		}
		$parser = new RssParser();
		$feeds = $parser->importOPML(file_get_contents($file['tmp_name']));
		if ($feeds === false) {
			return Trad::A_ERROR_IMPORT;
		}
		$ids = array();
		foreach ($feeds as $k => $f) {
			if (($id = $this->createNewFeed($f['url'])) !== false) {
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
				$this->feeds[$ids[$k]]['title'] = Text::chars($f['title']);
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
			if ($f['type'] != 'rss') { continue; }
			$urls[] = array(
				'title' => $f['title'],
				'url' => $f['url'],
				'link' => $f['link']
			);
		}
		$parser = new RssParser();
		$xml = $parser->exportOPML($urls);
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
		$unread = ' style="display:none"';
		$read = ' style="display:none"';
		$archived = ' style="display:none"';
		$tags = Manager::tagsList($l['tags'], false);
		if (!empty($tags)) { $tags = '<p>'.$tags.'</p>'; }
		if ($l['type'] == 'unread') { $read = ''; $archived = ''; }
		if ($l['type'] == 'read') { $unread = ''; $archived = ''; }
		if (isset($l['tweet'])) {
			$text = '<div class="div-table">'
				.'<div class="div-cell" style="width:54px">'
					.'<img src="'.$l['tweet']['user_img'].'" class="img-tweet" />'
				.'</div>'
				.'<div class="div-cell">'
					.'<p>'.$l['content'].'</p>'
					.$tags
				.'</div>'
			.'</div>';
		}
		else {
			$text = Text::intro($l['content'], 400, false).$tags;
		}
		return ''
.'<div class="div-link" id="link-'.$id.'">'
	.'<h2'.(($l['type'] == 'unread') ? ' class="unread"' : '').'>'
		.'<a href="'.Url::parse('links/'.$id).'">'.$l['title'].'</a>'
	.'</h2>'
	.$text
	.'<div class="div-actions">'
		.'<a href="'.$l['link'].'">'
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
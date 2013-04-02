<?php

class Manager {

	private static $instance;
	protected $feeds = array();
	protected $links = array();

	public function __construct() {
		global $config;
		$this->feeds = Text::unhash(get_file(FILE_FEEDS));
		$this->links = Text::unhash(get_file(FILE_LINKS)); //echo count($this->links);
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
		$links = array();
		if (isset($filter['feed']) && isset($this->feeds[$filter['feed']])) {
			$feed = $this->feeds[$filter['feed']];
			foreach ($feed['unread'] as $v) { $links[$v] = $this->links[$v]; }
			foreach ($feed['read'] as $v) { $links[$v] = $this->links[$v]; }
			foreach ($feed['archived'] as $v) { $links[$v] = $this->links[$v]; }
		}
		else {
			$links = $this->links;
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

	public function addFeed($post) {
		if (!isset($post['url'])
			|| !filter_var($post['url'], FILTER_VALIDATE_URL)
		) {
			return Trad::$settings['validate_url'];
		}
		foreach ($this->feeds as $f) {
			if ($f['url'] == $post['url']) {
				return Trad::A_ERROR_EXISTING_FEED;
			}
		}
		$parser = new RssParser();
		$ans = $parser->readFeed($post['url']);
		if ($ans === false) {
			return Trad::A_ERROR_BAD_FEED;
		}
		$unread = array();
		foreach ($ans['items'] as $i) {
			$id = md5($i['link']);
			$unread[] = $id;
			$this->links[$id] = array(
				'type' => 'unread',
				'title' => $i['title'],
				'content' => $i['content'],
				'date' => $i['date'],
				'link' => $i['link']
			);
		}
		$this->feeds[] = array(
			'title' => $ans['title'],
			'url' => $ans['url'],
			'link' => $ans['link'],
			'unread' => $unread,
			'read' => array(),
			'archived' => array(),
			'deleted' => array()
		);
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
		$urls = $parser->importOPML(file_get_contents($file['tmp_name']));
		if ($urls === false) {
			return Trad::A_ERROR_IMPORT;
		}
		foreach ($urls as $u) {
			$this->addFeed($u);
			foreach ($this->feeds as $k => $f) {
				if ($f['url'] == $u['url']) {
					if (!empty($u['title'])) {
						$this->feeds[$k]['title'] = Text::chars($u['title']);
					}
					if (!empty($u['link'])
						&& filter_var($u['link'], FILTER_VALIDATE_URL)
					) {
						$this->feeds[$k]['link'] = $u['link'];
					}
				}
			}
		}
		$this->save();
		return true;
	}

	public function export() {
		$urls = array();
		foreach ($this->feeds as $f) {
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

	public function refresh($feed = NULL) {
		$ans = array();
		$before = array_keys($this->links);
		if ($feed === NULL || !isset($this->feeds[$feed])) {
			foreach ($this->feeds as $i => $f) {
				$this->refreshFeed($i);
				foreach ($this->feeds[$i]['unread'] as $v) {
					if (!in_array($v, $before)) {
						$ans[$v] = $this->links[$v];
					}
				}
			}
		}
		else {
			$this->refreshFeed($feed);
			foreach ($this->feeds[$feed]['unread'] as $v) {
				if (!in_array($v, $before)) {
					$ans[$v] = $this->links[$v];
				}
			}
		}
		$this->save();
		return $ans;
	}

	public function editFeed($post, $id) {
		if (!isset($this->feeds[$id])
			|| !isset($post['title'])
			|| !isset($post['url'])
			|| !isset($post['link'])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (!filter_var($post['url'], FILTER_VALIDATE_URL)
			|| !filter_var($post['link'], FILTER_VALIDATE_URL)
		) {
			return Trad::$settings['validate_url'];
		}
		$oldfeed = $this->feeds[$id];
		$this->feeds[$id]['title'] =
			htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
		$this->feeds[$id]['url'] = $post['url'];
		$this->feeds[$id]['link'] = $post['link'];
		$ans = $this->refreshFeed($id);
		if ($ans !== true) {
			$this->feeds[$id] = $oldfeed;
			$this->save();
			return $ans;
		}
		$this->save();
		return true;
	}

	public function clear_feed($id) {
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

	public function delete_feed($id) {
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

	public function refreshFeed($id) {
		if (!isset($this->feeds[$id])) { return Trad::A_ERROR_UNKNOWN_FEED; }

		$parser = new RssParser();
		$ans = $parser->readFeed($this->feeds[$id]['url']);
		if ($ans === false) {
			return Trad::A_ERROR_BAD_FEED;
		}

		foreach ($ans['items'] as $i) {
			$id2 = md5($i['link']);
			if (!isset($this->links[$id2])
				&& !in_array($id2, $this->feeds[$id]['deleted'])
			) {
				$this->feeds[$id]['unread'][] = $id2;
				$this->links[$id2] = array(
					'type' => 'unread',
					'title' => $i['title'],
					'content' => $i['content'],
					'date' => $i['date'],
					'link' => $i['link']
				);
			}
		}
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
				//$this->feeds[$k]['deleted'][] = $id;
			}
			if (($key = array_search($id, $f['read'])) !== false) {
				unset($this->feeds[$k]['read'][$key]);
				//$this->feeds[$k]['deleted'][] = $id;
			}
			if (($key = array_search($id, $f['archived'])) !== false) {
				unset($this->feeds[$k]['archived'][$key]);
				//$this->feeds[$k]['deleted'][] = $id;
			}
		}
		unset($this->links[$id]);
		$this->save();
		return true;
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
		if ($l['type'] == 'unread') { $read = ''; $archived = ''; }
		if ($l['type'] == 'read') { $unread = ''; $archived = ''; }
		return ''
.'<div class="div-link" id="link-'.$id.'">'
	.'<h2'.(($l['type'] == 'unread') ? ' class="unread"' : '').'>'
		.'<a href="'.Url::parse('links/'.$id).'">'.$l['title'].'</a>'
	.'</h2>'
	.Text::intro($l['content'], 400, false)
	.'<div class="div-actions">'
		.'<a href="'.$l['link'].'">'
			.mb_strtolower(Trad::V_LINK)
		.'</a>'
		.'<a href="#" '.Text::click('mark_read_link', $id, $page).$read.'>'
			.mb_strtolower(Trad::V_MARK_READ)
		.'</a>'
		.'<a href="#" '.Text::click('mark_unread_link', $id, $page).$unread.'>'
			.mb_strtolower(Trad::V_MARK_UNREAD)
		.'</a>'
		.'<a href="#" '.Text::click('archive_link', $id, $page).$archived.'>'
			.mb_strtolower(Trad::V_ARCHIVE)
		.'</a>'
		.'<a href="#" '.Text::click('delete_link', $id, $page).'>'
			.mb_strtolower(Trad::V_DELETE)
		.'</a>'
	.'</div>'
.'</div>';
	}

	public static function sort($a) {
		uasort($a, function($a, $b) {
			if ($a['date'] > $b['date']) { return -1; }
			else if ($a['date'] == $b['date']) { return 0; }
			return 1;
		});
		return $a;
	}

}

?>
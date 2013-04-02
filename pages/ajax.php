<?php

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'mark_read' && isset($_POST['id'])) {
		$manager = Manager::getInstance();
		$ans = $manager->markRead($_POST['id']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'mark_unread' && isset($_POST['id'])) {
		$manager = Manager::getInstance();
		$ans = $manager->markUnread($_POST['id']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'archive' && isset($_POST['id'])) {
		$manager = Manager::getInstance();
		$ans = $manager->archive($_POST['id']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
		$manager = Manager::getInstance();
		$ans = $manager->delete($_POST['id']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'mark_read_all' && isset($_POST['ids'])) {
		$manager = Manager::getInstance();
		$ids_done = array();
		foreach (explode(',', $_POST['ids']) as $v) {
			$t = explode('-', $v);
			$id = (isset($t[1])) ? $t[1] : NULL;
			$ans = $manager->markRead($id);
			if ($ans === true) {
				$ids_done[] = $id;
			}
		}
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'clear' && isset($_POST['ids'])) {
		$manager = Manager::getInstance();
		$ids_done = array();
		foreach (explode(',', $_POST['ids']) as $v) {
			$t = explode('-', $v);
			$id = (isset($t[1])) ? $t[1] : NULL;
			if ($link = $manager->getLink($id)) {
				if ($link['type'] != 'archived') {
					$ans = $manager->delete($id);
					if ($ans === true) {
						$ids_done[] = $id;
					}
				}
			}
		}
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'clear_feed' && isset($_POST['feed'])) {
		$manager = Manager::getInstance();
		$ans = $manager->clear_feed($_POST['feed']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'delete_feed' && isset($_POST['feed'])) {
		$manager = Manager::getInstance();
		$ans = $manager->delete_feed($_POST['feed']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'refresh'
		&& isset($_POST['feed'])
		&& isset($_POST['page'])
	) {
		$manager = Manager::getInstance();
		$feed = ($_POST['feed'] == 'false') ? NULL : intval($_POST['feed']);
		$ans = $manager->refresh($feed);
		if (!empty($ans)) {
			$ans = Manager::sort($ans);
			$html = Manager::previewList($ans, $_POST['page']);
			die(json_encode(array('status' => 'success', 'html' => $html)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'load'
		&& isset($_POST['page'])
		&& isset($_POST['id'])
		&& isset($_POST['type'])
		&& isset($_POST['feed'])
	) {
		$manager = Manager::getInstance();
		$t = explode('-', $_POST['id']);
		$id = (isset($t[1])) ? $t[1] : NULL;
		$filter = array();
		if ($_POST['type'] != 'false') {
			$filter['type'] = $_POST['type'];
		}
		if ($_POST['feed'] != 'false') {
			$filter['feed'] = $_POST['feed'];
		}
		$ans = $manager->getLinks($filter, $id);
		if (!empty($ans)) {
			$html = Manager::previewList($ans, $_POST['page']);
			die(json_encode(array('status' => 'success', 'html' => $html)));
		}
		die(json_encode(array('status' => 'error')));
	}
}

die(json_encode(array('status' => 'error')));

?>
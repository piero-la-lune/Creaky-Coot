<?php

if (isset($_POST['action']) && isset($_POST['page'])) {

	$manager = Manager::getInstance();

	if ($_POST['action'] == 'load') {
		$id = (isset($_POST['id'])) ? $_POST['id'] : NULL;
		$filter = array();
		if (isset($_POST['type'])) {
			$filter['type'] = $_POST['type'];
		}
		if (isset($_POST['feed'])) {
			$filter['feed'] = $_POST['feed'];
		}
		if (isset($_POST['tag'])) {
			$filter['tag'] = $_POST['tag'];
		}
		if (isset($_POST['q'])) {
			$filter['q'] = Text::keywords(urldecode($_POST['q']));
		}
		$ans = $manager->getLinks($filter, $id);
		if (!empty($ans)) {
			$html = Manager::previewList($ans, $_POST['page']);
			die(json_encode(array('status' => 'success', 'html' => $html)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'refresh') {
		$manager = Manager::getInstance();
		$feed = (isset($_POST['feed'])) ? intval($_POST['feed']) : NULL;
		list(, $ans) = $manager->refreshFeed($feed);
		if (!empty($ans)) {
			$ans = Manager::sort($ans);
			$html = Manager::previewList($ans, $_POST['page']);
			die(json_encode(array('status' => 'success', 'html' => $html)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'read' && isset($_POST['ids'])) {
		$ids_done = $manager->markRead(explode(',', $_POST['ids']));
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'clear' && isset($_POST['ids'])) {
		$ids_done = $manager->clear(explode(',', $_POST['ids']));
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'unread' && isset($_POST['ids'])) {
		$ids_done = $manager->markUnread(explode(',', $_POST['ids']));
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'archive' && isset($_POST['ids'])) {
		$ids_done = $manager->archive(explode(',', $_POST['ids']));
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'delete' && isset($_POST['ids'])) {
		$ids_done = $manager->delete(explode(',', $_POST['ids']));
		if (!empty($ids_done)) {
			die(json_encode(array('status' => 'success', 'ids' => $ids_done)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'edit') {
		$manager = Manager::getInstance();
		$ans = $manager->edit($_POST);
		if ($ans === true) {
			$link = $manager->getLink($_POST['id']);
			die(json_encode(array(
				'status' => 'success',
				'comment' => $link['comment'],
				'tags' => $link['tags'],
				'tags_list' => Manager::tagsList($link['tags']),
				'title' => $link['title'],
				'content' => $link['content']
			)));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'clear_feed' && isset($_POST['feed'])) {
		$manager = Manager::getInstance();
		$ans = $manager->clearFeed($_POST['feed']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
	if ($_POST['action'] == 'delete_feed' && isset($_POST['feed'])) {
		$manager = Manager::getInstance();
		$ans = $manager->deleteFeed($_POST['feed']);
		if ($ans === true) {
			die(json_encode(array('status' => 'success')));
		}
		die(json_encode(array('status' => 'error')));
	}
}

die(json_encode(array('status' => 'error')));

?>
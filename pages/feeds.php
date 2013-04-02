<?php

	$manager = Manager::getInstance();

if (isset($_GET['id']) && $feed = $manager->getFeed($_GET['id'])) {

	if (isset($_GET['action']) && $_GET['action'] == 'edit') {

		if (isset($_POST['action']) && $_GET['action'] == 'edit') {
			$ans = $manager->editFeed($_POST, $_GET['id']);
			if ($ans === true) {
				$_SESSION['alert'] = array(
					'text' => Trad::A_SUCCESS_EDIT_FEED,
					'type' => 'alert-success'
				);
				header('Location: '.Url::parse('feeds'));
				exit;
			}
			else {
				$this->addAlert($ans);
			}
		}

		$title = $feed['title'];
		$content = '

<h1 class="center">'.$feed['title'].'</h1>

<form action="'
	.Url::parse('feeds/'.$_GET['id'], array('action' => 'edit'))
	.'" method="post">
	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'.$feed['title'].'" />
	<label for="url">'.Trad::F_FEED_URL.'</label>
	<input type="text" name="url" id="url" value="'.$feed['url'].'" />
	<label for="link">'.Trad::F_LINK.'</label>
	<input type="text" name="link" id="link" value="'.$feed['link'].'" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_EDIT.'" /></p>
	<input type="hidden" name="action" value="edit" />
</form>

		';

	}
	else {

		$title = $feed['title'];

		$content = '<h1 class="center">'.$feed['title'].'</h1>

			<div class="div-actions-top">
				<a href="#" '
					.Text::click('refresh', 'this', 'links', $_GET['id'])
				.'>'
					.mb_strtolower(Trad::V_REFRESH)
				.'</a>
				<a href="#" '.Text::click('mark_read_all', 'this', 'links').'>'
					.mb_strtolower(Trad::V_MARK_READ_ALL)
				.'</a>
				<a href="#" '.Text::click('clear_all', 'this').'>'
					.mb_strtolower(Trad::V_CLEAR)
				.'</a>
			</div>

		';

		$links = $manager->getLinks(array('feed' => $_GET['id']));
		$html = Manager::previewList($links, 'links');

		if (empty($html)) {
			$content .= '<p class="p-more"><span>'.Trad::S_NO_LINK.'</span></p>';
		}
		else {
			$content .= $html
			.'<p class="p-more">'
				.'<a href="#" '
					.Text::click('load', 'this', 'links', 'false', $_GET['id'])
				.'>'
					.Trad::S_LOAD_MORE
				.'</a>'
			.'</p>';
		}

	}

}
else {

	if (isset($_POST['action']) && $_POST['action'] == 'add_feed') {
		$ans = $manager->addFeed($_POST);
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_ADD_FEED, 'alert-success');
		}
	}
	elseif (isset($_POST['action'])
		&& $_POST['action'] == 'import'
		&& isset($_FILES['file'])
	) {
		$ans = $manager->import($_FILES['file']);
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_IMPORT, 'alert-success');
		}
	}
	elseif (isset($_POST['action']) && $_POST['action'] == 'export') {
		$manager->export();
		exit;
	}

	$title = Trad::T_FEEDS;

	$content = '';

	$feeds = $manager->getFeeds();
	foreach ($feeds as $k => $f) {
		$content .= ''
			.'<div class="div-feed" id="feed-'.$k.'">'
				.'<h2>'
					.'<a href="'.Url::parse('feeds/'.$k).'">'
						.$f['title']
					.'</a>'
				.'</h2>'
				.'<a href="'.$f['link'].'">'.$f['link'].'</a>'
				.'<div class="div-actions">'
					.'<a href="'.Url::parse('feeds/'.$k.'/edit').'">'
						.mb_strtolower(Trad::V_EDIT)
					.'</a>'
					.'<a href="#" '.Text::click('clear_feed', 'this', $k).'>'
						.mb_strtolower(Trad::V_CLEAR)
					.'</a>'
					.'<a href="#" '.Text::click('delete_feed', 'this', $k).'>'
						.mb_strtolower(Trad::V_DELETE)
					.'</a>'
				.'</div>'
			.'</div>'
		;
	}

	$content .= '

<div class="div-hr"></div>

<form action="'.Url::parse('feeds').'" method="post">

	<h2>'.Trad::T_ADD_FEED.'</h2>
	<label for="url">'.Trad::F_FEED_URL.'</a>
	<input type="url" name="url" id="url" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>
	<input type="hidden" name="action" value="add_feed" />

</form>

<form action="'.Url::parse('feeds').'" method="post" enctype="multipart/form-data">

	<h2>'.Trad::T_IMPORT_OPML.'</h2>
	<label for="file">'.Trad::F_OPML_FILE.'</label>
	<input type="file" name="file" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_IMPORT.'" /></p>
	<input type="hidden" name="action" value="import" />

</form>

<form action="'.Url::parse('feeds').'" method="post">

	<h2>'.Trad::T_EXPORT_OPML.'</h2>

	<p class="p-submit"><input type="submit" value="'.Trad::V_EXPORT.'" /></p>
	<input type="hidden" name="action" value="export" />

</form>

	';

}

?>
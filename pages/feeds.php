<?php

	$manager = Manager::getInstance();

if (isset($_GET['id']) && $feed = $manager->getFeed($_GET['id'])) {

	if (isset($_POST['action']) && $_POST['action'] == 'edit') {
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

<h1>'.$feed['title'].'</h1>

<form action="'
	.Url::parse('feeds/'.$_GET['id'].'/edit', array('action' => 'edit'))
	.'" method="post">
	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'.$feed['title'].'" />

	';

	if ($feed['type'] == 'rss') {
		$content .= '

	<label for="url">'.Trad::F_FEED_URL.'</label>
	<input type="text" name="url" id="url" value="'.$feed['url'].'" />

		';
	}
	else {
		$params = array();
		foreach ($feed['params'] as $k => $v) {
			$params[] = Text::chars($k).'='.Text::chars($v);
		}
		$content .= '

	<label for="twitter_url">'.Trad::F_TWITTER_URL.'</label>
	<input type="text" name="twitter_url" id="twitter_url" value="'.Text::chars($feed['url']).'" />
	<label for="params">'.Trad::F_PARAMS.'</label>
	<input type="text" name="params" id="params" value="'.implode(',', $params).'" />

		';
	}

	$content .= '

	<label for="link">'.Trad::F_LINK.'</label>
	<input type="text" name="link" id="link" value="'.$feed['link'].'" />

	<p>&nbsp;</p>

	<label for="content">'.Trad::F_P_CONTENT.'</label>
	<select name="content" id="content">'.Text::options(
		array(
			Manager::T_EMPTY => Trad::F_P_EMPTY,
			Manager::T_RSS => Trad::F_P_RSS,
			Manager::T_DLOAD => Trad::F_P_DLOAD
		),
		$feed['content']
	).'</select>

	<label for="comment">'.Trad::F_P_COMMENT.'</label>
	<select name="comment" id="comment">'.Text::options(
		array(
			Manager::T_EMPTY => Trad::F_P_EMPTY,
			Manager::T_RSS => Trad::F_P_RSS
		),
		$feed['comment']
	).'</select>

	<label for="filter_html">'.Trad::F_FILTER_HTML.'</label>
	<input type="text" name="filter_html" id="filter_html" value="'.Text::chars($feed['filter_html']).'" />
	<p class="p-tip">'.Trad::F_TIP_FILTER_HTML.'</p>

	<p class="p-submit"><input type="submit" value="'.Trad::V_SAVE.'" /></p>
	<input type="hidden" name="action" value="edit" />
</form>

	';

}
elseif (isset($_GET['id'])) {

	$load = 'error/404';

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
	elseif (isset($_POST['action']) && $_POST['action'] == 'import'
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
	elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'twitter_config') {
		$twitter = new Twitter();
		$ans = $twitter->configure();
		if ($ans !== true) {
			$this->addAlert($ans);
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_TWITTER, 'alert-success');
 		}
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
					.'<a href="#" '.Text::click('clear_feed', array('id' => $k)).'>'
						.mb_strtolower(Trad::V_CLEAR)
					.'</a>'
					.'<a href="#" '.Text::click('delete_feed', array('id' => $k)).'>'
						.mb_strtolower(Trad::V_DELETE)
					.'</a>'
				.'</div>'
			.'</div>'
		;
	}

	$content .= '

<div class="div-hr"></div>

<form action="'.Url::parse('feeds').'" method="post">

	<h1>'.Trad::T_ADD_FEED.'</h1>
	<label for="url">'.Trad::F_FEED_URL.'</a>
	<input type="url" name="url" id="url" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>
	<input type="hidden" name="action" value="add_feed" />

</form>

<form action="'.Url::parse('feeds').'" method="post" enctype="multipart/form-data">

	<h1>'.Trad::T_IMPORT_OPML.'</h1>
	<label for="file">'.Trad::F_OPML_FILE.'</label>
	<input type="file" name="file" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_IMPORT.'" /></p>
	<input type="hidden" name="action" value="import" />

</form>

<form action="'.Url::parse('feeds').'" method="post">

	<h1>'.Trad::T_EXPORT_OPML.'</h1>

	<p class="p-submit"><input type="submit" value="'.Trad::V_EXPORT.'" /></p>
	<input type="hidden" name="action" value="export" />

</form>

	';

	if ($config['twitter'] == NULL) {
		$content .= '

<form action="'.Url::parse('feeds').'" method="post">

	<h1>'.Trad::T_TWITTER.'</h1>

	<p class="p-submit"><input type="submit" value="'.Trad::V_CONFIGURE.'" /></p>
	<input type="hidden" name="action" value="twitter_config" />

</form>

		';
	}

	else {
		$content .= '

<form action="'.Url::parse('feeds').'" method="post">

	<h1>'.Trad::T_TWITTER.'</h1>

	<label for="twitter_url">'.Trad::F_TWITTER_URL.'</label>
	<input type="text" name="twitter_url" id="twitter_url" value="statuses/home_timeline" />
	<label for="params">'.Trad::F_PARAMS.'</label>
	<input type="text" name="params" id="params" value="" />
	<p class="p-tip">'.Trad::F_TIP_PARAMS.'</p>
	<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>
	<input type="hidden" name="action" value="add_feed" />

	<p>&nbsp;</p>'.Trad::F_TIP_TWITTER.'

</form>

		';
	}

}

?>
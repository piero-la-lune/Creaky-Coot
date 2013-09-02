<?php

	$manager = Manager::getInstance();

	if (isset($_POST['action']) && $_POST['action'] == 'add') {
		$ans = $manager->add($_POST);
		if ($ans === true) {
			$_SESSION['alert'] = array(
				'text' => Trad::A_SUCCESS_ADD,
				'type' => 'alert-success'
			);
			header('Location: '.Url::parse('links/'.$manager->lastInsert()));
			exit;
		}
		else {
			$this->addAlert($ans);
		}
	}

	if (isset($_GET['url']) && isset($_GET['title'])) {
		$title = Trad::T_ADD;
		$print_header = false;
		$content = '';

		$url = (isset($_GET['url'])) ? Text::chars($_GET['url']) : '';
		$title2 = (isset($_GET['title'])) ? Text::chars($_GET['title']) : '';
	}
	else {
		$title = Trad::T_NEW;
		$content = '<h1>'.Trad::T_NEW.'</h1>';

		$url = '';
		$title2 = '';
	}

	$url = (isset($_POST['url'])) ? Text::chars($_POST['url']) : $url;
	$title2 = (isset($_POST['title'])) ? Text::chars($_POST['title']) : $title2;
	$comment = (isset($_POST['comment'])) ? Text::chars($_POST['comment']) : '';
	$tags = (isset($_POST['tags'])) ? Text::chars($_POST['tags']) : '';

	$content .= '

<form action="'.Url::parse('add').'" method="post">

	<label for="url">'.Trad::F_URL.'</label>
	<input type="url" name="url" id="url" value="'.$url.'" />
	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'.$title2.'" />

	<label for="comment">'.Trad::F_COMMENT.'</label>
	<textarea name="comment" id="comment">'.$comment.'</textarea>
	<label for="addTag">'.Trad::F_TAGS.'</label>
	<div class="editTags">
		<span></span>
		<input type="text" name="addTag" id="addTag" placeholder="'.Trad::F_ADD.'" />
		<input type="hidden" name="tags" id="tags" value="'.$tags.'" />
	</span>

	<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>
	<input type="hidden" name="action" value="add" />

</form>

	';

	$content .= Manager::tagsPick($manager->getTags());

?>
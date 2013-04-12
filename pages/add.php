<?php

	if (isset($_POST['action']) && $_POST['action'] == 'add') {
		$manager = Manager::getInstance();
		$ans = $manager->add($_POST);
		if ($ans === true) {
			$this->addAlert(Trad::A_SUCCESS_ADD, 'alert-success');
		}
		else {
			$this->addAlert($ans);
		}
	}

	$title = Trad::T_ADD;

	$url = (isset($_GET['url'])) ? Text::chars($_GET['url']) : '';
	$title = (isset($_GET['title'])) ? Text::chars($_GET['title']) : '';

	$url = (isset($_POST['url'])) ? Text::chars($_POST['url']) : $url;
	$title = (isset($_POST['title'])) ? Text::chars($_POST['title']) : $title;
	$comment = (isset($_POST['comment'])) ? Text::chars($_POST['comment']) : '';
	$tags = (isset($_POST['tags'])) ? Text::chars($_POST['tags']) : '';

	$content = '

<form action="'.Url::parse('add').'" method="post">

	<label for="url">'.Trad::F_URL.'</label>
	<input type="url" name="url" id="url" value="'.$url.'" />
	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'.$title.'" />

	<label for="comment">'.Trad::F_COMMENT.'</label>
	<textarea name="comment" id="comment">'.$comment.'</textarea>
	<label for="tags">'.Trad::F_TAGS.'</label>
	<input type="text" name="tags" id="tags" value="'.$tags.'" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>
	<input type="hidden" name="action" value="add" />

</form>

	';

?>
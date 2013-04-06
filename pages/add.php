<?php

	$title = Trad::T_ADD;

	$url = (isset($_GET['url'])) ? Text::chars($_GET['url']) : '';
	$title = (isset($_GET['title'])) ? Text::chars($_GET['title']) : '';

	$content = '

<form action="'.Url::parse('add').'" method="post">

	<label for="url">'.Trad::F_URL.'</label>
	<input type="url" name="url" id="url" value="'.$url.'" />
	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'.$title.'" />

	<label for="comment">'.Trad::F_COMMENT.'</label>
	<textarea name="url" id="url"></textarea>
	<label for="tags">'.Trad::F_TAGS.'</label>
	<input type="text" name="tags" id="tags" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_ADD.'" /></p>
	<input type="hidden" name="action" value="add" />

</form>

	';

?>
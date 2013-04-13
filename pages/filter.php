<?php

if (isset($_POST['q'])) {
	header('Location: '.Url::parse('search/'.urlencode($_POST['q'])));
	exit;
}

$manager = Manager::getInstance();

$tags = $manager->getTags();
sort($tags);

$title = Trad::T_FILTER;

$content = '

	<h1>'.Trad::T_TAGS.'</h1>
	<p class="p-list-tags">'.Manager::tagsList($tags).'</p>

	<h1>'.Trad::T_SEARCH.'</h1>
	<form action="'.Url::parse('filter').'" method="post">
		<label for="q">'.Trad::F_KEY_WORDS.'</label>
		<input type="text" name="q" value="" />

		<p class="p-submit"><input type="submit" value="'.Trad::V_SEARCH.'" /></p>
		<input type="hidden" name="action" value="search" />
	</form>

';

?>
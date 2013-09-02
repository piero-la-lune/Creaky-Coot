<?php

	$manager = Manager::getInstance();

if (!isset($_GET['feed']) || $feed = $manager->getFeed($_GET['feed'])) {

	$title = Trad::T_ALL;
	$content = '';

	$filter = array();

	if (isset($_GET['tag'])) {
		$content .= '<p>'
			.str_replace(
				'%tag%',
				'<span class="tag">'.Text::chars($_GET['tag']).'</span>',
				Trad::S_FILTER_TAG
			)
		.'</p>';
		$filter['tag'] = $_GET['tag'];
	}
	if (isset($_GET['feed'])) {
		$content .= '<p>'
			.str_replace(
				'%feed%',
				'<span class="feed-title">'.$feed['title'].'</span>',
				Trad::S_FILTER_FEED
			)
		.'</p>';
		$filter['feed'] = intval($_GET['feed']);
	}
	if (isset($_GET['q'])) {
		$words = Text::keywords(urldecode($_GET['q']));
		$content .= '<p>'
			.str_replace(
				'%q%',
				'<span class="feed-title">'.implode(' ', $words).'</span>',
				Trad::S_FILTER_SEARCH
			)
		.'</p>';
		$filter['q'] = $words;
	}
	if (isset($_GET['type'])) {
		$type = false;
		if ($_GET['type'] == 'unread') { $type = Trad::W_UNREAD; }
		elseif ($_GET['type'] == 'read') { $type = Trad::W_READ; }
		elseif ($_GET['type'] == 'archived') { $type = Trad::W_ARCHIVED; }
		if ($type !== false) {
			$content .= '<p>'
				.str_replace(
					'%type%',
					'<span class="feed-title">'.$type.'</span>',
					Trad::S_FILTER_TYPE
				)
			.'</p>';
			$filter['type'] = $_GET['type'];
		}
	}

	$content .= '

<div class="div-actions-top">
	<a href="#" '.Text::click('refresh').'>'
		.mb_strtolower(Trad::V_REFRESH)
	.'</a>
	<a href="#" '.Text::click('allRead').'>'
		.mb_strtolower(Trad::V_MARK_READ_ALL)
	.'</a>
	<a href="#" '.Text::click('allClear').'>'
		.mb_strtolower(Trad::V_CLEAR)
	.'</a>
</div>

	';

	$links = $manager->getLinks($filter);
	$html = Manager::previewList($links, 'links');

	if (empty($html)) {
		$content .= '<p class="p-more"><span>'.Trad::S_NO_LINK.'</span></p>';
	}
	else {
		$content .= $html
		.'<p class="p-more">'
			.'<a href="#" '.Text::click('load').'>'
				.Trad::S_LOAD_MORE
			.'</a>'
		.'</p>';
	}

	$content .= '

<div class="div-actions-bottom">
	<a href="#" '.Text::click('refresh').'>'
		.mb_strtolower(Trad::V_REFRESH)
	.'</a>
	<a href="#" '.Text::click('allRead').'>'
		.mb_strtolower(Trad::V_MARK_READ_ALL)
	.'</a>
	<a href="#" '.Text::click('allClear').'>'
		.mb_strtolower(Trad::V_CLEAR)
	.'</a>
</div>

	';

}
else {

	$load = 'error/404';

}

?>
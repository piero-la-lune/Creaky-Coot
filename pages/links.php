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

}
else {

	$load = 'error/404';

}

?>
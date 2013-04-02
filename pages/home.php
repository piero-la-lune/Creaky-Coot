<?php

	$title = Trad::T_UNREAD;

	$content = '

<div class="div-actions-top">
	<a href="#" '.Text::click('refresh', 'this', 'unread').'>'
		.mb_strtolower(Trad::V_REFRESH)
	.'</a>
	<a href="#" '.Text::click('mark_read_all', 'this', 'unread').'>'
		.mb_strtolower(Trad::V_MARK_READ_ALL)
	.'</a>
	<a href="#" '.Text::click('clear_all', 'this').'>'
		.mb_strtolower(Trad::V_CLEAR)
	.'</a>
</div>

	';

	$manager = Manager::getInstance();

	$links = $manager->getLinks(array('type' => 'unread'));
	$html = Manager::previewList($links, 'unread');

	if (empty($html)) {
		$content .= '<p class="p-more"><span>'.Trad::S_NO_LINK.'</span></p>';
	}
	else {
		$content .= $html
		.'<p class="p-more">'
			.'<a href="#" '.Text::click('load', 'this', 'unread', 'unread').'>'
				.Trad::S_LOAD_MORE
			.'</a>'
		.'</p>';
	}

?>
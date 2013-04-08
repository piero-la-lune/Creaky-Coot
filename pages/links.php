<?php

	$manager = Manager::getInstance();

if (isset($_GET['id']) && $link = $manager->getLink($_GET['id'])) {

	if ($link['type'] == 'unread') {
		$manager->markRead($_GET['id']);
		$link = $manager->getLink($_GET['id']);
	}

	$title = $link['title'];

	$id = $_GET['id'];
	$unread = ' style="display:none"';
	$read = ' style="display:none"';
	$archived = ' style="display:none"';
	if ($link['type'] == 'unread') { $read = ''; $archived = ''; }
	if ($link['type'] == 'read') { $unread = ''; $archived = ''; }

	$content = ''

		.'<article id="link-'.$id.'">'
			.'<h1 class="center">'.$link['title'].'</h1>'
			.'<div class="div-infos">'
				.'<textarea name="comment" id="comment" style="display:none">'
					.Text::chars($link['comment'])
				.'</textarea>'
				.'<div class="div-comment">'
					.$link['comment']
				.'</div>'
				.'<div class="div-hr-light"></div>'
				.'<p class="p p-url">'
					.str_replace(
						array('%time%', '%url%'),
						array(
							Text::ago($link['date']),
							'<a href="'.$link['link'].'">'.$link['link'].'</a>'
						),
						Trad::S_PUBLISHED
					)
				.'</p>'
				.'<p class="p">'
					.Trad::F_TAGS.' '
					.'<span class="tags">'.Manager::tagsList($link['tags']).'</span>'
					.'<input type="text" name="tags" id="tags" value="'
						.implode(', ', $link['tags'])
						.'" style="display:none" />'
				.'</p>'
				.'<div class="div-actions">'
					.'<a href="#" '.Text::click('mark_read_link', $id).$read.'>'
						.mb_strtolower(Trad::V_MARK_READ)
					.'</a>'
					.'<a href="#" '.Text::click('mark_unread_link', $id).$unread.'>'
						.mb_strtolower(Trad::V_MARK_UNREAD)
					.'</a>'
					.'<a href="#" '.Text::click('archive_link', $id).$archived.'>'
						.mb_strtolower(Trad::V_ARCHIVE)
					.'</a>'
					.'<a href="#" '.Text::click('edit_link', $id).'>'
						.mb_strtolower(Trad::V_EDIT)
					.'</a>'
					.'<a href="#" '.Text::click('delete_link', $id).'>'
						.mb_strtolower(Trad::V_DELETE)
					.'</a>'
				.'</div>'
				.'<div class="div-actions" style="display:none">'
					.'<a href="#">'
						.mb_strtolower(Trad::V_SAVE)
					.'</a>'
					.'<a href="#">'
						.mb_strtolower(Trad::V_CANCEL)
					.'</a>'
				.'</div>'
			.'</div>'
			.'<div class="div-content">'
				.$link['content']
			.'</div>'
		.'</article>'

	.'';

}

else {

	$title = Trad::T_ALL;
	$content = '

<div class="div-actions-top">
	<a href="#" '.Text::click('refresh', 'this', 'links').'>'
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

	$links = $manager->getLinks();
	$html = Manager::previewList($links, 'links');

	if (empty($html)) {
		$content .= '<p class="p-more"><span>'.Trad::S_NO_LINK.'</span></p>';
	}
	else {
		$content .= $html
		.'<p class="p-more">'
			.'<a href="#" '.Text::click('load', 'this', 'links').'>'
				.Trad::S_LOAD_MORE
			.'</a>'
		.'</p>';
	}

}

?>
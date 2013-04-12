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
					.'<a href="#" '.Text::click('read', array('id' => $id))
						.$read.'>'
						.mb_strtolower(Trad::V_MARK_READ)
					.'</a>'
					.'<a href="#" '.Text::click('unread', array('id' => $id))
						.$unread.'>'
						.mb_strtolower(Trad::V_MARK_UNREAD)
					.'</a>'
					.'<a href="#" '.Text::click('archive', array('id' => $id))
						.$archived.'>'
						.mb_strtolower(Trad::V_ARCHIVE)
					.'</a>'
					.'<a href="#" '.Text::click('edit', array('id' => $id)).'>'
						.mb_strtolower(Trad::V_EDIT)
					.'</a>'
					.'<a href="#" '.Text::click('delete', array('id' => $id)).'>'
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

	$load = 'error/404';

}

?>
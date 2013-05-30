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

	function fmte($cmd, $value = false) {
		$params = array('cmd' => $cmd);
		if ($value != false) { $params['value'] = $value; }
		return Text::click('formate', $params);
	}
	$lines = '–<br />—<br />–<br />—<br />–';
	$lines2 = '—<br />—<br />—<br />—<br />—';
	$lines3 = '•—<br />•—<br />•—';
	$lines4 = '<i>1</i> —<br /><i>2</i> —<br /><i>3</i> —';

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
			.'<div class="div-edit" style="display:none">'
				.'<button '
					.fmte('removeFormat')
					.' title="'.Trad::W_F_REMOVE.'">×'
				.'</button>'
				.'<button '
					.fmte('bold')
					.' title="'.Trad::W_F_BOLD.'">B'
				.'</button>'
				.'<button '
					.fmte('italic')
					.' title="'.Trad::W_F_ITALIC.'">I'
				.'</button>'
				.'<button '
					.fmte('underline')
					.' title="'.Trad::W_F_UNDERLINE.'">U'
				.'</button>'
				.'<button '
					.fmte('formatblock', 'p')
					.' title="'.Trad::W_F_P.'">p'
				.'</button>'
				.'<button '
					.fmte('formatblock', 'h2')
					.' title="'.Trad::W_F_H2.'">H2'
				.'</button>'
				.'<button '
					.fmte('formatblock', 'h3')
					.' title="'.Trad::W_F_H3.'">H3'
				.'</button>'
				.'<button '
					.fmte('formatblock', 'h4')
					.' title="'.Trad::W_F_H4.'">H4'
				.'</button>'
				.'<button '
					.fmte('formatblock', 'pre')
					.' title="'.Trad::W_F_PRE.'">&lt;'
				.'</button>'
				.'<button '
					.fmte('formatblock', 'blockquote')
					.' title="'.Trad::W_F_QUOTE.'">«'
				.'</button>'
				.'<button '
					.fmte('justifyleft')
					.' title="'.Trad::W_F_LEFT.'">'.$lines
				.'</button>'
				.'<button '
					.fmte('justifycenter')
					.' title="'.Trad::W_F_CENTER.'">'.$lines
				.'</button>'
				.'<button '
					.fmte('justifyright')
					.' title="'.Trad::W_F_RIGHT.'">'.$lines
				.'</button>'
				.'<button '
					.fmte('justifyfull')
					.' title="'.Trad::W_F_JUSTIFY.'">'.$lines2
				.'</button>'
				.'<button '
					.fmte('insertunorderedlist')
					.' title="'.Trad::W_F_LISTU.'">'.$lines3
				.'</button>'
				.'<button '
					.fmte('insertorderedlist')
					.' title="'.Trad::W_F_LISTO.'">'.$lines4
				.'</button>'
				.'<button '
					.fmte('link')
					.' title="'.Trad::W_F_LINK.'">#'
				.'</button>'
				.'<button '
					.fmte('image')
					.' title="'.Trad::W_F_IMAGE.'">¤'
				.'</button>'
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
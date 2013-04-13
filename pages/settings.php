<?php

	if (isset($_POST['action']) && $_POST['action'] == 'edit') {
		$settings = new Settings();
		$ans = $settings->changes($_POST);
		if (!empty($ans)) {
			foreach ($ans as $v) {
				$this->addAlert(Trad::$settings[$v]);
			}
		}
		else {
			$this->addAlert(Trad::A_SUCCESS_SETTINGS, 'alert-success');
		}
	}

	$title = Trad::T_SETTINGS;

	$url_add = new Url('add', array(
		'url' => 'URL',
		'title' => 'TITLE'
	));
	$url_add = str_replace(
		array('URL', 'TITLE'),
		array('\'+encodeURIComponent(url)+\'', '\'+encodeURIComponent(title)+\''),
		$url_add->get()
	);
	$js = 'javascript:javascript:(function(){'
		.'var%20url%20=%20location.href;'
		.'var%20title%20=%20document.title%20||%20url;'
		.'window.open('
			.'\''.$url_add.'\','
			.'\'_blank\','
			.'\'menubar=no,height=440,width=424,toolbar=no,'
				.'scrollbars=no,status=no,dialog=1\''
		.');'
	.'})();';

	$content = '

<form action="'.Url::parse('settings').'" method="post">

	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'
		.Text::chars($config['title']).'" />
	<label for="url">'.Trad::F_URL.'</label>
	<input type="url" name="url" id="url" value="'
		.Text::chars($config['url']).'" />
	<label for="url_rewriting">'.Trad::F_URL_REWRITING.'</label>
	<input type="text" name="url_rewriting" id="url_rewriting" value="'
		.(($config['url_rewriting']) ? $config['url_rewriting'] : '').'" />
	<p class="p-tip">'.Trad::F_TIP_URL_REWRITING.'</p>

	<p>&nbsp;</p>

	<p><a onclick="alert(\''.Trad::A_ADD_POPUP.'\');return false;" href="'.$js.'">'.Trad::S_ADD_POPUP.'</a></p>

	<p>&nbsp;</p>

	<label for="links_per_page">'.Trad::F_LINKS_PER_PAGE.'</label>
	<input type="text" name="links_per_page" id="links_per_page" value="'
		.$config['links_per_page'].'" />

	<p>&nbsp;</p>

	<label for="login">'.Trad::F_USERNAME.'</label>
	<input type="text" name="login" id="login" value="'
		.Text::chars($config['user']['login'])
	.'" />
	<label for="password">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password" />
	<p class="p-tip">'.Trad::F_TIP_PASSWORD.'</p>

	<p class="p-submit"><input type="submit" value="'.Trad::V_EDIT.'" /></p>
	<input type="hidden" name="action" value="edit" />
</form>

	';


?>
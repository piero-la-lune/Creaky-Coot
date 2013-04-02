<?php

	if (file_exists(DIR_DATABASE.FILE_CONFIG)) {
		header('Location: '.Url::parse('home'));
	}

	if (isset($_POST['action']) && $_POST['action'] == 'install') {
		$settings = new Settings();
		$ans = $settings->changes($_POST, true);
		if (!empty($ans)) {
			foreach ($ans as $v) {
				$this->addAlert(Trad::$settings[$v]);
			}
		}
		else {
			$_SESSION['alert'] = array(
				'text' => Trad::A_SUCCESS_INSTALL,
				'type' => 'alert-success'
			);
			header('Location: '.Url::parse('home'));
			exit;
		}
	}

	$title = Trad::T_INSTALLATION;

	$content = '

<h1>'.Trad::T_INSTALLATION.'</h1>

<form action="'.Url::parse('install').'" method="post">
	<label for="login">'.Trad::F_USERNAME.'</label>
	<input type="text" name="login" id="login" />
	<label for="password">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password" />

	<p>&nbsp;</p>

	<label for="title">'.Trad::F_TITLE.'</label>
	<input type="text" name="title" id="title" value="'
		.Text::chars($config['title']).'" />
	<label for="url">'.Trad::F_URL.'</label>
	<input type="url" name="url" id="url" value="'
		.Text::chars($config['url']).'" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_CONTINUE.'" /></p>
	<input type="hidden" name="action" value="install" />
</form>

	';

?>
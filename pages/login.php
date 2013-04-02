<?php

	$title = Trad::T_CONNEXION;

	$content = '

<h1>Connexion</h1>

<form action="'.Url::parse('home').'" method="post">
	<label for="login">'.Trad::F_USERNAME.'</label>
	<input type="text" name="login" id="login" />
	<label for="password">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password" />

	<p class="p-submit"><input type="submit" value="'.Trad::V_LOGIN.'" /></p>
	<input type="hidden" name="action" value="login" />
</form>

	';

?>
<?php

	header('HTTP/1.1 403 Forbidden');

	$title = Trad::T_LOGIN;

	$content = '

<h1>'.Trad::T_LOGIN.'</h1>

<form action="" method="post">
	<label for="login">'.Trad::F_USERNAME.'</label>
	<input type="text" name="login" id="login" />
	<label for="password">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password" />

	<label for="cookie">'.Trad::F_COOKIE.'</label>
	<select name="cookie" id="cookie">
		<option value="false">'.Trad::F_COOKIE_FALSE.'</option>
		<option value="true">'.Trad::F_COOKIE_TRUE.'</option>
	</select>

	<p class="p-submit"><input type="submit" value="'.Trad::V_LOGIN.'" /></p>
	<input type="hidden" name="action" value="login" />
</form>

	';

?>
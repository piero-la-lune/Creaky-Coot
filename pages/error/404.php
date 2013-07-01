<?php

	header('HTTP/1.1 404 Not Found');

	$title = Trad::T_404;

	$content = '

<h1>'.Trad::T_404.'</h1>

<p>'.Trad::S_NOTFOUND.'</p>

	';

?>
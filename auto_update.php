<?php

$remote_addr = @$_SERVER['REMOTE_ADDR'];
if (PHP_SAPI != 'cli'
	&& (strncmp(PHP_SAPI, 'cgi', 3) || !empty($remote_addr))
) {
	# executed from a browser
	exit;
}

$auto_update = true;

require 'index.php';

?>
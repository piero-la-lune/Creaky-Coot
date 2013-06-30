<?php

define('D_HOUR', 3600);
define('D_DAY', 86400);
define('D_WEEK', 604800);
define('D_MONTH', 2419200);



### CONFIG ###

	# When turned to “true”, feeds will be automatically updated.
$auto_update = false;
	# When a duration is specified, read links (but not archived one's) older
	# than this duration be automatically deleted. Turn to “false” to disable
	# this behaviour.
	# Possible durations are : “Manager::D_HOUR”, “Manager::D_DAY”,
	# “Manager::D_WEEK”, “Manager::D_MONTH”, or even
	# “4*Manager::D_DAY+12*Manager::D_HOUR”.
$auto_delete = false;

### END CONFIG ###



$remote_addr = @$_SERVER['REMOTE_ADDR'];
if (PHP_SAPI != 'cli'
	&& (strncmp(PHP_SAPI, 'cgi', 3) || !empty($remote_addr))
) {
	# executed from a browser
	exit;
}



$cron_job = true;
require 'index.php';

?>
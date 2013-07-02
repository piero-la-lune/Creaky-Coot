<?php

define('D_HOUR', 3600);
define('D_DAY', 86400);
define('D_WEEK', 604800);
define('D_MONTH', 2419200);



### CONFIG ###

	# When turned to “true”, feeds will be automatically updated.
$auto_update = false;
	# When a duration is specified, the read articles (not the archived ones)
	# older than this duration will be automatically deleted. This will help
	# you keeping your database small. When turned to “false”, read articles
	# are never deleted.
	# Allowed durations are : “D_HOUR”, “D_DAY”, “D_WEEK”, “D_MONTH”
	# Example : “4*D_DAY+12*D_HOUR”.
$auto_delete = false;
	# Only the given number of deleted article IDs (the last ones) will be kept
	# in order not to show them again when the feed is updated. This will
	# help you keeping your database small. When turned to “false”, all deleted
	# artcile IDs will be kept.
	# Example : “50” should be a correct number.
$auto_clean = false;

### END CONFIG ###



$remote_addr = @$_SERVER['REMOTE_ADDR'];
if (PHP_SAPI != 'cli'
	&& (strncmp(PHP_SAPI, 'cgi', 3) || !empty($remote_addr))
) {
	# executed from a browser
	die('Can\'t be executed from a browser...');
}



$cron_job = true;
require 'index.php';

?>
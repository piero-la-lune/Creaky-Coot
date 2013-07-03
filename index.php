<?php

# Creaky Coot
# Copyright (c) 2013 Pierre Monchalin
# <http://creaky-coot.derivoile.fr>
# 
# Permission is hereby granted, free of charge, to any person obtaining
# a copy of this software and associated documentation files (the
# "Software"), to deal in the Software without restriction, including
# without limitation the rights to use, copy, modify, merge, publish,
# distribute, sublicense, and/or sell copies of the Software, and to
# permit persons to whom the Software is furnished to do so, subject to
# the following conditions:
# 
# The above copyright notice and this permission notice shall be
# included in all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

define('NAME', 'Creaky Coot');
define('VERSION', '2.0');
define('AUTHOR', 'Pierre Monchalin');
define('URL', 'http://creaky-coot.derivoile.fr');

### Languages
define('LANGUAGES', 'fr,en'); # Separated by a comma
define('DEFAULT_LANGUAGE', 'fr'); # Used only during installation

### Standart settings
define('SALT', 'How are you doing, pumpkin?');
define('TIMEOUT', 3600); # 1 hour
define('TIMEOUT_COOKIE', 3600*24*365); # 1 year

### Twitter integration
define('TWITTER_CONSUMER_KEY', 'AdxcbjMNjf5kWRo7PHVrg');
define('TWITTER_CONSUMER_SECRET', 'lwEhMdbc3eBnHbWsNdF1ecUkvM77ozflUhrfLJyuAU');

### Directories and files
define('DIR_CURRENT', dirname(__FILE__).'/');
define('DIR_DATABASE', dirname(__FILE__).'/database/');
define('DIR_LANGUAGES', dirname(__FILE__).'/languages/');
define('FILE_CONFIG', 'config.php');
define('FILE_FEEDS', 'feeds.php');
define('FILE_LINKS', 'links.php');

### Thanks to Sebsauvage and Shaarli for the way I store data
define('PHPPREFIX', '<?php /* '); # Prefix to encapsulate data in php code.
define('PHPSUFFIX', ' */ ?>'); # Suffix to encapsulate data in php code.

### UTF-8
mb_internal_encoding('UTF-8');

### Load classes
function loadclass($classe) { require './classes/'.$classe.'.class.php'; }
spl_autoload_register('loadClass');

### Default settings
if (is_file(DIR_DATABASE.FILE_CONFIG)) {
	$config = Text::unhash(get_file(FILE_CONFIG));
	# We need $config to load the correct language
	require DIR_LANGUAGES.'Trad_'.$config['language'].'.class.php';
}
else {
	# We load language first because we need it in $config
	if (isset($_POST['language']) && Text::check_language($_POST['language'])) {
		# Needed at installation
		require DIR_LANGUAGES.'Trad_'.$_POST['language'].'.class.php';
	}
	else {
		require DIR_LANGUAGES.'Trad_'.DEFAULT_LANGUAGE.'.class.php';
	}
	$config = Settings::get_default_config(DEFAULT_LANGUAGE);
}

### Upgrade
if ($config['version'] != VERSION) {
	require DIR_CURRENT.'upgrade.php';
	exit;
}

### Manage sessions
$cookie = session_get_cookie_params();
	# Force cookie path (but do not change lifetime)
session_set_cookie_params($cookie['lifetime'], Text::dir($_SERVER["SCRIPT_NAME"]));
	# Use cookies to store session.
ini_set('session.use_cookies', 1);
	# Force cookies for session.
ini_set('session.use_only_cookies', 1);
	# Prevent php to use sessionID in URL if cookies are disabled.
ini_set('session.use_trans_sid', false);
session_name('Creaky-Coot');
session_start();

$page = new Page();

### Returns the IP address of the client
# (used to prevent session cookie hijacking)
function getIPs() {
    $ip = $_SERVER["REMOTE_ADDR"];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    	$ip .= '_'.$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    	$ip .= '_'.$_SERVER['HTTP_CLIENT_IP'];
    }
    return $ip;
}

### Authentification
$settings = new Settings();
function logout($cookie = false) {
	if (isset($_SESSION['uid'])) {
		unset($_SESSION['uid']);
		unset($_SESSION['login']);
		unset($_SESSION['ip']);
		unset($_SESSION['expires_on']);
	}
	if ($cookie && isset($_COOKIE['login'])) {
		setcookie('login', NULL, time()-3600);
		unset($_COOKIE['login']);
	}
	return true;
}
function login($post, $bypass = false) {
	global $config, $page, $settings;
	$wait = $config['user']['wait'];
	if (isset($wait[getIPs()]) && $wait[getIPs()]['time'] > time()) {
		$page->addAlert(str_replace(
			array('%duration%', '%period%'),
			Text::timeDiff($wait[getIPs()]['time'], time()),
			Trad::A_ERROR_LOGIN_WAIT
		));
		return false;
	}
	if (!$bypass) {
		if (!isset($post['login']) || !isset($post['password'])) {
			return false;
		}
		if ($post['login'] != $config['user']['login']
			|| Text::getHash($post['password']) != $config['user']['password']
		) {
			$settings->login_failed();
			$page->addAlert(Trad::A_ERROR_LOGIN);
			return false;
		}
	}
	$uid = Text::randomKey(40);
	$_SESSION['uid'] = $uid;
	$_SESSION['login'] = $config['user']['login'];
	$_SESSION['ip'] = getIPs();
	$_SESSION['expires_on'] = time()+TIMEOUT;
		# 0 means "When browser closes"
	session_set_cookie_params(0, Text::dir($_SERVER["SCRIPT_NAME"]));
	session_regenerate_id(true);
	if (isset($post['cookie']) && $post['cookie'] == 'true') {
		$settings->add_cookie($uid);
		setcookie(
			'login',
			$uid,
			time()+TIMEOUT_COOKIE,
			Text::dir($_SERVER["SCRIPT_NAME"])
		);
	}
	return true;
}
if (isset($_POST['action']) && $_POST['action'] == 'login') {
	logout(true);
	login($_POST);
}
elseif (isset($_POST['action']) && $_POST['action'] == 'logout') {
	logout(true);
}
if (!isset($_SESSION['uid']) || empty($_SESSION['uid'])
	|| $_SESSION['ip'] != getIPs()
	|| time() > $_SESSION['expires_on']
) {
	logout();
	if (isset($_COOKIE['login'])
		&& $settings->check_cookie($_COOKIE['login'])
		&& login(array('cookie' => 'true'), true)
	) {
		$loggedin = true;
	}
	else {
		$loggedin = false;
	}
}
else {
	$_SESSION['expires_on'] = time()+TIMEOUT;
	$loggedin = true;
}

### Manage directories and files
function update_file($filename, $content) {
	if (file_put_contents(DIR_DATABASE.$filename, $content, LOCK_EX) === false
		|| strcmp(file_get_contents(DIR_DATABASE.$filename), $content) != 0)
	{
		die('Enable to write file “'. DIR_DATABASE.$filename.'”');
	}
}
function get_file($filename) {
	$text = file_get_contents(DIR_DATABASE.$filename);
	if ($text === false) {
		die('Enable to read file “'. DIR_DATABASE.$filename.'”');
	}
	return $text;
}
function check_dir($dirname) {
	if (!is_dir(DIR_DATABASE.$dirname)
		&& (!mkdir(DIR_DATABASE.$dirname, 0705)
			|| !chmod(DIR_DATABASE.$dirname, 0705))
	) {
		die('Enable to create directory “'. DIR_DATABASE.$filename.'”');
	}
}
function check_file($filename, $content = '') {
	if (!is_file(DIR_DATABASE.$filename)) {
		update_file($filename, $content);
	}
}
check_dir('');
check_file(FILE_FEEDS, Text::hash(array()));
check_file(FILE_LINKS, Text::hash(array()));
check_file('.htaccess', "Allow from none\nDeny from all\n");

### Cron jobs
if (isset($cron_job) && $cron_job == true) {
	$manager = Manager::getInstance();
	echo 'Cron jobs for Creaky Coot ('.date('r').')'."\n";
	echo '==========================================================='."\n\n";
	if (isset($auto_update) && $auto_update === true) {
		echo 'Updating feeds...'."\n";
		list($done, ) = $manager->refreshFeed();
		foreach ($done as $d) {
			echo "\t".$d['nb'].' articles taken from '.$d['title']."\n";
		}
		echo "\n";
	}
	if (isset($auto_delete) && $auto_delete !== false) {
		echo 'Deleting read articles...'."\n";
		$done = $manager->autoDelete($auto_delete);
		if ($done !== false) {
			foreach ($done as $d) {
				echo "\t".$d['nb'].' articles deleted from '.$d['title']."\n";
			}
		}
		else {
			echo "\t".'An error occurred.'."\n";
		}
		echo "\n";
	}
	if (isset($auto_clean) && $auto_clean !== false) {
		echo 'Cleaning old deleted articles...'."\n";
		$done = $manager->autoClean($auto_clean);
		if ($done !== false) {
			foreach ($done as $d) {
				echo "\t".$d['nb'].' article IDs deleted from '.$d['title']."\n";
			}
		}
		else {
			echo "\t".'An error occurred.'."\n";
		}
		echo "\n";
	}
	echo 'Done.'."\n";
	exit;
}

### Load page
if (!is_file(DIR_DATABASE.FILE_CONFIG)) {
	$page->load('install');
}
elseif (!$loggedin) {
	$page->load('login');
}
elseif (!isset($_GET['page'])) {
	$page->load('home');
}
else {
	$page->load($_GET['page']);
}

$pagename = $page->getPageName();

$menu = '';
if ($page->printHeader()) {
	$menu .= ''
		.'<a href="'.Url::parse('home').'"'
			.($pagename == 'home' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_UNREAD)
		.'</a>'
		.'<a href="'.Url::parse('links').'"'
			.($pagename == 'links' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_ALL)
		.'</a>'
		.'<a href="'.Url::parse('filter').'"'
			.($pagename == 'filter' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_FILTER)
		.'</a>'
		.'<a href="'.Url::parse('add').'"'
			.($pagename == 'add' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_NEW)
		.'</a>'
		.'<a href="'.Url::parse('feeds').'"'
			.($pagename == 'feeds' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_FEEDS)
		.'</a>'
		.'<a href="'.Url::parse('settings').'"'
			.($pagename == 'settings' ? 'class="selected"' : '').'>'
			.mb_strtolower(Trad::T_SETTINGS)
		.'</a>'
		.'<a href="#" '.Text::click('logout').'>'
			.mb_strtolower(Trad::T_LOGOUT)
		.'</a>';
}

?>

<!DOCTYPE html>

<html dir="ltr" lang="fr">

	<head>

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

		<link rel="stylesheet" href="<?php echo Url::parse('public/css/app.min.css'); ?>" />

		<title><?php echo $page->getTitle(); ?> – <?php echo $config['title']; ?></title>

	<body>

		<?php echo $page->getAlerts(); ?>

		<header>
			<nav>
				<?php echo $menu; ?>
			</nav>
		</header>

		<section class="inner">
			<?php echo $page->getContent(); ?>
		</section>

		<form id="form-logout" action="<?php echo Url::parse('home'); ?>" method="post">
			<input type="hidden" name="action" value="logout" />
		</form>

		<script><?php
echo           "var ajax_url = '".Url::parse('ajax')."';";
echo           "var home_url = '".Url::parse('home')."';";
echo       "var m_error_ajax = '".Text::js_str(Trad::A_ERROR_AJAX)."';";
echo      "var m_error_login = '".Text::js_str(Trad::A_ERROR_AJAX_LOGIN)."';";
echo     "var m_no_more_link = '".Text::js_str(Trad::S_NO_MORE_LINK)."';";
echo    "var m_confirm_clear = '".Text::js_str(Trad::A_CONFIRM_CLEAR)."';";
echo   "var m_confirm_delete = '".Text::js_str(Trad::A_CONFIRM_DELETE_LINK)."';";
echo  "var m_confirm_clear_f = '".Text::js_str(Trad::A_CONFIRM_CLEAR_FEED)."';";
echo "var m_confirm_delete_f = '".Text::js_str(Trad::A_CONFIRM_DELETE_FEED)."';";
echo        "var m_add_popup = '".Text::js_str(Trad::A_ADD_POPUP)."';";
echo        "var m_enter_url = '".Text::js_str(Trad::A_ENTER_URL)."';";
echo             "var page = '".Text::js_str($_GET['page'])."';";
if (isset($_GET['feed'])) { echo "var feed = ".intval($_GET['feed']).";"; }
if (isset($_GET['tag'])) { echo "var tag = '".Text::js_str($_GET['tag'])."';"; }
if (isset($_GET['q'])) { echo "var q = '".Text::js_str($_GET['q'])."';"; }
	?></script>
		<script src="<?php echo Url::parse('public/js/app.min.js'); ?>"></script>

	</body>

</html>
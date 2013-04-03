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
define('VERSION', '0.2.0');
define('AUTHOR', 'Pierre Monchalin');
define('URL', 'http://creaky-coot.derivoile.fr');

### Languages
define('LANGUAGES', 'fr'); # Separated by a comma
define('DEFAULT_LANGUAGE', 'fr'); # Used only during installation

### Standart settings
define('SALT', 'How are you doing, pumpkin?');
define('TIMEOUT', 3600); # 1 hour
define('TIMEOUT_COOKIE', 3600*24*365); # 1 year

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
	if (file_put_contents(DIR_DATABASE.$filename, $content) === false
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

$menu = ''
	.'<a href="'.Url::parse('home').'"'
		.($pagename == 'home' ? 'class="selected"' : '').'>'
		.mb_strtolower(Trad::T_UNREAD)
	.'</a>'
	.'<a href="'.Url::parse('links').'"'
		.($pagename == 'links' ? 'class="selected"' : '').'>'
		.mb_strtolower(Trad::T_ALL)
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

		<script>

var loader = document.createElement("span");
loader.className = 'loading';
loader.innerHTML = '<i class="n1"></i><i class="n2"></i><i class="n3"></i>';

function mark_read_link(id, preview) {
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=mark_read&id='+id);
	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				var div = document.getElementById('link-'+id);
				if (preview && preview == 'links') {
					div.querySelector('h2').className = '';
					div.querySelector('.div-actions a:nth-child(2)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(3)')
						.style.display = 'inline-block';
				}
				else if (preview) {
					div.parentNode.removeChild(div);
				}
				else {
					div.querySelector('.div-actions a:nth-child(1)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(2)')
						.style.display = 'inline-block';	
				}
			}
		}
	};
}
function mark_unread_link(id, preview) {
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=mark_unread&id='+id);
	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				var div = document.getElementById('link-'+id);
				if (preview && preview == 'links') {
					div.querySelector('h2').className = 'unread';
					div.querySelector('.div-actions a:nth-child(3)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(2)')
						.style.display = 'inline-block';
				}
				else {
					div.querySelector('.div-actions a:nth-child(2)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(1)')
						.style.display = 'inline-block';
				}
			}
		}
	};
}
function archive_link(id, preview) {
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=archive&id='+id);
	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				var div = document.getElementById('link-'+id);
				if (preview && preview == 'links') {
					div.querySelector('h2').className = '';
					div.querySelector('.div-actions a:nth-child(2)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(3)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(4)')
						.style.display = 'none';
				}
				else if (preview) {
					div.parentNode.removeChild(div);
				}
				else {
					div.querySelector('.div-actions a:nth-child(1)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(2)')
						.style.display = 'none';
					div.querySelector('.div-actions a:nth-child(3)')
						.style.display = 'none';
				}
			}
		}
	};
}
function delete_link(id, preview) {
	if (!confirm("<?php echo Trad::A_CONFIRM_DELETE_LINK; ?>")) { return false; }
	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=delete&id='+id);
	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				if (preview) {
					var div = document.getElementById('link-'+id);
					div.parentNode.removeChild(div);
				}
				else {
					window.location.href = "<?php echo Url::parse('home'); ?>";
				}
			}
		}
	};
}
function mark_read_all(elm, preview) {
	elm.parentNode.replaceChild(loader, elm);

	var ids = new Array();
	var divs = document.querySelectorAll(".div-link");
	for (var i = 0; i < divs.length; i++) {
		ids.push(divs[i].id);
	}

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=mark_read_all&ids='+ids.join(','));

	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				for (var i = 0; i < ans['ids'].length; i++) {
					var div = document.getElementById("link-"+ans['ids'][i]);
					if (preview && preview == 'links') {
						div.querySelector('h2').className = '';
						div.querySelector('.div-actions a:nth-child(2)')
							.style.display = 'none';
						div.querySelector('.div-actions a:nth-child(3)')
							.style.display = 'inline-block';
					}
					else {
						var parent = div.parentNode;
						parent.removeChild(div);
					}
				}
			}
			loader.parentNode.replaceChild(elm, loader);
		}
	};
}
function refresh(elm, preview, feed) {
	elm.parentNode.replaceChild(loader, elm);

	if (!feed) { var feed = false; }

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=refresh&page='+preview+'&feed='+feed);

	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				var div = document.createElement("div");
				div.innerHTML = ans['html'];
				var first = document.querySelector(".div-link");
				if (!first) { var first = document.querySelector(".p-more"); }
				first.parentNode.insertBefore(div, first);
			}
			loader.parentNode.replaceChild(elm, loader);
		}
	};
}
function clear_all(elm) {
	if (!confirm("<?php echo Trad::A_CONFIRM_CLEAR; ?>")) { return false; }
	elm.parentNode.replaceChild(loader, elm);

	var ids = new Array();
	var divs = document.querySelectorAll(".div-link");
	for (var i = 0; i < divs.length; i++) {
		ids.push(divs[i].id);
	}

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=clear&ids='+ids.join(','));

	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				for (var i = 0; i < ans['ids'].length; i++) {
					var div = document.getElementById("link-"+ans['ids'][i]);
					div.parentNode.removeChild(div);
				}
			}
			loader.parentNode.replaceChild(elm, loader);
		}
	};
}
function clear_feed(elm, feed) {
	if (!confirm("<?php echo Trad::A_CONFIRM_CLEAR_FEED; ?>")) { return false; }
	elm.parentNode.replaceChild(loader, elm);

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=clear_feed&feed='+feed);

	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			loader.parentNode.replaceChild(elm, loader);
		}
	};
}
function delete_feed(elm, feed) {
	if (!confirm("<?php echo Trad::A_CONFIRM_DELETE_FEED; ?>")) { return false; }
	elm.parentNode.replaceChild(loader, elm);

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=delete_feed&feed='+feed);

	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				var div = document.getElementById("feed-"+feed);
				div.parentNode.removeChild(div);
			}
			loader.parentNode.replaceChild(elm, loader);
		}
	};
}
function load(elm, preview, type, feed) {
	elm.parentNode.replaceChild(loader, elm);

	var last = document.querySelectorAll(".div-link");
	if (last.length < 1) { var id = false; }
	else { var id = last[last.length-1].id; }
	if (!preview) { var preview = false; }
	if (!type) { var type = false; }
	if (!feed) { var feed = false; }

	var xhr = new XMLHttpRequest();
	xhr.open('POST', '<?php echo Url::parse('ajax'); ?>');
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.send('action=load&page='+preview+'&type='+type+'&feed='+feed+'&id='+id);

	xhr.onreadystatechange = function() {
		if (xhr.readyState == xhr.DONE && xhr.status == 200) {
			var ans = JSON.parse(xhr.responseText);
			if (ans['status'] == 'success') {
				var div = document.createElement("div");
				div.innerHTML = ans['html'];
				var after = document.querySelector(".p-more");
				after.parentNode.insertBefore(div, after);
			}
			else {
				elm = document.createElement("span");
				elm.innerHTML = "<?php echo Trad::S_NO_MORE_LINK; ?>";
			}
			loader.parentNode.replaceChild(elm, loader);
		}
	};
}
function logout() {
	document.getElementById("form-logout").submit();
}

		</script>
	</body>

</html>
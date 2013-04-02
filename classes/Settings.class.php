<?php

class Settings {

	protected $config = array();
	protected $errors = array();

	public function __construct() {
		global $config;
		$this->config = $config;
	}

	protected function save() {
		global $config;
		$sav = $this->config;
		$sav['last_update'] = time();
		$sav['version'] = VERSION;
		$config = $sav;
		update_file(FILE_CONFIG, Text::hash($sav));
	}

	public function changes($post, $install = false) {
		global $loggedin;
		if (!$loggedin && file_exists(DIR_DATABASE.FILE_CONFIG)) {
			return array();
		}
		$this->errors = array();
		$this->c_global($post);
		$this->c_links($post);
		$this->c_user($post, $install);
		$this->save();
		return $this->errors;
	}

	protected function c_global($post) {
		if (isset($post['title'])) {
			$this->config['title'] = Text::chars($post['title']);
		}
		if (isset($post['url'])) {
			$post['url'] = preg_replace('#//$#', '/', $post['url']);
			if (filter_var($post['url'], FILTER_VALIDATE_URL)) {
				$this->config['url'] = $post['url'];
			}
			else {
				$this->errors[] = 'validate_url';
			}
		}
		if (isset($post['url_rewriting'])) {
			if (empty($post['url_rewriting'])) {
				$this->config['url_rewriting'] = false;
			}
			else {
				$this->config['url_rewriting'] = filter_var(
					$post['url_rewriting'],
					FILTER_SANITIZE_URL
				);
				if ($rewriting = Url::getRules()) {
					$base = $this->config['url_rewriting'];
					$text = 'ErrorDocument 404 '.$base.'error/404'."\n\n"
						.'RewriteEngine on'."\n"
						.'RewriteBase '.$base."\n\n";
					foreach ($rewriting as $r) {
						if (isset($r['condition'])
							&& $r['condition'] == 'file_doesnt_exist'
						) {
							$text .= "\n".'RewriteCond %{REQUEST_FILENAME} !-f'."\n";
						}
						$text .= 'RewriteRule '.$r['rule'].' '.$r['redirect'].' [QSA,L]'."\n";
					}
					file_put_contents('.htaccess', $text);
				}
			}
		}
	}

	protected function c_links($post) {
		if (isset($post['links_per_page'])) {
			$nb = intval($post['links_per_page']);
			if ($nb > 0) {
				$this->config['links_per_page'] = $nb;
			}
		}
	}

	protected function c_user($post, $install) {
		if ($install) {
			if (isset($post['login']) && isset($post['password'])) {
				$this->config['user'] = array(
					'login' => $post['login'],
					'password' => Text::getHash($post['password']),
					'wait' => array()
				);
			}
		}
		else {
			if (isset($post['login'])) {
				$this->config['user']['login'] = $post['login'];
			}
			if (isset($post['password']) && !empty($post['password'])) {
				$this->config['user']['password'] = Text::getHash($post['password']);
			}
		}
	}

	public function login_failed() {
		if (isset($this->config['user']['wait'][getIPs()])) {
			$wait = &$this->config['user']['wait'][getIPs()];
			$wait['nb']++;
			if ($wait['nb'] < 10) {
				$wait['time'] = time();
			}
			elseif ($wait['nb'] < 20) {
				$wait['time'] = time()+600; # 10 minutes
			}
			elseif ($wait['nb'] < 30) {
				$wait['time'] = time()+1800; # half hour
			}
			else {
				$wait['time'] = time()+3600; # one hour
			}
			unset($wait);
		}
		else {
			$this->config['user']['wait'][getIPs()] = array(
				'nb' => 1,
				'time' => time()
			);
		}
		$this->save();
	}

	public static function get_default_config($language = DEFAULT_LANGUAGE) {
		return array(
			'title' => 'Creaky Coot',
			'url' => 'http://'.$_SERVER['SERVER_NAME']
				.Text::dir($_SERVER["SCRIPT_NAME"]),
			'url_rewriting' => false,
			'language' => $language,
			'links_per_page' => 4,
			'user' => array(
				'login' => 'admin',
				'password' => 'admin',
				'wait' => array()
			),
			'salt' => Text::randomKey(40),
			'version' => VERSION,
			'last_update' => false
		);
	}
}

?>
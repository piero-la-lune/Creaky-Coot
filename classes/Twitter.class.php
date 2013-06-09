<?php

class Twitter {

	protected $ok = false;
	protected $oauth;
	protected $urls = array(
		'user' => 'http://twitter.com/%user%',
		'tweet' => 'http://twitter.com/%user%/status/%id%',
		'search' => 'http://twitter.com/search?q=%q%'
	);

	public function __construct() {
		global $config;
		if ($config['twitter'] != NULL) {
			$this->oauth = new tmhOAuth(array(
				'consumer_key' => TWITTER_CONSUMER_KEY,
				'consumer_secret' => TWITTER_CONSUMER_SECRET,
				'user_token' => $config['twitter']['user_token'],
				'user_secret' => $config['twitter']['user_secret']
			));
		}
		else {
			$this->oauth = new tmhOAuth(array(
				'consumer_key' => TWITTER_CONSUMER_KEY,
				'consumer_secret' => TWITTER_CONSUMER_SECRET
			));
		}
	}

	public function identify($user) {
		$this->oauth->config['user_token'] = $user['oauth_token'];
		$this->oauth->config['user_secret'] = $user['oauth_token_secret'];
	}

	public function configure() {
		if (!isset($_GET['oauth_verifier'])) {
			return $this->ask_authorization();
		}
		if (!isset($_SESSION['oauth'])
			|| !isset($_SESSION['oauth']['oauth_token'])
			|| !isset($_SESSION['oauth']['oauth_token_secret'])
		) {
			return Trad::A_ERROR_TWITTER;
		}
		$this->identify($_SESSION['oauth']);
		$code = $this->oauth->request(
			'POST',
			$this->oauth->url('oauth/access_token', ''),
			array('oauth_verifier' => $_GET['oauth_verifier'])
		);
		if ($code != 200) {
			return Trad::A_ERROR_TWITTER;
		}
		$settings = new Settings();
		$settings->twitter_auth(
			$this->oauth->extract_params($this->oauth->response['response'])
		);
		return true;
	}

	protected function ask_authorization() {
		$url = Url::parse('feeds', array('action' => 'twitter_config'));
		$code = $this->oauth->request(
			'POST',
			$this->oauth->url('oauth/request_token', ''),
			array('oauth_callback' => $url, 'x_auth_access_type' => 'read')
		);
		if ($code != 200) {
			return Trad::A_ERROR_TWITTER;
		}
		$_SESSION['oauth'] =
			$this->oauth->extract_params($this->oauth->response['response']);
		header('Location: '
			.$this->oauth->url('oauth/authorize', '')
			.'?oauth_token='.$_SESSION['oauth']['oauth_token']
		);
		exit;
	}

	public function get($url, $params, $deleted) {
		global $config;
		if (!isset($params['count'])) { $params['count'] = 50; }
		$code = $this->oauth->request(
			'GET',
			$this->oauth->url('1.1/'.$url),
			$params
		);
		if ($code != 200) { return false; }
		$manager = Manager::getInstance();
		$added = array();
		$tweets = json_decode($this->oauth->response['response']);
		if (isset($tweets->statuses)) { $tweets = $tweets->statuses; } # Search
		foreach ($tweets as $t) {
			$url = str_replace(
				array('%id%', '%user%'),
				array($t->id_str, $t->user->screen_name),
				$this->urls['tweet']
			);
			$id = md5($url);
			if ($manager->getLink($id) !== false || in_array($id, $deleted)) {
				continue;
			}
			if (isset($t->retweeted_status)) {
				$text = $this->formate_tweet($t->retweeted_status);
				$text = 'RT <a href="'.str_replace(
					'%user%',
					$t->retweeted_status->user->screen_name,
					$this->urls['user']
				).'">@'.$t->retweeted_status->user->screen_name.'</a>: '.$text;
			}
			else {
				$text = $this->formate_tweet($t);
			}
			$added[$id] = array(
				'user' => $t->user->name,
				'tweet' => $text,
				'date' => strtotime($t->created_at),
				'url' => $url,
				'user_img' => $t->user->profile_image_url
			);
		}
		return $added;
	}

	public function formate_tweet($t) {
		$text = $t->text;
		foreach ($t->entities->urls as $u) {
			$text = str_replace(
				$u->url,
				'<a href="'.$u->expanded_url.'">'.$u->display_url.'</a>',
				$text
			);
		}
		foreach ($t->entities->user_mentions as $u) {
			$url = str_replace('%user%', $u->screen_name, $this->urls['user']);
			$text = str_replace(
				'@'.$u->screen_name,
				'<a href="'.$url.'">@'.$u->screen_name.'</a>',
				$text
			);
		}
		foreach ($t->entities->hashtags as $h) {
			$url = str_replace('%q%', '%23'.$h->text, $this->urls['search']);
			$text = str_replace(
				'#'.$h->text,
				'<a href="'.$url.'">#'.$h->text.'</a>',
				$text
			);
		}
		if (isset($t->entities->media)) {
			foreach ($t->entities->media as $m) {
				$text = str_replace(
					$m->url,
					'<a href="'.$m->expanded_url.'">'.$m->display_url.'</a>',
					$text
				);
				$text .= '<p class="twitter-img"><img src="'.$m->media_url.'" /></p>';
			}
		}
		return str_replace("\n", '<br />', $text);
	}

}

?>
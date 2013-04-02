<?php

class Url {

	protected $page = '';
	protected $params = array();
	protected $anchor = array();

	protected static $rewriting = array(
		array(
			'rule' => '^$',
			'redirect' => 'index.php?page=home'
		),
		array(
			'rule' => '^home$',
			'redirect' => 'index.php?page=home'
		),
		array(
			'rule' => '^install$',
			'redirect' => 'index.php?page=install'
		),
		array(
			'rule' => '^login$',
			'redirect' => 'index.php?page=login'
		),
		array(
			'rule' => '^error/404$',
			'redirect' => 'index.php?page=error/404'
		),
		array(
			'rule' => '^feeds$',
			'redirect' => 'index.php?page=feeds'
		),
		array(
			'rule' => '^feeds/([0-9]+)$',
			'redirect' => 'index.php?page=feeds&id=$1'
		),
		array(
			'rule' => '^links$',
			'redirect' => 'index.php?page=links'
		),
		array(
			'rule' => '^links/([a-z0-9]{32})$',
			'redirect' => 'index.php?page=links&id=$1'
		),
		array(
			'rule' => '^feeds/([0-9]+)/edit$',
			'redirect' => 'index.php?page=feeds&id=$1&action=edit'
		),
		array(
			'rule' => '^settings$',
			'redirect' => 'index.php?page=settings'
		),
		array(
			'rule' => '^ajax$',
			'redirect' => 'index.php?page=ajax'
		)
	);

	public function __construct($page, $params = array(), $anchor = '') {
		$this->page = $page;
		$this->params = $params;
		$this->anchor = $anchor;
	}

	public function addParam($name, $value) {
		$this->params[$name] = $value;
	}

	public function get() {
		return self::parse($this->page, $this->params, $this->anchor);
	}
	public function getBase() {
		return self::parse($this->page);
	}
	public static function getRules() {
		return self::$rewriting;
	}

	public static function parse($page, $params = array(), $anchor = '') {
		global $config;
		$page = self::rewriting($page);
		$parts = explode('?', $page);
		if (isset($parts[1]) && !empty($parts[1])) {
			$query = explode('&', $parts[1]);
			foreach ($query as $v) {
				if (!empty($v)) {
					$v = explode('=', $v);
					if (isset($v[0]) && isset($v[1])) {
						$params[$v[0]] = $v[1];
					}
				}
			}
		}
		$ret = $config['url'].$parts[0];
		if (!empty($params)) {
			$ret .= '?'.http_build_query($params);
		}
		if (!empty($anchor)) { $ret .= '#'.$anchor; }
		return $ret;
	}

	protected static function rewriting($page) {
		global $config;
		if ($config['url_rewriting']) { return $page; }
		foreach (self::$rewriting as $v) {
			$rule = '#'.$v['rule'].'#';
			if (preg_match($rule, $page)) {
				if (isset($v['condition'])
					&& $v['condition'] == 'file_doesnt_exist'
				) {
					if (!file_exists($page)) {
						return preg_replace($rule, $v['redirect'], $page);
					}
				}
				else {
					return preg_replace($rule, $v['redirect'], $page);
				}
			}
		}
		return $page;
	}

}

?>
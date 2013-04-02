<?php

class Page {

	protected $page;
	protected $title;
	protected $content;
	protected $errors = array();

	private $pages = array(
		'home',
		'install',
		'login',
		'error/404',
		'feeds',
		'links',
		'settings',
		'ajax'
	);

	public function load($page) {
		global $config;
		$this->page = $page;
		$path = dirname(__FILE__).'/../pages/'.$page.'.php';
		if (!in_array($page, $this->pages)) {
			$this->page = 'error/404';
			$path = dirname(__FILE__).'/../pages/error/404.php';
		}
		include($path);
		$this->title = $title;
		$this->content = $content;
	}

	public function getPageName() {
		return $this->page;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getContent() {
		return $this->content;
	}

	public function addAlert($txt, $type = 'alert-error') {
		$this->errors[] = array('text' => $txt, 'type' => $type);
	}
	public function getAlerts() {
		$txt = '';
		if (isset($_SESSION['alert'])) {
			$this->errors[] = $_SESSION['alert'];
			unset($_SESSION['alert']);
		}
		foreach ($this->errors as $error) {
			$txt .= '<div class="alert '.$error['type'].'" '
				.'onclick="this.style.display = \'none\';">'
				.$error['text']
			.'</div>';
		}
		return $txt;
	}

}

?>
<?php

# Adaptation of a class named “Filter” taken from PicoFeed
# PicoFeed : http://github.com/fguillot/picoFeed

class Filter {

	protected $input;
	protected $url_base;
	protected $url_folders;
	protected $data;
	protected $title;
	protected $removed_tag = array();
	protected $empty_tag = array();
	protected $pre_tag = array();

	protected $allowed_tags = array(
		'dt' => array(),
		'dd' => array(),
		'dl' => array(),
		'table' => array(),
		'caption' => array(),
		'tr' => array(),
		'th' => array(),
		'td' => array(),
		'tbody' => array(),
		'thead' => array(),
		'h2' => array(),
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'h6' => array(),
		'strong' => array(),
		'em' => array(),
		'code' => array(),
		'pre' => array(),
		'blockquote' => array(),
		'p' => array(),
		'ul' => array(),
		'li' => array(),
		'ol' => array(),
		'br' => array(),
		'del' => array(),
		'a' => array('href'),
		'img' => array('src'),
		'figure' => array(),
		'figcaption' => array(),
		'cite' => array(),
		'time' => array('datetime'),
		'abbr' => array('title')
	);

	protected $forbidden_tags = array(
		'head',
		'script',
		'style',
		'header',
		'footer',
		'aside',
		'form',
		'button',
		'textarea',
		'menu',
		'object',
		'iframe',
		'h1'
	);

	protected $self_closing_tags = array(
		'br',
		'img',
		'hr'
	);

	protected $useless_words = array(
		'ad-break',
		'ads(-| |$)',
		'agegate',
		'combx',
		'comment',
		'community',
		'disqus',
		'down(-| |$)',
		'extra',
		'foot',
		'head',
		'links',
		'menu',
		'meta',
		'nav',
		'pager',
		'pagination',
		'popup',
		'promo',
		'related',
		'remark',
		'rss',
		'scroll',
		'share',
		'sharing',
		'shopping',
		'shoutbox',
		'sidebar',
		'social',
		'sponsor',
		'tag',
		'tool',
		'tweet',
		'twitter',
		'widget'
	);
	protected $words_regex;

	protected $allowed_protocols = array(
		'http://',
		'https://',
		'ftp://',
		'mailto://'
	);

	protected $protocol_attrs = array(
		'src',
		'href'
	);

	protected $blacklist_media = array(
		'feeds.feedburner.com',
		'share.feedsportal.com',
		'da.feedsportal.com',
		'rss.feedsportal.com',
		'res.feedsportal.com',
		'res3.feedsportal.com',
		'pi.feedsportal.com',
		'rss.nytimes.com',
		'feeds.wordpress.com',
		'stats.wordpress.com',
		'rss.cnn.com',
		'twitter.com/home?status=',
		'twitter.com/share',
		'twitter_icon_large.png',
		'www.facebook.com/sharer.php',
		'facebook_icon_large.png',
		'plus.google.com/share',
		'www.gstatic.com/images/icons/gplus-16.png',
		'www.gstatic.com/images/icons/gplus-32.png',
		'www.gstatic.com/images/icons/gplus-64.png'
	);

	protected $required_attrs = array(
		'a' => array('href'),
		'img' => array('src')
	);

	public function __construct() {
		$this->words_regex = '#'.implode('|', $this->useless_words).'#i';
	}

	public function execute($data, $url) {

		# Convert to UTF-8
		$encoding = mb_detect_encoding(
			$data,
			array('ASCII', 'UTF-8', 'Windows-1252', 'ISO-8859-1')
		);
		if ($encoding != 'UTF-8') {
			$data = mb_convert_encoding($data, 'UTF-8', $encoding);
		}

		# Convert bad formatted documents to XML
		$d = new DOMDocument();
		@$d->loadHTML('<?xml encoding="UTF-8">'.$data);

		$this->input = $d->saveXML($d->getElementsByTagName('body')->item(0));
		$this->data = '';

		# Get path of folder for relative ressources
		$path = parse_url($url);
		if (is_array($path) && isset($path['scheme']) && isset($path['host'])) {
			$this->url_base = $path['scheme'].'://'.$path['host'];
			if (isset($path['path'])) {
				$folder = explode('/', $path['path']);
				if (count($folder) > 2) {
					unset($folder[count($folder)-1]);
					$this->url_folders = implode('/', $folder).'/';
				}
				else {
					$this->url_folders = '/';
				}
			}
			else {
				$this->url_folders = '/';
			}
		}
		else {
			$this->url_base = $url;
			$this->url_folders = '';
		}

		# Find title
		$title = $d->getElementsByTagName('h1')->item(0);
		if ($title) {
			$this->title =
				htmlentities($title->nodeValue, ENT_QUOTES, 'UTF-8', false);
		}
		else {
			$title = $d->getElementsByTagName('title')->item(0);
			if ($title) {
				$this->title =
					htmlentities($title->nodeValue, ENT_QUOTES, 'UTF-8', false);
			}
			else {
				$this->title = $this->url_base.$this->url_folders;
			}
		}

		$parser = xml_parser_create();
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'startTag', 'endTag');
		xml_set_character_data_handler($parser, 'dataTag');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

		if (!xml_parse($parser, $this->input, true)) {
			return strip_tags($data);
		}

		xml_parser_free($parser);

		return $this->data;

	}

	public function getTitle() { return $this->title; }

	public function startTag($parser, $name, $attributes) {
		array_push($this->pre_tag, ($name == 'pre') ? true : false);

		# Useless tags
		if (end($this->empty_tag) || $this->isForbiddenTag($name)) {
			array_push($this->removed_tag, false);
			array_push($this->empty_tag, true);
			return true;
		}

		# Useless tag because of class name
		if ($this->isUselessTag($attributes)) {
			array_push($this->removed_tag, false);
			array_push($this->empty_tag, true);
			return true;
		}

		# Useless image
		if ($this->isPixelTracker($name, $attributes)) {
			array_push($this->removed_tag, true);
			array_push($this->empty_tag, false);
			return true;
		}

		# Not allowed tag
		if (!$this->isAllowedTag($name)) {
			array_push($this->removed_tag, true);
			array_push($this->empty_tag, false);
			return true;
		}

		# Clean up attributes
		$attrs = array();
		foreach ($attributes as $a => $v) {
			if (empty($v) || !$this->isAllowedAttribute($name, $a)) {
				continue;
			}
			if ($this->isResource($a)) {
				if (strpos($v, '://') === false) {
					if (strpos($v, '/') === 0) {
						$attrs[$a] = $a.'="'.$this->url_base.$v.'"';
					}
					else {
						$v = preg_replace('#^./#', '', $v);
						$attrs[$a] = $a.'="'.$this->url_base
							.$this->url_folders.$v.'"';
					}
				}
				elseif ($this->isAllowedProtocol($v)
					&& !$this->isBlacklistMedia($v)
				) {
					$attrs[$a] = $a.'="'.$v.'"';
				}
			}
			else {
				$attrs[$a] = $a.'="'.$v.'"';
			}
		}

		# Check for required attributes
		if (isset($this->required_attrs[$name])) {
			foreach ($this->required_attrs[$name] as $a) {
				if (!isset($attrs[$a])) {
					array_push($this->removed_tag, true);
					array_push($this->empty_tag, false);
					return true;
				}
			}
		}

		$this->data .= '<'.$name;
		if (!empty($attrs)) { $this->data .= ' '.implode(' ', $attrs); }
		if (!$this->isSelfClosingTag($name)) {
			$this->data .= '>';
		}

		array_push($this->removed_tag, false);
		array_push($this->empty_tag, false);
		return true;
	}

	public function endTag($parser, $name) {
		if (!end($this->removed_tag) && !end($this->empty_tag)) {
			if ($this->isSelfClosingTag($name)) {
				$this->data .= '/>';
			}
			else {
				$this->data .= '</'.$name.'>';
			}
		}
		array_pop($this->pre_tag);
		array_pop($this->removed_tag);
		array_pop($this->empty_tag);
		return true;
	}

	public function dataTag($parser, $content) {
		if (!end($this->empty_tag)) {
			if (!end($this->pre_tag)) {
				$content = preg_replace('#\s+#u', ' ', $content);
			}
			$this->data .= htmlentities($content, ENT_QUOTES, 'UTF-8', false);
		}
		return true;
	}

	public function isAllowedTag($name) {
		return isset($this->allowed_tags[$name]);
	}

	public function isForbiddenTag($name) {
		return in_array($name, $this->forbidden_tags);
	}

	public function isSelfClosingTag($name) {
		return in_array($name, $this->self_closing_tags);
	}

	public function isAllowedAttribute($tag, $attribute) {
		return in_array($attribute, $this->allowed_tags[$tag]);
	}

	public function isResource($attribute) {
		return in_array($attribute, $this->protocol_attrs);
	}

	public function isAllowedProtocol($value) {
		foreach ($this->allowed_protocols as $protocol) {
			if (strpos($value, $protocol) === 0) {
				return true;
			}
		}
		return false;
	}

	public function isBlacklistMedia($resource) {
		foreach ($this->blacklist_media as $name) {
			if (strpos($resource, $name) !== false) {
				return true;
			}
		}
		return false;
	}

	# Return true if this is an useless image
	public function isPixelTracker($tag, $attributes) {
		return $tag === 'img'
			&& ((
				isset($attributes['height'])
					&& isset($attributes['width'])
					&& $attributes['height'] == 1
					&& $attributes['width'] == 1)
				|| (isset($attributes['src'])
					&& strpos($attributes['src'], 'piwik'))
			);
	}

	public function isUselessTag($attrs) {
		$search = '';
		if (isset($attrs['class'])) { $search .= $attrs['class']; }
		if (isset($attrs['id'])) { $search .= $attrs['id']; }
		return preg_match($this->words_regex, $search);
	}
}

?>
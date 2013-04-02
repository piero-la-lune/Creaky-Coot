<?php

# Adaptation of a class named “Filter” taken from PicoFeed
# PicoFeed : http://github.com/fguillot/picoFeed

# RSS 1.0
#    specifications: http://www.scriptol.fr/rss/RSS-1.0.html
#    example: http://planete-jquery.fr/feed.php?type=rss
# RSS 2.0
#    specifications: http://www.scriptol.fr/rss/RSS-2.0.html
#    example: http://wordpress.org/news/feed/
# Atom
#    specifications: http://www.ietf.org/rfc/rfc4287
#    example: http://feeds.feedburner.com/blogspot/MKuf

class Filter {

	protected $input;
	protected $url;
	protected $data;
	protected $empty_tag = array();

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

	protected $allowed_protocols = array(
		'http://',
		'https://',
		'ftp://',
		'mailto://'
	);

	protected $protocol_attributes = array(
		'src',
		'href'
	);

	protected $blacklist_media = array(
		'feeds.feedburner.com',
		'feedsportal.com',
		'rss.nytimes.com',
		'feeds.wordpress.com',
		'stats.wordpress.com'
	);

	protected $required_attributes = array(
		'a' => array('href'),
		'img' => array('src')
	);

	public function execute($data, $url) {
		$data = preg_replace_callback('#<pre>(.*)</pre>#isU', function($m) {
			return '<pre>'.htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8').'</pre>';
		}, $data);

		# Convert bad formatted documents to XML
		$d = new DOMDocument();
		@$d->loadHTML('<?xml encoding="UTF-8">'.$data);

		$this->input = $d->saveXML($d->getElementsByTagName('body')->item(0));
		$this->url = dirname($url).'/';
		$this->data = '';

		$parser = xml_parser_create();
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'startTag', 'endTag');
		xml_set_character_data_handler($parser, 'dataTag');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

		if (!xml_parse($parser, $this->input, true)) {
			return strip_tags($data);
		}

		xml_parser_free($parser);

		$this->data = str_replace('<p>&nbsp;</p>', '', $this->data);

		return $this->data;
	}


	public function startTag($parser, $name, $attributes) {
		$empty_tag = false;

		# Useless image
		if ($this->isPixelTracker($name, $attributes)) {
			$empty_tag = true;
		}
		else if ($this->isAllowedTag($name)) {
			$attr_data = '';
			$used_attributes = array();

			foreach ($attributes as $attribute => $value) {
				if ($this->isAllowedAttribute($name, $attribute)) {
					if ($this->isResource($attribute)) {
						if (strpos($value, '://') === false) {
							$attr_data .= ' '.$attribute.'="'.$this->url.$value.'"';
							$used_attributes[] = $attribute;
						}
						if ($this->isAllowedProtocol($value)
							&& !$this->isBlacklistMedia($value)
						) {
							$attr_data .= ' '.$attribute.'="'.$value.'"';
							$used_attributes[] = $attribute;
						}
					}
					else {
						$attr_data .= ' '.$attribute.'="'.$value.'"';
						$used_attributes[] = $attribute;
					}
				}
			}

			if (isset($this->required_attributes[$name])) {
				foreach ($this->required_attributes[$name] as $required_attribute) {
					if (!in_array($required_attribute, $used_attributes)) {
						$empty_tag = true;
						break;
					}
				}
			}

			if (!$empty_tag) {
				$this->data .= '<'.$name.$attr_data;
				if ($name !== 'img' && $name !== 'br') { $this->data .= '>'; }
			}
		}
		else {
			$empty_tag = true;
		}
		array_push($this->empty_tag, $empty_tag);
	}

	public function endTag($parser, $name) {
		if (!end($this->empty_tag)) {
			if ($name !== 'img' && $name !== 'br') {
				$this->data .= '</'.$name.'>';
			}
			else {
				$this->data .= '/>';
			}
		}
		array_pop($this->empty_tag);
	}

	public function dataTag($parser, $content) {
		$this->data .= htmlentities($content, ENT_QUOTES, 'UTF-8', false);
	}

	public function isAllowedTag($name) {
		return isset($this->allowed_tags[$name]);
	}

	public function isAllowedAttribute($tag, $attribute) {
		return in_array($attribute, $this->allowed_tags[$tag]);
	}

	public function isResource($attribute) {
		return in_array($attribute, $this->protocol_attributes);
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
			&& isset($attributes['height'])
			&& isset($attributes['width'])
			&& $attributes['height'] == 1
			&& $attributes['width'] == 1;
	}
}

?>
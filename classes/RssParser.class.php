<?php

# RSS 1.0
#    specifications: http://www.scriptol.fr/rss/RSS-1.0.html
#    example: http://planete-jquery.fr/feed.php?type=rss
# RSS 2.0
#    specifications: http://www.scriptol.fr/rss/RSS-2.0.html
#    example: http://wordpress.org/news/feed/
# Atom
#    specifications: http://www.ietf.org/rfc/rfc4287
#    example: http://feeds.feedburner.com/blogspot/MKuf

class RssParser {

	protected $filter;

	public function __construct($filter) {
		$this->filter = $filter;
	}

	public function readFeed($content, $url) {

			# Do not display errors
		$errors = libxml_use_internal_errors();
		libxml_use_internal_errors(true);

			# Load document
		$d = new DOMDocument();
		if (!$d->loadXML($content)) { return false; }

			# Determine document syntax
		$syntax = $d->documentElement->localName;
		if ($syntax == 'RDF') {
			$feed = $this->parseRss10($d, $url);
		}
		elseif ($syntax == 'rss') {
			$feed = $this->parseRss20($d, $url);
		}
		elseif ($syntax == 'feed') {
			$feed = $this->parseRssAtom($d, $url);
		}
		else {
			return false;
		}

			# Reset old configuration
		libxml_use_internal_errors($errors);

		return $feed;
	}

	protected function parseRss10($d, $url) {

		$title = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
		$items = array();

			# Retrieve channel
		$channel = $d->getElementsByTagName('channel')->item(0);
		if (!$channel) { return false;  }

			# Retrieve title
		$node = $channel->getElementsByTagName('title')->item(0);
		if ($node) {
			$title = htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
		}

			# Retrieve URL
		$link = $this->getURL($channel, $url);

			# Retrieve links
		foreach ($d->getElementsByTagName('item') as $i) {
			$item = array(
				'title' => NULL,
				'link' => NULL,
				'content' => NULL,
				'date' => time()
			);
				# Retrieve title
			$node = $i->getElementsByTagName('title')->item(0);
			if ($node) {
				$item['title'] = htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
			}
				# Retrieve link
			$item['link'] = $this->getURL($i, $link);
				# Retrieve content
			$node = $i->getElementsByTagName('encoded')->item(0);
			if ($node) {
				$item['content'] = $this->filter->execute(
					$node->nodeValue,
					$item['link']
				);
			}
			else {
				$node = $i->getElementsByTagName('description')->item(0);
				if ($node) {
					$item['content'] = $this->filter->execute(
						$node->nodeValue,
						$item['link']
					);
				}
			}
				# Retrieve date
			$node = $i->getElementsByTagName('date')->item(0);
			if ($node) {
				$item['date'] = strtotime($node->nodeValue);
			}
			$items[] = $item;
		}

		return array(
			'title' => $title,
			'url' => $url,
			'link' => $link,
			'items' => $items
		);
	}

	protected function parseRss20($d, $url) {

		$title = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
		$items = array();

			# Retrieve channel
		$channel = $d->getElementsByTagName('channel')->item(0);
		if (!$channel) { return false;  }

			# Retrieve title
		$node = $channel->getElementsByTagName('title')->item(0);
		if ($node) {
			$title = htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
		}

			# Retrieve URL
		$link = $this->getURL($channel, $url);

			# Retrieve links
		foreach ($channel->getElementsByTagName('item') as $i) {
			$item = array(
				'title' => NULL,
				'link' => NULL,
				'content' => NULL,
				'date' => time()
			);
				# Retrieve title
			$node = $i->getElementsByTagName('title')->item(0);
			if ($node) {
				$item['title'] = htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
			}
				# Retrieve link
			$item['link'] = $this->getURL($i, $link);
				# Retrieve content
			$node = $i->getElementsByTagName('encoded')->item(0);
			if ($node) {
				$item['content'] = $this->filter->execute(
					$node->nodeValue,
					$item['link']
				);
			}
			else {
				$node = $i->getElementsByTagName('description')->item(0);
				if ($node) {
					$item['content'] = $this->filter->execute(
						$node->nodeValue,
						$item['link']
					);
				}
			}
				# Retrieve date
			$node = $i->getElementsByTagName('pubDate')->item(0);
			if ($node) {
				$item['date'] = strtotime($node->nodeValue);
			}
			$items[] = $item;
		}

		return array(
			'title' => $title,
			'url' => $url,
			'link' => $link,
			'items' => $items
		);
	}

	protected function parseRssAtom($d, $url) {

		$title = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
		$items = array();

			# Retrieve title
		$node = $d->getElementsByTagName('title')->item(0);
		if ($node) {
			$title = htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
		}

			# Retrieve URL
		$link = $this->getURL($d, $url);

			# Retrieve links
		foreach ($d->getElementsByTagName('entry') as $i) {
			$item = array(
				'title' => NULL,
				'link' => NULL,
				'content' => NULL,
				'date' => time()
			);
				# Retrieve title
			$node = $i->getElementsByTagName('title')->item(0);
			if ($node) {
				$item['title'] = htmlspecialchars($node->nodeValue, ENT_QUOTES, 'UTF-8');
			}
				# Retrieve link
			$item['link'] = $this->getURL($i, $link);
				# Retrieve content
			$node = $i->getElementsByTagName('content')->item(0);
			if ($node) {
				$item['content'] = $this->filter->execute(
					$node->nodeValue,
					$item['link']
				);
			}
			else {
				$node = $i->getElementsByTagName('summary')->item(0);
				if ($node) {
					$item['content'] = $this->filter->execute(
						$node->nodeValue,
						$item['link']
					);
				}
			}
				# Retrieve date
			$node = $i->getElementsByTagName('updated')->item(0);
			if ($node) {
				$item['date'] = strtotime($node->nodeValue);
			}
			$items[] = $item;
		}

		return array(
			'title' => $title,
			'url' => $url,
			'link' => $link,
			'items' => $items
		);
	}

	protected function getURL($e, $url) {
		$node = $e->getElementsByTagName('origLink')->item(0);
		if ($node && filter_var($node->nodeValue, FILTER_VALIDATE_URL)) {
			return $node->nodeValue;
		}
		foreach ($e->getElementsByTagName('link') as $l) {
			if ($l->parentNode != $e) { continue; }
			if (filter_var($l->nodeValue, FILTER_VALIDATE_URL)) {
				return $l->nodeValue;
			}
			elseif ($l->hasAttribute('href')
				&& filter_var($l->getAttribute('href'), FILTER_VALIDATE_URL)
				&& (!$l->hasAttribute('type')
					|| $l->getAttribute('type') == 'text/html')
			) {
				return $l->getAttribute('href');
			}
		}
		return $url;
	}

	public static function importOPML($data) {

		$urls = array();

			# Do not display errors
		$errors = libxml_use_internal_errors();
		libxml_use_internal_errors(true);

			# Load document
		$d = new DOMDocument();
		if (!$d->loadXML($data)) { return false; }

		foreach ($d->getElementsByTagName('outline') as $o) {
			$urls[] = array(
				'title' => $o->getAttribute('text'),
				'url' => $o->getAttribute('xmlUrl'),
				'link' => $o->getAttribute('htmlUrl')
			);
		}

			# Reset old configuration
		libxml_use_internal_errors($errors);

		return $urls;
	}

	public static function exportOPML($urls) {

			# Do not display errors
		$errors = libxml_use_internal_errors();
		libxml_use_internal_errors(true);

			# Load document
		$d = new DOMDocument();

		$opml = $d->appendChild($d->createElement('opml'));
		$opml->setAttribute('version', '2.0');

		$head = $opml->appendChild($d->createElement('head'));
		$head->appendChild($d->createElement('dateCreated', date(DATE_RFC822)));

		$body = $opml->appendChild($d->createElement('body'));

		foreach ($urls as $u) {
			$o = $body->appendChild($d->createElement('outline'));
			$o->setAttribute('text', $u['title']);
			$o->setAttribute('type', 'rss');
			$o->setAttribute('xmlUrl', $u['url']);
			$o->setAttribute('htmlUrl', $u['link']);
		}

		$xml = $d->saveXML();

			# Reset old configuration
		libxml_use_internal_errors($errors);

		return $xml;

	}
}

?>
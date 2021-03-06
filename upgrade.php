<?php

if (!isset($config)) {
	exit;
}

function strict_lower($a, $b) {
	$ea = explode('.', $a);
	$eb = explode('.', $b);
	for ($i=0; $i < count($ea); $i++) { 
		if (!isset($eb[$i])) { $eb[$i] = 0; }
		$na = intval($ea[$i]);
		$nb = intval($eb[$i]);
		if ($na > $nb) { return false; }
		if ($na < $nb) { return true; }
	}
	return false;
}

if (strict_lower($config['version'], '1.0')) {

	$config['user']['cookie'] = array();
	$config['auto_tag'] = true;

	$links = Text::unhash(get_file(FILE_LINKS));
	foreach ($links as $k => $l) {
		$links[$k]['comment'] = NULL;
		$links[$k]['tags'] = array();
	}
	update_file(FILE_LINKS, Text::hash($links));

}

if (strict_lower($config['version'], '1.1')) {

	$config['twitter'] = NULL;
	$feeds = Text::unhash(get_file(FILE_FEEDS));
	foreach ($feeds as $k => $l) {
		$feeds[$k]['type'] = 'rss';
		$feeds[$k]['params'] = array();
	}
	update_file(FILE_FEEDS, Text::hash($feeds));

}

if (strict_lower($config['version'], '1.2')) {

	$feeds = Text::unhash(get_file(FILE_FEEDS));
	foreach ($feeds as $k => $l) {
		$feeds[$k]['content'] = Manager::T_RSS;
		$feeds[$k]['comment'] = Manager::T_EMPTY;
		$feeds[$k]['filter_html'] = '';
	}
	update_file(FILE_FEEDS, Text::hash($feeds));

}

if (strict_lower($config['version'], '1.3')) {

	$config['open_new_tab'] = false;

}

if (strict_lower($config['version'], '2.1')) {

	$tags = array();
	$links = Text::unhash(get_file(FILE_LINKS));
	foreach ($links as $id => $l) {
		foreach ($l['tags'] as $t) {
			$tags[$t][] = $id;
		}
	}
	update_file(FILE_TAGS, Text::hash($tags));

}

$settings = new Settings();
if ($config['url_rewriting']) { $settings->url_rewriting(); }
$settings->save();

header('Content-Type: text/html; charset=utf-8');
die('Mise à jour effectuée avec succès ! Raffraichissez cette page pour accéder à Creaky Coot.');

?>
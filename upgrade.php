<?php

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

$settings = new Settings();
if ($config['url_rewriting']) { $settings->url_rewriting(); }
$settings->save();

header('Content-Type: text/html; charset=utf-8');
die('Mise à jour effectuée avec succès ! Raffraichissez cette page pour accéder à Creaky Coot.');

?>
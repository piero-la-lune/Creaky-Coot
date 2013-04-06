<?php

$settings = new Settings();
$config['user']['cookie'] = array();
$settings->save();

$links = Text::unhash(get_file(FILE_LINKS));
foreach ($links as $k => $l) {
	$links[$k]['comment'] = NULL;
	$links[$k]['tags'] = array();
}
update_file(FILE_LINKS, Text::hash($links));

header('Content-Type: text/html; charset=utf-8');
die('Mise à jour effectuée avec succès ! Raffraichissez cette page pour accéder à Creaky Coot.');

?>
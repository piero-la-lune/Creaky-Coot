<?php

$settings = new Settings();
$config['user']['cookie'] = array();
$settings->save();

header('Content-Type: text/html; charset=utf-8');
die('Mise à jour effectuée avec succès ! Raffraichissez cette page pour accéder à Creaky Coot.');

?>
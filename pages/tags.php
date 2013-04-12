<?php

$manager = Manager::getInstance();

$tags = $manager->getTags();
sort($tags);

$title = Trad::T_TAGS;

$content = '<p class="p-list-tags">'.Manager::tagsList($tags).'</p>';

?>
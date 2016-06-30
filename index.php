<?php

require __DIR__ . '/vendor/autoload.php';

Firebus\Logger::log('action=start', 'main');

/** 
 * @todo: Handle Accept headers with mutliple elements
 * @see https://en.wikipedia.org/wiki/Content_negotiation
 */
$output = new Firebus\Output($_SERVER['HTTP_ACCEPT']);
$textStatistics = new DaveChild\TextStatistics\TextStatistics();

if (!empty($_GET['apiUrl'])
	&& !empty($_GET['category'])) {
	Firebus\Logger::log('action=catread apiUrl=' . $_GET['apiUrl'] . ' category="' . $_GET['category'] . '"','main');
	$catread = new Firebus\CategoryReadability($_GET['apiUrl'], $_GET['category'], $output, $textStatistics);
	$catread->execute();
} else {
	Firebus\Logger::log('action=usage message="missing parameters"', 'main');
	$output->helpMessage();
}

Firebus\Logger::log('action=end', 'main');
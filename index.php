<?php

require __DIR__ . '/vendor/autoload.php';

/** 
 * @todo: Handle Accept headers with mutliple elements
 * @see https://en.wikipedia.org/wiki/Content_negotiation
 */
$output = new Firebus\Output($_SERVER['HTTP_ACCEPT']);
$textStatistics = new DaveChild\TextStatistics\TextStatistics();

if (!empty($_GET['apiUrl'])
	&& !empty($_GET['category'])) {
	$catread = new Firebus\CategoryReadability($_GET['apiUrl'], $_GET['category'], $output, $textStatistics);
	$catread->execute();
} else {
	$output->helpMessage();
}
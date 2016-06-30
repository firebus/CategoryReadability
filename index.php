<?php

require __DIR__ . '/vendor/autoload.php';

/** 
 * @todo: Handle Accept headers with mutliple elements
 * @see https://en.wikipedia.org/wiki/Content_negotiation
 */
$output = new Firebus\Output($_SERVER['HTTP_ACCEPT']);
$catread = new Firebus\CategoryReadability();

$output->helpMessage();
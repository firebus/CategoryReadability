<?php

namespace Firebus;

/**
 * Output class, also stubby. The idea is to eventually change output based on Accept header.
 * But if I get that far, I'm going to be way too lazy to make this an abstract class with subclasses based on the Accept header...
 */
class Output {
	
	/** @var string $contentType */
	private $contentType;
	
	public function __construct($contentType) {
		$this->$contentType = $contentType;
	}
	
	/**
	 * Usage instructions for this applicaiton
	 */
	public function helpMessage() {
		$this->header();
		echo "<p>Generate a list of mediawiki articles from a specific category, ordered from least to most readable, "
		. "according to the Flesch-Kincaid Reading Ease score of the first paragraph of each article<p>\n"
		. "<p>Query parameters:</p>\n"
		. "<dl>\n"
		. "<dt>apiUrl</dt><dd>API URL excluding protocol, e.g. en.wikipedia.org/w/api.php</dd>\n"
		. "<dt>category</dt><dd>Category to score, excluding initial 'Category:', e.g. Hypertext_Transfer_Protocol</dd>\n"
		. "</dl>\n";
		$this->footer();
	}
	
	public function articleList($category, $pages) {
		$this->header();
		echo "<h2>Results for $category</h2>\n"
			. "<ol>\n";
		foreach ($pages as $page) {
			echo "<li><a href=\"{$page->fullurl}\">{$page->title}</a> {$page->score}</li>\n";
		}
		echo "</ol>\n";
		$this->footer();
	}

	private function header() {
		echo "<html>\n<body>\n<h1>Category Readability</h1>\n";
	}

	private function footer() {
		echo "</body>\n</html>\n";
	}
}
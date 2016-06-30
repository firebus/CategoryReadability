<?php

namespace Firebus;

/**
 * Procedural logic for category-readability here. Based on the parameters passed in, we're going to:
 * - Query a mediawiki API endpoint for the list of pages in a category
 * - Get a parse representation of each page
 * - Pull out the first paragraph from that parsed representation
 * - Score the first paragraph for readability using the Flesch-Kincaid Reading Ease algorithm from https://github.com/DaveChild/Text-Statistics
 * - Send an array of pages to Output
 */
class CategoryReadability {

	/** @var string $apiUrl without protocol we're going to assume https:// */
	private $apiUrl;
	
	/** @var string $category without leading 'Category:' */
	private $category;
	
	/** @var Output */
	private $output;
	
	public function __construct($apiUrl, $category, $output) {
		$this->apiUrl = $apiUrl;
		$this->category = $category;
	}
	
	public function execute() {
		$scoredPages = array();
		$pages = $this->getPagesForCategory();
		foreach ($pages as $page) {
			$parsedPage = $this->getParsedPage($page['id']);
			$firstGraph = $this->getFirstGraph($parsedPage);
			$score = $this->scoreText($firstGraph);
			$scoredPages[$page['id']] = array(
				'title' => $page['title'],
				'url' => $page['url'],
				'score' => $page['score'],
			);
		}
		
		$this->output->articleList($scoredPages);
	}
	
	private function getPagesForCategory() {}
	private function getParsedPage($pageId) {}
	private function getFirstGraph($html) {}
	private function scoreText($text) {}
}
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
		$this->output = $output;
	}
	
	public function execute() {
		$scoredPages = array();
		$pages = $this->getPagesForCategory();
		foreach ($pages as &$page) {
			$extract = $this->getExtract($page->pageid);
			$page->score = $this->scoreText($extract);
		}
		
		$this->output->articleList($pages);
	}
	
	/**
	 * Make an API call to Categorymembers
	 * @todo sanity apiUrl and category
	 * @return array of pages
	 */
	private function getPagesForCategory() {
		$url = "https://{$this->apiUrl}?action=query&list=categorymembers&cmtitle=Category:{$this->category}&cmlimit=50&cmtype=page&format=json";		
		$pages = json_decode(file_get_contents($url));
		return $pages->query->categorymembers;
	}
	
	private function getExtract($pageId) {}
	private function scoreText($text) {}
}
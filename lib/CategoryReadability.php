<?php

namespace Firebus;

/**
 * Turn the requested category into a list of articles sorted by readability
 */
class CategoryReadability {
	
	const CMLIMIT = 5;

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

	/**
	 * Procedural logic goes here.
 	 * # Query the mediawiki categorymembers API for the list of pages in a category
	 * # Query the mediawiki extracts API for an extract from each page
	 * # Score the extract for readability using the Flesch-Kincaid Reading Ease algorithm via https://github.com/DaveChild/Text-Statistics
	 * # Sort the pages by score
	 * # Send the pages to Output
	 */
	public function execute() {
		$pages = $this->getPagesForCategory();
		foreach ($pages as &$page) {
			$extract = $this->getExtract($page->pageid);
			$page->score = $this->scoreText($extract);
		}

		$sortedPages = $this->sortPages($pages);
		$this->output->articleList($sortedPages);
	}
	
	/**
	 * Make an API call to categorymembers
	 * @todo sanitize apiUrl and category
	 * @return array of page object
	 */
	private function getPagesForCategory() {
		$url = "https://{$this->apiUrl}?action=query&list=categorymembers&cmtitle=Category:{$this->category}"
			. "&cmlimit=" . self::CMLIMIT . "&cmtype=page&format=json";
		$pages = json_decode(file_get_contents($url));
		return $pages->query->categorymembers;
	}
	
	/**
	 * Make an API call to extracts
	 * @note I tried setting multiple pageids and setting exlimit=max, but MW refused to give me more than one extract
	 * @param string $pageid
	 * @return string
	 */
	private function getExtract($pageid) {
		$url = "https://{$this->apiUrl}?action=query&prop=extracts&exsentences=10&explaintext&pageids=$pageid"
			. "&format=json";
		$extracts = json_decode(file_get_contents($url));
		return $extracts->query->pages->$pageid->extract;
	}
	
	private function scoreText($text) {}
	private function sortPages($pages) {}
}
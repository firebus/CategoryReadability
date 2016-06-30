<?php

namespace Firebus;

/**
 * Turn the requested category into a list of articles sorted by readability
 */
class CategoryReadability {
	
	const CMLIMIT = 50;

	/** @var string $apiUrl without protocol we're going to assume https:// */
	private $apiUrl;
	
	/** @var string $category without leading 'Category:' */
	private $category;
	
	/** @var Output */
	private $output;
	
	/** @var TextStatistics */
	private $textStatistics;
	
	public function __construct($apiUrl, $category, $output, $textStatistics) {
		$this->apiUrl = $apiUrl;
		$this->category = $category;
		$this->output = $output;
		$this->textStatistics = $textStatistics;
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
		try {
			$pages = $this->getPagesForCategory();
			Logger::log('action="get pages"' . 'count=' . count($pages), __METHOD__);
		} catch (\Exception $e) {
			Logger::log('action="get pages" error="no results" url=' . $e->getMessage(), __METHOD__, 'ERROR');
			$this->output->error($this->category, 'Error getting pages for category');
		}
		
		try {
			$pages = $this->getPageUrls($pages);
			Logger::log('action="get urls" count=' . count($pages), __METHOD__);
		} catch (\Exception $e) {
			Logger::log('action="get urls" error="no results" url=' . $e->getMessage(), __METHOD__, 'ERROR');
			$this->output->error($this->category, 'Error getting urls for pages');
		}

		// Performance could be improved with rolling curl...
		foreach ($pages as &$page) {
			try {
				$extract = $this->getExtract($page->pageid);
				$page->score = $this->scoreText($extract);
				Logger::log('action=score pageid=' . $page->pageid . ' extract="' . addcslashes($extract, '"') . '" score=' . $page->score, __METHOD__);
			} catch (\Exception $e) {
				Logger::log('action="get urls" error="no results" url=' . $e->getMessage(), __METHOD__, 'WARNING');
				$page->score = 'error';
			}
		}

		usort($pages, array('Firebus\CategoryReadability', 'sortPages'));
		$this->output->articleList($this->category, $pages);
	}
	
	/**
	 * Make an API call to categorymembers
	 * @todo sanitize apiUrl and category
	 * @return array of page objects
	 * @throws UnexpectedValueException
	 */
	private function getPagesForCategory() {
		$url = "https://{$this->apiUrl}?action=query&list=categorymembers&cmtitle=Category:{$this->category}"
			. "&cmlimit=" . self::CMLIMIT . "&cmtype=page&format=json";
		$pages = json_decode(file_get_contents($url));
		if (is_null($pages) 
			|| !isset($pages->query->categorymembers)) {
			throw new \UnexpectedValueException($url);
		}
		return $pages->query->categorymembers;
	}

	/**
	 * Make an API call to info to decorate each page with a URL
	 * @param array $pages of page objects
	 * @return array of page objects
	 * @throws UnexpectedValueException
	 */
	private function getPageUrls($pages) {
		$pageIds = array();
		foreach ($pages as $page) {
			$pageIds[] = $page->pageid;
		}
		$url = "https://{$this->apiUrl}?action=query&pageids=" . implode("|", $pageIds) . "&prop=info&inprop=url&formatversion=2"
			. "&format=json";
		$pages = json_decode(file_get_contents($url));
		if (is_null($pages) 
			|| !isset($pages->query->pages)) {
			throw new \UnexpectedValueException($url);
		}
		return $pages->query->pages;
	}
	
	/**
	 * Make an API call to extracts
	 * @note I tried setting multiple pageids and setting exlimit=max, but MW refused to give me more than one extract
	 * @note Spec requires that we get the first paragraph, instead I'm getting the first 10 sentences.
	 * @param string $pageid
	 * @return string
	 * @throws UnexpectedValueException
	 */
	private function getExtract($pageid) {
		$url = "https://{$this->apiUrl}?action=query&prop=extracts&exsentences=10&explaintext&pageids=$pageid"
			. "&format=json";
		$extracts = json_decode(file_get_contents($url));
		if (is_null($extracts) 
			|| !isset($extracts->query->pages->$pageid->extract)) {
			throw new \UnexpectedValueException($url);
		}
		return $extracts->query->pages->$pageid->extract;
	}
	
	/**
	 * Calculate Flesh-Kincaid Reading Ease using external library
	 * Note, higher scores are easier to read.
	 * @see https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests#Flesch_reading_ease
	 * @see https://github.com/DaveChild/Text-Statistics
	 * @param type $text
	 * @return float
	 */
	private function scoreText($text) {
		return $this->textStatistics->fleschKincaidReadingEase($text);		
	}

	/**
	 * Sort pages by score ascending (i.e. hardest to read/lowest score first)
	 * @param array $pages array of page objects
	 * @return array
	 */
	private function sortPages($pageA, $pageB) {
		if ($pageA->score == $pageB->score) {
			return 0;
		}
		return ($pageA->score < $pageB->score) ? -1 : 1;
	}
}
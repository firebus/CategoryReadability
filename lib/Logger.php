<?php

namespace Firebus;

/**
 * Just a stub for future expansion, or to make it easier to swap in other logging libraries later
 */
class Logger {
	
	/**
	 * Write a message to the error log.
	 * 
	 * @param type $message
	 * @param type $brackets typically __METHOD__
	 * @param type $severity
	 */
	static public function log($message, $brackets = "", $severity = "INFO") {
		error_log("$severity: [$brackets] $message");
	}
}
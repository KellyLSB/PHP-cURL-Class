<?php
/**
 * cURL Class
 * 
 * Helps you use the cURL functions within PHP
 * to manipulate remote URL's through the PHP cURL extension
 * 
 * @author Kelly Becker
 * @since July 23rd 2012
 */
class cURL {

	private $userAgent = 'cURL Class by Kelly Becker';
	private $cookieJar = '/tmp/cookies.txt';
	private $postData = array();
	private $saveFile = false;
	private $opts = array();

	/**
	 * Set any post data to the postData fields
	 */
	public function data($array = array(), $val = null) {
		if(is_string($array) && is_string($val))
			return $this->postData[$array] = $val;
		else if(!is_array($array) || empty($array))
			throw new Exception("cURL::data() expects an array with key value pairs.");

		return $this->postData = array_merge($this->postData, $array);
	}

	/**
	 * Allow setting of a custom userAgent
	 */
	public function agent($agent = null) {
		if(is_null($agent))
			throw new Exception("cURL::agent() expects a string.");

		$this->userAgent = $agent;
	}

	/**
	 * Allow to specify a file save location on disk
	 */
	public function save($loc = null, $overwrite = false) {
		if(is_null($loc))
			throw new Exception("cURL::save() expects a file path of where to save the requested file.");

		if(!is_file($loc))
			throw new Exception("cURL::save() File `$loc` already exists. Please run cURL::save($file, true) to contine.");

		if(!is_dir(dirname($loc)))
			throw new Exception("cURL::save() Directory `" . dirname($loc) . "` does not exist.");

		if(!is_writable(dirname($loc)))
			throw new Exception("cURL::save() Directory `" . dirname($loc) . "` is not writable by PHP.");

		$this->saveFile = $loc;
	}

	/**
	 * Allow to specify where to save cURL cookies
	 */
	public function cookieJar($loc) {
		if(is_null($loc))
			throw new Exception("cURL::cookieJar() expects a file path of where to save the requested file.");

		if(!is_dir(dirname($loc)))
			throw new Exception("cURL::cookieJar() Directory `" . dirname($loc) . "` does not exist.");

		if(!is_writable(dirname($loc)))
			throw new Exception("cURL::cookieJar() Directory `" . dirname($loc) . "` is not writable by PHP.");

		$this->cookieJar = $loc;
	}

	/**
	 * Allow setting additional options
	 */
	public function setOpt($opt = null, $val = null) {
		if(empty($opt) || empty($val)) return false;

		$this->opts[$opt] = $val;
		return true;
	}

	/**
	 * Unset any previously set options
	 */
	public function unsetOpt($opt = null) {
		if(empty($opt)) return false;

		unset($this->opts[$opt]);
		return true;
	}

	/**
	 * Get the file with cURL
	 */
	public function fetch($url = null, $cookies = false, $reset = true) {
		if(is_null($url))
			throw new Exception("cURL::fetch() expects a url to fetch.");

		// Initialize cURL and set the URL to Fetch
		$cURL = curl_init($url);

		// Set the user agent to the predefined string
		curl_setopt($cURL, CURLOPT_USERAGENT, $this->userAgent . ' / ' . phpversion());
		//die(print_r($this->postData));

		// If any post data exists pass it through
		if(!empty($this->postData)) curl_setopt($cURL, CURLOPT_POSTFIELDS, $this->postData);

		// Handle cookies during this transfer?
		if($cookies) {
			curl_setopt($cURL, CURLOPT_COOKIEJAR, $this->cookieJar);
			curl_setopt($cURL, CURLOPT_COOKIEFILE, $this->cookieJar);
		}

		// Return the results on execution
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);

		// Set any additional opts and override any previously set options
		if(!empty($this->opts)) foreach($this->opts as $opt => $val)
			curl_setopt($cURL, $opt, $val);

		// Run the request
		$results = curl_exec($cURL);

		// Exit/Shutdown cURL
		curl_close($cURL);

		// Save file if requested above
		if($this->saveFile)
			file_put_contents($this->saveFile, $results);

		// Reset the the data that could be considered specific to this transaction
		if($reset) {
			$this->postData = array();
			$this->saveFile = false;
		}

		// Return the results
		return $results;
	}

	/**
	 * Reset the curl class
	 */
	public function reset() {
		$this->userAgent = 'cURL Class by Kelly Becker';
		$this->cookieJar = '/tmp/cookies.txt';
		$this->postData = array();
		$this->saveFile = false;
		$this->opts = array();
	}

}
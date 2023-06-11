<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\functions\validators.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Abstract class for fetching data from api
*/
abstract class ApiFetch
{
	protected $baseUrl;
	
	/*
		Validates and sets up the common url for fetch requests
		
		@param string $baseUrl common url
	*/
	function __construct($baseUrl)
	{
		if(checkNonEmptyString($baseUrl) == false || validateUrl($baseUrl) == false)
			throw new Exception(exceptionString('ApiFetch', '__construct', 'baseUrl', 'must be non-empty url string'));
		
		$this->baseUrl = $baseUrl;
	}
	
	/*
		Function to be implemented, operates on $baseUrl and provided directory
		
		@param string $directory provides the rest of url that is specific
		@return should null if the data couldn't be fetched, otherwise array created from decoded data
	*/
	abstract protected function fetch($directory);
}

?>
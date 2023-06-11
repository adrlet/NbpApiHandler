<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\classes\ApiFetch.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	ApiFetch implementation for http api
	Handles http request with specific options
*/

class HttpFetch extends ApiFetch
{
	protected $httpOptions;
	
	/*
		Validates and sets up the common url and context options for fetch requests
		
		@param string $baseUrl common url
		@param array $httpOptions http options for stream context
	*/
	function __construct($baseUrl, $httpOptions = null)
	{
		parent::__construct($baseUrl);
		
		if(is_null($httpOptions) == false)
		{
			if(is_array($httpOptions) == false)
				throw new Exception(exceptionString('HttpFetch', '__construct', 'httpOptions', 'must be array'));
			
			$this->httpOptions = array('http' => array());
			
			foreach($httpOptions as $httpOption => $httpValue)
				$this->httpOptions['http'][$httpOption] = $httpValue;
		}
	}
	
	/*
		Implementation of ApiFetch fetch function
		
		@param string $directory provides the rest of url that is specific
		@return null if the data couldn't be fetched, otherwise array created from decoded data
	*/
	protected function fetch($directory)
	{
		$context = stream_context_create($this->httpOptions);
		
		$httpHandle = fopen($this->baseUrl.$directory, 'r', false, $context);
		if($httpHandle == false)
			return null;
		
		$data = stream_get_contents($httpHandle);
		fclose($httpHandle);
		
		if($data == false)
			return null;
		
		return json_decode($data, true);
	}
}

?>
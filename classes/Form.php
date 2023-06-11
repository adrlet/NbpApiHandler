<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\functions\formTools.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Abstract form implementing initial input processing
*/

abstract class Form
{
	protected $method;
	
	/*
		Sets up method of form
		
		@param string $method either POST or GET
	*/
	function __construct($method = null)
	{
		if(is_null($method))
			$method = 'POST';
		
		if(is_string($method) == false || strlen($method) == 0)
			throw new Exception(genericExceptionString('NbpHtmlForm', '__construct', 'method', 'string'));
		
		if(strcmp($method, 'POST') != 0 && strcmp($method, 'GET') != 0)
			throw new Exception(exceptionString('Form', '__construct', 'method', 'must be either GET or POST'));
		
		$this->method = $method;
	}
	
	/*
		Retrieves POST or GET inputs based on provided keys
		Marks not received keys as err
		
		@param array $keys array of input name's coming with the form submit
		@return array of either $key value  or $keyErr with 'required' string
	*/
	protected function receiveData($keys)
	{
		if(checkNonEmptyArray($keys) == false)
			throw new Exception(genericExceptionString('Form', 'receiveData', '$keys', 'array'));
		
		$data = array();
		
		if($this->method == 'POST')
		{
			foreach($keys as $key)
			{
				if(empty($_POST[$key]) == false)
					$data[$key] = prepareFormInput($_POST[$key]);
				else
					$data[$key.'Err'] = 'Wymagane';
			}
		}
		
		if($this->method == 'GET')
		{
			foreach($keys as $key)
			{
				if(empty($_GET[$key]) == false)
					$data[$key] = prepareFormInput($_GET[$key]);
				else
					$data[$key.'Err'] = 'Wymagane';
			}
		}
		
		return $data;
	}
	
	/*
		To implement function for complete form processing
		
		@return should return boolean whether processing succeeded
	*/
	abstract public function processForm();
	
	/*
		To implement function for printing html form
		
		@return string html form
	*/
	abstract public function toHtml();
}

?>
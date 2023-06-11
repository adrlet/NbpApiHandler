<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\cfg\cfg.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\HttpFetch.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	HttpFetch implementation for specific NbpApi
	Provides required info for fetch and handling of various requests
*/

final class NbpApiFetch extends HttpFetch
{
	private $directoryTableA;
	private $directoryTableB;
	
	/*
		Validates and sets up the common url and context options for fetch requests
		Sets up the url for each table type
	*/
	function __construct()
	{
		parent::__construct($this->getBaseUrl(), $this->getHttpOptions());
		
		$this->directoryTableA = $this->getTableDirectory('A');
		$this->directoryTableB = $this->getTableDirectory('B');
	}
	
	/*
		Request to fetch tables on common url
		
		@return array of table types
	*/
	public function getTables()
	{
		return $this->fetchTables('');
	}
	
	/*
		Request to fetch tables for n-latest records
		
		@Param int $topCount specifies the n-latest records
		@return array of table types
	*/
	public function getTablesByTopCount($topCount = 1)
	{
		return $this->fetchTable($this->getTopCountDirectory($topCount));
	}
	
	/*
		Request to fetch tables for specific date periods
		If only startDate is provided then fetches records for this day
		If startDate and endDate is provided then fetches records between them
		
		@Param string $startDate specifies the start date, must be Y-m-d format
		@Param string $endDate specifies the end date, must be Y-m-d format
		@return array of table types
	*/
	public function getTablesByDate($startDate = null, $endDate = null)
	{
		return $this->fetchTable($this->getDateDirectory($startDate, $endDate));
	}
	
	/*
		Issues fetch for each table type for specific directory
		
		@Param string $directory specifies the rest of url for fetch
		@return array of table types
	*/
	private function fetchTables($directory)
	{
		$tableA = $this->fetch($this->directoryTableA.$directory);
		$tableB = $this->fetch($this->directoryTableB.$directory);
		
		return array('A' => $tableA, 'B' => $tableB);
	}
	
	/*
		Reads base url from config file
		
		@return string base url
	*/
	protected function getBaseUrl()
	{
		global $cfg_api_url;
		global $cfg_api_directory_exchange;
		
		return $cfg_api_url.$cfg_api_directory_exchange;
	}
	
	/*
		Reads table directory for specified type from config file
		
		@param string $tableName the tableType
		@return string directory of specific table type
	*/
	private function getTableDirectory($tableName)
	{
		global $cfg_api_directory_table;
		global $cfg_api_tables;
		
		if(checkNonEmptyString($cfg_api_directory_table) == false)
			throw new Exception(genericExceptionString('NbpApiFetch', 'getTableDirectory', 'cfg_api_directory_table', 'string'));
		
		if(checkNonEmptyArray($cfg_api_tables) == false)
			throw new Exception(genericExceptionString('NbpApiFetch', 'getTableDirectory', 'cfg_api_tables', 'array'));
		
		if(array_key_exists($tableName, $cfg_api_tables) == false)
			throw new Exception(exceptionString('NbpApiFetch', 'getTableDirectory', 'tableName', 'is invalid key for cfg_api_tables'));
		
		return $cfg_api_directory_table.$cfg_api_tables[$tableName].'/';
	}
	
	/*
		Reads top count directory for specified top count number from config file
		
		@param int $topCount is the top count number
		@return string directory of specified top count
	*/
	private function getTopCountDirectory($topCount)
	{
		global $cfg_api_directory_last;
		
		if(checkNonEmptyString($cfg_api_directory_table) == false)
			throw new Exception(genericExceptionString('NbpApiFetch', 'getTopCountDirectory', 'cfg_api_directory_last', 'string'));
		
		if(checkInt($topCount) == false || $topCount < 1)
			throw new Exception(exceptionString('NbpApiFetch', 'getTopCountDirectory', 'topCount', 'must be positive integer'));
		
		return $cfg_api_directory_last.$topCount.'/';
	}
	
	/*
		Creates directory for specified date range
		
		@param string $startDateString is the start date of records, Y-m-d format
		@param string $endDateString is the end date of records, Y-m-d format
		@return string directory of date range
	*/
	private function getDateDirectory($startDateString, $endDateString = null)
	{
		if(checkNonEmptyString($startDateString) == false)
			throw new Exception(genericExceptionString('NbpApiFetch', 'getDateDirectory', 'startDateString', 'string'));
		
		$startDate = strtotime($startDateString);
		if($startDate == false)
			throw new Exception(exceptionString('NbpApiFetch', 'getDateDirectory', 'startDateString', 'must represent date'));
		
		$dateDirectory = date('Y-m-d', $startDate).'/';
		
		if(is_null($endDateString) == false)
		{
			if(is_string($endDateString) == false || empty($endDateString))
				throw new Exception(genericExceptionString('NbpApiFetch', 'getDateDirectory', 'endDateString', 'string'));
			
			$endDate = strtotime($endDateString);
			if($endDate == false)
				throw new Exception(exceptionString('NbpApiFetch', 'getDateDirectory', 'endDateString', 'must represent date'));
		
			$dateDirectory .= date('Y-m-d', $endDate).'/';
		}
		
		return $dateDirectory;
	}
	
	/*
		Provides options for http fetch
		
		@return array of custom options for http fetch
	*/
	private function getHttpOptions()
	{
		return array(
			'method' => 'GET',
			'header' => 'Accept: application/json',
		);
	}
}

?>
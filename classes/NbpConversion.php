<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\cfg\cfg.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Represents sole conversion between currencies
	Is related losely to NbpExchangeTable and NbpRate
*/

class NbpConversion extends Entity
{
	public $idSourceCurrency;
	public $sourceValue;
	public $idDestinationCurrency;
	public $destinationValue;
	
	/*
		Sets up attributes of NbpConversion
		
		@param class Database $database class database object connected to DBMS
		@param int $id id of record representing Conversion in table
		@param int $idSourceCurrency is the id of source currency (NbpRate)
		@param string $sourceValue is the value of source currency
		@param int $idDestinationCurrency is the id of destination currency (NbpRate)
		@param string $destinationValue is the value of source currency converted to destination currency
		@param string $tableName table holding record of NbpConversion
	*/
	function __construct($database, $id = null, $idSourceCurrency = null, $sourceValue = null, $idDestinationCurrency = null, $destinationValue = null, $tableName = null)
	{
		parent::__construct($database, $tableName);
		
		$this->idSourceCurrency = $idSourceCurrency;
		$this->sourceValue = $sourceValue;
		$this->idDestinationCurrency = $idDestinationCurrency;
		$this->destinationValue = $destinationValue;
	}
	
	/*
		Basic CRUD create
		
		@return boolean true if succedeed, false otherwise
	*/
	public function create()
	{
		$database = $this->database;
		
		$result = $database->prepareInsert($this->tableName, array('idSourceCurrency', 'sourceValue', 'idDestinationCurrency', 'destinationValue'), 'idid');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->idSourceCurrency, $this->sourceValue, $this->idDestinationCurrency, $this->destinationValue));
		$database->prepareClose();
		
		return true;
	}
	
	/*
		Basic CRUD read
		
		@return boolean true if succedeed, false otherwise
	*/
	public function read()
	{
		$database = $this->database;
		
		$result = $database->prepareSelect($this->tableName, array('id', 'idSourceCurrency', 'sourceValue', 'idDestinationCurrency', 'destinationValue'), 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$data = $database->prepareExec(array($this->id));
		$database->prepareClose();
		
		if(empty($data))
			return false;
		
		$data = $data[0];
		
		$this->id = $data[0];
		$this->idSourceCurrency = $data[1];
		$this->sourceValue = $data[2];
		$this->idDestinationCurrency = $data[3];
		$this->destinationValue = $data[4];
		
		return true;
	}
	
	/*
		Basic CRUD update
		
		@return boolean true if succedeed, false otherwise
	*/
	public function update()
	{
		$database = $this->database;
		
		$result = $database->prepareUpdate($this->tableName, array('idSourceCurrency', 'sourceValue', 'idDestinationCurrency', 'destinationValue'), 'idid', 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->idSourceCurrency, $this->sourceValue, $this->idDestinationCurrency, $this->destinationValue));
		$database->prepareClose();
		
		return true;
	}
	
	/*
		Basic CRUD delete
		
		@return boolean true if succedeed, false otherwise
	*/
	public function delete()
	{
		$database = $this->database;
		
		$result = $database->prepareDelete($this->tableName, 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->id));
		$database->prepareClose();
		
		return true;
	}
	
	/*
		Attempt to recent currency conversions within given limit
		
		@param int $limitUp is the left argument of mysql limit statement
		@param int $limitUp is the right argument of mysql limit statement
		@return array of NbpConversion objects
	*/
	public function getConversions($limitUp = null, $limitDown = null)
	{
		$conversionArray = array();
		$database = $this->database;
		
		$result = $database->prepareSelect($this->tableName, array('id', 'idSourceCurrency', 'sourceValue', 'idDestinationCurrency', 'destinationValue'), order: 'id', limitUp: $limitUp, limitDown: $limitDown);
		if($result == NULL)
			return conversionArray;
		
		$conversions = $database->prepareExec(null);
		$database->prepareClose();
		
		foreach($conversions as $conversion)
			$conversionArray[] = new NbpConversion($this->database, $conversion[0], $conversion[1], $conversion[2], $conversion[3], $conversion[4]);
			
		return $conversionArray;
	}
	
	/*
		Returns html representation of given conversions
		
		@param class Database $database class database object connected to DBMS
		@param array $conversions array of NbpConversion objects to be html'ized
		@return string html code
	*/
	static public function toHtml($database, $conversions)
	{
		$query = '<div class="NbpConversion"><p>Ostatnie konwersje</p><ul>';
		
		if(is_null($database))
			throw new Exception(exceptionString('NbpConversion', 'toHtml', 'database', 'must be class database object'));
		
		foreach($conversions as $conversion)
		{
			$rateSource = new NbpRate($database, $conversion->idSourceCurrency);
			$rateSource->read();
			
			$rateDest = new NbpRate($database, $conversion->idDestinationCurrency);
			$rateDest->read();
				
			$query .= '<li>';
			$query .= '<div>'.$rateSource->code.' => '.$rateDest->code.'</div>';
			$query .= '<div>'.$conversion->sourceValue.' => '.$conversion->destinationValue.'</div>';
			$query .= '</li>';
		}
		
		return $query;
	}
	
	/*
		Provides common NbpRate table name
		
		@return string table name
	*/
	protected function getTableName()
	{
		global $cfg_database_table_conversion;
		
		if(checkNonEmptyString($cfg_database_table_conversion) == false)
			throw new Exception(genericExceptionString('NbpConversion', 'getTableName', 'cfg_database_table_conversion', 'string'));
		
		return $cfg_database_table_conversion;
	}
}

?>
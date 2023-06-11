<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\cfg\cfg.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Represents sole rate contained within fetched table
	Is related strictly to NbpExchangeTable
*/

class NbpRate extends Entity
{
	public $idExchangeRates;
	public $currency;
	public $code;
	public $mid;
	
	/*
		Sets up attributes of NbpRate
		
		@param class Database $database class database object connected to DBMS
		@param int $id id of record representing Rate in table
		@param int $idExchangeRates is the id of parent table
		@param string $currency represents human readable string identifying currency
		@param string $code represents the code of currency
		@param double $mid represent rate in PLN of currency
		@param string $tableName table holding record of NbpRate
	*/
	function __construct($database, $id = null, $idExchangeRates = null, $currency = null, $code = null, $mid = null, $tableName = null)
	{
		parent::__construct($database, $id, $tableName);
		
		$this->idExchangeRates = $idExchangeRates;
		$this->currency = $currency;
		$this->code = $code;
		$this->mid = $mid;
	}
	
	/*
		Basic CRUD create
		
		@return boolean true if succedeed, false otherwise
	*/
	public function create()
	{
		$database = $this->database;
		
		$result = $database->prepareInsert($this->tableName, array('idExchangeRates', 'currency', 'code', 'mid'), 'issd');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->idExchangeRates, $this->currency, $this->code, $this->mid));
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
		
		$result = $database->prepareSelect($this->tableName, array('id', 'idExchangeRates', 'currency', 'code', 'mid'), 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$data = $database->prepareExec(array($this->id));
		$database->prepareClose();
		
		if(empty($data))
			return false;
		
		$data = $data[0];
		
		$this->id = $data[0];
		$this->idExchangeRates = $data[1];
		$this->currency = $data[2];
		$this->code = $data[3];
		$this->mid = $data[4];
		
		return true;
	}
	
	/*
		Basic CRUD update
		
		@return boolean true if succedeed, false otherwise
	*/
	public function update()
	{
		$database = $this->database;
		
		$result = $database->prepareUpdate($this->tableName, array('currency', 'code', 'mid'), 'ssd', 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->id));
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
		Attempt to read rate by its code and parent table
		
		@param string $code code of currency
		@param int $tableId id of the parent NbpExchangeTable
		@return boolean true if succedeed, false otherwise
	*/
	public function readByCodeAndTableId($code, $tableId)
	{
		$database = $this->database;
		
		$result = $database->prepareSelect($this->tableName, array('id', 'idExchangeRates', 'currency', 'code', 'mid'), 'code = ? and idExchangeRates = ? ', 'si');
		if($result == false)
			return false;
		
		$data = $database->prepareExec(array($code, $tableId));
		$database->prepareClose();
		
		if(empty($data))
			return false;
		
		$data = $data[0];
		
		$this->id = $data[0];
		$this->idExchangeRates = $data[1];
		$this->currency = $data[2];
		$this->code = $data[3];
		$this->mid = $data[4];
		
		return true;
	}
	
	/*
		Provides common NbpRate table name
		
		@return string table name
	*/
	protected function getTableName()
	{
		global $cfg_database_table_rate;
		
		if(checkNonEmptyString($cfg_database_table_rate) == false)
			throw new Exception(genericExceptionString('NbpRate', 'getTableName', 'cfg_database_table_rate', 'string'));
		
		return $cfg_database_table_rate;
	}
}

?>
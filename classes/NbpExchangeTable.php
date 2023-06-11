<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\cfg\cfg.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpRate.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Class representing fetched table of currency records
*/

class NbpExchangeTable extends Entity
{
	public $tableType;
	public $no;
	public $effectiveDate;

	/*
		Sets up attributes of NbpExchangeTable
		
		@param class Database $database class database object connected to DBMS
		@param int $id id of record representing ExchangeTable in table
		@param string $tableType is the type of fetched table
		@param string $no represents the order relative to other tables
		@param string $effectiveDate represents the date for which table holds rates
		@param string $tableName table holding record of ExchangeTable
	*/
	function __construct($database, $id = null, $tableType = null, $no = null, $effectiveDate = null, $tableName = NULL)
	{
		parent::__construct($database, $id, $tableName);
		
		$this->tableType = $tableType;
		$this->no = $no;
		$this->effectiveDate = $effectiveDate;
	}
	
	/*
		Basic CRUD create
		
		@return boolean true if succedeed, false otherwise
	*/
	public function create()
	{
		$database = $this->database;
		
		$result = $database->prepareInsert($this->tableName, array('tableType', 'no', 'effectiveDate'), 'sss');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->tableType, $this->no, $this->effectiveDate));
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
		
		$result = $database->prepareSelect($this->tableName, array('id', 'tableType', 'no', 'effectiveDate'), 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$data = $database->prepareExec(array($this->id));
		$database->prepareClose();
		
		if(empty($data))
			return false;
		
		$data = $data[0];
		
		$this->id = $data[0];
		$this->tableType = $data[1];
		$this->no = $data[2];
		$this->effectiveDate = $data[3];
		
		return true;
	}
	
	/*
		Basic CRUD update
		
		@return boolean true if succedeed, false otherwise
	*/
	public function update()
	{
		$database = $this->database;
		
		$result = $database->prepareUpdate($this->tableName, array('tableType', 'no', 'effectiveDate'), 'sss', 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->tableType, $this->no, $this->effectiveDate, $this->id));
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
		
		$this->deleteRates();
		
		$result = $database->prepareDelete($this->tableName, 'id = ? ', 'i');
		if($result == false)
			return false;
		
		$database->prepareExec(array($this->id));
		$database->prepareClose();
		
		return true;
	}
	
	/*
		Attempt to read most recent table of given type from database
		
		@param string $type represents table type
		@return boolean true if succedeed, false otherwise
	*/
	public function readLatestByType($type)
	{
		if(checkNonEmptyString($type) == false)
			throw new Exception(genericExceptionString('NbpExchangeTable', 'readLatestByType', 'type', 'string'));
		
		$database = $this->database;
		
		$result = $database->prepareSelect($this->tableName, array('id', 'tableType', 'no', 'effectiveDate'), 'tableType = ? ', 's', 'effectiveDate');
		if($result == false)
			return false;
		
		$data = $database->prepareExec(array($type));
		$database->prepareClose();
		
		if(empty($data))
			return false;
		
		$data = $data[0];
		
		$this->id = $data[0];
		$this->tableType = $data[1];
		$this->no = $data[2];
		$this->effectiveDate = $data[3];
		
		return true;
	}
	
	/*
		Attempt to read all of rates related to the table
		
		@return array of NbpRate objects
	*/
	public function readRates()
	{
		$database = $this->database;
		$nbpRate = new NbpRate($database);
		$rateArray = array();
		
		$result = $database->prepareSelect($nbpRate->tableName, array('id', 'idExchangeRates', 'currency', 'code', 'mid'), 'idExchangeRates = ? ', 'i');
		if($result == false)
			return $rateArray;
		
		$rates = $database->prepareExec(array($this->id));
		$database->prepareClose();
		if(empty($rates))
			return $rateArray;
		
		foreach($rates as $rate)
			$rateArray[] = new NbpRate($database, $rate[0], $rate[1], $rate[2], $rate[3], $rate[4]);
		
		return $rateArray;
	}
	
	/*
		Attempt to delete all of rates related to the table
		
		@return boolean true if succedeed, false otherwise
	*/
	public function deleteRates()
	{
		$database = $this->database;
		$nbpRate = new NbpRate($database);
		
		$result = $database->prepareDelete($nbpRate->tableName, 'idExchangeRates = ? ', 'i');
		if($result == false)
			return $result;
		
		$database->prepareExec(array($this->id));
		$database->prepareClose();
		
		return true;
	}
	
	/*
		Returns html representation of record
		Will fetch related rates if not provided
		
		@param array $rates contains array of prefetched NbpRate object
		@return string html code
	*/
	public function toHtml($rates = null)
	{
		if(is_null($rates))
			$rates = $this->readRates();
		
		if(is_array($rates) == false || empty($rates))
			throw new Exception(genericExceptionString('NbpExchangeTable', 'toHtml', 'rates', 'array'));
		
		$query = '<div class="NbpExchangeTable"><p>Aktualizacja z '.$this->effectiveDate.'</p><ul>';
		
		foreach($rates as $rate)
		{
			$query .= '<li><div>'
			.$rate->currency.'</div><div class="code">'
			.$rate->code.'</div><div>'
			.$rate->mid.'</div></li>';
		}
		
		$query .= '</ul></div>';
		
		return $query;
	}
	
	/*
		Provides common NbpExchangeTable table name
		
		@return string table name
	*/
	protected function getTableName()
	{
		global $cfg_database_table_exchange;
		
		if(checkNonEmptyString($cfg_database_table_exchange) == false)
			throw new Exception(genericExceptionString('NbpExchangeTable', 'getTableName', 'cfg_database_table_exchange', 'string'));
		
		return $cfg_database_table_exchange;
	}
}

?>
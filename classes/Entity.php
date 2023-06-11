<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	CRUD is interface of methods for basic class <=> table communication
*/
interface CRUD
{
	/*
		Stands for insert of class record to table
	*/
	public function create();
	
	/*
		Stands for select class record from table
	*/
	public function read();
	
	/*
		Stands for update of class record
	*/
	public function update();
	
	/*
		Stands for delete class record from table
	*/
	public function delete();
}

/*
	Abstract class that describes class related with database table
	Provides basic means required for database querying
*/
abstract class Entity implements CRUD
{
	public $id;
	
	protected $database;
	protected $tableName;
	
	/*
		Sets up database for querying, id of entity and table name of class table-equivalent
		
		@param class Databse $database class database object connected to DBMS
		@param int $id id of record representing entity in table
		@param string $tableName holding record of entity
	*/
	function __construct($database, $id = null, $tableName = null)
	{
		if(is_null($database))
			throw new Exception(exceptionString('Entity', '__construct', 'database', 'must be class database object'));
		
		if(is_null($tableName))
			$tableName = $this->getTableName();
		
		$this->id = $id;
		$this->database = $database;
		$this->tableName = $tableName;
	}
	
	/*
		This function should return the common name of table representing entity
		
		@return string table name of entity
	*/
	abstract protected function getTableName();
}

?>
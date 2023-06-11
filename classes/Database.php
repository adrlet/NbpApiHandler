<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Database class oriented for objective mysqli prepared statements
	Holds connection to DBMS
	Allows using prepared select, insert, update and delete with multiple executions
	Provides simple query mechanism for not-prepared statements
*/

class Database
{
	public $databaseName;
	
	protected $databaseConnection;
	protected $stmt;
	protected $stmtIn;
	protected $stmtOut;
	
	/*
		Connects to database using provided arguments
		
		@param string $host is the address or name of host of DBMS
		@param string $login is the name for database profile
		@param string $pass is the password for database profile
		@param string $database is the name of the database in DBMS
		@param int $port is the port of DBMS
	*/
	function __construct($host, $login, $pass, $database, $port)
	{
		if(checkNonEmptyString($host) == false)
			throw new Exception(genericExceptionString('Database', '__construct', 'host', 'string'));
		
		if(checkNonEmptyString($login) == false)
			throw new Exception(genericExceptionString('Database', '__construct', 'login', 'string'));
		
		if(is_null($pass) || is_string($pass) == false /*|| empty($pass)*/)
			throw new Exception(exceptionString('Database', '__construct', 'pass', 'must be string'));
		
		if(checkNonEmptyString($database) == false)
			throw new Exception(genericExceptionString('Database', '__construct', 'database', 'string'));
		
		if(checkInt($port) == false)
			throw new Exception(exceptionString('Database', '__construct', 'port', 'must be int'));
		
		$this->databaseConnection = new mysqli($host, $login, $pass, $database, $port);
		$this->databaseName = $database;
	}
	
	/*
		Closes the connection to DBMS
	*/
	function __destruct()
	{
		$this->databaseConnection->close();
	}
	
	/*
		Starts the prepared query for select operation
		
		@param string $table is the name of table within database
		@param array $keys is the array of table attributes to fetch
		@param string $where is the string describing in mysql format where statement without where keyword
		@param string $whereTypes is the string describing mysqli prepared statement data types for $where
		@param string $order is the string describing the table name for order statement
		@param string $desc specifies whether order should be desc or asc
		@param int $limitUp is the left number for limit statement of mysql
		@param int $limitDown is the right number for limit statement of mysql
		@return boolean true if statement is valid, false if it is invalid
	*/
	public function prepareSelect($table, $keys, $where=null, $whereTypes = null, $order=null, $desc = true, $limitUp = null, $limitDown = null)
	{
		if(checkNonEmptyString($table) == false)
			throw new Exception(genericExceptionString('Database', 'prepareSelect', 'table', 'string'));
		
		if(checkNonEmptyArray($keys) == false)
			throw new Exception(genericExceptionString('Database', 'prepareSelect', 'keys', 'array'));
		
		$query = 'SELECT '.implode(',', $keys).
		' FROM `'.$table.'`';
		
		$whereLen = 0;
		if(is_null($where) == false)
		{
			if(is_string($where) == false || empty($where))
				throw new Exception(genericExceptionString('Database', 'prepareSelect', 'where', 'string'));
			
			if(checkNonEmptyString($whereTypes) == false)
				throw new Exception(exceptionString('Database', 'prepareSelect', 'whereTypes', 'must be non-empty string for non null where'));
			
			$query .= ' WHERE '.$where;
			$whereLen = strlen($whereTypes);
		}
		
		if(is_null($order) == false)
		{
			if(is_string($order) == false || empty($order))
				throw new Exception(genericExceptionString('Database', 'order', 'where', 'string'));
			
			$query .= ' ORDER BY '.$order.' '.($desc ? 'DESC' : 'ASC');
		}
		
		if(is_null($limitUp) == false)
		{
			if(is_int($limitUp) == false || $limitUp < 1)
				throw new Exception(exceptionString('Database', 'prepareSelect', 'limitUp', 'must be positive int'));
			
			$query .= ' LIMIT '.$limitUp;
			
			if(is_null($limitDown) == false)
			{
				if(is_int($limitDown) == false || $limitDown < 1)
					throw new Exception(exceptionString('Database', 'prepareSelect', 'limitDown', 'must be positive int'));
				
				$query .= ','.$limitDown;	
			}
		}
			
		return $this->prepareStatement($query, $whereTypes, $whereLen, count($keys));
	}
	
	/*
		Starts the prepared query for insert operation
		
		@param string $table is the name of table within database
		@param array $keys is the array of table attributes to insert
		@param string $types is the string describing mysqli prepared statement data types for $keys
		@return boolean true if statement is valid, false if it is invalid
	*/
	public function prepareInsert($table, $keys, $types)
	{
		if(checkNonEmptyString($table) == false)
			throw new Exception(genericExceptionString('Database', 'prepareInsert', 'table', 'string'));
		
		if(checkNonEmptyArray($keys) == false)
			throw new Exception(genericExceptionString('Database', 'prepareInsert', 'keys', 'array'));
		
		if(checkNonEmptyString($types) == false)
			throw new Exception(genericExceptionString('Database', 'prepareInsert', 'types', 'string'));
		
		$query = 'INSERT INTO `'.$table.'` ('.
		implode(',', $keys).
		') VALUES ('.
		str_repeat('?,', count($keys)-1).'?'.
		')';
		
		return $this->prepareStatement($query, $types, count($keys));
	}
	
	/*
		Starts the prepared query for update operation
		
		@param string $table is the name of table within database
		@param array $keys is the array of table attributes to update
		@param string $types is the string describing mysqli prepared statement data types for $keys
		@param string $where is the string describing in mysql format where statement without where keyword
		@param string $whereTypes is the string describing mysqli prepared statement data types for $where
		@return boolean true if statement is valid, false if it is invalid
	*/
	public function prepareUpdate($table, $keys, $types, $where, $whereTypes)
	{
		if(checkNonEmptyString($table) == false)
			throw new Exception(genericExceptionString('Database', 'prepareUpdate', 'table', 'string'));
		
		if(checkNonEmptyArray($keys) == false)
			throw new Exception(genericExceptionString('Database', 'prepareUpdate', 'keys', 'array'));
		
		if(checkNonEmptyString($types) == false)
			throw new Exception(genericExceptionString('Database', 'prepareUpdate', 'types', 'string'));
		
		if(checkNonEmptyString($where) == false)
			throw new Exception(genericExceptionString('Database', 'prepareUpdate', 'where', 'string'));
		
		if(checkNonEmptyString($whereTypes) == false)
			throw new Exception(genericExceptionString('Database', 'prepareUpdate', 'whereTypes', 'string'));
		
		foreach($keys as $key => $val)
			$keys[$key] = $val.' = ?';
		
		$query = 'UPDATE `'.$table.
		'` SET '.implode(',', $keys).
		' WHERE '.$where;
		
		return $this->prepareStatement($query, $types.$whereTypes, count($keys)+strlen($whereTypes));
	}
	
	/*
		Starts the prepared query for delete operation
		
		@param string $table is the name of table within database
		@param string $where is the string describing in mysql format where statement without where keyword
		@param string $whereTypes is the string describing mysqli prepared statement data types for $where
		@return boolean true if statement is valid, false if it is invalid
	*/
	public function prepareDelete($table, $where, $whereTypes)
	{
		if(checkNonEmptyString($table) == false)
			throw new Exception(genericExceptionString('Database', 'prepareDelete', 'table', 'string'));
		
		if(checkNonEmptyString($where) == false)
			throw new Exception(genericExceptionString('Database', 'prepareDelete', 'where', 'string'));
		
		if(checkNonEmptyString($whereTypes) == false)
			throw new Exception(genericExceptionString('Database', 'prepareDelete', 'whereTypes', 'string'));
		
		$query = 'DELETE FROM `'.$table.
		'` WHERE '.$where;
		
		return $this->prepareStatement($query, $whereTypes, strlen($whereTypes)); 
	}
	
	/*
		Executes prepared statement for input data
		Should be preceded by one of four prepare functions
		For no more excecutions prepareClose shall be used
		
		@param array $in is the array of data to be inserted, array must be indexed from 0, with the same order as provided in prepare functions
		@return array of fetched data, array is empty if prepared statement doesn't fetch data
	*/
	public function prepareExec($in = null)
	{
		if(is_null($in) == false)
		{
			if(is_array($in) == false)
				throw new Exception(exceptionString('Database', 'prepareExec', 'in', 'must be array'));
			
			$i = 0;
			foreach($in as $val)
				$this->stmtIn[$i++] = $val;
		}
			
		$this->stmt->execute();
		
		$result = array();
		if(is_null($this->stmtOut) == false)
		{
			$i = 0;
			while($this->stmt->fetch())
			{
				$result[] = array();
				foreach($this->stmtOut as $val)
					$result[$i][] = $val;
				$i++;
			}
		}
		
		return $result;
	}
	
	/*
		Ends the prepared statement
	*/
	public function prepareClose()
	{
		$this->stmt->close();
		
		$this->stmt = null;
		$this->stmtIn = null;
		$this->stmtOut = null;
	}
	
	/*
		Executes mysql query and retrieves data
		
		@param string $query is string describing mysql query
		@return data if query queries for data
	*/
	public function query($query)
	{
		if(checkNonEmptyString($query))
			throw new Exception(genericExceptionString('Database', 'queryDatabase', 'query', 'string'));
		
		return $this->databaseConnection->query($query);
	}
	
	/*
		Attemps a prepared statement for given query
		
		@param string $query is string describing mysql query for prepared statement
		@param string $types is string describing mysql types for placehordes in $query
		@param int $inNum is the number of data inputs that statement requires
		@param int $outNum is the number of data outputs that statement provides
		@return boolean true if statement is valid, otherwise false
	*/
	protected function prepareStatement($query, $types, $inNum = 0, $outNum = 0)
	{	
		$stmt = $this->databaseConnection->prepare($query);
		if($stmt == false)
			return false;
		
		$this->stmt = $stmt;
		
		if($inNum > 0)
		{
			$valuesIn = array();
			
			// older versions
			/*
			$valRefs = [];
			for($i = 0; $i < $inNum; $i++)
			{
				$valuesIn[] = 0;
				$valRefs[$i] = & $valuesIn[$i];
			}
			*/
			
			// newer version
			for($i = 0; $i < $inNum; $i++)
				$valuesIn[] = 0;
		
			$this->stmtIn = & $valuesIn;
			
			// older versions
			//call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $valRefs));
			
			// newer versions
			$stmt->bind_param($types, ...$valuesIn);
		}
		
		if($outNum > 0)
		{
			$valuesOut = array();
			
			// older versions
			/*
			$valRefs = [];
			for($i = 0; $i < $outNum; $i++)
			{
				$valuesOut[] = 0;
				$valRefs[$i] = & $valuesOut[$i];
			}
			*/
			
			// newer version
			for($i = 0; $i < $outNum; $i++)
				$valuesOut[] = 0;
		
			$this->stmtOut = & $valuesOut;
			
			// older versions
			//call_user_func_array(array($stmt, 'bind_result'), array_merge(array($types), $valRefs));
			
			// newer versions
			$stmt->bind_result(...$valuesOut);
		}
		
		return true;
	}
}

?>
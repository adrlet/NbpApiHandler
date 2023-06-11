<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\cfg\cfg.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\Database.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';

/*
	Extends Database, to provide connection info from config
*/

final class NbpApiDatabase extends Database
{
	/*
		Call parent constructor for data received from config
	*/
	function __construct()
	{
		parent::__construct($this->getHost(), $this->getLogin(), $this->getPassword(),
							$this->getDatabase(), $this->getPort());
	}
	
	/*
		Gets host name or address of DBMS from config file
		
		@return string host name or address
	*/
	protected function getHost()
	{
		global $cfg_database_host;
		
		return $cfg_database_host;
	}
	
	/*
		Gets login for database account from config file
		
		@return string login
	*/
	protected function getLogin()
	{
		global $cfg_database_login;
		
		return $cfg_database_login;
	}
	
	/*
		Gets password for database account from config file
		
		@return password
	*/
	protected function getPassword()
	{
		global $cfg_database_pass;
		
		return $cfg_database_pass;
	}
	
	/*
		Gets database name from config file
		
		@return string database name
	*/
	protected function getDatabase()
	{
		global $cfg_database_name;
		
		return $cfg_database_name;
	}
	
	/*
		Gets port of DBMS from config file
		
		@return int port
	*/
	protected function getPort()
	{
		global $cfg_database_port;
		
		return $cfg_database_port;
	}
}

?>
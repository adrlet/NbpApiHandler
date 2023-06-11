<?php
	// nbp_api
	$cfg_api_url = 'http://api.nbp.pl/api/';
	$cfg_api_directory_exchange = 'exchangerates/';
	$cfg_api_directory_table = 'tables/';
	$cfg_api_directory_last = 'last/';
	$cfg_api_tables = array('A'=>'A', 'B'=>'B');
	
	// Database
	$cfg_database_host = "localhost";
	$cfg_database_port = 3306;
	$cfg_database_name = "nbp";
	$cfg_database_login = "root";
	$cfg_database_pass = "";
	
	// Tables
	$cfg_database_table_exchange = 'exchange_rates';
	$cfg_database_table_rate = 'rates';
	$cfg_database_table_conversion = 'conversions';
?>
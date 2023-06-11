<?php

/*
	Performs validation for assumed non-empty string
	
	@param string $str checked string
	@return boolean true if $str is non-empty string, false otherwise
*/
function checkNonEmptyString($str)
{
	return (is_null($str) == false && is_string($str) && empty($str) == false);
}

/*
	Performs validation for assumed non-empty array
	
	@param array $arr checked array
	@return boolean true if $str is non-empty array, false otherwise
*/
function checkNonEmptyArray($arr)
{
	return (is_null($arr) == false && is_array($arr) && empty($arr) == false);
}

/*
	Performs validation for assumed int value
	
	@param int $int checked integer
	@return boolean true if $int is integer, false otherwise
*/
function checkInt($int)
{
	return (is_null($int) == false && is_int($int));
}

/*
	Allows to generate prepared exception description string containing class, function, variable and custom description
	for faulty variable
	
	@param string $class is the class name of method, should be '' for functions
	@param string $function is either name of function or method
	@param string $variable is the name of fault variable
	@param string $type is the type for faulty variable
	@return string space separated string of arguments and common issue description
*/

function genericExceptionString($class, $function, $variable, $type)
{	
	$desc = '';
	
	switch($type)
	{
	case 'string':
		$desc = 'must be non-empty string';
	case 'array':
		$desc = 'must be non-empty array';
	}
	
	return exceptionString($class, $function, $variable, $desc);
}

/*
	Allows to generate exception description string containing class, function, variable and custom description
	for faulty variable
	
	@param string $class is the class name of method, should be '' for functions
	@param string $function is either name of function or method
	@param string $variable is the name of fault variable
	@param string $desc is description of problem
	@return string space separated string of arguments
*/
function exceptionString($class, $function, $variable, $desc)
{
	return $class.' '.$function.' '.$variable.' '.$desc;
}

?>
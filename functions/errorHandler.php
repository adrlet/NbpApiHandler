<?php

/*
	Dummy for ending http request handling and sending response 500
*/
function errorHandler500($errno, $errstr, $errfile, $errline)
{
	http_response_code(500);
	echo 'Internal Server Error 500';
	die();
    
    return true;
}

?>
<?php

/*
	Performs validation of string whether it represents valid URL
	
	@param string $url represents url
	@return boolean true is string represents URL, false otherwise
*/
function validateUrl($url)
{
	if(preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url))
		return true;
	
	return false;
}

?>
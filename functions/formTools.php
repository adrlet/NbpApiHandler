<?php

/*
	Performs basic processing of form input
	
	@param string $input form input value
	@return string trimmed (redundant white char removal), slash stripped (added slash to special chars with \)
	and html characters encoded (converting html related chars to representable format, which prevents injection)
*/
function prepareFormInput($input)
{
	return htmlspecialchars(stripslashes(trim($input)));
}

?>
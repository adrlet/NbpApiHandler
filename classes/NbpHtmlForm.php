<?php

require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'\classes\Form.php';

class NbpHtmlForm extends Form
{
	protected $method;
	
	public $sourceCode;
	public $sourceValue;
	public $destinationCode;
	public $destinationValue;
	
	private $sourceCodeErr;
	private $sourceValueErr;
	private $destinationCodeErr;
	
	/*
		Function for converting value from source currency to destination currency without rounding
		
		@param Class NbpRate $rateSource is the NbpRate object
		@param double $value is the source currency value
		@param Class NbpRate $rateDestination is the NbpRate object
		@return double $value converted to rateDestination currency
	*/
	static public function convertRates($rateSource, $value, $rateDestination)
	{
		return $value*$rateSource->mid/$rateDestination->mid;
	}
	
	/*
		Handles submitted data, marks invalid input and saves them
		Calculates the currency conversion
		Return value should be used for further processing
		
		@param array $rates is the array of NbpRate objects
		@return boolean true if everything went fine, otherwise false
	*/
	public function processForm($rates = null)
	{
		if($_SERVER['REQUEST_METHOD'] == $this->method)
		{
			$data = $this->receiveData(array('source_code', 'currency_value', 'destination_code', 'submit'));
			// Compare submit input with submit button of form
			if(isset($data['submitErr']) || $data['submit'] != 'przewalutuj')
				return false;
			
			// Check whether required inputs are existing, set Err otherwise
			if(isset($data['source_code']))
				$this->sourceCode = $sourceCode = $data['source_code'];
			else
				$this->sourceCodeErr = $data['source_codeErr'];
			
			if(isset($data['currency_value']))
				$sourceValue = $data['currency_value'];
			else
				$this->sourceValueErr = $data['currency_valueErr'];
			
			if(isset($data['destination_code']))
				$this->destinationCode = $destinationCode = $data['destination_code'];
			else
				$this->destinationCodeErr = $data['destination_codeErr'];
			
			if(isset($this->sourceCodeErr) || isset($this->sourceValueErr) || isset($this->destinationCodeErr))
				return false;
			
			// Check whether inputs are valid for our form
			if(is_string($sourceCode) == false || strlen($sourceCode) != 3)
				$this->sourceCodeErr = 'Wymagany 3-znakowy kod waluty';
			
			if(is_numeric($sourceValue) == false)
				$this->sourceValueErr = 'Wymagana kwota';
			else
				$this->sourceValue = round($sourceValue, 2);
			
			if(is_string($destinationCode) == false || strlen($destinationCode) != 3)
				$this->destinationCodeErr = 'Wymagany 3-znakowy kod waluty';
			
			if(isset($this->sourceCodeErr) || isset($this->sourceValueErr) || isset($this->destinationCodeErr))
				return false;
			
			// Check whether inputted currency code are existing
			$rateSource = null;
			foreach($rates as $rate)
			{
				if(strcmp($rate->code, $sourceCode) == 0)
				{
					$rateSource = $rate;
					break;
				}
			}
			
			if(isset($rateSource) == false)
				$this->sourceCodeErr = 'Nierozponany kod waluty';
			
			$rateDestination = null;
			foreach($rates as $rate)
			{
				if(strcmp($rate->code, $destinationCode) == 0)
				{
					$rateDestination = $rate;
					break;
				}
			}
			
			if(isset($rateDestination) == false)
				$this->destinationCodeErr = 'Nierozponany kod waluty';
			
			if(isset($this->sourceCodeErr) || isset($this->destinationCodeErr))
				return false;
			
			// Convert currency if all is fine
			$this->destinationValue = round(NbpHtmlForm::convertRates($rateSource, $sourceValue, $rateDestination), 2);
			return true;
		}
	}
	
	/*
		Returns html form for submitting input
		
		@return string html form
	*/
	public function toHtml()
	{
		$destinatonValue = (isset($this->destinationValue) ? round($this->destinationValue, 2) : null);
		
		$query = '<div class="NbpHtmlForm"><p>Konwertuj walutę</p>'.
			'<form method="'.$this->method.'" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'">'.
			
			'<label class="pointer" for="source_code">Kod waluty źródłowej</label>'.
			'<input type="text" id="source_code" name="source_code" value="'.$this->sourceCode.'">'.
			(isset($this->sourceCodeErr) ? '<label class="error" for="source_code">'.$this->sourceCodeErr.'</label>' : '').
			
			'<label class="pointer" for="currency_value">Wartość waluty bazowej</label>'.
			'<input type="numeric" id="currency_value" name="currency_value" value="'.$this->sourceValue.'">'.
			(isset($this->sourceValueErr) ? '<label class="error"  for="currency_value">'.$this->sourceValueErr.'</label>' : '').
			
			'<label class="pointer" for="destination_code">Kod waluty docelowej</label>'.
			'<input type="text" id="destination_code" name="destination_code" value="'.$this->destinationCode.'">'.
			(isset($this->destinationCodeErr) ? '<label class="error"  for="destination_code">'.$this->destinationCodeErr.'</label>' : '').
			
			'<input class="submit" type="submit" name="submit" value="przewalutuj">'.
			
			'<label class="pointer" for="destinaton_value">Wartość waluty docelowej</label>'.
			'<input type="numeric" id="destinaton_value" name="destinaton_value" value="'.$destinatonValue.'">'.
			
			'</form></div>';
		
		return $query;
	}
}

?>
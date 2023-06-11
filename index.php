<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpApiFetch.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpApiDatabase.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpExchangeTable.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpRate.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpConversion.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\classes\NbpHtmlForm.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\functions\basicExceptions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'\functions\errorHandler.php';

// Check for any exceptions
/*try {
	// Error handler - abort request
	set_error_handler('errorHandler500', E_ALL);*/
	
	// Create objects
	$fetcher = new NbpApiFetch();
	$database = new NbpApiDatabase();
	
	$tableA = new NbpExchangeTable($database);
	$tableB = new NbpExchangeTable($database);
	
	$form = new NbpHtmlForm('POST');
	
	$conversion = new NbpConversion($database);
	
	// Try to fetch tables from database
	$today = date('Y-m-d');
	$tableA->readLatestByType('A');
	$tableB->readLatestByType('B');
	
	// Check whether tables should try updating
	if($tableA->effectiveDate != $today || $tableB->effectiveDate != $today)
	{	
		// Fetch from api
		$dataTables = $fetcher->getTables();
		if(empty($dataTables['A']) || empty($dataTables['B']))
		{
			// Conditional throw for fetch failure
			throw new Exception('Data fetching failed');
		}
		
		// Retrieve data
		$dataTableA = $dataTables['A'][0];
		$dataTableB = $dataTables['B'][0];
		
		// Check if fetched tableA is newer than current tableA, if it is, insert it along with rates
		if($dataTableA['effectiveDate'] != $tableA->effectiveDate)
		{
			$tableA = new NbpExchangeTable($database, null, $dataTableA['table'], $dataTableA['no'], $dataTableA['effectiveDate']);
			$tableA->create();
			$tableA->readLatestByType('A');
			
			foreach($dataTableA['rates'] as $dataRate)
			{
				$rate = new NbpRate($database, null, $tableA->id, $dataRate['currency'], $dataRate['code'], $dataRate['mid']);
				$rate->create();
			}
		}
		
		// Check if fetched tableB is newer than current tableB, if it is, insert it along with rates
		if($dataTableB['effectiveDate'] != $tableB->effectiveDate)
		{
			$tableB = new NbpExchangeTable($database, null, $dataTableB['table'], $dataTableB['no'], $dataTableB['effectiveDate']);
			$tableB->create();
			$tableB->readLatestByType('B');
			
			foreach($dataTableB['rates'] as $dataRate)
			{
				$rate = new NbpRate($database, null, $tableB->id, $dataRate['currency'], $dataRate['code'], $dataRate['mid']);
				$rate->create();
			}
		}
	}
	
	// Prefetch both table rates
	$ratesA = $tableA->readRates();
	$ratesB = $tableB->readRates();
	
	// Try to process converison form data, if succeded, store result in database
	$formProcessed = $form->processForm(array_merge($ratesA, $ratesB));
	if($formProcessed)
	{
		$rateSource = new NbpRate($database);
		
		// Received source currency code is either in tableA or tableB
		$result = $rateSource->readByCodeAndTableId($form->sourceCode, $tableA->id);
		if($result == false)
			$result = $rateSource->readByCodeAndTableId($form->sourceCode, $tableB->id);
		
		$rateDest = new NbpRate($database);
		
		// Received destination currency code is either in tableA or tableB
		$result = $rateDest->readByCodeAndTableId($form->destinationCode, $tableA->id);
		if($result == false)
			$result = $rateDest->readByCodeAndTableId($form->destinationCode, $tableB->id);
		
		$conversion = new NbpConversion($database, null, $rateSource->id, $form->sourceValue, $rateDest->id, $form->destinationValue);
		$conversion->create();
	}
	$conversions = $conversion->getConversions(5);
	
	// Store html representation of objects
	$TableAHtml = $tableA->toHtml($ratesA);
	$TableBHtml = $tableB->toHtml($ratesB);
	
	$formHtml = $form->toHtml();
	
	$conversionsHtml = NbpConversion::toHtml($database, $conversions);
	
/*}
catch(Throwable $e) {
// If any throwable arrived, abort request
	http_response_code(500);
	echo 'Internal Server Error 500';
	die();
}*/
?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<title>Waluty</title>
	<link rel="stylesheet" href="/index.css">
</head>
<body>

<?php
	//Check validity and prints html representation of objects
	if(checkNonEmptyString($TableAHtml))
		echo $TableAHtml;
	
	if(checkNonEmptyString($TableBHtml))
		echo $TableBHtml;
	
	if(checkNonEmptyString($formHtml))
		echo $formHtml;
	
	if(checkNonEmptyString($conversionsHtml))
		echo $conversionsHtml;

?>
</body>

</html>
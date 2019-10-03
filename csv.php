<?php

$csvFile  ='./checksum.csv';

$fileHandle = fopen($csvFile, 'a');
fclose($fileHandle);

function checkIfExist_csv($fileName){

	global $csvFile;
	$fileHandle = fopen($csvFile, "r");
	while (($row = fgetcsv($fileHandle, 0, ",")) !== FALSE) {
    	if ($row[0] === $fileName){

    		fclose($fileHandle);
			return (int)$row[1];
		}
	}

	fclose($fileHandle);
	return -1 ;

}

function getChecksum_csv($fileName){

	global $csvFileContents;
	$rows = array();
	
	foreach ($csvFileContents as $line){

		$test = array();
		$test[] = str_getcsv($line);
		if ($test[0][0] === $fileName){
			
			return (int)$test[0][1];

		}	
	}
	return;
}

function replace_csv($fileToReplace, $newchecksum ){

	delete_csv($fileToReplace);
	$data[] = array($fileToReplace, $newchecksum);
	append_csv($data );


}




function read_csv($csvFileName){

	global $csvFile;
	$fileHandle = fopen($csvFile, "r");
	while (($row = fgetcsv($fileHandle, 0, ",")) !== FALSE) {

    	echo $row[0].','.$row[1].'<br>';
	}
	
}

function delete_csv($fileToDelete){

	global $csvFile;
	$fileHandle = fopen($csvFile, "r");
	while (($row = fgetcsv($fileHandle, 0, ",")) !== FALSE) {

		if ($row[0] === $fileToDelete) {

			continue;

		}

    	$rows[] = array($row[0],$row[1]);
	}

	unlink($csvFile)	;
	write_csv($csvFile,$rows);
	fclose($fileHandle);
}

function write_csv($data){
	
	global $csvFile;
	$file = fopen($csvFile, 'w');
	foreach ($data as $key => $value) {

		fputcsv($file, $value);
	}
	
	fclose($file);

}

function append_csv($data){

	global $csvFile;
	$file = fopen($csvFile, 'a');
	foreach ($data as $key => $value) {

		fputcsv($file, $value);
	}
	
	fclose($file);

}







?>
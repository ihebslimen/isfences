<?php 

//require 'vendor/autoload.php';
include 'sendmail.php';
include 'csv.php';
include 'mongodb.php';


$suspeciousFileList = "";
setupdb();
ini_set('max_execution_time', '0');
ini_set('set_time_limit', '0');
			
// --------------------------------------------------------------------------------
// UTILITY FUNCTIONS.
// OUTPUT TEXT IN SPECIFIED COLOR, CLEANING IT WITH HTMLENTITIES().
function CleanColorText($text, $color){

	$outputColor = 'black';
	$color = trim($color);
	if(preg_match('/^(red|blue|green|black)$/i', $color))
	$outputColor = $color;
	return '<span style="color:' . $outputColor . ';">' . htmlentities($text, ENT_QUOTES) . '</span>';

}

// --------------------------------------------------------------------------------
// THIS FUNCTION RECURSIVELY FINDS FILES AND PROCESSES THEM THROUGH THE SPECIFIED CALLBACK FUNCTION.
// DIFFERENT TYPES OF FILES NEED TO BE HANDLED BY DIFFERENT CALLBACK FUNCTIONS.

function find_files($path, $pattern, $callback) {

	global $suspeciousFileList ;
	$path = rtrim(str_replace("\\", "/", $path), '/') . '/'; 
	if(!is_readable($path)){

		$suspeciousFileList = $suspeciousFileList . CleanColorText($path, 'blue'). "=> Unable to open and enter directory "  . "=> Check its permissions<br>".PHP_EOL;
		return;
	}

	$dir = dir($path); 
	$entries = array(); 
	while(($entry = $dir->read()) !== FALSE) 
	$entries[] = $entry; 
	$dir->close(); 
	foreach($entries as $entry) { 

		$fullName = $path . $entry; 
		if(($entry !== '.') && ($entry !== '..') && is_dir($fullName))
		find_files($fullName, $pattern, $callback); 
		else
		if(is_file($fullName) && preg_match($pattern, $entry)) 
			call_user_func($callback, $fullName); 
	} 
} 
	
function maliciouscodesnippets($fileName) { 

	global $suspeciousFileList ;
	global $csvFile;
	if(!is_readable($fileName)){

		$suspeciousFileList = $suspeciousFileList . CleanColorText($fileName, 'blue') . " => Unable to read ". "=> Check manually its access permissions<br>".PHP_EOL;
		return;
	}

	$file = file_get_contents($fileName); //READ THE FILE 
	$oldChecksum = checkIfExist_csv($fileName);
	$newChecksum = (int)crc32($file);
	if ($oldChecksum === $newChecksum) {

		return;

	}else if($oldChecksum === -1){
		
		$data[] = array($fileName, $newChecksum);
		append_csv($data);

	}else{

		replace_csv($fileName, $newChecksum);

	}

	$suspiciousSnippets = array(
	'/eval/i',
	'/edoced_46esab/i',
	'/passthru *\(/i',
	
	'/document\.write *\(unescape *\(/i',

// THESE CAN GIVE MANY FALSE POSITIVES WHEN CHECKING WORDPRESS AND OTHER CMS.
// NONETHELESS, THEY CAN BE IMPORTANT TO FIND, ESPECIALLY BASE64_DECODE.
	'/base64_decode *\(/i',
	'/system *\(/i', 
	 

// OTHER SUSPICIOUS TEXT STRINGS
	'/web[\s-]*shell/i', // TO FIND BACKDOOR WEB SHELL SCRIPTS.
	'/c99/i', // THE NAMES OF TWO POPULAR WEB SHELLS.
	'/r57/i',
	'/wso/i',
	'/backdoor/i',
	'/inject/i',
		'/spoof/i',
	'/shell_exec/i',
	'/vuln/i',
	
	'/proxy/i',
	'/b374k/i',
	'/b3cak/i',
	'/DOCUMENT_ROOT/i',
	'/$_REQUEST/i',
	'/get_headers/i',
	'/isset/i',


// YOU COULD ADD IN THE SPACE BELOW SOME REGULAR EXPRESSIONS TO MATCH THE NAMES OF MALICIOUS DOMAINS 
// AND IP ADDRESSES MENTIONED IN YOUR GOOGLE SAFEBROWSING DIAGNOSTIC REPORT. SOME EXAMPLES:
	'/gumblar\.cn/i',
	'/martuz\.cn/i',
	'/beladen\.net/i',
	'/gooqle/i', // NOTE THIS HAS A Q IN IT.

// THESE 2 ARE THE WORDPRESS CODE INJECTION IN FRONT OF EVERY INDEX.PHP AND SOME OTHERS 
	'/_analist/i',
	'/anaiytics/i' // THE LAST ENTRY IN THE LIST MUST HAVE NO COMMA AFTER IT.
	);

foreach($suspiciousSnippets as $i) {

	if(preg_match($i, $file)) 
		
		$suspeciousFileList = $suspeciousFileList. CleanColorText($fileName, 'blue') . '=>' . CleanColorText($i, 'red') . '<br>'.PHP_EOL; 

	}

	if(!strpos($fileName,"network.php") && !strpos($fileName,"rewrite.php") && stripos($file,"RewriteRule")) 
	$suspeciousFileList = $suspeciousFileList. CleanColorText($fileName, 'blue') . "=>" . CleanColorText("RewriteRule", 'red') . 
	"=> Malicious Redirects<br>".PHP_EOL; 



	if(stripos($file, "AddHandler")) {

// THIS IS HOW THEY MAKE THE IMAGE FILES EXECUTABLE.
		$suspeciousFileList = $suspeciousFileList. CleanColorText($fileName, 'blue') . "=>" . CleanColorText('AddHandler', 'red') . 
		"=> Make Images Executable.<br>".PHP_EOL.PHP_EOL; 
// IF YOU FIND NINE ZILLION OF THESE, UNCOMMENT IT BECAUSE IT IS A PAIN TO DELETE THEM BY HAND.
// BUT CHECK THE LIST CAREFULLY FIRST TO MAKE SURE YOU REALLY WANT TO DELETE 
// ALL THE FILES AND NONE OF THEM ARE FALSE POSITIVES. 
//unlink($fileName); // THIS DELETES THE FILE WITHOUT GIVING YOU THE OPTION OF EXAMINING IT!
	} 
} 


// CALLBACK FUNCTION TO REPORT PHARMA LINK HACKS.
function pharma($fileName) { 

	global $suspeciousFileList ;
	global $csvFile;
	$oldChecksum = checkIfExist_csv($fileName);
	if($oldChecksum === -1)
		$suspeciousFileList = $suspeciousFileList. CleanColorText($fileName, 'blue') . "=>". CleanColorText('pharma hack', 'red'). "=>likely"  . ".<br>".PHP_EOL; 
} 

// CALLBACK FUNCTION TO REPORT FILES WHOSE NAMES ARE SUSPICIOUS.
function badnames($fileName) { 

	global $suspeciousFileList ;
	$oldChecksum = checkIfExist_csv($fileName);
	if($oldChecksum === -1)
		$suspeciousFileList = $suspeciousFileList. CleanColorText($fileName, 'blue') . "=>" . CleanColorText('suspicious file name', 'red') . ".<br>".PHP_EOL; 

} 

$startPath = './';

$filetypesToSearch = array
(
'/\.htaccess$/i',
'/\.php[45]?$/i',
'/\.html?$/i',
'/\.aspx?$/i',
'/\.inc$/i',
'/\.cfm$/i',
'/\.js$/i',
'/\.css$/i'
);

// FILES OR FOLDERS WITH THESE STRINGS IN THEIR *NAMES* WILL BE REPORTED AS SUSPICIOUS.
$suspiciousFileAndPathNames = array
(
// '/root/i',
// '/kit/i',
'/c99/i',
'/r57/i',
'/gifimg/i'
);

// fileNameS RELATED TO WORDPRESS PHARMA HACK, USING THE NAMING CONVENTIONS 
// DESCRIBED AT http://www.pearsonified.com/2010/04/wordpress-pharma-hack.php 
// FILES MATCHING THESE NAMES WILL BE REPORTED AS POSSIBLE PHARMA HACK FILES.
$pharmaFilenames = array
(
'/^\..*(cache|bak|old)\.php/i', // HIDDEN FILES WITH PSEUDO-EXTENSIONS IN THE MIDDLE OF THE FILENAME
'/^db-.*\.php/i',

// PERMIT THE STANDARD WORDPRESS FILES THAT START WITH CLASS-, BUT FLAG ALL OTHERS AS SUSPICIOUS.
// THE (?!) IS CALLED A NEGATIVE LOOKAHEAD ASSERTION. IT MEANS "NOT FOLLOWED BY..."

'/^class-(?!snoopy|smtp|feed|pop3|IXR|phpmailer|json|simplepie|phpass|http|oembed|ftp-pure|wp-filesystem-ssh2|wp-filesystem-ftpsockets|ftp|wp-filesystem-ftpext|pclzip|wp-importer|wp-upgrader|wp-filesystem-base|ftp-sockets|wp-filesystem-direct)\.php/i');

// --------------------------------------------------------------------------------
// FINALLY, DO THE SEARCHES, USING THE ABOVE ARRAYS AS THE STRING DATA SOURCES.

// REPORT FILES WITH SUSPICIOUS NAMES
function scanner(){

	global $suspeciousFileList;
	global $startPath;
	global $suspiciousFileAndPathNames;
	global $pharmaFilenames;
	global $filetypesToSearch;
	global $alertMessageFile;

	foreach( $suspiciousFileAndPathNames as $i)
		find_files($startPath, $i, 'badnames'); 

// REPORT FILES WITH SUSPICIOUS PHARMA-RELATED NAMES
	foreach($pharmaFilenames as $i)
		find_files($startPath, $i, 'pharma'); 

// REPORT FILES CONTAINING SUSPICIOUS CODE OR TEXT
	foreach( $filetypesToSearch as $i)
		find_files($startPath, $i, 'maliciouscodesnippets');

	if ($suspeciousFileList){

		$alertMessageFileHandler = fopen($alertMessageFile, 'a');
		fputs($alertMessageFileHandler, $suspeciousFileList);
		fclose($alertMessageFileHandler);
		$suspeciousFileList = "";
		
	}
}

//call the function if it has been more than one hour for the last scan
	


function callthescanner(){

	global $wp_scancollection;
	global $lastscan;

	$now = time();
	$client = new MongoDB\Client;
	$wordpress = $client->wordpress;
	$wp_scancollection = $wordpress->wp_scancollection;
	$results = $wp_scancollection->find(
		[],
		[ 'limit' => 1,
		  'sort' => ['time' => -1]
		]
	);
	foreach ($results as $key) {

		$lastscan = $key['time'];
	}
	
	if ( !$lastscan || (($now - $lastscan)> 10)){

		if (!$wp_scancollection) {

			$replaceresult = $wp_scancollection->insert(

				['_id' => '1' , 'time' => $now]
			);

		}else{

			$replaceresult = $wp_scancollection->replaceone(

				['_id' => '1'],
				['_id' => '1' , 'time' => $now]

			);

		}
		
		scanner();

	}else{

		$replaceresult = $wp_scancollection->replaceone(

			['_id' => '1'],
			['_id' => '1' , 'time' => $now]

		);
		
	}
	
}

callthescanner();
 			
?> 
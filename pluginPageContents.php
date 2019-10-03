
<style type="text/css">
	//<?php include 'style.css' ;?>
</style>
<?php 

//require 'vendor/autoload.php';
include 'variables.php';

function unblock($ipToUnblock){

		$client = new MongoDB\Client;
		$wordpress = $client->wordpress;
		$wp_ipcollection = $wordpress->wp_ipcollection;
		$blockedips = $wp_ipcollection->deleteOne(
			
			['ipAddress' => $ipToUnblock]
		);

	}

	function fillAlertTable(){

		global $alertMessageFile;
		
    	$alertMessageFileContents = file($alertMessageFile);
    	($alertMessageFileContents);
		$alertMessage = $alertMessage . "<tr>";
    	$alertMessage = $alertMessage . '<th class = "file">Suspecious File</th>';
   		$alertMessage = $alertMessage . '<th class = "threat">threat</th>';
   		$alertMessage = $alertMessage . '<th>Notes</th>';
  		$alertMessage = $alertMessage . "</tr>"; 

		foreach ($alertMessageFileContents as $alert) {

		 	if (! $alert) {

		 		continue;
		 	}

		 	$alertMessageArray = explode( '=>', $alert ); 		 
		 	$alertMessage = $alertMessage . "<tr>";
   			$alertMessage = $alertMessage . "<td align = 'left' class = 'file' >$alertMessageArray[0]</td>";
   			$alertMessage = $alertMessage . "<td align = 'left' class = 'threat'>$alertMessageArray[1]</td>";

   			if (! $alertMessage[2]) {

		 		continue;
		 	}

   			$alertMessage = $alertMessage . "<td align = 'left'>$alertMessageArray[2]</td>";
   			$alertMessage = $alertMessage . "</tr>"; 
   			
		}
  		
		$alertMessage = $alertMessage . "</table>" ;
		return $alertMessage ;

	}

	function fillBlockedIPTable(){

		$client = new MongoDB\Client;
		$wordpress = $client->wordpress;
		$wp_ipcollection = $wordpress->wp_ipcollection;
		$blockedips = $wp_ipcollection->find(

			[],
			[ 'sort' => ['lastAccessAttemptTime' => 1] ]

		);

		$blockedIPTabel = "";
		$blockedIPTabel = $blockedIPTabel . "<tr>";
    	$blockedIPTabel = $blockedIPTabel . '<th >Blocked IP</th>';
   		$blockedIPTabel = $blockedIPTabel . '<th >Last Access Attempt Time</th>';
   		$blockedIPTabel = $blockedIPTabel . '<th>Uncheck</th>';
  		$blockedIPTabel = $blockedIPTabel . "</tr>";
  		$numIP = 0;

		 foreach ($blockedips as $blockedip) {

		 	if (! $blockedip->ipAddress) {

		 		continue;
		 	}

		 	$numIP = $numIP + 1;
		 	$lastAccessAttemptTime = date("Y-m-d H:i:s", $blockedip->lastAccessAttemptTime);
		 	$blockedIPTabel = $blockedIPTabel . "<tr>";
   			$blockedIPTabel = $blockedIPTabel . "<td align = 'middle'>$blockedip->ipAddress</td>";
   			$blockedIPTabel = $blockedIPTabel . "<td align = 'middle'>$lastAccessAttemptTime</td>";
   			$blockedIPTabel = $blockedIPTabel . "<td align = 'middle'><form method='post' enctype='multipart/form-data'><input type='submit' class='btn' value='Unblock!'' name=$blockedip->ipAddress></form></td>";
   			$blockedIPTabel = $blockedIPTabel . "</tr>"; 
   			
		}
  		
		$blockedIPTabel = $blockedIPTabel . "</table>" ;
		return $blockedIPTabel ;

}

$table = fillBlockedIPTable();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
	foreach ($_POST as $key => $value) {

		$ipToUnblock =  str_replace('_','.',$key);
	}

	Unblock($ipToUnblock);
	$table = fillBlockedIPTable();
	
}
	
 
if(isset($_POST['Done!'])) {
    
    $alertMessageFileHandler = fopen($alertMessageFile, "w");
    ftruncate($alertMessageFileHandler, 0);
    fclose($alertMessageFileHandler);
    $alertMessage = file_get_contents($alertMessageFile);

}

?>



<div align="middle" class="wrap" style="width: 98%;height: 300px;background-color: #F6CED8;">

		<h1><?php esc_html_e("Blocked IP Address");?></h1>
		<div align = "middle" style=" height:70%;border:2px solid #ccc;font:16px/26px Georgia, Garamond, Serif;overflow:auto; background-color: #F5A9BC">
			<table  style="">
			<?php echo $table;?>
			
		</div>
		
</div>
<div align="middle" class="wrap" style="width: 98%;height: 300px;background-color: #F6CED8;">

	<h1><?php esc_html_e("Malware Scan Alert");?></h1>
	<div align = "left" style=" height:70%;border:2px solid #ccc;font:16px/26px Georgia, Garamond, Serif;overflow:auto; background-color: #F5A9BC">
		<table  style="">
		<p><?php echo fillAlertTable() ;?></p>
		

	</div>

	<form method="post" enctype="multipart/form-data">
   		 <input type="submit" class='btn' value="Done Checking!" name="Done!">
	</form>
	
</div>

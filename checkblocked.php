<?php
// require 'vendor/autoload.php';

// defin a function that check if the ip address is blocked
 
function checkIfBlocked($ipAddress){
	// Select the Blocked IP Collection
	$client = new MongoDB\Client;
	$wordpress = $client->wordpress;
	$wp_ipcollection = $wordpress->wp_ipcollection;

	// Check if the ip address is a blocked one
	$blockedip = $wp_ipcollection->findOne(

		['ipAddress' => $ipAddress]
	);

	if ( $blockedip ){

		$blockedip = $wp_ipcollection->replaceone(
			['ipAddress' => $ipAddress],
			['ipAddress' => $ipAddress,'lastAccessAttemptTime' => $now]
		);

		die("You Are Blocked From This Site !!! ");
	}
}






?>
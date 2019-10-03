<?php

/// require 'vendor/autoload.php';

function setupdb(){
	
	global $wp_scancollection;
	global $wp_ipcollection;
	global $wp_fileschecksum;

	$client = new MongoDB\Client;
	$wordpress = $client->wordpress;
	if ($wordpress) {

		return;

	}else{

		$client = new MongoDB\Client;
		$wordpress = $client->wordpress;
		$result = $wordpress->createCollection('wp_scancollection');
 		$wp_scancollection = $wordpress->wp_scancollection;
		$result = $wordpress->createCollection('wp_ipcollection');
		$wp_ipcollection = $wordpress->wp_ipcollection;
		
	}
}





  ?>
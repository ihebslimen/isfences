<?php
/**
 * @package ISFences
 * @version 1.0.0
 */
/*
Plugin Name: ISFences
Plugin URI: 
Description: This plugin blockes blacklisted ip address', scans the wenbsites files for malicious code and scans files before they can be uploaded.
Author: Iheb Slimen
Version: 1.0.0
Author URI: 
*/


// require 'vendor/autoload.php';
require 'checkblocked.php';
include 'scan.php';



function wpSettingsPage(){

	add_menu_page(

		'ISFences Plugin',
		'ISFences',
		'manage_options',
		'wpISfences',
		'wpSettingsPageMarkup',
		'dashicons-shield',
		10

	);
}

add_action('admin_menu','wpSettingsPage');

function wpSettingsPageMarkup(){

	if (!current_user_can('manage_options')){

		return;
	}

	include 'pluginPageContents.php';
}

function wpISFencesSettings(){

	add_settings_section(

		'wpisfences_blacklisted_ip',
		_('Blacklisted IP Addresses','wpISFences'),
		'blacklistedIPAddressesSection',
		'wpISFences'


	);

}

add_action('admint_init', 'wpISFencesSettings');

function blacklistedIPAddressesSection(){
	?>
	<div>
		<h2><?php esc_html_e("Blacklisted IP Adresses");?></h2>
	</div>
	<?php

}

exec("php ./scan.php");



?>
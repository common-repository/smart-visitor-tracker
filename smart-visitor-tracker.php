<?php
/*
 * @package Custom_Visitor_Details
 * @version 1.0.0
*/

/*
Plugin Name: Smart Visitor Tracker
Plugin URI: https://pulsemediagroup.co.uk/product/visitor-profiling/
Description: Smart Visitor Tracker Plugin to keep and track visitor IP details in deep.
Author: Pulse Media Group Ltd
Version: 1.0.0
Author URI: https://pulsemediagroup.co.uk/product/visitor-profiling/
*/

require_once (dirname(__FILE__) . '/visitor-admin-data.php');
require_once (dirname(__FILE__) . '/class-visitor-popup.php');
defined ('ABSPATH') or die ('There is a problem in WordPress :('); 
class SmartVisitorTracker {
		function SmartVisitorTracker_activate() {
			//all processes of plugin activation goes here
			global $wpdb;
			$table_name = $wpdb->prefix."vdcustom_data";
				$sql = "CREATE TABLE IF NOT EXISTS ".$table_name."(
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					ipaddress varchar(255) DEFAULT NULL,
					latitude varchar(255) DEFAULT NULL,
					longitude varchar(255) DEFAULT NULL,
					daterecorded datetime DEFAULT NULL,
					detail_data longtext
				  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
						$results = $wpdb->query($sql);
			flush_rewrite_rules();
		}
		/* Other Functions goes here */
}

function SmartVisitorTracker_uninstall() {
	//All processes of plugin uninstall goes here
	if ( !defined('WP_UNINSTALL_PLUGIN') ) {
		die;
	} else {
		global $wpdb;
		$table_name = $wpdb->prefix."vdcustom_data";
		$wpdb->query("DROP TABLE IF EXISTS $table_name");
	}
	flush_rewrite_rules();
}
register_uninstall_hook(__FILE__, 'plugin_uninstall');

if ( class_exists('SmartVisitorTracker') ) {
	$vd_plugin = new SmartVisitorTracker;
	register_activation_hook(__FILE__, array($vd_plugin, 'SmartVisitorTracker_activate') );
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}






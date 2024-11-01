<?php
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
	die;
}
		global $wpdb;
		$table_name = $wpdb->prefix."posts_embed";
		$sql = "DROP TABLE ".$table_name.";";
		$results = $wpdb->query($sql);
?>
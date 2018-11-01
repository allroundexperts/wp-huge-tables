<?php

/*
* @wordpress-plugin
 * Plugin Name:       All Timelines Custom Tables
 * Plugin URI:        https://www.alltimelines.com
 * Description:       This plugin is used to display the timeline items in a customized table, using a shortcode.
 * Version:           1.0.0
 * Author:            Sibtain Ali
 * Author URI:        https://www.upwork.com/freelancers/~01ee96f841f75f9dd1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

define('ATCTP_NAME','all-timelines-custom-tables');
define('ATCTP_VERSION', '1.0.0');

require plugin_dir_path( __FILE__ ) . 'includes/class-all-timelines-custom-tables.php';

function run_all_timelines_custom_tables() {

	$plugin = new All_Timelines_Custom_Tables();
	$plugin->run();

}

run_all_timelines_custom_tables();
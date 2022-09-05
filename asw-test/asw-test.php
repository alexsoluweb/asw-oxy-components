<?php
/**
 * Plugin Name:     ASW test
 * Plugin URI:      TODO
 * Description:     Test plugin
 * Author:          Alexandre Gravel Ménard
 * Author URI:      https://alexsoluweb.digital
 * License:         GPLv2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     asw-test
 * Version:         1.1.1
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ASW_TEST_LANGUAGE_PATH', __DIR__ . '/languages/' );


add_action( 'plugins_loaded', 'asw_test_load_textdomain' );
function asw_test_load_textdomain() {
	load_plugin_textdomain( 'asw-test', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Set the textdomain to our Oxygen plugin
add_filter( 'asw_oxygen_textdomain', 'asw_test_oxygen_textdomain' );
function asw_test_oxygen_textdomain() {
	return 'asw-test';
}

// Set the language path for the Oxygen translation component
add_filter( 'asw_oxygen_language_path', 'asw_test_oxygen_language_path' );
function asw_test_oxygen_language_path() {
	return ASW_TEST_LANGUAGE_PATH;
}

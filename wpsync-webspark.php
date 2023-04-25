<?php
/**
 * Plugin Name: Wpsync Webspark
 * Version: 0.1
 * Text Domain: wpsync-webspark
 */

if ( ! defined( 'WP_WEBSPARK_PLUGIN_FILE' ) ) {
	define( 'WP_WEBSPARK_PLUGIN_FILE', __FILE__ );
}

use App\Activator;

require_once plugin_dir_path( __FILE__ ) . '/app/class-activator.php';

new Activator();

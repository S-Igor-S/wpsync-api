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

//require_once(ABSPATH . 'wp-admin/includes/media.php');
//require_once(ABSPATH . 'wp-admin/includes/file.php');
//require_once(ABSPATH . 'wp-admin/includes/image.php');

require_once plugin_dir_path( __FILE__ ) . '/app/class-activator.php';

new Activator();

$request = wp_remote_get( 'https://wp.webspark.dev/wp-api/products',
	[
		'timeout' => 20,
	]
);
if ( is_wp_error( $request ) ) {
	wp_die();
}

$products = json_decode( $request['body'] );

if ( $products->error ) {
	wp_die();
}

$products_request = $products->data;
echo "<pre>";
var_dump(base64_decode($products_request[0]->picture));
echo "</pre>";

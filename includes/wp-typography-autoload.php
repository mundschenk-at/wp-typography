<?php

/**
 * An autoloader implementation for the WP_Typography classes.
 *
 * @param string $class_name
 */
function wp_typography_autoloader( $class_name ) {
	if ( false === strpos( $class_name, 'WP_Typography' ) ) {
		return; // abort
	}

	error_log("WP-autoloader $class_name");

	static $classes_dir;
	if ( empty( $classes_dir ) ) {
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	}

	$class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
	if ( is_file( $class_file_path = $classes_dir . $class_file ) ) {
		require_once( $class_file_path );
	}
}
spl_autoload_register( 'wp_typography_autoloader' );

<?php
/*
  Plugin Name: BuddyPress Media
  Plugin URI: http://rtcamp.com/buddypress-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
  Description: This plugin adds missing media rich features like photos, videos and audio uploading to BuddyPress which are essential if you are building social network, seriously!
  Version: 2.14
  Author: rtCamp
  Text Domain: buddypress-media
  Author URI: http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
 */

/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package BuddyPressMedia
 * @subpackage Main
 */

if ( ! defined( 'RT_MEDIA_PATH' ) ){

	/**
	 *  The server file system path to the plugin directory
	 *
	 */
	define( 'RT_MEDIA_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'RT_MEDIA_URL' ) ){

	/**
	 * The url to the plugin directory
	 *
	 */
	define( 'RT_MEDIA_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rt_media_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/services/' . $class_name . '.php',
		'app/helper/' . $class_name . '.php',
                'app/helper/db/' . $class_name . '.php',
		'app/admin/' . $class_name . '.php',
		'app/main/interactions/' . $class_name . '.php',
		'app/main/routers/' . $class_name . '.php',
		'app/main/routers/query/' . $class_name . '.php',
		'app/main/controllers/upload/' . $class_name . '.php',
		'app/main/controllers/upload/processors/' . $class_name . '.php',
		'app/main/controllers/shortcodes/' . $class_name . '.php',
		'app/main/controllers/template/' . $class_name . '.php',
		'app/main/deprecated/' . $class_name . '.php',
		'app/main/contexts/' . $class_name . '.php',
		'app/main/' . $class_name . '.php',
		'app/main/activity/' . $class_name . '.php',
		'app/main/profile/' . $class_name . '.php',
		'app/main/group/' . $class_name . '.php',
		'app/main/query/' . $class_name . '.php',
                'app/main/privacy/' . $class_name . '.php',
		'app/main/group/dummy/' . $class_name . '.php',
		'app/main/includes/' . $class_name . '.php',
		'app/main/widgets/' . $class_name . '.php',
		'app/main/upload/' . $class_name . '.php',
		'app/main/upload/processors/' . $class_name . '.php',
		 'app/main/template/' . $class_name . '.php',
		'app/main/shortcodes/' . $class_name . '.php',
		'app/log/' . $class_name . '.php',
		'app/importers/' . $class_name . '.php',
	);
	foreach ( $rtlibpath as $i => $path ) {
		$path = RT_MEDIA_PATH . $path;
		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rt_media_autoloader' );

/**
 * Instantiate the BuddyPressMedia class.
 */
global $rt_media;
$rt_media = new RTMedia();

/*
 * Look Ma! Very few includes! Next File: /app/main/BuddyPressMedia.php
 */
?>

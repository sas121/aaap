<?php
/*
 Plugin Name: Synthesis Site Management
 Plugin URI: http://websynthesis.com/
 Description: WordPress worker plugin for Synthesis Site Management
 Version: 1.2.0
 Author: Copyblogger Media
 Author URI: http://www.copyblogger.com
 */

// don't allow direct execution of this file
if ( ! defined( 'ABSPATH' ) )
	die;

// create constants for the child plugins
if ( ! defined( 'SYNTHESIS_SITE_PLUGIN_DIR' ) )
	define( 'SYNTHESIS_SITE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) . 'synthesis/' );

if ( ! defined( 'SYNTHESIS_SITE_PLUGIN_URL' ) )
	define( 'SYNTHESIS_SITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) . 'synthesis/' );

if ( ! defined( 'SYNTHESIS_CHILD_PLUGIN_DIR' ) )
	define( 'SYNTHESIS_CHILD_PLUGIN_DIR', SYNTHESIS_SITE_PLUGIN_DIR . '.plugins/' );

if ( ! defined( 'SYNTHESIS_SITE_TOOLS_DIR' ) )
	define( 'SYNTHESIS_SITE_TOOLS_DIR', SYNTHESIS_SITE_PLUGIN_DIR . '.tools/' );

if ( ! defined( 'SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR' ) )
    define( 'SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR', SYNTHESIS_CHILD_PLUGIN_DIR . 'includes/' );

if (! defined( 'SYNTHESIS_SITE_PLUGIN_JS_URL' ) )
	define( 'SYNTHESIS_SITE_PLUGIN_JS_URL', SYNTHESIS_SITE_PLUGIN_URL . 'js/' );

// Search for synthesis plugins
$syn_plugins = array();

foreach ( array( SYNTHESIS_CHILD_PLUGIN_DIR, SYNTHESIS_SITE_TOOLS_DIR ) as $plugin_dir ) {

	if ( ! is_dir( $plugin_dir ) || ! $dh = opendir( $plugin_dir ) )
		continue;
	
	while ( ( $syn_plugin = readdir( $dh ) ) !== false ) {
	
		if ( substr( $syn_plugin, -4 ) == '.php' )
			$syn_plugins[] = $plugin_dir . $syn_plugin;
	
	}
	
	closedir( $dh );

}

if ( empty( $syn_plugins ) )
	return;

// Load the plugins in alphabetical order so file name can be used for load order
sort( $syn_plugins );

foreach ( $syn_plugins as $syn_plugin )
	require_once( $syn_plugin );

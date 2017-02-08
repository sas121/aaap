<?php
function ra_no_update_check_30() {
	
	static $current = false;

	// allow Synthesis support using wp-cli to see updates	
	if ( defined( 'WP_CLI' ) && WP_CLI )
		return false;

	if ( ! $current ) {

		$current = new stdClass();
		$current->last_checked = time();
		$current->updates = array();

	}

	return $current;

}


if ( 'rainmaker' == get_option( 'sng_level' ) ) {

	// Network  filters
	add_filter( 'pre_transient_update_core', 'ra_no_update_check_30' );
	add_filter( 'pre_transient_update_themes', 'ra_no_update_check_30' );
	add_filter( 'pre_transient_update_plugins', 'ra_no_update_check_30' );
	
	// Single site filters
	add_filter( 'pre_site_transient_update_core', 'ra_no_update_check_30' );
	add_filter( 'pre_site_transient_update_themes', 'ra_no_update_check_30' );
	add_filter( 'pre_site_transient_update_plugins', 'ra_no_update_check_30' );

}
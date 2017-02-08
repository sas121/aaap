<?php
/*
Plugin Name: RoyalSlider
Plugin URI: http://dimsemenov.com/plugins/royal-slider-wp/
Description: Premium jQuery slider plugin.
Author: Dmitry Semenov
Version: 1.8
Author URI: http://dimsemenov.com
*/

if (!class_exists("RoyalSliderAdmin")) {
	
	require_once dirname( __FILE__ ) . '/RoyalSliderAdmin.php';	
	$royalSlider =& new RoyalSliderAdmin(__FILE__);		
	
	function get_royalslider($id) {
		global $royalSlider;		
		return $royalSlider->get_slider($id);
	}
}





add_filter('site_transient_update_plugins', 'dd_remove_update_nag');
function dd_remove_update_nag($value) {
	unset($value->response[ plugin_basename(__FILE__) ]);
	return $value;
}










?>
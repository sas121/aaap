<?php

function scribe_convert_bytes($value) {
	if(is_numeric($value)) {
		return $value;
	} else {
		$value_length = strlen($value);
		$quantity = substr($value, 0, $value_length - 1);
		$unit = strtolower(substr($value, $value_length - 1));
		switch ( $unit ) {
			case 'k':
				$quantity *= 1024;
				break;
			case 'm':
				$quantity *= 1048576;
				break;
			case 'g':
				$quantity *= 1073741824;
				break;
		}
		return $quantity;
	}
}

function scribe_get_upload_iframe_src($type, $tab = null, $args = array()) {
	return apply_filters('scribe_get_upload_iframe_src', Scribe_SEO::get_upload_iframe_src($type, $tab, $args), $type, $tab, $args);
}

function scribe_the_upload_iframe_src($type, $tab = null, $args = array(), $escape = true) {
	$url = scribe_get_upload_iframe_src($type, $tab, $args);
	if($escape) { $url = esc_url($url); }
	echo apply_filters('scribe_the_upload_iframe_src', $url, $type, $tab, $args, $escape);
}

function scribe_urldecode_deep($item) {
	if(!is_array($item)) {
		return urldecode($item);
	} else {
		$return = array();
		foreach($item as $key => $value) {
			$return[$key] = scribe_urldecode_deep($value);
		}
		return $return;
	}
}
<?php

/**
 * Save area resource category custom filelds data
 * 
 * @param int $term_id ID of taxonomy that we want to edit
 */
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_area_resource_category', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_area_resource_category', 'save_taxonomy_custom_meta', 10, 2 );

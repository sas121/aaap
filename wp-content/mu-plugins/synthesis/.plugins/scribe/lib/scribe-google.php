<?php

if ( ! class_exists( 'Scribe_Google' ) ) {

	/**
	 * Encapsulates interaction with Google suggest in regards to keyword suggestions and volume.
	 */
	class Scribe_Google {
		
		const ENDPOINT = 'http://google.com/complete/search?output=toolbar';
		
		/**
		 * Returns an array of search suggestions from the unofficial completion API located 
		 * at the endpoint specified in this class. &q=query
		 * 
		 * Parses the output into an array of associative arrays with keys of term, volume and
		 * current. "current" is a boolean that determines whether the result in question is the searched
		 * for term.
		 * 
		 * @return array|WP_Error WP_Error if something goes wrong. Otherwise, an array as described above.
		 */
		public static function get_suggestions($search_term) {
			$search_term = trim($search_term);
			
			if ( empty( $search_term ) )
				return new WP_Error('empty_term', __('Please provide a search term.', 'scribeseo'));

			$response = wp_remote_get( add_query_arg( array( 'q' => urlencode( $search_term ) ), self::ENDPOINT ) );

			if ( is_wp_error( $response ) )
				return $response;

			$result = array();

			// turn on user error handing
			$user_errors = libxml_use_internal_errors( true );
			$complete_suggestions = simplexml_load_string( wp_remote_retrieve_body( $response ) );
			// get any errors
			$xml_errors = libxml_get_errors();
			// restore error handling setting
			libxml_use_internal_errors( $user_errors );
			if ( ! empty( $xml_errors ) )
				return new WP_Error('xml_error', __('The XML from the Google Completion API could not be loaded appropriately.', 'scribeseo'));

			$complete_suggestions_po = json_decode( json_encode( $complete_suggestions ) );

			if ( ! is_object( $complete_suggestions_po ) || ! isset( $complete_suggestions_po->CompleteSuggestion ) )
				return new WP_Error('xml_error', __('The XML from the Google Completion API could not be loaded appropriately.', 'scribeseo'));

			foreach( $complete_suggestions_po->CompleteSuggestion as $suggestion ) {

				$term = $suggestion->suggestion->{'@attributes'}->data;
				$volume = intval( $suggestion->num_queries->{'@attributes'}->int );
				$volume_nice = number_format_i18n( $volume );
				$current = $term == $search_term;

				$result[] = compact( 'term', 'volume', 'volume_nice', 'current' );
			}
			
			return $result;
		}
	}
}

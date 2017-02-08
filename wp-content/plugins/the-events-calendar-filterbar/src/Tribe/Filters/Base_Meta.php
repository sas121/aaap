<?php

/**
 * Class Tribe__Events__Filterbar__Filters__Country
 */
class Tribe__Events__Filterbar__Filters__Base_Meta extends Tribe__Events__Filterbar__Filter {
	public $type = 'select';

	public function get_admin_form() {
		$title = $this->get_title_field();
		$type = $this->get_multichoice_type_field();
		return $title . $type;
	}

	public $searched_meta = null;
	public $relation_meta = null;
	public $join_name = null;

	public function get_searched_post_type() {
		return '';
	}

	public function get_related_post_type() {
		return '';
	}

	public function filter_related_name( $data ) {
		return trim( $data->meta_value );
	}

	public function filter_related_value( $data ) {
		return $data->post_id;
	}

	public function is_valid_data( $name, $value ) {
		return true;
	}

	protected function get_values() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$search_in = implode( "', '", array_map( 'esc_sql', (array) $this->searched_meta ) );

		$search_sql =
			"SELECT m.post_id, m.meta_value
			FROM {$wpdb->postmeta} m
			INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE
				p.post_type = %s AND
				p.post_status = 'publish' AND m.meta_key IN ( '{$search_in}' ) AND m.meta_value != ''";

		// Get The Searched Meta
		$search_data = $wpdb->get_results( $wpdb->prepare( $search_sql, $this->get_searched_post_type() ) );

		// Fetch the possible related ids
		$possible_ids = array_unique( array_filter( wp_list_pluck( $search_data, 'post_id' ), 'is_numeric' ) );

		// Build a SQL structure for Integers for a IN compare
		$related_sql = implode( ', ', array_map( 'esc_sql', $possible_ids ) );
		$search_related_sql =
			"SELECT m.post_id, m.meta_value
			FROM {$wpdb->postmeta} m
			INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE
				p.post_type = %s AND
				p.post_status = 'publish' AND
				m.meta_key = '{$this->relation_meta}' AND
				m.meta_value IN ( {$related_sql} )";

		// Get Related data from DB
		$related_data = $wpdb->get_results( $wpdb->prepare( $search_related_sql, $this->get_related_post_type() ) );

		$related_ids = array_unique( array_filter( wp_list_pluck( $related_data, 'meta_value' ), 'is_numeric' ) );

		if ( empty( $search_data ) ) {
			return array();
		}

		foreach ( $search_data as $data ) {
			if ( ! in_array( $data->post_id, $related_ids ) ) {
				continue;
			}

			$name = $this->filter_related_name( $data );
			$value = $this->filter_related_value( $data );

			if ( ! $this->is_valid_data( $name, $value ) ) {
				continue;
			}

			$related[ $name ][] = $value;
		}

		if ( empty( $related ) ) {
			return array();
		}

		// Order It alphabetically
		ksort( $related );

		foreach ( $related as $name => $related_ids ) {
			$return[] = array(
				'name' => $name,
				'value' => implode( ',', $related_ids ),
			);
		}

		return $return;
	}

	protected function setup_join_clause() {
		global $wpdb;
		$this->joinClause = "LEFT JOIN {$wpdb->postmeta} AS {$this->join_name} ON ({$wpdb->posts}.ID = {$this->join_name}.post_id AND {$this->join_name}.meta_key = '{$this->relation_meta}')";
	}

	protected function setup_where_clause() {
		$related = (array) $this->currentValue;
		$related_array = array();
		foreach ( $related as $related_string ) {
			if ( empty( $related_string ) ) {
				continue;
			}
			$related_array += explode( ',', $related_string );
		}

		$related_array = array_unique( array_map( 'absint', array_filter( $related_array ) ) );
		$related_in_sql = implode( ',', $related_array );

		$this->whereClause = " AND {$this->join_name}.meta_value IN ( {$related_in_sql} ) ";
	}
}

<?php

class Scribe_XMLRPC_Server {
	/**
	 * This class provides XML-RPC access to the MyScribe Web by extending WP XML-RPC & requests needed to retrieve & update various pieces of information.
	 * 
	 * The most important thing to remember about this library is that it is WordPress specific. It returns WP XML-RPC XML for all requests.
	 */

	function __construct() {

		add_filter( 'xmlrpc_methods', array( $this, 'filter_methods' ) );
		
	}

	function filter_methods( $methods ) {

		$methods['scribe.getPost'] = array( __CLASS__, 'getPost' );
		$methods['scribe.getPosts'] = array( __CLASS__, 'getPosts' );
		$methods['scribe.getPostsLimit'] = array( __CLASS__, 'getPostsLimit' );
		$methods['scribe.editPost'] = array( __CLASS__, 'editPost' );

		return $methods;

	}
	function getPost( $args ) {

		$post = self::_get_post( $args );

		if ( ! is_array( $post ) )
			return $post;

		// filter out unneeded post fields
		$scribe_post = array();
		$scribe_fields = array( 
			'post_id', 
			'post_status', 
			'post_title', 
			'post_content', 
			'post_date', 
			'post_date_gmt', 
			'post_modified', 
			'post_modified_gmt', 
			'post_type',
		);

		foreach( $scribe_fields as $key )
			$scribe_post[$key] = $post[$key];
 
		// add scribe post fields
		$scribe_elements = array(
			'post_url' => $post['link']
		); 

		if ( ! isset( $args[5] ) || $args[5] ) {

			$scribe_elements['seo_title'] = Scribe_SEO::get_post_meta_title( $scribe_post['post_id'] );
			$scribe_elements['seo_description'] = Scribe_SEO::get_post_meta_description( $scribe_post['post_id'] );

		}

		return apply_filters( 'scribe_xmlrpc_post_detail', array_merge( $scribe_post, $scribe_elements ) );

	}
	function getPosts( $args ) {

		return self::getPostsBy( 'date', $args );

	}
	function getPostsLimit( $args ) {

		return self::getPostsBy( 'id', $args );

	}
	function getPostsBy( $field, $args ) {

		global $wp_xmlrpc_server, $wpdb;

		// use core xml-rpc to handle error conditions
		if ( ! is_array( $args ) || count( $args ) < 4 )
			return $wp_xmlrpc_server->wp_getPosts( $args );

		if ( $field == 'date' ) {

			// if a valide date is not passed return the default xml-rpc get posts
			$since = strtotime( $args[3] );
			if ( $since < 1 )
				return $wp_xmlrpc_server->wp_getPosts( $args );

			$since = date( 'Y-m-d H:i:s', $since );
			$where = $wpdb->prepare( '(post_date >= %s OR post_modified >= %s) AND ', $since, $since );
			$order = 'post_date DESC';
			$limit = '';

		} else {

			if ( ! (int) $args[3] )
				return $wp_xmlrpc_server->wp_getPosts( $args );

			$where = $wpdb->prepare( 'ID >= %d AND ', $args[3] );
			$order = 'ID ASC';
			$limit = 'LIMIT 0, 10';
		}
			
		// get post ids
		$post_types = Scribe_SEO::get_option( 'post-types' );
		if ( empty( $post_types ) || ! is_array( $post_types ) )
			return array();

		$post_list = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE $where post_type IN ('" . implode( "','", $post_types ) . "') AND post_status != 'auto-draft' ORDER BY $order $limit" );
		if ( ! $post_list )
			return array();

		$posts = array();

		foreach( $post_list as $post_id ) {

			$post_args = array_slice( $args, 0, 3 );
			$post_args[3] = $post_id;
			$post_args[5] = true;
			$post = self::getPost( $post_args );
			if ( is_array( $post ) )
				$posts[] = $post;
		
		}

		return $posts;

	}

	function editPost( $args ) {

		global $wp_xmlrpc_server;

		$post = self::_get_post( $args );
		if ( ! is_array( $post ) )
			return $post;

		$edit_args = array_slice( $args, 0, 4 );
		$edit_args[4] = array(
			'post_title' => isset( $args[4] ) ? $args[4] : '',
			'post_content' => isset( $args[5] ) ? $args[5] : ''
		);

		$result = $wp_xmlrpc_server->wp_editPost( $edit_args );
		if ( $result === true && class_exists( 'Scribe_SEO' ) ) {

			Scribe_SEO::set_post_meta_title( $post['post_id'], isset( $args[6] ) ? $args[6] : '' );
			Scribe_SEO::set_post_meta_description( $post['post_id'], isset( $args[7] ) ? $args[7] : '' );

		}

		return $result;

	}

	private function _get_post( $args ) {

		global $wp_xmlrpc_server;

		// use core xml-rpc to handle error conditions
		if ( ! is_array( $args ) )
			return $wp_xmlrpc_server->wp_getPost( $args );

		// don't retrieve terms or custom fields
		$scribe_args = array_slice( $args, 0, 4 );
		$scribe_args[4] = array( 'post' );

		// use core xml-rpc to retrieve post
		return $wp_xmlrpc_server->wp_getPost( $scribe_args );

	}
}

new Scribe_XMLRPC_Server;

<?php

if(!class_exists('Scribe_API')) {
	
	/**
	 * This class provides API access to the MyScribe API by implementing requests needed to retrieve various pieces of information and then 
	 * returning that information to a calling client in a form that makes sense.
	 * 
	 * The most important thing to remember about this library is that it is WordPress specific. It returns WP_Error objects when things go
	 * wrong and uses the WordPress request API to get data from remote locations. 
	 */
	class Scribe_API {
		
		const DEBUG = false;
		
		private static $api_base = 'http://api.scribeseo.com/';
		private static $api_base_secure = 'https://api.scribeseo.com/';
		
		private static $original_base;
		private static $original_base_secure;
		
		private $_api_key = null;
		private $_secure = true;
		private $_user_detail = null;

		var $_possible_Item = array();
		var $_possible_Items = array();
		var $_possible_CurrentType = array();
		var $_possible_CurrentData = array();

		public function __construct( $api_key, $secure = true ) {

			$this->_api_key = trim( $api_key );
			$this->_secure = (bool)$secure;

			if ( ! self::DEBUG )
				return;

			self::$original_base = self::$api_base;
			self::$original_base_secure = self::$api_base_secure;
			
			self::$api_base = 'http://staging.ecordia.com/scribe-api';
			self::$api_base_secure = self::$api_base;

		}
		
		/**
		 * Retrieve the content analysis for a post. The call retrieves a variety of information regarding the keywords 
		 * in the content, the various information regarding content that should be changed, and all types of scores.
		 * 
		 * The object returned from this method on success contains the following fields:
		 * 
		 * * da-recs: The document analysis recommendations - an array of strings
		 * * da-srps: The search engine result pages preview - an array of objects with properties as follows:
		 * ** engine: The search engine that the SERP preview is for
		 * ** snippit: The snippit for the engine described above
		 * ** title: The title for the engine described above
		 * ** url: The url for the engine described above
		 * * kas: The keyword analysis for the content passed - an array of objects with properties as follows:
		 * ** dens: The keyword density
		 * ** freq: The keyword frequency
		 * ** prom: The keyword prominence
		 * ** rank: The rank of the keyword according to Scribe SEO
		 * ** recs: The recommendations for the keyword - an array of strings
		 * ** score: The score for the keyword (A, B, C, D)
		 * ** search: The search score for the keyword
		 * ** text: The keyword itself
		 * ** writing: The writing score for the keyword
		 * ** x: The coordinate on the x plane for analysis
		 * ** y: The coordinate on the y plane for analysis
		 * * tags: The tags for the content - an array of strings
		 * 
		 * @param string $title The title to use for analysis.
		 * @param string $description The description to use for analysis.
		 * @param string $content The body to use for analysis.
		 * @param string $headline The headline to use for analysis.
		 * @param string $keyword The keyword to use in analysis.
		 * @param string $url The url to use for analysis.
		 * @return WP_Error | object If an error ocurred somewhere along the way, return a WP_Error object 
		 * with a descriptive message. Otherwise, return a stdClass object with the properties specified 
		 * above.
		 */
		public function analyze_content( $title, $description, $content, $headline, $keyword, $url ) {

			$body = array(
				'htmlBody' => $content,
				'htmlDescription' => $description,
				'htmlTitle' => $title,
				'htmlHeadline' => $headline,
				'targetedKeyword' => $keyword,
				'domain' => $url
			);
			$request_result = $this->send_request( 'analysis/content', array(), $body, true );

			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( is_object( $request_result ) && isset( $request_result->docScore ) )
				return $request_result;

			if ( isset( $request_result->Message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->Message );

			return new WP_Error( 'scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}
		
		/**
		 * Retrieve the keyword details for a particular site and keyword.  This call retrieves a variety of 
		 * information about a keyword in relation to the specified site.
		 * 
		 * The object returned from this method on success contains the following fields:
		 * 
		 * * scoreDifficulty: The difficulty score for the keyword
		 * * scoreContent: The content score for the keyword
		 * * scoreLinks: The link score for the keyword
		 * * scoreDomainAuthority: The domain authority score for the keyword
		 * * scoreFacebookLikes: The Facebook like score for the keyword
		 * * scoreGenderMale: The male gender score for the keyword
		 * * scoreGenderFemale: The female gender score for the keyword
		 * * agePrimaryDescription: The top age demographic description
		 * * agePrimaryValue: The top age demographic value
		 * * ageSecondaryDescription: The secondary age demographic description
		 * * ageSecondaryValue: The secondary age demographic value
		 * * ppc: The PPC AdCenter cost
		 * * volumeAnnual: The annual search volume
		 * * volumeMonthly: The monthly search volume
		 * 
		 * The raw JSON response looks like the following:
		 * {
				"scoreDifficulty" : 10, // difficulty score
				"scoreContent" : 22, // content score
				"scoreLinks" : 87, // link score
				"scoreDomainAuthority" : 94, // domain authority score
				"scoreFacebookLikes" : 8, // facebook like score
				"scoreGenderMale" : 45.8333321, // male gender score
				"scoreGenderFemale" : 45.8333321, // female gender score
				"agePrimaryDescription" : "Age Unknown", // top age demographic description
				"agePrimaryValue" : 0, // top age demographic value
				"ageSecondaryDescription" : "Age 65+", // secondary age demographic description
				"ageSecondaryValue" : 0, // secondary age demographic value
				"ppc" : 0.82, // PPC AdCenter cost
				"volumeAnnual" : 266400, // annual search volume
				"volumeMonthly" : 22200 // monthly search volume
			}
		 * 
		 * @param string $keyword The keyword to get details for
		 * @param string $domain The domain to use as the context for keyword details
		 * @return WP_Error | object If an error ocurred somewhere along the way, return a WP_Error object 
		 * with a descriptive message. Otherwise, return a stdClass object with the properties specified 
		 * above.
		 */
		public function get_keyword_details($facebook_oauth_token, $keyword, $domain) {

			$request_result = $this->send_request( 'analysis/kw/detail', array(), array( 'fbKey' => $facebook_oauth_token, 'domain' => $domain, 'query' => $keyword ), true );
			
			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( is_object( $request_result ) && isset( $request_result->volumeMonthly ) )
				return $request_result;

			if ( isset( $request_result->Message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->Message );

			return new WP_Error( 'scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}
		
		/**
		 * Retrieve the keyword suggestions from the Scribe SEO service given a keyword. Each related keyword
		 * has information regarding the term itself, the annual search volume, the monthly search volume, 
		 * and the competition level.
		 * 
		 * Each keyword object has the following properties:
		 * * term: the suggested keyword
		 * * comp: the competition level for the keyword
		 * * popularity: the popularity level for the keyword
		 * 
		 * The raw JSON response looks like the following:
		 * 
		 * {
			"suggestions" : [{
					"term" : "car insurance",			// term
					"comp" : 0.89,						// competition
					"pop" : 4							// popularity
				}, {
					"term" : "insurance",
					"comp" : 1,
					"pop" : 4
				}
			 ]
		   }
		 * 
		 * @param string $keyword The keyword for which to retrieve suggestions
		 * @return WP_Error | object If an error occurred somewhere along the way, return a WP_Error object 
		 * with a descriptive message. Otherwise, return an array of stdClass objects with the properties 
		 * specified above.
		 */
		public function get_keyword_suggestions($keyword) {

			$request_result = $this->send_request('analysis/kw/suggestions', array(), array('query' => $keyword), true);

			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( isset( $request_result->suggestions ) && is_array( $request_result->suggestions ) ) {

				$result = $request_result->suggestions;
				usort( $request_result->suggestions, array( __CLASS__, 'sort_by_pop' ) );

				return $request_result->suggestions;
			}

			if ( isset( $request_result->Message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->Message );

			return new WP_Error('scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}
		
		public static function sort_by_pop($a, $b) {
			return $a->popularity < $b->popularity;
		}
		
		/**
		 * Retrieve link suggestions from the Scribe SEO service given a keyword, domain, and Twitter credentials 
		 * for performing Twitter queries.
		 * 
		 * This method returns an object with the following properties:
		 * 
		 * * external: array of external link objects
		 * * internal: array of internal link objects
		 * * score: number indicating Scribe link score
		 * 
		 * The external link objects have the following properties:
		 * * da: domain authority
		 * * email: domain owner's email address
		 * * ks: number of Facebook likes
		 * * name: domain owner's name
		 * * org: domain owner's organization
		 * * phone: domain owner's phone number
		 * * tw: domain owner's twitter handle
		 * * url: the url of the external link
		 * 
		 * The internal link objects have the following properties:
		 * * pa: the page authority score as calculated by Scribe
		 * * title: the search page snippet of the page title
		 * * url: the URL of the page from which to link
		 */
		public function get_link_analysis( $keyword, $domain, $type ) {

			if ( ! in_array( $type, array( 'scr', 'int', 'ext', 'soc' ) ) )
				return new WP_Error('scribe_api_invalid_param', __( 'An invalid analysis type was provided.', 'scribeseo' ) );

			$request_result = $this->send_request( 'analysis/link', array( 'type' => $type ), array( 'domain' => $domain, 'query' => $keyword ), true );

			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( isset( $request_result->message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->message );

			if ( isset( $request_result->googlePlusActivities) || isset( $request_result->twitterProfiles ) )
				return $request_result;

			if ( is_array( $request_result ) || is_numeric( $request_result ) )
				return $request_result;

			return new WP_Error('scribe_api_test_error', serialize( $request_result ) );
			return new WP_Error('scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}
		
		public function get_comment_analysis( $keyword, $domain ) {

			$request_result = $this->send_request( 'analysis/comments', array(), array( 'domain' => $domain, 'query' => $keyword ), true );

			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( isset( $request_result->comments ) )
				return $request_result;

			if ( isset( $request_result->message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->message );

			return new WP_Error('scribe_api_test_error', serialize( $request_result ) );
			return new WP_Error('scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}

		public function get_supported_dependencies() {

			$request_result = $this->send_request( 'media/dependencies.json', array(), array(), false, false, false );

			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( is_object( $request_result ) && isset( $request_result->plugins ) && isset( $request_result->themes ) )
				return $request_result;

			if ( isset( $request_result->Message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->Message );

			return new WP_Error( 'scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}

		public function get_latest_plugin_info() {

			$request_result = $this->send_request( 'plugin/info', array(), array(), false, false, false );

			// if there is an error with the api wait for next time
			if ( is_wp_error( $request_result ) )
				return false;
				
			return $request_result;

		}
		/**
		 * Retrieves the details for the user account associated with the API Key belonging to this
		 * object.
		 * 
		 * User details are returned as an object with the following fields:
		 * 
		 * * as: Account Status
		 * * at: Scribe Plan
		 * * evals: array of objects
		 * ** er: Evaluations Remaining
		 * ** et: Evaluations Total
		 * ** ket: Evaluation Renewal Type (monthly, anually)
		 * ** type: The type of evaluation being described. (kw = keyword research, ca = content analysis)
		 * 
		 * The raw JSON response looks like the following:
		 * 
		 * {
			"accountStatus" : 1,
			"accountType" : "professional", // scribe plan
			"evaluations" : [{
					"remaining" : 100, // evals remaining
					"term" : "monthly", // when evals are renewed (monthly, annually)
					"total" : 200, // evals total
					"type" : "KeywordIdeaResearch" // eval type (keyword research)
				}, {
					"remaining" : 400,
					"term" : "monthly",
					"total" : 700,
					"type" : "ContentAnalysis" // eval type (content analysis)
				}
			]
			}
		 * 
		 * @return WP_Error | object If an error ocurred somewhere along the way, return a WP_Error object 
		 * with a descriptive message. Otherwise, return a stdClass object with the properties specified 
		 * above.
		 */
		public function get_user_details() {

			$request_result = $this->send_request( 'membership/user/detail/' );
			
			if ( is_wp_error( $request_result ) )
				return $request_result;

			if ( is_object( $request_result ) && isset( $request_result->accountStatus ) )
				return $request_result;

			if ( isset( $request_result->message ) )
				return new WP_Error( 'scribe_api_messge_error', $request_result->message );

			return new WP_Error( 'scribe_api_unknown_error', __( 'An unknown error occurred when contacting the Scribe SEO API service.', 'scribeseo' ) );

		}
		
		/// UTILITY
		
		/**
		 * Sends a request to the Scribe SEO service and parses the result.
		 * 
		 * @param string $path the path to append to the API base url
		 * @param array $get_data the collection of arguments to append to the query string
		 * @param array $post_data the collection of arguments to send as post data after JSON encoding
		 * @param bool $is_post whether or not to make a post request
		 * @return WP_Error | object If an error ocurrs, return a WP_Error object. Otherwise, return an
		 * object with data from the API request.
		 */
		private function send_request( $path, $get_data = array(), $post_data = array(), $is_post = false, $verify_key = true, $trailing_slash = true ) {

			if ( ! ini_get( 'safe_mode' ) )
				set_time_limit( 60 );
			
			if(empty($this->_api_key) && $verify_key) {
				return new WP_Error('scribe_api_empty_api_key', __('An API Key must be provided in order to make requests from the Scribe SEO API service.', 'scribeseo'));
			}
			
			$base_url = $this->_secure ? self::$api_base_secure : self::$api_base;
			$request_url = path_join( $base_url, $path );
			if ( $trailing_slash )
				$request_url = trailingslashit( $request_url );
			
			$get_data = array_map( 'urlencode', array_merge( array( 'apikey' => $this->_api_key ), $get_data ) );

			$request_url = add_query_arg( $get_data, $request_url );

			$base_args = array(
				'timeout' => 60,
				'sslverify' => false,
				'user-agent' => 'Scribe for WordPress',
			);

			if ( $is_post || ! empty( $post_data ) ) {

				$body = self::urlencode_json_encode($post_data);

				$response = wp_remote_post(
					$request_url, 
					array_merge( $base_args, array(
						'body' => $body,
						'headers' => array(
							'Content-Type' => 'application/json'
						)
					) )
				);
			} else {

				$response = wp_remote_get( $request_url, $base_args );

			}

			if ( ! is_wp_error( $response ) ) {

				$code = wp_remote_retrieve_response_code( $response );
				$body = str_replace( '-INF', 0, wp_remote_retrieve_body( $response ) );
				$object = @self::urldecode_json_decode( $body );
				if ( null === $object )
					$result = new WP_Error( 'scribe_api_could_not_decode_response', __( 'The system could not decode the response from the Scribe SEO API server.', 'scribeseo' ) );
				else
					$result = $object;

			} else {
				$result = $response;
			}
			
			return $result;
		}

		public static function urlencode_json_encode($item) {
			return json_encode(self::recursive_urlencode($item));
		}
		
		public static function urldecode_json_decode($string) {
			return self::recursive_urldecode(json_decode($string));
		}
		
		/**
		 * Takes an object, array or scalar value and, for an object or array, iterates over its properties
		 * and recursively urlencodes them. Otherwise 
		 */
		private static function recursive_urlencode($item) {
			if(is_object($item)) {
				foreach($item as $property => $value) {
					$item->$property = self::recursive_urlencode($value);
				}
				
				return $item;
			} else if(is_array($item)) {
				return array_map(array(__CLASS__, 'recursive_urlencode'), $item);
			} else if(is_string($item)) {
				return urlencode($item);
			} else {
				return $item;
			}
		}
		
		private static function recursive_urldecode($item) {
			if(is_object($item)) {
				foreach($item as $property => $value) {
					$item->$property = self::recursive_urldecode($value);
				}
				
				return $item;
			} else if(is_array($item)) {
				return array_map(array(__CLASS__, 'recursive_urldecode'), $item);
			} else if(is_string($item)) {
				return urldecode($item);
			} else {
				return $item;
			}
		}
	}
}

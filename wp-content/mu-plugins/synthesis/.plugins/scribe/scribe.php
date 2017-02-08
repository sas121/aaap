<?php
/*
 Plugin Name: Scribe
 Plugin URI: http://scribecontent.com
 Description: Increase your online traffic with Scribe – the intelligent content marketing tool for creative content producers . Scribe combines in-depth semantic keyword research, sophisticated content analysis and optimization tools, and targeted social media insights to help you focus your content marketing efforts. You will need a Scribe API Key in order to use the application. If you do not have an API Key, go to <a href="http://scribecontent.com" title="Get Scribe API Key">http://scribecontent.com</a>. Please make sure you are using a supported theme or plugin. For an updated list, go to <a href="http://scribecontent.com/compatibility/" title="Scribe Compatibility List">http://scribecontent.com/compatibility/</a>.
 Version: 4.0.15
 Author: Copyblogger Media
 Author URI: http://www.copyblogger.com
 */

if(!class_exists('Scribe_SEO')) {
	class Scribe_SEO {
		/// CONSTANTS
		
		//// VERSION
		const VERSION = '4.0.15';
		
		//// KEYS
		const SHOW_PREMISE_NAG_KEY = '_scribe_show_premise_nag';
		const SETTINGS_KEY = '_scribe_settings';
		const VERSION_KEY = '_scribe_version';
		const PLUGIN_INFO_CACHE_KEY = 'scribe_plugin_info';
		
		//// SLUGS
		const SETTINGS_TOP_PAGE_SLUG = 'scribe-settings';
		const SETTINGS_SUB_PAGE_SLUG_SETTINGS = 'scribe-settings';
		const SETTINGS_SUB_PAGE_SLUG_CONNECTIONS = 'scribe-connections';
		const SETTINGS_SUB_PAGE_SLUG_ACCOUNT = 'scribe-account';
		const SETTINGS_SUB_PAGE_SLUG_COMPATIBILITY = 'scribe-compatibility';
		const SETTINGS_SUB_PAGE_SLUG_SUPPORT = 'scribe-help';
		const SETTINGS_SUB_PAGE_SLUG_NEWS = 'scribe-news';
		
		//// USER META
		const CONTENT_ANALYSIS_BASE_KEY = 'scribe_content_analysis';
		const KEYWORD_DETAILS_BASE_KEY = '_skwd_'; // We have to abbreviate this because of the option name limit of 64 - Scribe Keyword Details
		const KEYWORD_SUGGESTIONS_TRANSIENT_BASE_KEY = 'skws_'; // We have to abbreviate this because of the option name limit - Scribe Keyword Suggestions
		const LINK_BUILDING_INFO_BASE_KEY = 'scribe_link_building_info'; // We have to abbreviate this because of the option name limit
		const PREVIOUS_KEYWORD_SUGGESTIONS_KEY = 'scribe_previous_keyword_suggestions_';
		const TARGET_TERM_BASE_KEY = 'scribe_target_term_';
		
		//// CACHE
		const CACHE_PERIOD = 86400; // 24 HOURS
		const KEYWORD_SUGGESTIONS_TRANSIENT_TIME = 86400;
		
		//// URLS
		const SEO_NEWS_FEED_URL = 'http://pipes.yahoo.com/pipes/pipe.run?_id=b06fc721302776ca0b56b791d92ecb78&_render=rss';

		/// DATA STORAGE
		private static $admin_page_hooks = array('post.php', 'post-new.php', 'media-upload-popup', 'edit.php');
		private static $default_settings = array('post-types' => array('post','page'));
		
		/*
		 * @var Scribe_API $scribe_api
		 */
		private static $scribe_api = null;
		private static $scribe_api_account = null;
		private static $upload_iframe_args = null;
		// Upgraded from Scribe v3
		private static $scribe_has_v3_data = false;

		public static function init() {
		
			if ( ! defined( 'SCRIBE_PLUGIN_DIR' ) )
				define( 'SCRIBE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			require_once( SCRIBE_PLUGIN_DIR . 'lib/scribe-connector.php' );

			if ( ! is_admin() )
				return;

			if ( ! defined( 'SCRIBE_PLUGIN_URL' ) )
				define( 'SCRIBE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			require_once( SCRIBE_PLUGIN_DIR . 'lib/scribe-api.php' );
			require_once( SCRIBE_PLUGIN_DIR . 'lib/scribe-display-helpers.php' );
			require_once( SCRIBE_PLUGIN_DIR . 'lib/scribe-google.php' );

			if ( ! self::is_hosted() && ! self::is_managed() )
				require_once( SCRIBE_PLUGIN_DIR . 'lib/scribe-support.php' );

			require_once( SCRIBE_PLUGIN_DIR . 'lib/scribe-utility.php' );
			require_once( SCRIBE_PLUGIN_DIR . 'lib/class-admin.php' );
			require_once( SCRIBE_PLUGIN_DIR . 'views/backend/settings/class-main-settings.php' );
	
			self::add_actions();
			self::add_filters();
			self::initialize_defaults();
			self::initialize_memory_limit();
			self::initialize_scribe_api();

			// check for Scribe v3 data
			self::$scribe_has_v3_data = (bool) get_option( '_ecordia_settings', false );
			if ( self::$scribe_has_v3_data )
				require_once( SCRIBE_PLUGIN_DIR . '/lib/history/scribe.php' );

			// check for update
			if ( ! self::is_hosted() )
				self::update();

		}

		private static function add_actions() {

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_administrative_resources' ) );
			add_action( 'admin_footer', array( __CLASS__, 'output_script_that_adds_target_blank_to_scribe_help_item' ) );
			add_action( 'admin_menu', array( __CLASS__, 'add_administrative_interface_items' ) );
			add_action( 'admin_menu', array( __CLASS__, 'modify_administrative_interface_items' ) );

			// plugin updates
			if ( ! self::is_hosted() && ! self::is_managed() ) {

				add_action( 'load-update-core.php', array( __CLASS__, 'delete_plugin_info' ) );
				add_filter( 'transient_update_plugins', array( __CLASS__, 'check_plugin_updates' ) );
				add_filter( 'site_transient_update_plugins', array( __CLASS__, 'check_plugin_updates' ) );

			}

			/// ANALYSIS POPUPS
			add_action( 'media_upload_scribe-analysis-keywords', array( __CLASS__, 'display_analysis_tabs' ) );
			add_action( 'media_upload_scribe-analysis-document', array( __CLASS__, 'display_analysis_tabs' ) );
			add_action( 'media_upload_scribe-analysis-tags', array( __CLASS__, 'display_analysis_tabs' ) );
			add_action( 'media_upload_scribe-analysis-help', array( __CLASS__, 'display_analysis_tabs' ) );

			/// KEYWORD POPUPS
			add_action( 'media_upload_scribe-keyword-suggestions', array( __CLASS__, 'display_keyword_tabs' ) );
			add_action( 'media_upload_scribe-keyword-details', array( __CLASS__, 'display_keyword_tabs' ) );

			/// LINK BUILDING POPUPS
			add_action( 'media_upload_scribe-link-building', array( __CLASS__, 'display_link_building_tabs' ) );
			add_action( 'media_upload_scribe-link-building-twitter-info', array( __CLASS__, 'display_link_building_tabs' ) );

			/// AJAX

			//// CONTENT ANALYSIS
			add_action( 'wp_ajax_scribe_analyze_content', array( __CLASS__, 'ajax_handle_analyze_content' ) );

			/// KEYWORD SUGGESTIONS
			add_action( 'wp_ajax_scribe_keyword_suggestions', array( __CLASS__, 'ajax_handle_keyword_suggestions' ) );

			//// KEYWORD RESEARCH
			add_action( 'wp_ajax_scribe_research_keyword', array( __CLASS__, 'ajax_handle_research_keyword' ) );
			add_action( 'wp_ajax_scribe_research_google_trends', array( __CLASS__, 'ajax_handle_research_google_trends' ) );

			//// LINK BUILDING
			add_action( 'wp_ajax_scribe_build_links', array( __CLASS__, 'ajax_handle_build_links' ) );

			//// TARGET TERM
			add_action( 'wp_ajax_scribe_clear_target_term', array( __CLASS__, 'ajax_handle_clear_target_term' ) );
			add_action( 'wp_ajax_scribe_set_target_term', array( __CLASS__, 'ajax_handle_set_target_term' ) );

		}

		private static function add_filters() {

			add_filter('pre_scribe_settings_save', array(__CLASS__, 'sanitize_scribe_settings'));

			$settings = self::get_settings();

			if ( isset( $settings['permissions-level'] ) && $settings['permissions-level'] == 'edit_posts' )
				add_filter( 'user_has_cap', array( __CLASS__, 'contributor_allow_scribe' ), 99, 3 );

		}
		
		private static function initialize_defaults() {
			self::$default_settings['your-url'] = home_url('/');
		}

		private static function initialize_memory_limit() {
			$memory_limit = scribe_convert_bytes(ini_get('memory_limit'));
			$memory_limit_megabytes = ($memory_limit / (1024 * 1024));
			if($memory_limit < 64) {
				ini_set('memory_limit', 64*1024*1024);
			}
		}

		private static function initialize_scribe_api() {

			if ( null !== self::$scribe_api )
				return;

			$settings = self::get_settings();
			$api_key = isset( $settings['api-key'] ) ? $settings['api-key'] : '';
			$security_method = isset( $settings['security-method'] ) && 1 == $settings['security-method'];

			self::$scribe_api = new Scribe_API( $api_key, $security_method );

		}

		/// AJAX CALLBACKS
		
		public static function ajax_handle_analyze_content() {
			$data = stripslashes_deep($_REQUEST);
			
			if(isset($data['scribe-analyze-content-nonce']) && wp_verify_nonce($data['scribe-analyze-content-nonce'], 'scribe-analyze-content')) {
				$post_id = $data['scribe-post-id'];
				
				$title = $data['scribe-title'];
				$description = $data['scribe-description'];
				$content = $data['scribe-content'];
				$headline = $data['scribe-headline'];
				$keyword = self::get_target_term($post_id);
				
				$settings = self::get_settings();
				$url = $settings['your-url'];
				
				$content_analysis = self::analyze_content($title, $description, $content, $headline, $keyword, $url);

				if(is_wp_error($content_analysis)) {
					$results = array('error' => true, 'error_message' => $content_analysis->get_error_message());
				} else {
					$request = new stdClass;
					$request->html_title = $title;
					$request->description = $description;
					$request->content = $content;
					$request->url = $url;
					
					$content_analysis->request = $request;
					self::set_content_analysis($content_analysis, $post_id);
					
					$account = self::$scribe_api->get_user_details();
					$results = array('content_analysis' => self::process_content_analysis($content_analysis), 'evaluations_remaining' => number_format_i18n(self::get_content_analysis_evaluations_remaining($account)));
				}
			} else {
				$results = array('error' => true, 'error_message' => __('Invalid request.', 'scribeseo'));
			}
			
			echo json_encode($results);
			exit;
		}
		
		public static function ajax_handle_clear_target_term() {
			$data = stripslashes_deep($_REQUEST);
			
			if(isset($data['_wpnonce']) && wp_verify_nonce($data['_wpnonce'], 'scribe-clear-target-term')) {
				$post_id = $data['scribe-post-id'];
				$user_id = get_current_user_id();
				
				self::clear_target_term($post_id, $user_id);
				
				$results = array('error' => false, 'post_id' => $post_id, 'user_id' => $user_id);
			} else {
				$results = array('error' => true, 'error_message' => __('Invalid request.', 'scribeseo'));
			}
			
			echo json_encode(compact('post_id', 'user_id'));
			exit;
		}
		
		public static function ajax_handle_set_target_term() {
			$data = stripslashes_deep($_REQUEST);
			
			if(isset($data['scribe-set-target-term-nonce']) && wp_verify_nonce($data['scribe-set-target-term-nonce'], 'scribe-set-target-term')) {
				$post_id = $data['scribe-post-id'];
				$target_term = $data['scribe-target-term'];
				$user_id = get_current_user_id();
				
				self::set_target_term($target_term, $post_id, $user_id);
				
				$results = array('error' => false, 'post_id' => $post_id, 'user_id' => $user_id, 'target_term' => $target_term);
			} else {
				$results = array('error' => true, 'error_message' => __('Invalid request.', 'scribeseo'));
			}
			
			echo json_encode($results);
			exit;
		}
		
		public static function ajax_handle_keyword_suggestions() {

			$data = stripslashes_deep( $_REQUEST );
			$term = isset( $data['term'] ) ? $data['term'] : '';

			if ( $term && isset( $data['scribe-keyword-suggestions-nonce'] ) && wp_verify_nonce( $data['scribe-keyword-suggestions-nonce'], 'scribe-keyword-suggestions' ) )
				$results = Scribe_Google::get_suggestions( $term );

			if ( empty( $results ) || ! is_array( $results ) )
				$results = array();
		
			$return = array();
			foreach( $results as $result )
				$return[] = array( 'value' => $result['term'], 'label' => esc_js( $result['term'] ) );
			
			echo json_encode( $return );
			exit;
		}
		
		public static function ajax_handle_research_keyword() {
			$data = stripslashes_deep($_REQUEST);
			
			if(isset($data['scribe-research-keyword-nonce']) && wp_verify_nonce($data['scribe-research-keyword-nonce'], 'scribe-research-keyword')) {
				$post_id = $data['scribe-post-id'];
				$target_keyword = preg_replace('/[^0-9a-zA-Z ]/', '', $data['scribe-keyword']);
				$user_id = get_current_user_id();
				
				$suggestions = self::get_keyword_suggestions( $target_keyword, $post_id, $user_id );
				$previous_suggestions = self::get_archived_keyword_suggestions_for_term( $post_id, $user_id, $data['scribe-keyword'] );
				
				$account = self::$scribe_api->get_user_details();
				$results = array('error' => false, 'keyword' => $target_keyword, 'suggestions' => $suggestions, 'previous_suggestions' => $previous_suggestions, 'evaluations_remaining' => number_format_i18n(self::get_keyword_evaluations_remaining($account)));
			} else {
				$results = array('error' => true, 'error_message' => __('Invalid request.', 'scribeseo'));
			}

			echo json_encode($results);
			exit;
		}

		public static function ajax_handle_research_google_trends() {

			$data = wp_parse_args( stripslashes_deep( $_REQUEST ), array(
				'scribe-google-trends-nonce' => '',
				'scribe-keyword' => '',
			) );

			$results = '';
			$content = '<script type="text/javascript" src="http://www.google.com/trends/embed.js?hl=en-US&q=___KEYWORD___&content=1&cid=TIMESERIES_GRAPH_0&export=5&w=500&h=330"></script>
<script type="text/javascript" src="http://www.google.com/trends/embed.js?hl=en-US&q=___KEYWORD___&content=1&cid=TOP_QUERIES_0_0&export=5&w=300&h=420"></script>
<script type="text/javascript" src="http://www.google.com/trends/embed.js?hl=en-US&q=___KEYWORD___&content=1&cid=RISING_QUERIES_0_0&export=5&w=300&h=420"></script>';


			if ( wp_verify_nonce( $data['scribe-google-trends-nonce'], 'scribe-research-google-trends' ) )
				$results = str_replace( '___KEYWORD___', $data['scribe-keyword'], $content );

			echo '<html><body>' . $results . '</body></html>';
			exit;
		}

		public static function ajax_handle_build_links() {
			$data = stripslashes_deep($_REQUEST);

			if(isset($data['scribe-build-links-nonce']) && wp_verify_nonce($data['scribe-build-links-nonce'], 'scribe-build-links')) {

				$post_id = isset( $data['scribe-post-id'] ) ?  $data['scribe-post-id'] : 0;
				$type = strip_tags( $data['type'] );
				$keywords = array_filter( (array)$data['scribe-keywords'] );

				$analysis = self::build_links( implode( ' + ', $keywords ), $type );

				if ( is_wp_error( $analysis ) ) {
					$results = array( 'error' => true, 'error_message' => $analysis->get_error_message() );
				} else {
					if ( $type != 'scr' )
						$link_analysis = self::get_link_buiding_info( $post_id );

					switch( $type ) {
						case 'scr':
							$link_analysis = new stdClass();
							$link_analysis->score = $analysis;
							$link_analysis->linkScore = $analysis;
							$link_analysis->score_description = sprintf( __( scribe_get_link_building_score_description( $link_analysis->score ) ), implode( __( ' and ', 'scribeseo' ), $keywords ) );
							break;
						case 'int':
							$link_analysis->internalLinks = $analysis;
							break;
						case 'ext':
							$link_analysis->externalLinks = $analysis;
							break;
						case 'soc':
							$link_analysis->googlePlusActivities = $analysis->googlePlusActivities;
							$link_analysis->twitterProfiles = $analysis->twitterProfiles;
							break;
					}

					$link_analysis->keywords = $keywords;

					self::set_link_building_info($link_analysis, $post_id);

					if ( $type == 'soc' )
						$results = self::build_social_activity( $keywords, $link_analysis );
					else
						$results = array('error' => false, 'link_analysis' => $link_analysis);
				}
			} else {
				$results = array('error' => true, 'error_message' => esc_js( __( 'Invalid request.', 'scribeseo' ) ) );
			}

			echo json_encode($results);
			exit;
		}

		/// CALLBACKS
		
		public static function add_administrative_interface_items() {
			self::$admin_page_hooks[] = $connections = add_submenu_page(self::SETTINGS_TOP_PAGE_SLUG, __( 'Site Connections', 'scribeseo' ), __( 'Site Connections', 'scribeseo' ), 'manage_options', self::SETTINGS_SUB_PAGE_SLUG_CONNECTIONS, array(__CLASS__, 'display_connections_page'));
			self::$admin_page_hooks[] = $account = add_submenu_page(self::SETTINGS_TOP_PAGE_SLUG, __('Account', 'scribeseo'), __('Account', 'scribeseo'), 'manage_options', self::SETTINGS_SUB_PAGE_SLUG_ACCOUNT, array(__CLASS__, 'display_account_page'));

			if ( ! self::is_hosted() && ! self::is_managed() ) {

				self::$admin_page_hooks[] = $compatibility = add_submenu_page(self::SETTINGS_TOP_PAGE_SLUG, __('Compatibility', 'scribeseo'), __('Compatibility', 'scribeseo'), 'manage_options', self::SETTINGS_SUB_PAGE_SLUG_COMPATIBILITY, array(__CLASS__, 'display_compatibility_page'));
				add_action("load-{$compatibility}", array(__CLASS__, 'process_compatibility_check'));

			}

			self::$admin_page_hooks[] = $news = add_submenu_page(self::SETTINGS_TOP_PAGE_SLUG, __('SEO News', 'scribeseo'), __('SEO News', 'scribeseo'), 'manage_options', self::SETTINGS_SUB_PAGE_SLUG_NEWS, array(__CLASS__, 'display_news_page'));

			if ( ! self::is_hosted() && ! self::is_managed() ) {

				self::$admin_page_hooks[] = $support = add_submenu_page(self::SETTINGS_TOP_PAGE_SLUG, __( 'Scribe Help', 'scribeseo' ), __( 'Scribe Help', 'scribeseo' ), 'manage_options', self::SETTINGS_SUB_PAGE_SLUG_SUPPORT, array(__CLASS__, 'display_support_page'));
				add_action("load-{$support}", array(__CLASS__, 'redirect_to_support'));

			}
			
			$settings = self::get_settings();
			$permission_level = isset( $settings['permissions-level'] ) ? $settings['permissions-level'] : 'manage_options';
			if ( current_user_can( $permission_level ) ) {
				foreach((array)$settings['post-types'] as $post_type) {
					add_action("add_meta_boxes_{$post_type}", array(__CLASS__, 'add_meta_boxes'));
					add_filter("manage_{$post_type}_posts_columns", array(__CLASS__, 'add_scribe_columns'));
					add_action("manage_{$post_type}_posts_custom_column", array(__CLASS__, 'add_scribe_column_output'), 11, 2);
				}
			}
		}

		public static function add_meta_boxes($post) {
			add_meta_box( 'scribe-keyword-research', __( 'Scribe Keyword Research', 'scribeseo' ), array( __CLASS__, 'display_keyword_research_meta_box'), $post->post_type, 'side', 'high' );
			add_meta_box( 'scribe-analysis', __( 'Scribe Content Optimizer', 'scribeseo' ), array( __CLASS__, 'display_analysis_meta_box'), $post->post_type, 'side', 'high' );
			add_meta_box( 'scribe-link-building', __( 'Scribe Link Building', 'scribeseo' ), array( __CLASS__, 'display_link_building_meta_box'), $post->post_type, 'side', 'high' );
		}
		
		public static function add_scribe_columns($columns) {
			$columns['scribe-doc-score'] = __('Scribe Page Score', 'scribeseo');
			$columns['scribe-site-score'] = __('Scribe Site Score', 'scribeseo');
			$columns['scribe-keywords'] = __('Scribe Keywords', 'scribeseo');
			if ( self::$scribe_has_v3_data )
				$columns['scribe-v3'] = __('Scribe v3 Score', 'scribeseo' );

			return $columns;
		}

		public static function add_scribe_column_output($column_name, $post_id) {

			if ( $column_name == 'scribe-v3' ) {

				$meta = get_post_meta( $post_id, '_ecordia_seo_info', true );
				if ( ! $meta )
					return;

				$ecordia_seo_info = maybe_unserialize( base64_decode( $meta ) );
				echo isset( $ecordia_seo_info['GetAnalysisResult']['Analysis']['SeoScore']['Score']['Value'] ) ? esc_html( $ecordia_seo_info['GetAnalysisResult']['Analysis']['SeoScore']['Score']['Value'] ) : '';
				return;				

			}

			if ( ! in_array( $column_name, array( 'scribe-doc-score', 'scribe-site-score', 'scribe-keywords' ) ) )
				return;

			$content_analysis = self::get_content_analysis($post_id);
			if ( empty( $content_analysis ) ) {
				switch( $column_name ) {
					case 'scribe-doc-score':
					case 'scribe-site-score':
						echo '-';
						break;
					case 'scribe-keywords':
						esc_html_e( 'Awaiting Analysis', 'scribeseo' );
						break;
				}
				return;
			}

			switch( $column_name ) {
				case 'scribe-doc-score':
					if ( empty( $content_analysis->docScore ) )
						esc_html_e( 'N/A', 'scribeseo' );
					else
						printf( '<span class="%1$s">%2$s</span>', scribe_score_class( (int)$content_analysis->docScore ), (int)$content_analysis->docScore );

					break;
				case 'scribe-site-score':
					if ( empty( $content_analysis->scribeScore ) )
						esc_html_e( 'N/A', 'scribeseo' );
					else
						printf( '<span class="%1$s">%2$s</span>', scribe_score_class( (int)$content_analysis->scribeScore, 'site' ), (int)$content_analysis->scribeScore );

					break;
				case 'scribe-keywords':
					$keywords = self::get_content_analysis_keywords( $content_analysis, ' + ', false );
					if ( empty( $keywords ) )
						esc_html_e( 'None', 'scribeseo' );
					else
						printf( '<span class="scribe-post-column-keywords">%s</span>', esc_html( $keywords ) );

					break;
			}
		}
		
		public static function add_tiny_mce_init_instance_callback($initialization_array) {
			$initialization_array['init_instance_callback'] = 'scribe_tiny_mce_add_change_callback';
			
			return $initialization_array;
		}
		
		public static function add_upload_iframe_args( $url ) {
			$args = array_map( 'urlencode', self::$upload_iframe_args );
			return add_query_arg( self::$upload_iframe_args, $url );
		}
		
		public static function enqueue_administrative_resources($hook) {
			if(!in_array($hook, self::$admin_page_hooks)) { return; }
			
			wp_enqueue_style('scribe-bootstrap-necessary', SCRIBE_PLUGIN_URL . 'resources/backend/bootstrap/bootstrap-necessary.css', array(), '1.4.0');
			wp_enqueue_script('scribe-bootstrap-twipsy', SCRIBE_PLUGIN_URL . 'resources/backend/bootstrap/bootstrap-twipsy.js', array('jquery'), '1.4.0');
			wp_enqueue_script('scribe-bootstrap-popover', SCRIBE_PLUGIN_URL . 'resources/backend/bootstrap/bootstrap-popover.js', array('jquery', 'scribe-bootstrap-twipsy'), '1.4.0');
			
			// jQuery Autocomplete - Prevent collision with WordPress SEO
			if(!wp_script_is('jquery-ui-autocomplete')) {
				wp_enqueue_script('jquery-ui-autocomplete', SCRIBE_PLUGIN_URL . 'resources/backend/jquery-ui-autocomplete.min.js', array('jquery', 'jquery-ui-core'), self::VERSION, true);
			}
			wp_enqueue_script('jquery-ui-autocomplete-html', SCRIBE_PLUGIN_URL . 'resources/backend/jquery-ui-autocomplete-html.js', array('jquery-ui-autocomplete'), self::VERSION, true);
			
			wp_enqueue_script('scribe-jqplot', SCRIBE_PLUGIN_URL . 'resources/backend/jqplot/jquery.jqplot.min.js', array('jquery'), '1.0.0b2_r947');
			wp_enqueue_script('scribe-jqplot-point-labels', SCRIBE_PLUGIN_URL . 'resources/backend/jqplot/plugins/jqplot.pointLabels.min.js', array('scribe-jqplot'), '1.0.0b2_r947');
			wp_enqueue_script('scribe-backend', SCRIBE_PLUGIN_URL . 'resources/backend/scribe.js', array('jquery', 'thickbox', 'jquery-ui-tabs', 'scribe-jqplot', 'suggest'), self::VERSION);
			
			wp_enqueue_style('scribe-jqplot', SCRIBE_PLUGIN_URL . 'resources/backend/jqplot/jquery.jqplot.min.css', array(), '1.0.0b2_r947');
			wp_enqueue_style('scribe-backend', SCRIBE_PLUGIN_URL . 'resources/backend/scribe.css', array('thickbox'), self::VERSION);
			
			$settings = self::get_settings();
			$seo_tool_settings = ! empty( $settings['seo-tool-settings'] ) && is_object( $settings['seo-tool-settings'] ) ? $settings['seo-tool-settings'] : new stdClass;

			$configuration = array(
				'content_analysis_label' => __('Content Analysis', 'scribeseo'),
				'keyword_research_label' => __('Keyword Research', 'scribeseo'),
			
				'content_id' => apply_filters( 'scribeseo_post_content_id', 'content' ),
				'seo_title_id' => ! empty( $seo_tool_settings->title ) ? trim( $seo_tool_settings->title ) : '',
				'seo_description_id' => ! empty( $seo_tool_settings->description ) ? trim( $seo_tool_settings->description ) : '',
				
				'autocomplete_url' => add_query_arg( array( 'action' => 'scribe_keyword_suggestions', 'scribe-keyword-suggestions-nonce' => wp_create_nonce( 'scribe-keyword-suggestions' ) ), admin_url( 'admin-ajax.php' ) ),
			);
			wp_localize_script('scribe-backend', 'Scribe_SEO_Configuration', $configuration);

			add_filter('tiny_mce_before_init', array(__CLASS__, 'add_tiny_mce_init_instance_callback'));
		}
		
		public static function modify_administrative_interface_items() {
			global $menu;
			$menu['58.11'] = array( '', 'read', 'separator-scribe', '', 'wp-menu-separator' );

		}
		
		public static function output_script_that_adds_target_blank_to_scribe_help_item() {
			include( SCRIBE_PLUGIN_DIR . 'views/backend/misc/help-script.php' );
		}
		
		public static function process_compatibility_check() {
			$data = stripslashes_deep($_POST);
			
			if(!empty($data['send-scribe-compatibility-report-nonce']) && wp_verify_nonce($data['send-scribe-compatibility-report-nonce'], 'send-scribe-compatibility-report')) {
				$compatibility = $data['scribe-compatibility'];
				
				$send_successful = self::send_test_results( $compatibility );
				
				if($send_successful) {
					add_settings_error('general', 'settings_updated', __('Your results were successfully sent to the Copyblogger support team.', 'scribeseo'), 'updated');
				} else {
					add_settings_error('general', 'settings_updated', sprintf( esc_html__('There was an issue sending your report. Please copy the below information and email it directly to %s.', 'scribeseo'), '<a href="mailto:support@copyblogger.com">support@copyblogger.com</a>' ), 'error');
				}
				set_transient('settings_errors', get_settings_errors(), 30);
				
				$location = add_query_arg( array( 'page' => urlencode( self::SETTINGS_SUB_PAGE_SLUG_COMPATIBILITY ), 'settings-updated' => 'true' ), admin_url( 'admin.php' ) );
				wp_redirect( $location );
				
				exit;
			}
		}
		
		public static function redirect_to_support() {
			wp_redirect( 'http://my.scribecontent.com/help/default.aspx' );
			exit;
		}
		
		public static function return_analysis_tabs($tabs) {
			$tabs = array(
				'scribe-analysis-keywords' => __('Keyword Analysis', 'scribeseo'),
				'scribe-analysis-document' => __('Page Analysis', 'scribeseo'),
				'scribe-analysis-tags' => __('Tags', 'scribeseo'),
				'scribe-analysis-help' => __('Help', 'scribeseo'),
			);
			
			return $tabs;
		}
		
		public static function return_keyword_tabs($tabs) {
			$tabs = array(
				'scribe-keyword-suggestions' => __('Suggestions', 'scribeseo')
			);
			
			if($_GET['tab'] == 'scribe-keyword-details') {
				$tabs['scribe-keyword-details'] = __('Details', 'scribeseo');
			}
			
			return $tabs;
		}
		
		public static function return_link_tabs($tabs) {
			return array();
		}
		
		public static function sanitize_scribe_settings($settings) {
			$settings['post-types'] = is_array($settings['post-types']) ? $settings['post-types'] : array();
			
			return $settings;
		}
		
		/// DISPLAY CALLBACKS
		
		//// META BOXES
		
		public static function display_analysis_meta_box($post) {
			$content_analysis = self::get_content_analysis($post->ID);
			$remaining_evaluations = self::get_content_analysis_evaluations_remaining(self::get_account());
			if ( is_object( $content_analysis ) )
				$content_analysis->keywordList = self::get_content_analysis_keywords( $content_analysis );

			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/content-analysis.php' );
		}
		
		public static function display_keyword_research_meta_box( $post ) {

			$previous_keywords = self::get_archived_keyword_suggestions( $post->ID );
			$target_term = self::get_target_term( $post->ID );
			$remaining_evaluations = self::get_keyword_evaluations_remaining( self::get_account() );

			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/keyword-research.php' );
		}
		
		public static function display_link_building_meta_box($post) {
			$content_analysis = self::get_content_analysis($post->ID);
			$content_score = ! empty( $content_analysis ) ? $content_analysis->docScore : '';

			$link_building_info = self::get_link_buiding_info($post->ID);
			$link_terms = self::get_content_analysis_keywords( $content_analysis );
			$link_terms_array = self::get_content_analysis_keywords( $content_analysis, false );
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/link-building.php' );
		}
		
		//// SETTINGS
		public static function display_connections_page() {
			$social_activity = self::build_social_activity ( '' );			
			add_meta_box('scribe-main-connections', __( 'Site Connections', 'scribeseo' ), array(__CLASS__, 'display_connections_meta_box'), 'scribe-connections', 'normal');
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/link-building/site-connections.php' );
		}
		public static function display_connections_meta_box($account) {
			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/connections.php' );
		}
		
		public static function display_account_page() {
			$settings = self::get_settings();
			$account = self::get_account($settings['api-key']);
			
			add_meta_box('scribe-main-account', __('Account Information', 'scribeseo'), array(__CLASS__, 'display_account_meta_box'), 'scribe-account', 'normal');
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/settings/account.php' );
		}
		
		public static function display_account_meta_box($account) {
			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/account.php' );
		}
		
		public static function display_compatibility_page() {
			add_meta_box('scribe-information-compatibility', __('Your Information', 'scribeseo'), array(__CLASS__, 'display_compatibility_info_meta_box'), 'scribe-compatibility', 'normal');
			add_meta_box('scribe-results-compatibility', __('Your Install Details', 'scribeseo'), array(__CLASS__, 'display_compatibility_results_meta_box'), 'scribe-compatibility', 'normal');
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/settings/compatibility.php' );
		}

		public static function display_compatibility_info_meta_box() {
			$user = wp_get_current_user();
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/compatibility-info.php' );
		}
		
		public static function display_compatibility_results_meta_box() {
			$test_results = self::get_test_results();
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/meta-boxes/compatibility-results.php' );
		}
		
		public static function display_news_page() {
			$feed = fetch_feed(self::SEO_NEWS_FEED_URL);
			
			include( SCRIBE_PLUGIN_DIR . 'views/backend/settings/news.php' );
		}
		
		public static function get_query_domain() {

			$settings = self::get_settings();
			$domain = parse_url( $settings['your-url'], PHP_URL_HOST );

			return $domain ? $domain : $settings['your-url'];

		}
		//// IFRAMES
		
		///// ANALYSIS
		
		public static function display_analysis_tabs() {
			wp_enqueue_style('media');
			return wp_iframe('scribe_analysis_iframe_callback');
		}
		
		public static function display_analysis_iframe() {
			add_filter('media_upload_tabs', array(__CLASS__, 'return_analysis_tabs'));
			media_upload_header();
			
			$data = stripslashes_deep($_REQUEST);
			$post_id = isset( $data['post_id'] ) ? (int)$data['post_id'] : 0;
			
			if ( empty( $post_id ) ) {
				include( SCRIBE_PLUGIN_DIR . 'views/backend/misc/popup-error.php' );
				return;
			}

			$tab = $data['tab'];
			$content_analysis = self::get_content_analysis($post_id);

			switch($tab) {
				case 'scribe-analysis-document':

					$serp_url = self::get_permalink( $post_id );

					include( SCRIBE_PLUGIN_DIR . 'views/backend/content-analysis/document-analysis.php' );
					break;
				case 'scribe-analysis-tags':
					$tags = array();
					foreach( $content_analysis->tags as $tag )
						$tags[$tag] = false;

					foreach( $content_analysis->keywords as $keyword ) {

						if ( isset( $tags[$keyword->text] ) && $keyword->kwl != 4 )
							$tags[$keyword->text] = true;

					}
					include( SCRIBE_PLUGIN_DIR . 'views/backend/content-analysis/tags.php' );
					break;
				case 'scribe-analysis-help':
					include( SCRIBE_PLUGIN_DIR . 'views/backend/content-analysis/help.php' );
					break;
				case 'scribe-analysis-keywords':
				default:
					$keywords = array();
					foreach( $content_analysis->keywords as $keyword ) {

						$keyword->padding = 0;
						$keywords[$keyword->kwr] = $keyword;

					}

					ksort( $keywords, SORT_NUMERIC );
					// check for overlap
					$maxkwr = max( array_keys( $keywords ) );
					for( $i = 0; $i < $maxkwr; $i++ ) {
						if ( ! isset( $keywords[$i] ) )
							continue;

						for( $j = $i + 1; $j <= $maxkwr; $j++ ) {
							if ( ! isset( $keywords[$j] ) )
								continue;

							if ( abs( $keywords[$i]->kwc - $keywords[$j]->kwc ) < 0.4 && abs( $keywords[$i]->kws - $keywords[$j]->kws ) < 1.0 )
								$keywords[$j]->padding++;

						}
					}
					$content_analysis->keywords = $keywords;
					$document_tab_url = add_query_arg( array( 'post_id' => $post_id, 'type' => 'scribe-analysis-keywords', 'tab' => 'scribe-analysis-document', 'scribe-content-analysis-review' => 'true' ), admin_url( 'media-upload.php' ) );
					include( SCRIBE_PLUGIN_DIR . 'views/backend/content-analysis/keyword-analysis.php' );
					break;
			}
?>
<script type="text/javascript">
// style the media tab

jQuery(document).ready(function($) {
	jQuery('ul#sidemenu').addClass('scribe-content-analysis-tabs');
	jQuery('ul#sidemenu li a').addClass('nav-tab').filter('.current').addClass('nav-tab-active');
});
</script>
<?php
		}
		
		///// KEYWORDS
		
		public static function display_keyword_tabs() {
			wp_enqueue_style('media');
			return wp_iframe('scribe_keyword_iframe_callback');
		}
		
		public static function display_keyword_iframe() {
			add_filter('media_upload_tabs', array(__CLASS__, 'return_keyword_tabs'));
			media_upload_header();
			
			$data = wp_parse_args( stripslashes_deep( $_REQUEST ), array(
				'post_id' => '',
				'tab' => '',
				'scribe-details-keyword' => '',
				'scribe-force-details-keyword-refresh' => '',
				'scribe-previous-keywords' => '',
				'scribe-details-previous' => '',
				'scribe-keyword' => '',
				'scribe-research-term' => ''
			) );
			$post_id = $data['post_id'];
			$user_id = get_current_user_id();
			
			if ( empty( $post_id ) ) {
				include( SCRIBE_PLUGIN_DIR . 'views/backend/misc/popup-error.php' );
				return;
			}

			$tab = $data['tab'];
			$queried_keyword = '';
			switch($tab) {
				case 'scribe-keyword-details':
					$queried_keyword = $data['scribe-details-keyword'];

					$details = self::get_keyword_details($queried_keyword, 'true' == $data['scribe-force-details-keyword-refresh']);

					include( SCRIBE_PLUGIN_DIR . 'views/backend/keyword-research/details.php' );
					break;
				case 'scribe-keyword-suggestions':
				default:
					$previous = $data['scribe-previous-keywords'] == 'true' || $data['scribe-details-previous'] == '1';

					$target_term = self::get_target_term( $post_id, $user_id );
					$previous_research_term = $data['scribe-research-term'];

					if ( $previous ) {

						$keywords = self::get_archived_keyword_suggestions_for_term( $post_id, $user_id, $previous_research_term );

					} else {

						$queried_keyword = urldecode($data['scribe-keyword']);
						$keywords = self::get_keyword_suggestions( $queried_keyword, $post_id, $user_id );

					}

					include( SCRIBE_PLUGIN_DIR . 'views/backend/keyword-research/suggestions.php' );
					break;
			}
		}
		
		//// LINK BUILDING
		
		public static function display_link_building_tabs() {
			wp_enqueue_style('media');
			return wp_iframe('scribe_link_building_iframe_callback');
		}
		
		public static function display_link_building_iframe() {
			add_filter('media_upload_tabs', array(__CLASS__, 'return_link_tabs'));
			media_upload_header();
						
			$data = stripslashes_deep($_REQUEST);
			$post_id = $data['post_id'];
			$user_id = get_current_user_id();
			
			$content_analysis = self::get_content_analysis( $post_id );
			$content_analysis_keywords = self::get_content_analysis_keywords( $content_analysis, false );
			$target_term = self::get_target_term( $post_id );

			$link_building_info = null;
			if ( isset( $data['scribe-link-building-review'] ) && 1 == $data['scribe-link-building-review'] )
				$link_building_info = self::get_link_buiding_info($post_id);

			$link_building_complete = isset( $link_building_info->keywords ) ? 'true' : 'false';

			if ( ! is_object( $link_building_info ) )
				$link_building_info = new stdClass;

			$initial_term = '';
			$link_building_keywords = isset( $link_building_info->keywords ) ? $link_building_info->keywords : array();
			if ( isset( $_GET['scribe-link-building-term'] ) ) {

				$initial_term = urldecode( $_GET['scribe-link-building-term'] );
				if ( ! in_array( $initial_term, $link_building_keywords ) )
					$link_building_keywords[] = ucwords( $initial_term );

			}

			include( SCRIBE_PLUGIN_DIR . 'views/backend/link-building/analysis.php' );
		}
		
		/// POST META
		
		//// WEB CONNECTOR
		
		private static function get_post_meta_item($post_id, $item) {
			if(empty($post_id)) {
				return false;
			}
			
			$value = apply_filters('scribe_pre_get_post_meta_item', false, $post_id, $item);
			
			if(empty($value)) {
				$settings = self::get_settings();
				if(!is_object($settings['seo-tool-settings'])) {
					return false;
				}
				
				$property = "meta_storage_{$item}";
				switch($settings['seo-tool-settings']->meta_storage) {
					case 'flat':
						$value = get_post_meta($post_id, $settings['seo-tool-settings']->{$property}, true);
						break;
					case 'array':
						$components = explode('.', $settings['seo-tool-settings']->{$property});
						$value = get_post_meta($post_id, $settings['seo-tool-settings']->meta_storage_key, true);
						
						while(!empty($components)) {
							$piece = array_shift($components);
							$value = $value[$piece];
						}
						break;
				}
			}
			
			return $value;
		}
		
		private static function set_post_meta_item($post_id, $item, $value) {
			if(empty($post_id)) {
				return false;
			}
			
			$handled = apply_filters('scribe_set_post_meta_item', false, $post_id, $item, $value);
			if($handled) {
				return true;
			}
			
			$settings = self::get_settings();
			if(!is_object($settings['seo-tool-settings'])) {
				return false;
			}
			
			$property = "meta_storage_{$item}";
			switch($settings['seo-tool-settings']->meta_storage) {
				case 'flat':
					update_post_meta($post_id, $settings['seo-tool-settings']->$property, $value);
					break;
				case 'array':
					$components = explode('.', $settings['seo-tool-settings']->$property);
					$existing = get_post_meta($post_id, $settings['seo-tool-settings']->meta_storage_key, true);
					
					$array_access_string = '';
					while(!empty($components)) {
						$piece = array_shift($components);
						$array_access_string .= "['{$piece}']";
					}
					eval("\$existing$array_access_string = \$value;");
					
					update_post_meta($post_id, $settings['seo-tool-settings']->meta_storage_key, $existing);
					break;
			}
			
			return true;
		}
		
		public static function get_post_meta_description($post_id) {
			return self::get_post_meta_item($post_id, 'description');
		}
		
		public static function get_post_meta_title($post_id) {
			return self::get_post_meta_item($post_id, 'title');
		}
		
		public static function set_post_meta_description($post_id, $post_meta_description) {
			return self::set_post_meta_item($post_id, 'description', $post_meta_description);
		}
		
		public static function set_post_meta_title($post_id, $post_meta_title) {
			return self::set_post_meta_item($post_id, 'title', $post_meta_title);
		}
		
		/// USER META
		
		//// CONTENT ANALYSIS
		
		private static function get_content_analysis_key() {
			return '_' . self::CONTENT_ANALYSIS_BASE_KEY;
		}
		
		private static function get_content_analysis_user_key( $post_id, $prefix = true ) {
			global $wpdb;
			return ( $prefix ? $wpdb->prefix : '' ) . self::CONTENT_ANALYSIS_BASE_KEY . '_' . $post_id;
		}
		
		private static function get_content_analysis($post_id = null, $user_id = null) {

			global $post;

			if ( empty( $post_id ) )
				$post_id = $post->ID;
			
			$content_analysis = get_post_meta( $post_id, self::get_content_analysis_key(), true );

			if ( empty( $content_analysis ) ) {

				if ( empty( $user_id ) )
					$user_id = get_current_user_id();

				$content_analysis = get_user_option( self::get_content_analysis_user_key( $post_id, false ), $user_id );

				if ( is_object( $content_analysis ) )
					self::set_content_analysis( $content_analysis, $post_id );
				else
					$content_analysis = false;
				
			}
			
			return self::process_content_analysis( $content_analysis );
		}

		private static function get_content_analysis_keywords( $content_analysis, $sep = ' + ', $all = true ) {

			if ( empty( $content_analysis->keywords ) || ! is_array( $content_analysis->keywords ) )
				return $sep ? '' : array();

			$keywords = array();
			foreach( $content_analysis->keywords as $keyword ) {

				if ( $all || $keyword->kwl == 1 )
					$keywords[] = $keyword->text;

			}

			$keywords = array_unique( $keywords );
			if ( $sep )
				return ucwords( implode( $sep, $keywords ) );

			return $keywords;

		}
		private static function process_content_analysis( $content_analysis ) {
			if ( ! empty( $content_analysis->KWS ) && is_array( $content_analysis->KWS ) )
				usort( $content_analysis->KWS, array( __CLASS__, 'sort_by_keyword_score' ) );
			
			if ( ! empty( $content_analysis->request ) && is_object( $content_analysis->request ) ) {
				$bing = new stdClass;
				$bing->engine = 'bing';
				
				$google = new stdClass;
				$google->engine = 'google';
				
				$content_analysis->serps = array($bing, $google);
				
				$content_analysis->serp_title = $content_analysis->request->html_title;
				if(strlen($content_analysis->serp_title) > 69) {
					$content_analysis->serp_title = substr($content_analysis->serp_title, 0, 69) . '...';
				}
				
				$content_analysis->serp_description = $content_analysis->request->description;
				if(strlen($content_analysis->serp_description) > 140) {
					$content_analysis->serp_description = substr($content_analysis->serp_description, 0, 140) . '...';
				}
				
				$content_analysis->serp_url = $content_analysis->request->url;
			}
			
			return $content_analysis;
		}
		
		public static function sort_by_keyword_score($a, $b) {
			return $b->KwC - $a->KwC;
		}
		
		private static function set_content_analysis($content_analysis, $post_id = null ) {

			global $post;

			if ( ! is_object( $content_analysis ) )
				return;

			if ( empty( $post_id ) )
				$post_id = $post->ID;
			
			$key = self::get_content_analysis_key();
			
			update_post_meta( $post_id, $key, $content_analysis );

		}
		
		//// LINK BUILDING
		
		private static function get_link_building_info_key() {
			return '_' . self::LINK_BUILDING_INFO_BASE_KEY;
		}
		
		private static function get_link_building_info_user_key( $post_id, $prefix = true ) {
			global $wpdb;
			return ( $prefix ? $wpdb->prefix : '' ) . self::LINK_BUILDING_INFO_BASE_KEY . '_' . $post_id;
		}
		
		private static function get_link_buiding_info( $post_id = null, $user_id = null ) {

			global $post;

			if ( empty( $post_id ) )
				$post_id = isset( $post->ID ) ? $post->ID : 0;
			
			$link_building_info = get_post_meta( $post_id, self::get_link_building_info_key(), true );
			
			if ( empty( $link_building_info ) ) {

				if ( empty( $user_id ) )
					$user_id = get_current_user_id();

				$link_building_info = get_user_option( self::get_link_building_info_user_key( $post_id, false ), $user_id );

				if ( is_object( $link_building_info ) )
					self::set_link_building_info( $link_building_info, $post_id );
				else
					$link_building_info = false;
				
			}
			
			return $link_building_info;
		}
		
		private static function set_link_building_info( $link_building_info, $post_id = null ) {

			global $post;

			if ( ! is_object( $link_building_info ) )
				return;

			if ( empty( $post_id ) )
				$post_id = isset( $post->ID ) ? $post->ID : 0;

			if ( ! empty( $post_id ) )
				update_post_meta( $post_id, self::get_link_building_info_key(), $link_building_info );

		}
		
		//// TARGET TERM
		
		private static function clear_target_term($post_id = null, $user_id = null) {
			if(empty($post_id)) {
				global $post;
				$post_id = $post->ID;
			}
			
			if(empty($user_id)) {
				$user_id = get_current_user_id();
			}
			
			$key = self::get_target_term_key( $post_id, false );
			delete_user_option( $user_id, $key );
		}
		
		private static function get_target_term_key( $post_id, $prefix = true ) {
			global $wpdb;

			return ( $prefix ? $wpdb->prefix : '' ) . self::TARGET_TERM_BASE_KEY . $post_id;
		}
		
		private static function get_target_term($post_id = null, $user_id = null) {
			if(empty($post_id)) {
				global $post;
				$post_id = $post->ID;
			}
			
			if(empty($user_id)) {
				$user_id = get_current_user_id();
			}
			
			$key = self::get_target_term_key($post_id);
			
			$target_term = wp_cache_get($key, $user_id);
			if(false === $target_term) {
				$target_term = trim( get_user_option( self::get_target_term_key( $post_id, false ), $user_id ) );
				wp_cache_set($key, $target_term, $user_id, time() + self::CACHE_PERIOD);
			}
			
			return $target_term;
		}
		
		private static function set_target_term($target_term, $post_id = null, $user_id = null) {
			global $post;
			if ( empty( $post_id ) )
				$post_id = $post->ID;
			
			if ( empty( $user_id ) )
				$user_id = get_current_user_id();
			
			$key = self::get_target_term_key($post_id);

			$target_term = strtolower( $target_term );
			update_user_option( $user_id, self::get_target_term_key( $post_id, false ), $target_term );
			wp_cache_set($key, $target_term, $user_id, time() + self::CACHE_PERIOD);
		}
		
		/// SETTINGS
		
		public static function get_settings() {
			$settings = wp_cache_get(self::SETTINGS_KEY);
			
			if(!is_array($settings)) {
				$settings = wp_parse_args(get_option(self::SETTINGS_KEY, self::$default_settings), self::$default_settings);
				wp_cache_set(self::SETTINGS_KEY, $settings, null, time() + self::CACHE_PERIOD);
			}
			
			return $settings;
		}
		
		private static function set_settings($settings) {
			if(is_array($settings)) {
				$settings = wp_parse_args($settings, self::$default_settings);
				update_option(self::SETTINGS_KEY, $settings);
				wp_cache_set(self::SETTINGS_KEY, $settings, null, time() + self::CACHE_PERIOD);
			}
		}
		public static function get_option( $key, $setting = null ) {
		
			/**
			 * Get setting. The default is set here, once, so it doesn't have to be
			 * repeated in the function arguments for accesspress_option() too.
			 */
			$setting = $setting ? $setting : self::SETTINGS_KEY;
		
			/** setup caches */
			static $settings_cache = array();
			static $options_cache = array();
		
			/** Short circuit */
			$pre = apply_filters( 'scribe_pre_get_option_'.$key, false, $setting );
			if ( false !== $pre )
				return $pre;
		
			/** Check options cache */
			if ( isset( $options_cache[$setting][$key] ) ) {
		
				// option has been cached
				return $options_cache[$setting][$key];
		
			}
		
			/** check settings cache */
			if ( isset( $settings_cache[$setting] ) ) {
		
				// setting has been cached
				$options = apply_filters( 'scribe_options', $settings_cache[$setting], $setting );
		
			} else {
		
				// set value and cache setting
				$options = $settings_cache[$setting] = apply_filters( 'scribe_options', get_option( $setting ), $setting );
		
			}
		
			// check for non-existent option
			if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) ) {
		
				// cache non-existent option
				$options_cache[$setting][$key] = '';
		
				return '';
			}
		
			// option has been cached, cache option
			$options_cache[$setting][$key] = is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );
		
			return $options_cache[$setting][$key];
		
		}
		public static function admin_redirect( $page = '', $query_args = array() ) {
		
			if ( ! $page )
				return;
		
			$url = html_entity_decode( menu_page_url( $page, 0 ) );
		
			foreach ( (array) $query_args as $key => $value ) {
				if ( empty( $key ) && empty( $value ) ) {
					unset( $query_args[$key] );
				}
			}
		
			$url = add_query_arg( $query_args, $url );
		
			wp_redirect( esc_url_raw( $url ) );
		
		}
		
		/// API DELEGATION
		
		//// ACCOUNT
		
		private static function get_account($api_key = null) {
			$settings = self::get_settings();
			
			if ( ! empty( $api_key ) ) {

				$scribe_api = new Scribe_API( $api_key, isset( $settings['security-method'] ) && 1 == $settings['security-method'] );
				return $scribe_api->get_user_details();

			}

			if ( empty( self::$scribe_api_account ) )
				self::$scribe_api_account = self::$scribe_api->get_user_details();
				
			return self::$scribe_api_account;

		}
		
		public static function get_content_analysis_evaluations_remaining( $account = null ) {

			if ( empty( $account ) )
				$account = self::get_account();

			return self::get_evaluations_remaining( $account, 'ContentAnalysis' );

		}
		
		public static function get_keyword_evaluations_remaining( $account = null ) {

			if ( empty( $account ) )
				$account = self::get_account();

			return self::get_evaluations_remaining( $account, 'KeywordIdeaResearch' );

		}

		private static function get_evaluations_remaining( $account, $type ) {

			if ( is_wp_error( $account ) || empty( $type ) )
				return 0;

			foreach( $account->evaluations as $evaluation_information ) {

				if ( $type == $evaluation_information->type )
					return $evaluation_information->remaining;

			}

			return 0;

		}
		//// CONTENT ANALYSIS
		
		private static function analyze_content($title, $description, $content, $headline, $keyword, $url) {
			return self::$scribe_api->analyze_content($title, $description, $content, $headline, $keyword, $url);
		}
		
		private static function get_keyword_recommendations( $possessed_keys, $keyword ) {

			$desired_keys = array();
			$desired_keys[0] = array(
				'KwET' => __( 'Add the keyword to the HTML title tag of your content.', 'scribeseo' ),
				'KwETP' => __( 'Add the keyword to the beginning of your HTML title tag.', 'scribeseo' ),
				'KwEM' => __( 'Add the keyword to the META DESCRIPTION tag of your content.', 'scribeseo' ),
				'KwEMP' => __( 'Add the keyword to the beginning of the META DESCRIPTION tag of your content.', 'scribeseo' ),
				'KwED' => __( 'Reduce the number of times you use the keyword in your content.', 'scribeseo' ),
				'KwEH' => __( 'Include the keyword in the title attribute of your hyperlink/anchor text.', 'scribeseo' ),
				'KwELF' => __( 'Add this keyword more frequently to the page.', 'scribeseo' ),
			);
			$desired_keys[1] = array(
				'KwET' => __( 'Add the keywords to the HTML title tag of your content.', 'scribeseo' ),
				'KwETP' => __( 'Add the keywords to the beginning of your HTML title tag.', 'scribeseo' ),
				'KwEM' => __( 'Add the keywords to the META DESCRIPTION tag of your content.', 'scribeseo' ),
				'KwEMP' => __( 'Add the keywords to the beginning of the META DESCRIPTION tag of your content.', 'scribeseo' ),
				'KwED' => __( 'Reduce the number of times you use the keywords in your content.', 'scribeseo' ),
				'KwEH' => __( 'Include the keywords in the title attribute of your hyperlink/anchor text.', 'scribeseo' ),
				'KwELF' => __( 'Add these keywords more frequently to the page.', 'scribeseo' ),
			);
			
			
			$index = ( count( explode(' ', $keyword ) ) > 1 ? 1 : 0 );
			$recommendations = array();
			foreach( $desired_keys[0] as $desired_key => $recommendation ) {

				if ( in_array( $desired_key, $possessed_keys ) )
					$recommendations[$desired_key] = $desired_keys[$index][$desired_key];

			}
			
			return $recommendations;
		}
		
		private static function get_content_recommendations($possessed_keys) {
			$desired_keys = array(
				'TitleLen' => __('The HTML title of the page contains less than three words, consider adding more words to the HTML title.', 'scribeseo'),
				'TitleCharLen' => __('The HTML title of the page is too long, consider shortening the title to less than 72 characters.', 'scribeseo'),
				'MetaCharLen' => __('The META DESCRIPTION is too long, consider shortening the description to less than 165 characters.', 'scribeseo'),
				'BodyLen' => __('The page is too short, consider adding more words to the page.', 'scribeseo'),
				'Hyper' => __('No hyperlinks were found in the page, consider adding hyperlinks to relevant terms.', 'scribeseo'),
				'HyperLen' => __('No hyperlinks were found in the first part of the page, consider adding a hyperlink to the first paragraph.', 'scribeseo'),
				'NoPKW' => __( 'No Primary Keywords were found in your content. See the information under Improve Keyword Rank to improve the identification of a keyword.', 'scribeseo' ),
			);
			
			$recommendations = array();
			foreach($desired_keys as $desired_key => $recommendation) {
				if(in_array($desired_key, $possessed_keys)) {
					$recommendations[$desired_key] = $recommendation;
				}
			}
			
			return $recommendations;
		}
		
		//// DEPENDENCIES
		
		public static function get_available_dependencies() {
			$available_dependencies = self::$scribe_api->get_supported_dependencies();

			$all_plugins = get_plugins();
			$active_plugins = array();
			foreach( (array) $all_plugins as $plugin_file => $plugin_data ) {

				if ( is_plugin_active( $plugin_file ) )
					$active_plugins[] = $plugin_data['Name'];

			}

			foreach( (array) $available_dependencies->plugins as $key => $value ) {

				$display_name = empty( $value->display_name ) ? $value->name : $value->display_name;
				if ( ! in_array( $display_name, $active_plugins ) )
					unset( $available_dependencies->plugins[$key] );

			}

			return $available_dependencies;

		}
		
		//// KEYWORD RESEARCH

		private static function get_keyword_details_key($keyword) {
			return self::KEYWORD_DETAILS_BASE_KEY . md5($keyword);
		}
		
		private static function get_keyword_details($keyword, $force_refresh) {
			$option_key = self::get_keyword_details_key($keyword);
			
			$details = wp_cache_get($option_key);
			if(empty($details) || $force_refresh) {
				$details = get_option($option_key);
			
				if(empty($details) || $force_refresh) {
					$settings = self::get_settings();
					
					$facebook_key = 'testapikey';
					$details = self::$scribe_api->get_keyword_details( $facebook_key, $keyword, self::get_query_domain() );
					
					if(!is_wp_error($details)) {
						$cached_details = clone $details;
						$cached_details->cached = true;
						update_option($option_key, $cached_details);
					}
				} else {
					$cached_details = $details;
				}
				wp_cache_set($option_key, $cached_details, null, time() + self::CACHE_PERIOD);
			}
			
			return $details;
		}
		
		private static function get_keyword_suggestions_key($keyword) {

			return self::KEYWORD_SUGGESTIONS_TRANSIENT_BASE_KEY . md5($keyword);

		}
		
		private static function get_keyword_suggestions($keyword, $post_id, $user_id = null) {

			$transient_key = self::get_keyword_suggestions_key( $keyword );
			$suggestions = get_transient( $transient_key );

			if ( ! empty( $suggestions ) )
				return $suggestions;

			$suggestions = self::$scribe_api->get_keyword_suggestions( $keyword );
			if ( is_wp_error( $suggestions ) )
				return $suggestions;

			$stamp = current_time( 'timestamp' );
			foreach( $suggestions as $suggestion )
				$suggestion->time_retrieved = $stamp;

			if ( empty( $user_id ) )
				$user_id = get_current_user_id();

			self::update_archived_keyword_suggestions( $suggestions, $keyword, $post_id, $user_id );
			
			set_transient( $transient_key, $suggestions, self::KEYWORD_SUGGESTIONS_TRANSIENT_TIME );
			
			return $suggestions;
		}
		
		private static function get_archived_keyword_suggestions( $post_id, $user_id = null ) {
			global $wpdb;

			if ( empty( $user_id ) )
				$user_id = get_current_user_id();

			$base_key = self::PREVIOUS_KEYWORD_SUGGESTIONS_KEY . $post_id;
			$keywords = wp_cache_get( $wpdb->prefix . $base_key, $user_id );
			
			if(false === $keywords) {
				$keywords = get_user_option( $base_key, $user_id );
				wp_cache_set( $wpdb->prefix . $base_key, $keywords, $user_id, time() + self::CACHE_PERIOD );
			}

			if ( empty( $keywords ) || ! is_array( $keywords ) )
				return array();

			// is it an array of previous keyword researches
			if ( is_array( current( $keywords ) ) )
				return $keywords;

			return array( $keywords );

		}

		private static function get_archived_keyword_suggestions_for_term( $post_id, $user_id, $term ) {

			$research = self::get_archived_keyword_suggestions( $post_id, $user_id );
			if ( array_key_exists( $term, $research ) )
				return $research[$term];

			return array();

		}

		private static function update_archived_keyword_suggestions( $suggestions, $keyword, $post_id, $user_id = null ) {
			global $wpdb;

			if ( empty( $user_id ) )
				$user_id = get_current_user_id();

			if ( empty( $suggestions ) || ! is_array( $suggestions ) )
				return;

			// merge previous research terms with the current ones for this research term
			$previous = self::get_archived_keyword_suggestions_for_term( $post_id, $user_id, $keyword );
			if ( ! empty( $previous ) ) {

				$suggestions = $suggestions + $previous;
				$suggestions = array_slice( $suggestions, 0, 10 );

			}

			// add or replace archived research for this research term
			$archived = self::get_archived_keyword_suggestions( $post_id, $user_id );
			$archived[$keyword] = $suggestions;

			// sort & keep 10 most recent research terms for this post & user
			$sort = array();
			foreach( $archived as $term => $keywords ) {

				$research_time = 0;
				do {

					$research_item = current( $keywords );
					if ( empty( $research_item->time_retrieved ) )
						continue;

					$research_time = $research_item->time_retrieved;

				} while ( ! $research_time && ! empty( $research_item ) );

				$sort[$research_time] = $term;

			}

			krsort( $sort );
			$sorted_archive = array();
			$count = 1;

			foreach( $sort as $term ) {

				$sorted_archive[$term] = $archived[$term];
				if ( $count++ > 9 )
					break;

			}
			$base_key = self::PREVIOUS_KEYWORD_SUGGESTIONS_KEY . $post_id;

			update_user_option( $user_id, $base_key, $sorted_archive );
			wp_cache_set( $wpdb->prefix . $base_key, $suggestions, $user_id, time() + self::CACHE_PERIOD );

		}

		/// G+ Activity

		private static function build_social_activity( $keyword, $links = null ) {

			if ( empty( $keyword ) && empty( $links ) )
				return '';

			if ( empty( $links ) )
				$links = self::build_links( $keyword, 'soc' );

			if ( is_wp_error( $links ) )
				return '<p>' . $links->get_error_message() . '</p>';

			if ( ( empty( $links->googlePlusActivities ) && empty( $links->twitterProfiles ) ) || ( ! is_array( $links->googlePlusActivities ) && ! is_array( $links->twitterProfiles ) ) )
				return '<p>' . esc_html__( 'No Activity', 'scribeseo' ). '</p>';

			$plus_format = '<li class="scribe-google-plus-item"><a href="%1$s" title="%2$s" class="img" target="_blank"><img src="%3$s" /></a><h4><a href="%1$s" title="%2$s" target="_blank">%2$s</a></h4> %4$s</li>';
			$twitter_format = '<li class="scribe-google-plus-item"><a href="%1$s" class="img" target="_blank"><img src="%2$s" /></a><h4><a href="%1$s" target="_blank">%1$s</a></h4> %3$s</li>';
			$output = '<ul class="scribe-google-plus-items">';

			foreach( (array)$links->googlePlusActivities as $activity )
				$output .= sprintf( $plus_format, esc_url( 'http://plus.google.com/u/0/' . $activity->profileId ), esc_attr( $activity->profileDisplayName ), esc_url( $activity->profileImageUrl ), wp_kses( $activity->content, self::formatting_allowedtags() ) );

			foreach( (array)$links->twitterProfiles as $activity )
				$output .= sprintf( $twitter_format, esc_url( 'http://twitter.com/' . $activity->profileDisplayName ), esc_url( $activity->profileImageUrl ), wp_kses( $activity->content, self::formatting_allowedtags() ), $activity->klout );

			$output .= '</ul>';

			return $output;

		}

		private static function build_gplus_comments( $keyword, $comments = null ) {

			if ( empty( $keyword ) && empty( $comments ) )
				return '';

			if ( empty( $comments ) )
				$comments = self::get_gplus_comments( $keyword );

			if ( is_wp_error( $comments ) )
				return '<p>' . esc_html( $comments->get_error_message() ) . '</p>';

			if ( empty( $comments->comments ) || ! is_array( $comments->comments ) )
				return '<p>' . esc_html__( 'No Activity', 'scribeseo' ). '</p>';

			$plus_format = '<li class="scribe-google-plus-item"><a href="%1$s" title="%2$s" class="img" target="_blank"><img src="%3$s" /></a><div class="scribe-google-plus-comment"><span class="scribe-google-plus-content">%4$s</span> <p>%5$s <span class="scribe-google-plus-author"><a href="%1$s" title="%2$s" target="_blank">%2$s</a></span></p></div></li>';
			$output = '<ul class="scribe-google-plus-items">';
			foreach( (array)$comments->comments as $comment )
				$output .= sprintf( $plus_format, esc_url( 'http://plus.google.com/u/0/' . $comment->profileId ), esc_attr( $comment->profileDisplayName ), esc_url( $comment->profileImageUrl ), wp_kses( $comment->content, self::formatting_allowedtags() ), esc_html__( 'Posted by', 'scribeseo' ) );

			$output .= '</ul>';

			return $output;

		}

		private static function get_gplus_comments( $keyword ) {

			return self::$scribe_api->get_comment_analysis( $keyword, self::get_query_domain() );

		}

		/// Twitter activity

		private static function build_twitter_activity( $links ) {

			if ( empty( $links ) )
				return '';

			if ( is_wp_error( $links ) )
				return '<p>' . esc_html( $links->get_error_message() ) . '</p>';

			if ( empty( $links->twitterProfiles ) || ! is_array( $links->twitterProfiles ) )
				return '<p>' . esc_html__( 'No Activity', 'scribeseo' ). '</p>';

			$twitter_format = '<li class="scribe-google-plus-item"><a href="http://twitter.com/%1$s" class="img" target="_blank"><img src="%2$s" /></a><h4><a href="http://twitter.com/%1$s" target="_blank">%2$s</a></h4> %3$s</li>';
			$output = '<ul class="scribe-google-plus-items">';
			foreach( (array)$links->twitterProfiles as $activity )
				$output .= sprintf( $twitter_format, esc_url( $activity->profileDisplayName ), esc_url( $activity->profileImageUrl ), esc_html( $activity->content ) );

			$output .= '</ul>';

			return $output;

		}

		/// LINK BUILDING
		
		private static function build_links( $keyword, $type ) {

			return self::$scribe_api->get_link_analysis( $keyword, self::get_query_domain(), $type );

		}
		
		/// SUPPORT SCRIPT
		
		private static function get_test_results() {
			$scribe_support = new Scribe_Support;
			return $scribe_support->get_test_results();
		}
		
		private static function send_test_results( $support ) {
			$name = trim( $support['name'] );
			$email = trim( $support['email'] );
			$issue = trim( $support['issue'] );
			
			if ( ! is_email( $email ) )
				return false;

			if ( empty( $name ) )
				$name = __('Unknown User', 'scribeseo');
			
			if(!empty($issue)) {
				$issue = sprintf(__("They also added the following comments:\n\n===\n\n%s\n\n===", 'scribeseo'), $issue);
			}
			
			$site_name = get_bloginfo('name');
			$site_domain = home_url('/');
			$email_subject = sprintf(__('Scribe Compatibility Check from %1$s at %2$s', 'scribeseo'), $name, $site_name);

			$test_results = self::get_test_results();
			$test_results_text = sprintf( "\n\nScreen Resolution: %s\nWindow Resolution: %s\nUser Agent: %s\n\n\n",
				$support['screen-size'],
				$support['window-size'],
				$_SERVER['HTTP_USER_AGENT']
			);

			foreach( $test_results as $test_result )
				$test_results_text .= "**{$test_result['name']}**\n{$test_result['value']}\n\n\n";

			$test_results_text = trim($test_results_text);
			
			$email_body = sprintf(__('Support Team,

This message is the result of running the Scribe SEO Compatibility Check on %1$s located at %2$s . The user provided the name %3$s and the email address %4$s. %5$s

The test results from the compatibility checker were as follows:

%6$s

Regards,
Scribe SEO Plugin', 'scribeseo'), $site_name, $site_domain, $name, $email, $issue, $test_results_text);

			$email_to = 'support@copyblogger.com';
			$email_headers = array(sprintf('From: %s', $email));
			return wp_mail($email_to, $email_subject, $email_body, $email_headers);
		}
		
		/// UTILITY

		public static function get_upload_iframe_src($type, $tab, $args) {
			if(empty($tab)) {
				$tab = $type;
			}
			
			if(!is_array($args)) {
				$args = array();
			}
			
			if(empty($args['post_id']) && !empty($_REQUEST['post_id'])) {
				$args['post_id'] = $_REQUEST['post_id'];
			}

			add_filter("{$type}_upload_iframe_src", array(__CLASS__, 'add_upload_iframe_args'));
			self::$upload_iframe_args = array_merge(array('type' => $type, 'tab' => $tab), $args);
			$url = get_upload_iframe_src($type);
			self::$upload_iframe_args = null;
			remove_filter("{$type}_upload_iframe_src", array(__CLASS__, 'add_upload_iframe_args'));
			return $url;
		}

		public static function sort_by_annual_volume($a, $b) {
			return $b->av - $a->av;
		}
		
		public static function verify_scribe_api_key( $key ) {

			$account = self::get_account( $key );
			return ! is_wp_error( $account );

		}

		public static function clear_keyword_research_cache() {

			global $wpdb;

			$blog_users = get_users();
			$ids = array();
			foreach( $blog_users as $user )
				$ids[] = $user->ID;

			$meta_query = $wpdb->prepare( "SELECT user_id, meta_key FROM {$wpdb->usermeta} WHERE  user_id IN (" . implode( ',', $ids ) . ') AND meta_key LIKE %s', $wpdb->get_blog_prefix() . self::PREVIOUS_KEYWORD_SUGGESTIONS_KEY . '%' );
			$cached_ids = $wpdb->get_results( $meta_query );

			if ( empty( $cached_ids ) )
				return;

			$prefix_length = strlen( $wpdb->get_blog_prefix() );
			foreach( $cached_ids as $row )
				delete_user_option( $row->user_id, substr( $row->meta_key, $prefix_length ) );

		}

		public static function formatting_allowedtags() {
		
			return array(
					'a'          => array( 'href' => array(), 'title' => array(), ),
					'b'          => array(),
					'blockquote' => array(),
					'br'         => array(),
					'div'        => array( 'align' => array(), 'class' => array(), 'style' => array(), ),
					'em'         => array(),
					'i'          => array(),
					'p'          => array( 'align' => array(), 'class' => array(), 'style' => array(), ),
					'span'       => array( 'align' => array(), 'class' => array(), 'style' => array(), ),
					'strong'     => array(),
		
			);
		
		}

		private static function get_permalink( $post_id ) {

			$post = get_post( $post_id );
			if ( empty( $post ) )
				return home_url( '/' );

			if ( $post->post_status == 'publish' )
				return get_permalink( $post_id );

			$rewritecode = array(
				'%year%',
				'%monthnum%',
				'%day%',
				'%hour%',
				'%minute%',
				'%second%',
				'%postname%',
				'%post_id%',
				'%category%',
				'%author%',
				'%pagename%',
			);

			$unixtime = strtotime( $post->post_date ? $post->post_date : time() );
			$permalink = get_option('permalink_structure');

			$category = '';
			if ( strpos($permalink, '%category%') !== false ) {
				$cats = get_the_category($post->ID);
				if ( $cats ) {
					usort($cats, '_usort_terms_by_ID'); // order by ID
					$category = $cats[0]->slug;
					if ( $parent = $cats[0]->parent )
						$category = get_category_parents($parent, false, '/', true) . $category;
				}
				// show default category in permalinks, without
				// having to assign it explicitly
				if ( empty($category) ) {
					$default_category = get_category( get_option( 'default_category' ) );
					$category = is_wp_error( $default_category ) ? '' : $default_category->slug;
				}
			}

			$author = '';
			if ( strpos($permalink, '%author%') !== false ) {
				$authordata = get_userdata($post->post_author);
				$author = $authordata->user_nicename;
			}

			$date = explode(" ",date('Y m d H i s', $unixtime));
			$rewritereplace =
			array(
				$date[0],
				$date[1],
				$date[2],
				$date[3],
				$date[4],
				$date[5],
				$post->post_name ? $post->post_name : sanitize_title_with_dashes( $post->post_title ),
				$post->ID,
				$category,
				$author,
				$post->post_name,
			);
			$permalink = home_url( str_replace($rewritecode, $rewritereplace, $permalink) );
			$permalink = user_trailingslashit($permalink, 'single');

			return $permalink;
		}
		// update database function
		private static function update() {

			global $wpdb;

			$settings = self::get_settings();
			$version = isset( $settings['version'] ) ? $settings['version'] : '4.0';
			// stop if the version is already the current one 
			if ( $version == self::VERSION )
				return;

			// 4.0.7
			if ( version_compare( $version, '4.0.8'. '<=' ) ) {

				$scribe_user_meta = $wpdb->get_results( "SELECT umeta_id, user_id, meta_key FROM {$wpdb->usermeta} WHERE meta_key like '_scribe%'" );
				if ( empty( $scribe_user_meta ) )
					return self::update_settings_version();

				foreach( $scribe_user_meta as $key => $meta_row ) {

					$scribe_user_meta[$key]->post_id = preg_replace( '|(\D+)|', '', $meta_row->meta_key );
					if ( ! is_multisite() )
						continue;

					// try to eliminate any that we know do not belong to this site
					if ( ! $scribe_user_meta[$key]->post_id || ! user_can( $meta_row->user_id, 'edit_posts' ) ) {

						unset( $scribe_user_meta[$key] );
						continue;

					}
					$post = get_post( $scribe_user_meta[$key]->post_id );
					if ( empty( $post ) || in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ) ) )
						unset( $scribe_user_meta[$key] );

				}

				$new_meta_keys = array(
					'content' => $wpdb->prefix . self::CONTENT_ANALYSIS_BASE_KEY,
					'link' => $wpdb->prefix . self::LINK_BUILDING_INFO_BASE_KEY,
					'target' => $wpdb->prefix . self::TARGET_TERM_BASE_KEY,
					'previous' => $wpdb->prefix . self::PREVIOUS_KEYWORD_SUGGESTIONS_KEY,
				);
					
				foreach( $scribe_user_meta as $meta_row ) {

					$key_parts = explode( '_', trim( $meta_row->meta_key, '_' ) );
					if ( ! isset( $new_meta_keys[$key_parts[1]] ) )
						continue;

					$wpdb->update( $wpdb->usermeta, array( 'meta_key' => $new_meta_keys[$key_parts[1]] . $meta_row->post_id ), array( 'umeta_id' => $meta_row->umeta_id ) );
				}

			} // 4.0.8

			self::update_settings_version();

			// remove the premise nag option
			delete_option( self::SHOW_PREMISE_NAG_KEY );


		}
		private static function update_settings_version() {

			$settings = self::get_settings();
			$settings['version'] = self::VERSION;
			self::set_settings( $settings );

			return true;
			
		}
		// plugin update handling
		public static function delete_plugin_info() {

			delete_transient( self::PLUGIN_INFO_CACHE_KEY );

		}
		public static function check_plugin_updates( $option ) {
			$info = get_transient( self::PLUGIN_INFO_CACHE_KEY );

			if ( ! $info ) {

				$info =	self::$scribe_api->get_latest_plugin_info();
				if( !$info )
					return $option;

				set_transient(  self::PLUGIN_INFO_CACHE_KEY, $info, self::CACHE_PERIOD );
			}

			$basename = plugin_basename( __FILE__ );
			if( ! isset( $option->response[$basename] ) || ! is_object( $option->response[$basename] ) )
				$option->response[$basename] = new stdClass();
	
			//Empty response means that the key is invalid. Do not queue for upgrade
			if( ! is_object( $info ) || ! isset( $info->version ) || version_compare( self::VERSION, $info->version, '>=' ) ) {
				unset( $option->response[$basename] );
			} else {
				$option->response[$basename]->url = $info->homepage;
				$option->response[$basename]->slug = $info->slug;
				$option->response[$basename]->package = $info->download_link;
				$option->response[$basename]->new_version = $info->version;
				$option->response[$basename]->id = "0";
			}
	
			return $option;
		}
		public static function is_menu_page( $pagehook = '' ) {

			global $page_hook;

			if ( isset( $page_hook ) && $page_hook == $pagehook )
				return true;

			/* May be too early for $page_hook */
			if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == $pagehook )
				return true;

			return false;

		}

	    function is_hosted() {

			return defined( 'SCRIBE_IS_HOSTED' ) && SCRIBE_IS_HOSTED;

		}

	    function is_managed() {

			return defined( 'SCRIBE_IS_MANAGED' ) && SCRIBE_IS_MANAGED;

		}

		public function contributor_allow_scribe( $allcaps, $caps, $args ) {

			$required_cap = 'upload_files';
			$cap = $args[0];
			$user_id = isset( $args[1] ) ? $args[1] : get_current_user_id();
		
			// only current user and upload cap
			if ( ! $user_id || $user_id != get_current_user_id() || $cap != $required_cap )
				return $allcaps;
		
			// check to see if the user already has the required cap
			if ( isset( $allcaps[$required_cap] ) )
				return $allcaps;
		
			// only provide cap for contributors
			if ( ! current_user_can( 'edit_posts' ) )
				return $allcaps;
		
			// restrict to media upload modal
			if ( strpos( $_SERVER['SCRIPT_NAME'], 'media-upload.php' ) === false || empty( $_GET['tab'] ) )
				return $allcaps;
		
			if ( ! in_array( $_GET['tab'], array(
				'scribe-analysis-keywords',
				'scribe-analysis-document',
				'scribe-analysis-tags',
				'scribe-analysis-help',
				'scribe-keyword-suggestions',
				'scribe-keyword-details',
				'scribe-link-building',
				'scribe-link-building-twitter-info'
			) ) )
				return $allcaps;
		
			$allcaps[$required_cap] = true;
			return $allcaps;
		
		}
	}
	
	// IFrame Callbacks
	
	function scribe_analysis_iframe_callback() {
		Scribe_SEO::display_analysis_iframe();
	}
	
	function scribe_keyword_iframe_callback() {
		Scribe_SEO::display_keyword_iframe();
	}
	
	function scribe_link_building_iframe_callback() {
		Scribe_SEO::display_link_building_iframe();
	}
	
	
	add_action( 'init', array( 'Scribe_SEO', 'init' ) );
}

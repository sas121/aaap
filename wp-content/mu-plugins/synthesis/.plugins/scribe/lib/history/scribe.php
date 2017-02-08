<?php
if ( !defined( 'ECORDIA_DEBUG' ) )
	define( 'ECORDIA_DEBUG', false );

require_once ('lib/ecordia-access/ecordia-content-optimizer.class.php');

if (!function_exists('json_encode') && file_exists(ABSPATH.'/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php')) {
	require_once (ABSPATH.'/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');
	function json_encode($data) {
		$json == new Moxiecode_JSON();
		return $json->encode($data);
	}
}

if (!class_exists('Ecordia')) {
	class Ecordia {

		var $version = '3.0.11';
		var $_meta_seoInfo = '_ecordia_seo_info';
		var $_meta_linkResearchInfo = '_ecordia_link_research';
		var $_option_ecordiaSettings = '_ecordia_settings';
		var $_option_cachedUserResults = '_ecordia_cachedUserResults';
		var $_option_keywordResearchList = '_ecordia_keyword_research_history';

		var $settings = null;
		var $_utility_DependencyUrl = 'http://vesta.ecordia.com/optimizer/docs/scribe-dependencies.xml';
		var $_possible_Item = array();
		var $_possible_Items = array();
		var $_possible_CurrentType = array();
		var $_possible_CurrentData = array();

		function Ecordia() {
			$this->addActions();

			wp_register_style('ecordia', plugins_url('resources/ecordia.css', __FILE__), array(), $this->version);
			wp_register_script('ecordia', plugins_url('resources/ecordia.js', __FILE__), array('jquery'), $this->version);
		}

		function addActions() {
			add_action('admin_head', array(&$this, 'addAdminHeaderCode'));
			add_action('admin_init', array(&$this, 'settingsSave'));
			add_action('admin_menu', array(&$this, 'addAdminInterfaceItems'));

			// Thickbox interfaces
			add_action('media_upload_ecordia-score', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-keyword-analysis', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-change-keywords', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-keyword-alternates', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-tags', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-serp', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-seo-best-practices', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-error', array(&$this, 'displayThickboxInterface'));
			add_action('media_upload_ecordia-debug', array(&$this, 'displayThickboxInterface'));

		}

		function addAdminHeaderCode() {

			global $pagenow;
			if (false !== strpos($pagenow, 'post') || false !== strpos($pagenow, 'page') || false !== strpos($pagenow, 'media-upload')) {
				include ( SCRIBE_PLUGIN_DIR . 'lib/history/views/admin-header.php' );
			}

		}

		function addAdminInterfaceItems() {

			$permission = $this->getPermissionLevel();

			if(current_user_can($permission)) {
				$dependency = $this->getEcordiaDependency();
				$title = esc_html__('Scribe v3 Score', 'scribeseo');
				$displayFunction = array(&$this, 'displayMetaBox');

				foreach($this->getSupportedPostTypes() as $type)
					add_meta_box('ecordia', $title, $displayFunction, $type, 'side', 'core');

			}

			global $pagenow;
			if (false !== strpos($pagenow, 'post') || false !== strpos($pagenow, 'page') || false !== strpos($pagenow, 'scribe') || false !== strpos($pagenow, 'edit')) {
				wp_enqueue_style('ecordia');
				wp_enqueue_script('ecordia');
			}
		}

		function addInitInstanceCallback($initArray) {
			$initArray['init_instance_callback'] = 'ecordia_addTinyMCEEvent';
			return $initArray;
		}

		function sanitizeForCall($value) {
			return str_replace(array('<![CDATA[',']]>'),array('',''),trim($value));
		}
		function getScheduledUserInfo() {
			$userInfo = $this->getUserInfo(true);
		}

		function getKeywordIdeas($phrase, $matchType) {
			$settings = $this->getSettings();
			$keywords = new EcordiaKeywordResearch($settings['api-key'], $settings['use-ssl']);
			$keywords->GetKeywordIdeas($phrase, $matchType);
			if(!$keywords->hasError()) {
				$this->setKeywordResearchIdeas($phrase, $matchType, $keywords->getRawResults());
			}
			return $keywords;
		}

		// DISPLAY CALLBACKS

		function displayMetaBoxError() {
			include ( SCRIBE_PLUGIN_DIR . 'lib/history/views/meta-box/error.php' );
		}

		function displayMetaBox($post) {

			if ( $this->postHasBeenAnalyzed( $post->ID ) )
				include ( SCRIBE_PLUGIN_DIR . 'lib/history/views/meta-box/after.php' );
			else
				include (SCRIBE_PLUGIN_DIR . 'lib/history/views/meta-box/before.php' );

		}

		function displayKeywordResearchMetaBox($post) {
			include(SCRIBE_PLUGIN_DIR . 'lib/history/views/keyword-research/meta-box.php' );
		}

		function displayLinkBuildingMetaBox($post) {
			if($this->postHasBeenAnalyzed($post->ID)) {
				include( SCRIBE_PLUGIN_DIR . 'lib/history/views/link-building/meta-box-after.php' );
			} else {
				include( SCRIBE_PLUGIN_DIR . 'lib/history/views/link-building/meta-box-before.php' );
			}
		}

		function displayThickboxInterface() {
			wp_enqueue_style('ecordia');
			wp_enqueue_script('ecordia');
			wp_enqueue_style('global');
			wp_enqueue_style('media');
			wp_iframe('ecordia_thickbox_include');
		}

		function thickboxInclude() {
			$pages = array('ecordia-score', 'ecordia-keyword-analysis', 'ecordia-change-keywords', 'ecordia-keyword-alternates', 'ecordia-tags', 'ecordia-serp', 'ecordia-seo-best-practices', 'ecordia-error');
			if( defined( 'ECORDIA_DEBUG' ) && ECORDIA_DEBUG ) {
				$pages[] = 'ecordia-debug';
			}
			$tab = in_array($_GET['tab'], $pages) ? $_GET['tab'] : 'ecordia-score';
			$page = str_replace('ecordia-', '', $tab);


			if (false === strpos($tab, 'error')) {
				add_filter('media_upload_tabs', array(&$this, 'thickboxTabs'));
				media_upload_header();
			}

			$info = $this->getSeoInfoForPost($_GET['post']);
			if (false === $info && false === strpos($tab, 'error')) {
				print '<form><p>No analysis present.</p></form>';
				return;
			}

			include ( SCRIBE_PLUGIN_DIR . 'lib/history/views/popup/'.$page.'.php' );
		}

		function keywordResearchThickboxInclude() {
			$phrase = urldecode($_GET['phrase']);
			$type = urldecode($_GET['match-type']);


			if($_GET['tab'] == 'ecordia-keyword-research-review') {
				include( SCRIBE_PLUGIN_DIR . 'lib/history/views/keyword-research/keyword-research-review.php' );
			} else {
				$info = $this->getKeywordResearchIdeas($phrase, $type);
				include( SCRIBE_PLUGIN_DIR . 'lib/history/views/keyword-research/keyword-research.php' );
			}
		}

		function settingsSave() {
			if (isset($_POST['save-ecordia-api-key-information']) && current_user_can('manage_options') && check_admin_referer('save-ecordia-api-key-information')) {
				$settings = $this->getSettings();
				$settings['api-key'] = trim(stripslashes($_POST['ecordia-api-key']));
				$settings['use-ssl'] = stripslashes($_POST['ecordia-connection-method']) == 'https';

				$permissions = array('manage_options','delete_others_posts','delete_published_posts','delete_posts');
				$settings['permissions-level'] = in_array($_POST['ecordia-permissions-level'],array_keys($permissions)) ? $_POST['ecordia-permissions-level'] : 'manage_options';

				$settings['seo-tool-method'] = empty($_POST['ecordia-seo-tool-method']) ? '' : '1';
				$settings['seo-tool'] = empty($_POST['ecordia-seo-tool-chooser']) ? array() : unserialize(stripslashes($_POST['ecordia-seo-tool-chooser']));

				$settings['ecordia-post-types'] = !is_array($_POST['ecordia-post-types']) ? array() : $_POST['ecordia-post-types'];


				$this->setSettings($settings);
				wp_redirect(admin_url('options-general.php?page=scribe&updated=true'));
				exit();
			}
		}

		function thickboxTabs($tabs) {
			$pages = array(
				'ecordia-score' => esc_html__('SEO Score', 'scribeseo'), 
				'ecordia-keyword-analysis' => esc_html__('Keyword Analysis', 'scribeseo'), 
				'ecordia-change-keywords' => esc_html__('Change Keywords', 'scribeseo'), 
				'ecordia-keyword-alternates' => esc_html__('Alternate Keywords', 'scribeseo'), 
				'ecordia-tags' => esc_html__('Tags', 'scribeseo'), 
				'ecordia-serp' => esc_html__('SERP', 'scribeseo'), 
				'ecordia-seo-best-practices' => esc_html__('SEO Best Practices', 'scribeseo')
			);
			if( defined( 'ECORDIA_DEBUG' ) && ECORDIA_DEBUG ) {
				$pages['ecordia-debug'] = esc_html__( 'Debug Info' , 'scribeseo');
			}
			return $pages;
		}
		function linkBuildingThickboxTabs($tabs) {
			return array(
				'ecordia-link-building-external' => esc_html__('External Links', 'scribeseo'), 
				'ecordia-link-building-internal' => esc_html__('Internal Links', 'scribeseo'), 
				'ecordia-link-building-social' => esc_html__('Social Media', 'scribeseo')
			);
		}

		// UTILITY - changed the order to support AIOSEO first before themes

		function getSupportedPostTypes() {
			$settings = $this->getSettings();

			if ( ! isset( $settings['ecordia-post-types'] ) || ! is_array( $settings['ecordia-post-types'] ) )
				return array( 'post', 'page' );

			return $settings['ecordia-post-types'];

		}

		function getNumberEvaluationsRemaining() {
			$userInfo = $this->getUserInfo(false);
			if(is_wp_error($userInfo)) {
				return '...';
			} else {
				return $userInfo->getCreditsRemaining();
			}
		}

		function getNumberKeywordEvaluationsRemaining() {
			$userInfo = $this->getUserInfo(false);
			if(is_wp_error($userInfo)) {
				return '...';
			} else {
				return $userInfo->getKeywordCreditsRemaining();
			}
		}

		function getAutomaticDependency() {
			return '';
		}

		function getAutomaticDependencyNiceName($automatic) {
			switch( $automatic ) {
				case 'aioseo':
					$currently = esc_html__( 'All in One SEO Pack Plugin' , 'scribeseo');
					break;
				case 'headwa':
					$currently = esc_html__( 'Headway Theme' , 'scribeseo');
					break;
				case 'hybrid':
					$currently = esc_html__( 'Hybrid Theme' , 'scribeseo');
					break;
				case 'genesis':
					$currently = esc_html__( 'Genesis Theme' , 'scribeseo');
					break;
				case 'thesis':
					$currently = esc_html__( 'Thesis Theme' , 'scribeseo');
					break;
				case 'fvaioseo':
					$currently = esc_html__( 'FV All in One SEO Pack' , 'scribeseo');
					break;
				case 'woothemes':
					$currently = esc_html__( 'WooThemes' , 'scribeseo');
					break;
				default:
					$currently = sprintf( '<span class="ecordia-error">%1$s</span>.  %2$s <a href="http://scribeseo.com/compatibility" target="_blank">http://scribeseo.com/compatibility</a>.', esc_html__( 'unable to detected theme/plugin', 'scribeseo' ), esc_html__( 'Please select the "Choose the SEO tool" option below. To see a list of compatible plugins and themes go to ' , 'scribeseo' ) );
					break;
			}
			return $currently;
		}

		function getEcordiaDependency() {

			$settings = $this->getSettings();
			if ( empty( $settings['seo-tool-method'] ) )
				return $this->getAutomaticDependency();

			return 'user-defined';

		}

		function getPossibleDependencies() {
			$response = wp_remote_get($this->_utility_DependencyUrl);
			if(is_wp_error($response)) {
				return array('plugins'=>array(),'themes'=>array());
			} else {
				$xml = $response['body'];
				$parser = xml_parser_create();
				xml_set_element_handler( $parser, array( $this, 'getPossibleDependenciesStartTag' ), array( $this, 'getPossibleDependenciesEndTag' ) );
				xml_set_character_data_handler( $parser, array( $this,'getPossibleDependenciesContents' ) );
				if(!xml_parse($parser,$xml)) {
					return array();
				} else {
					return $this->_possible_Items;
				}
			}
		}

		function getPossibleDependenciesStartTag($parser,$data) {
			$name = strtolower($data);
			switch($name) {
				case 'plugins':
				case 'themes':
					$this->_possible_CurrentType = $name;
					break;
				case 'name':
				case 'titleelementid':
				case 'descriptionelementid':
					$this->_possible_CurrentData = str_replace('elementid','',$name);
					break;
				case 'item':
					$this->_possible_Item = array();
					break;
			}
		}

		function getPossibleDependenciesEndTag($parser,$data) {
			$name = strtolower($data);
			switch($name) {
				case 'item':
					$this->_possible_Items[$this->_possible_CurrentType][] = $this->_possible_Item;
					break;
				case 'name':
				case 'titleelementid':
				case 'descriptionelementid':
					$this->_possible_CurrentData = '';
					break;
			}
		}

		function getPossibleDependenciesContents($parser,$data) {
			if(!empty($this->_possible_CurrentData)) {
				$this->_possible_Item[$this->_possible_CurrentData] = $data;
			}
		}

		function getSeoInfoForPost($postId) {
			$info = get_post_meta($postId, $this->_meta_seoInfo, true);
			if ( empty($info)) {
				$info = false;
			} else {
				if (is_array($info)) {
					$info = base64_encode(serialize($info));
				}
				$info = unserialize(base64_decode($info));
			}
			return $info;
		}
		function getLinkBuildingResearchForPostAndType($postId, $type) {
			$info = get_post_meta($postId, $this->_meta_linkResearchInfo.'_'.$type, true);
			if(empty($info)) {
				$info = false;
			} else {
				$info = unserialize(base64_decode($info));
			}
			return $info;
		}
		function setLinkBuildingResearchForPostAndType($postId, $type, $info) {
			if(is_array($info)) {
				$info = base64_encode(serialize($info));
				update_post_meta($postId, $this->_meta_linkResearchInfo.'_'.$type, $info);
			}
		}
		function getNumberExternalLinkResearch($postId) {
			$info = $this->getLinkBuildingResearchForPostAndType($postId, 'external');
			if(!$info) {
				return 0;
			} else {
				$links = $info['GetSearchEngineResultsResult']['Links']['SearchEngineLink'];
				return is_array($links) ? count($links) : 0;
			}
		}
		function getNumberInternalLinkResearch($postId) {
			$info = $this->getLinkBuildingResearchForPostAndType($postId, 'internal');
			if(!$info) {
				return 0;
			} else {
				$links = $info['GetSearchEngineResultsResult']['Links']['SearchEngineLink'];
				return is_array($links) ? count($links) : 0;
			}
		}
		function getNumberSocialLinkResearch($postId) {
			$info = $this->getLinkBuildingResearchForPostAndType($postId, 'social');
			if(!$info) {
				return 0;
			} else {
				$links = $info['GetSocialMediaSearchResultsResult']['Entries']['SocialMediaUser'];
				return count($links);
			}
		}

		function setKeywordResearchIdeas($phrase, $matchType, $ideas) {
			$key = 'keyword_research_info_'.md5($phrase.$matchType);
			$this->addKeywordResearchHistory($key, $phrase, $matchType);
			if(!is_array($ideas)) {
				$ideas = array();
			}
			update_option($key, $ideas);
		}

		function getKeywordResearchIdeas($phrase, $matchType) {
			$key = 'keyword_research_info_'.md5($phrase.$matchType);
			$info = get_option($key, array());
			if(!is_array($info)) {
				$info = array();
			}
			return $info;
		}

		function addKeywordResearchHistory($key, $phrase, $matchType) {
			$meta = $this->getKeywordResearchHistory();
			$meta[$key] = array('phrase'=>$phrase, 'type'=>$matchType);
			update_option($this->_option_keywordResearchList, $meta);
		}

		function getKeywordResearchHistory() {
			$meta = get_option($this->_option_keywordResearchList, array());
			if(!is_array($meta)) {
				$meta = array();
			}

			usort($meta, create_function('$a, $b', 'return strcmp($a["phrase"], $b["phrase"]);'));

			return $meta;
		}

		function getSeoScoreForPost($postId) {
			$info = $this->getSeoInfoForPost($postId);
			if (@is_numeric($info['GetAnalysisResult']['Analysis']['SeoScore']['Score']['Value'])) {
				return intval($info['GetAnalysisResult']['Analysis']['SeoScore']['Score']['Value']);
			}
			return false;
		}

		function getSeoScoreClassForPost($score) {
			$score = intval($score);
			if ($score <= 50) {
				return 'ecordia-score-low';
			} elseif ($score <= 75) {
				return 'ecordia-score-medium';
			} else {
				return 'ecordia-score-high';
			}
		}

		function getSeoPrimaryKeywordsForPost($postId) {
			$info = $this->getSeoInfoForPost($postId);
			if (false === $info) {
				return array();
			} else {
				$allKeywords = (array) $info['GetAnalysisResult']['Analysis']['KeywordAnalysis']['Keywords']['Keyword'];
				$primaryKeywords = array();
				foreach ($allKeywords as $keyword) {
					if ($keyword['Rank'] == 'Primary') {
						$primaryKeywords[] = $keyword['Term'];
					}
				}
				return $primaryKeywords;
			}
		}

		function postHasBeenAnalyzed($postId) {
			return false !== $this->getSeoInfoForPost($postId);
		}

		function getPostSeoData($postId) {
			$seoData = get_post_meta($postId, $this->_meta_seoInfo, true);
			if (!$seoData) {
				return false;
			} else {
				return $seoData;
			}
		}

		function getUserInfo($live = false) {

			$settings = $this->getSettings();
			if ( $live && empty( $settings['api-key'] ) ) {

				delete_option( $this->_option_cachedUserResults );
				return new WP_Error(-1, esc_html__('You must set an API key.', 'scribeseo'));

			}

			$userAccountAccess = new EcordiaUserAccount( $settings['api-key'], $settings['use-ssl'], $live );

			if ( $live ) {

				$userAccountAccess->UserAccountStatus();
				if ( $userAccountAccess->hasError() )
					return new WP_Error($userAccountAccess->getErrorType(), $userAccountAccess->getErrorMessage() . $userAccountAccess->client->response . '<br /> ' .$userAccountAccess->client->request, $userAccountAccess);

			} elseif ( ! $userAccountAccess->has_results() ) {

				return new WP_Error(-100, esc_html__('Fetching Information...', 'scribeseo'));

			}

			return $userAccountAccess;

		}

		function getSettings() {
			if (null === $this->settings) {
				$this->settings = get_option($this->_option_ecordiaSettings, array());
				$this->settings = is_array($this->settings) ? $this->settings : array();
			}
			return $this->settings;
		}
		function getPermissionLevel() {
			$settings = $this->getSettings();
			if(empty($settings['permissions-level'])) {
				$settings['permissions-level'] = 'administrator';
				$this->setSettings($settings);
			}
			return $settings['permissions-level'];
		}

		function setSettings($settings) {
			if (!is_array($settings)) {
				return;
			}
			$this->settings = $settings;
			update_option($this->_option_ecordiaSettings, $this->settings);
		}

		function displaySection($section) {
			include ( SCRIBE_PLUGIN_DIR . 'lib/history/views/misc/section-display.php' );
		}

		function getPostTypes() {
			// Scribe now requires WP 3.3 or higher
			return get_post_types( array( 'show_ui'=>true ), 'objects' );
		}
	}

	global $ecordia;
	$ecordia = new Ecordia;
	function ecordia_thickbox_include() {
		global $ecordia;
		$ecordia->thickboxInclude();
	}

	function ecordia_keyword_research_thickbox_include() {
		global $ecordia;
		$ecordia->keywordResearchThickboxInclude();
	}
	function ecordia_link_building_research_thickbox_include() {
		global $ecordia;
		$ecordia->linkBuildingResearchThickboxInclude();
	}
}

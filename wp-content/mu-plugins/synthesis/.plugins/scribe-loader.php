<?php

/**
* Plugin Name: Synthesis Managed Scribe
* Version: 1.0.0
* Description: Loads a managed implementation of Scribe
* Plugin Author: CopyBlogger Media
* Plugin URL: http://websynthesis.com
*/

if ( ! class_exists( 'Synthesis_Scribe_Loader' ) ) :

class Synthesis_Scribe_Loader {
/**
* The Synthesis provided Scribe API key.
*
* @since 1.0.0
*
* @var string Scribe API key
*/
private $api_key = false;	

/**
* CSS selectors for hiding research buttons in post edit screen.
*
* @since 1.0.0
*
* @var array CSS selectors
*/
private $button_selectors = array();	

function __construct() {

add_action( 'plugins_loaded', array( $this, 'load_scribe' ) );

}

function load_scribe() {

if ( class_exists( 'Scribe_SEO' ) || class_exists( 'Scribe_Data' ) )
return;

if ( ! get_site_option( 'synthesis_scribe_api_key' ) )
return;

// ensure the constants are set for our folders
define( 'SCRIBE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) . 'scribe/' );
define( 'SCRIBE_PLUGIN_URL', SYNTHESIS_SITE_PLUGIN_URL . 'scribe/' );

// substitute synthesis managed settings
add_filter( 'pre_option__ecordia_settings', '__return_zero' );
add_filter( 'option__scribe_settings', array( $this, 'scribe_settings_filter' ), 1 );
add_filter( 'pre_update_option__scribe_settings', array( $this, 'update_scribe_settings' ), 1, 2 );

define( 'SCRIBE_IS_MANAGED', true );

if ( is_file( SCRIBE_PLUGIN_DIR . 'scribe.php' ) )
require_once( SCRIBE_PLUGIN_DIR . 'scribe.php' );

add_action( 'admin_notices', array( $this, 'eval_notice' ) );
add_action( 'scribe_admin_before_metaboxes', array( $this, 'upgrade_notice' ) );
add_action( 'admin_footer-post.php', array( $this, 'admin_footer' ) );
add_action( 'admin_footer-post-new.php', array( $this, 'admin_footer' ) );
add_filter( 'http_response', array( $this, 'cache_account_info' ), 10, 3 );

}

function scribe_settings_filter( $default ) {

$this->api_key = get_site_option( 'synthesis_scribe_api_key' );
$security = get_site_option( 'synthesis_scribe_security' );

if ( ! is_array( $default ) )
$default = array();

$default['api-key'] = $this->api_key;
$default['security-method'] = $security;

return $default;

}

function update_scribe_settings( $newvalue, $oldvalue ) {

// remove our filter so we get the last saved value
remove_filter( 'option__scribe_settings', array( $this, 'scribe_settings_filter' ), 1 );
$settings = get_option( '_scribe_settings' );
add_filter( 'option__scribe_settings', array( $this, 'scribe_settings_filter' ), 1 );

$newvalue['api-key'] = isset( $settings['api-key'] ) ? $settings['api-key'] : '';
$newvalue['security-method'] = isset( $settings['security-method'] ) ? $settings['security-method'] : '';

return $newvalue;

}

function eval_notice() {

if ( ! class_exists( 'Scribe_SEO' ) )
return;

$remaining = $this->get_evaluations_remaining();

if ( ! is_array( $remaining ) )
return;

$api_key = $this->api_key ? $this->api_key : get_site_option( 'synthesis_scribe_api_key' );
if ( ! $api_key )
return;

remove_action( 'scribe_admin_before_metaboxes', array( $this, 'upgrade_notice' ) );
$notice = __( 'You have used the last of your complimentary %s evaluations. (You still have %s evaluations and %s evaluations remaining.)', 'scribeseo' );
$upsell = __( 'To continue turbocharging your content with Scribe, %s to register your risk-free account.', 'scribeseo' );
$upsell_url = sprintf( 'https://purchase.scribecontent.com/synthesis.aspx?apiKey=%s&plan=professional4&promo=20130424', urlencode( $api_key ) );
?>
<div id="synthesis-scribe-premise-nav" class="updated settings-error">
<p>
<?php printf( esc_html( $notice ), $remaining[0], $remaining[1], $remaining[2] ); ?>
</p>
<p>
<?php printf( esc_html( $upsell ), '<a href="' . $upsell_url . '">' . __( 'click here', 'scribeseo' ) . '</a>' ); ?>
</p>
<div class="clear"></div>
</div>
<?php
}

function upgrade_notice() {

if ( ! class_exists( 'Scribe_SEO' ) )
return;

$api_key = $this->api_key ? $this->api_key : get_site_option( 'synthesis_scribe_api_key' );
if ( ! $api_key )
return;	

$upsell = __( "Want even more content marketing power? You've had the chance to start using our Scribe content marketing software -- from right inside WordPress, Maybe it's time for more ... at an amazing price? %s", 'scribeseo' );
$upsell_url = sprintf( 'https://purchase.scribecontent.com/synthesis.aspx?apiKey=%s&plan=professional4&promo=20130424', urlencode( $api_key ) );
?>
<div id="synthesis-scribe-premise-nav" class="updated settings-error">
<p>
<?php printf( esc_html( $upsell ), '<a href="' . $upsell_url . '">' . __( 'Click here for the details', 'scribeseo' ) . '</a>' ); ?>
</p>
<div class="clear"></div>
</div>
<?php
}

function cache_account_info( $response, $args, $url ) {

$account_path = 'membership/user/detail/';
if ( strpos( $url, $account_path ) === false )
return $response;

$url = wp_parse_args( $url );
$apikey = current( $url );
if ( $apikey != get_site_option( 'synthesis_scribe_api_key' ) )
return $response;

if ( is_wp_error( $response ) || ! isset( $response['body'] ) )
return $response;

$body = str_replace( '-INF', 0, $response['body'] );
$object = @Scribe_API::urldecode_json_decode( $body );
if ( null !== $object )
update_site_option( 'synthesis_scribe_account', $object );

return $response;

}

function get_evaluations_remaining() {

$account_info = get_site_option( 'synthesis_scribe_account' );
if ( ! $account_info || ! isset( $account_info->evaluations ) )
return null;

$eval_types = array(
'KeywordIdeaResearch' => 'keyword',
'LinkAnalysis' => 'link',
'ContentAnalysis' => 'content',
);
$selectors = array(
'keyword' => '.scribe-keyword-research-search-button',
'link' => '.scribe-link-building-research-button',
'content' => '#scribe-content-analysis-analyze-button',
);

$remaining = array();
$out_of_evals = false;
foreach( $account_info->evaluations as $evaluation ) {

if ( ! isset( $evaluation->type ) || ! isset( $eval_types[$evaluation->type] ) )
continue;

$name = $eval_types[$evaluation->type];
$remaining[$name] = isset( $evaluation->remaining ) ? $evaluation->remaining : 0;
if ( ! $remaining[$name] ) {

$out_of_evals = $name;
$this->button_selectors[] = $selectors[$name];

}
}

if ( $out_of_evals ) {

unset( $remaining[$out_of_evals] );
$content = array( $out_of_evals );
foreach( $remaining as $description => $count )
$content[] = sprintf( '%d %s', $count, $description );

}

return $out_of_evals ? $content : true;

}

function admin_footer() {

if ( empty( $this->button_selectors ) )
return;

$selectors = implode( ', ', $this->button_selectors );
?>
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
jQuery('<?php echo esc_js( $selectors ); ?>').hide().siblings('.scribe-out-of-evals').show();
});
//-->
</script>
<?php
}
}

new Synthesis_Scribe_Loader;

endif; // Synthesis_Scribe_Loader

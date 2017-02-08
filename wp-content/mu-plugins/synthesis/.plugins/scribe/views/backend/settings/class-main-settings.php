<?php
class Scribe_Main_Settings extends Scribe_Admin_Boxes {

	function __construct() {

		$settings_field = Scribe_SEO::SETTINGS_KEY;
		$default_settings = array(
			'post-types' => array(
				'post',
				'page',
			),
			'your-url' => '',
			'api-key' => '',
			'seo-tool' => '',
			'security-method' => '',
			'permission-level' => 'manage_options',
			'seo-tool-settings' => null,
			'version' => Scribe_SEO::VERSION,
		);

		$menu_ops = array(
			'main_menu' => array(
				'page_title'	=> __( 'Scribe - Settings', 'scribeseo' ),
				'menu_title'	=> __( 'Scribe', 'scribeseo' ),
				'capability'	=> 'manage_options',
				'icon_url'		=> SCRIBE_PLUGIN_URL . 'resources/backend/img/16x16.png',
				'position'		=> '58.12'
			),
			'first_submenu' => array( /** Do not use without 'main_menu' */
				'page_title'	=> __( 'Scribe - Settings', 'scribeseo' ),
				'menu_title'	=> __( 'Settings', 'scribeseo' ),
				'capability'	=> 'manage_options'
			),
		);

		$page_ops = array(
					'screen_icon'       => 'scribe-settings',
		);

		$this->create( Scribe_SEO::SETTINGS_TOP_PAGE_SLUG, $menu_ops, $page_ops, $settings_field, $default_settings );

		add_filter( 'sanitize_option_' . $settings_field, array( $this, 'sanitize' ), 10, 2 );
	}

	function metaboxes() {

		add_meta_box( 'scribe-main-settings', __( 'Scribe Settings', 'scribeseo'), array( $this, 'main_settings_metabox' ), $this->pagehook, 'main' );

	}

	function main_settings_metabox() {
		$dependencies = Scribe_SEO::get_available_dependencies();

	?>
<table class="form-table">
	<tbody>
		<?php if ( ! Scribe_SEO::is_managed() ) { ?>
		<tr valign="top">
			<th scope="row">
				<label for="scribe-api-key"><?php esc_html_e( 'API Key', 'scribeseo' ); ?></label>
				<a class="scribe-help-marker" rel="popover" title="<?php esc_html_e( 'API Key', 'scribeseo' ); ?>" data-content="<?php esc_attr_e( 'Enter your API key for Scribe SEO. You will find your API key by logging in to https://my.scribeseo.com. Be sure to include the scribe- at the beginning of the key and that you do not have an extra space at the end after pasting it in the box. If you require an API key, go to https://purchase.scribeseo.com.', 'scribeseo' ); ?>" href="#">?</a>
			</th>
			<td>
				<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'api-key' ); ?>" id="<?php echo $this->get_field_id( 'api-key' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'api-key' ) ); ?>" />
			</td>
		</tr>
		<?php } ?>
		<tr valign="top">
			<th scope="row">
				<label for="scribe-seo-tool"><?php esc_html_e( 'SEO Tool', 'scribeseo' ); ?></label>
				<a class="scribe-help-marker" rel="popover" title="<?php esc_html_e( 'SEO Tool', 'scribeseo' ); ?>" data-content="<?php esc_attr_e( 'Use the drop down box to select the compatible SEO theme, framework or plugin you are using to set the title and meta description for your posts. See http://scribeseo.com/compatibility for a complete list of supported SEO themes and plugins.', 'scribeseo' ); ?>" href="#">?</a>
			</th>
			<td>
<?php
			$hiddens = array();
			if ( is_wp_error( $dependencies ) ) {
				printf( esc_html__( 'The list of available dependencies could not be retrieved. Please check the %1$s and submit a support ticket.', 'scribeseo' ), '<a href="' . add_query_arg( array( 'page' => 'scribe-compatibility' ), admin_url( 'admin.php' ) ) . '">' . esc_html__( 'compatibility page', 'scribeseo' ) . '</a>' );
			} else {
				$cumulative_count = count( $dependencies->plugins ) + count( $dependencies->themes );
				if ( 0 == $cumulative_count ) {
					printf( __( 'Please install and activate a valid SEO tool. You can see a full list of tools supported by Scribe at the Scribe SEO <a href="%s">compatibility</a> page.', 'scribeseo' ), 'http://scribeseo.com/compatibility/' );
				} elseif ( 1 == $cumulative_count ) {
					if ( ! empty( $dependencies->plugins ) ) {
						$plugin_dependency =  current( $dependencies->plugins );
						$name = $plugin_dependency->name . ( isset( $plugin_dependency->version ) ? ' (' . $plugin_dependency->version . ')' : '' );
						$hiddens[] = sprintf( '<input type="hidden" name="scribe-seo-tool-settings-plugin-%s" value="%s" />', sanitize_title_with_dashes( $name ), esc_attr( serialize( $plugin_dependency ) ) );
						echo '<input type="hidden" name="scribe[seo-tool]" value="plugin-' . esc_attr( $name ) . '" />';
						echo esc_html( $plugin_dependency->name );
					} else {
						$theme_dependency = current($dependencies->themes);
						$name = $theme_dependency->name . ( isset( $theme_dependency->version ) ? ' (' . $theme_dependency->version . ')' : '' );
						$hiddens[] = sprintf( '<input type="hidden" name="scribe-seo-tool-settings-theme-%s" value="%s" />', sanitize_title_with_dashes( $name ), esc_attr( serialize( $theme_dependency ) ) );
						echo '<input type="hidden" name="scribe[seo-tool]" value="theme-' . esc_attr( $name ) . '">';
						echo esc_html( $theme_dependency->name);
					}
?>
				<br />
				<small><em><?php esc_html_e( 'This tool was automatically chosen because it is the only supported tool you currently have activated.', 'scribeseo' ); ?></em></small>
<?php 
				} else {
?>
				<select class="scribe-select" name="<?php echo $this->get_field_name( 'seo-tool' ); ?>" id="<?php echo $this->get_field_id( 'seo-tool' ); ?>">
					<option value=""><?php esc_html_e( '-- Select One --', 'scribeseo' ); ?></option>
					<?php if ( ! empty( $dependencies->plugins ) ) { ?>
					<optgroup label="<?php esc_html_e( 'Plugins', 'scribeseo' ); ?>">
<?php 
						foreach( $dependencies->plugins as $plugin_dependency ) {
							$name = $plugin_dependency->name . ( isset( $plugin_dependency->version ) ? ' (' . $plugin_dependency->version . ')' : '' );
							$hiddens[] = sprintf( '<input type="hidden" name="scribe-seo-tool-settings-plugin-%s" value="%s" />', sanitize_title_with_dashes( $name ), esc_attr( serialize( $plugin_dependency ) ) );
?>
						<option <?php selected( "plugin-{$name}", $this->get_field_value( 'seo-tool' ) ); ?> value="plugin-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</optgroup>
					<?php } 
					
					if(!empty($dependencies->themes)) { ?>
					<optgroup label="<?php esc_html_e( 'Themes', 'scribeseo' ); ?>">
<?php
						foreach( $dependencies->themes as $theme_dependency ) {
							$name = $theme_dependency->name . ( isset( $theme_dependency->version ) ? ' (' . $theme_dependency->version . ')' : '' );
							$hiddens[] = sprintf( '<input type="hidden" name="scribe-seo-tool-settings-theme-%s" value="%s" />', sanitize_title_with_dashes( $name ), esc_attr( serialize( $theme_dependency ) ) );
?>
						<option <?php selected( "theme-{$name}", $this->get_field_value( 'seo-tool' ) ); ?> value="theme-<?php echo esc_attr( $name); ?>"><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</optgroup>
					<?php } ?>
				</select>
<?php
				}
			}
			echo implode( '', $hiddens );
?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="scribe-your-url"><?php esc_html_e( 'Your URL', 'scribeseo' ); ?></label>
				<a class="scribe-help-marker" rel="popover" title="<?php esc_attr_e( 'Your URL', 'scribeseo' ); ?>" data-content="<?php esc_attr_e( 'By default, the URL of your site is entered (and required). If your current WordPress site is not public, please enter a URL that is on the web.', 'scribeseo' ); ?>" href="#">?</a>
			</th>
			<td>
				<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'your-url' ); ?>" id="<?php echo $this->get_field_id( 'your-url' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'your-url' ) ); ?>" />
			</td>
		</tr>
		<?php if ( ! Scribe_SEO::is_managed() ) { ?>
		<tr valign="top">
			<th scope="row">
				<label for="scribe-security-method"><?php esc_html_e( 'Security Method', 'scribeseo' ); ?></label>
				<a class="scribe-help-marker" rel="popover" title="<?php esc_attr_e( 'Security Method', 'scribeseo' ); ?>" data-content='<?php esc_attr_e( ' By default, all communications with our servers are not encrypted. This means that we are not using SSL to hide your information on the web. For some, this may be acceptable. Other users may not want this. If you require SSL connections, then enable "Enhanced SSL" as described below. Otherwise, leave it at "Basic Non-SSL.”', 'scribeseo' ); ?>' href="#">?</a>
			</th>
			<td>
				<select class="scribe-select" name="<?php echo $this->get_field_name( 'security-method' ); ?>" id="<?php echo $this->get_field_id( 'security-method' ); ?>">
					<option <?php selected(false,$this->get_field_value( 'security-method' ) ); ?> value="0"><?php esc_html_e('Basic Non-SSL', 'scribeseo') ?></option>
					<option <?php selected(true, $this->get_field_value( 'security-method' ) ); ?> value="1"><?php esc_html_e('Enhanced SSL', 'scribeseo'); ?></option>
				</select>
			</td>
		</tr>
		<?php } ?>
		<tr valign="top">
			<th scope="row">
				<label for="scribe-permissions-level"><?php esc_html_e( 'Permissions', 'scribeseo' ); ?></label>
				<a class="scribe-help-marker" rel="popover" title="<?php _e( 'Permissions', 'scribeseo' ); ?>" data-content="<?php _e( 'Scribe enables you to control which user roles have access to Scribe. Click the drop down box to restrict Permissions to Administrators, Editors, Authors, or Contributors or higher. Set the lowest Role within WordPress that is available to use Scribe.', 'scribeseo' ); ?>" href="#">?</a>
			</th>
			<td>
				<select class="scribe-select" name="<?php echo $this->get_field_name( 'permissions-level' ); ?>" id="<?php echo $this->get_field_id( 'permissions-level' ); ?>">
					<option <?php selected('manage_options', $this->get_field_value( 'permissions-level' ) ); ?> value="manage_options"><?php esc_html_e('Administrator', 'scribeseo'); ?></option>
						<option <?php selected('delete_others_posts', $this->get_field_value( 'permissions-level' ) ); ?> value="delete_others_posts"><?php esc_html_e('Editor', 'scribeseo'); ?></option>
						<option <?php selected('delete_published_posts', $this->get_field_value( 'permissions-level' ) ); ?> value="delete_published_posts"><?php esc_html_e('Author', 'scribeseo'); ?></option>
						<option <?php selected('edit_posts', $this->get_field_value( 'permissions-level' ) ); ?> value="edit_posts"><?php esc_html_e('Contributor', 'scribeseo'); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php _e( 'Post Types', 'scribeseo' ); ?>
				<a class="scribe-help-marker" rel="popover" title="<?php esc_attr_e( 'Post Types', 'scribeseo' ); ?>" data-content="<?php esc_attr_e( 'By default, Posts and Pages in WordPress can use Scribe. If you have other Custom Post types, please select them for use with Scribe. Add a check mark next to each type of post that you want to be able to run Scribe SEO. No check mark indicates that Scribe will not be availble when editing the respective post type.', 'scribeseo' ); ?>" href="#">?</a>
			</th>
			<td>
				<ul>
					<?php foreach( get_post_types( array( 'show_ui' => true, 'public' => true ), 'objects' ) as $post_type_key => $post_type ) { ?>
					<li>
						<label>
							<input name="<?php echo $this->get_field_name( 'post-types' ); ?>[]" type="checkbox" <?php checked(true, in_array($post_type_key, (array)$this->get_field_value( 'post-types' ) ) ); ?> value="<?php echo esc_attr( $post_type_key ); ?>" />
							<?php echo esc_html( $post_type->labels->name ); ?>
						</label>
					</li>
					<?php } ?>
				</ul>
			</td>
		</tr>
	</tbody>
</table><?php
	}
	function sanitize( $newvalue, $option ) {

		if ( $option != $this->settings_field || empty( $_POST ) )
			return $newvalue;

		$data = stripslashes_deep( $_POST );
		$seo_tool_settings_key = isset( $newvalue['seo-tool'] ) ? 'scribe-seo-tool-settings-' . sanitize_title_with_dashes( $newvalue['seo-tool'] ) : '';
		$seo_tool_settings = isset( $data[$seo_tool_settings_key] ) ? maybe_unserialize( $data[$seo_tool_settings_key] ) : array();

		$newvalue['seo-tool-settings'] = $seo_tool_settings;

		$scribe_api_key_status = Scribe_SEO::is_managed();
		if ( ! $scribe_api_key_status )
			$scribe_api_key_status = ! empty( $newvalue['api-key'] ) ? Scribe_SEO::verify_scribe_api_key( $newvalue['api-key'] ) : false;

		if ( isset( $newvalue['your-url'] ) && $this->get_field_value( 'your-url' ) != $newvalue['your-url'] )
			Scribe_SEO::clear_keyword_research_cache();

		if ( ! $scribe_api_key_status )
			add_settings_error( '', 'scribe-api-key-invalid', __( 'Your API key may not be valid. Please verify you have entered the correct key.' , 'scribeseo'), 'error' );
		elseif ( ! empty( $newvalue['seo-tool'] ) && empty( $newvalue['your-url'] ) )
			add_settings_error( '', 'scribe-url-invalid', __( 'Scribe requires Your URL to function properly.', 'scribeseo' ), 'error' );

		return $newvalue;
	}
	public function enqueue_admin_css() {
			wp_enqueue_style( 'scribe-backend', SCRIBE_PLUGIN_URL . 'resources/backend/scribe.css', array('thickbox'), Scribe_SEO::VERSION );
	}
}

new Scribe_Main_Settings;

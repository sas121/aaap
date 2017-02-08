<?php
/**
 * Plugin Name: Synthesis Security
 * Description: Synthesis Security is proactive security monitoring and blocking software implemented as a 'must use' plugin specifically designed for the Synthesis Managed WordPress hosting stack.
 * Version: 1.1.14
 */

class Synthesis_Security {

    // Keep this up to date for upgrade purposes
    const VERSION = '1.1.14';

    // Where to redirect users when an attack is detected
    // '404page' redirects to home_url('404')
    // 'home' redirects to home_url()
    const REDIRECT_PAGE = 'home';

    // Nonce key for Synthesis settings page
    const NONCE_KEY = 'synthesis-security-disable-nonce';

    // Key used to store whether Synthesis Security is disabled
    const DISABLE_KEY = 'synthesis_disabled';

    // Key used to disable the new Synthesis Security features
    const AGGRESSIVE_MODE_KEY = 'synthesis_aggressive_mode';

    // How long to disable Synthesis in minutes
    const DISABLE_LENGTH = 5;

    // Admin messages are stored here for display on the admin_notices hook
    private static $admin_message = '';

    // Kick off the basic functionality. Hooks everything else together
    public static function start() {

        // Turn off synthesis security if in a WP_CLI instance 
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return false;
        }

        self::check_direct_load();
        add_action( 'init', array( __CLASS__, 'maybe_install' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_styles' ) );
        add_action( 'admin_notices', array( __CLASS__, 'check_wp_firewall_2' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
        add_action( 'wp_login_failed', array( __CLASS__, 'log_authentication_failure' ) );
        $disable = get_transient( self::DISABLE_KEY );
        if ( !$disable ) {
            self::maybe_block_request();
        }
    }

    // Register an admin page for Synthesis Security as well as a "save settings" callback
    public static function add_menu_page() {
        $page = add_options_page( __( 'Synthesis Security Settings' ), __( 'Synthesis Security' ), 'manage_options', 'synthesis-security', array( __CLASS__, 'settings_page' ) );
        add_action( "load-$page", array( __CLASS__, 'save_settings' ) );
    }

    // Save Synthesis Security settings
    // Currently the only "setting" is whether it is enabled
    public static function save_settings() {
        if ( isset( $_POST['synthesis-disable'] ) || isset( $_POST['synthesis-enable'] ) ) {
            $disable = isset( $_POST['synthesis-disable'] );
            $nonce = $_POST[self::NONCE_KEY];
            if ( wp_verify_nonce( $nonce, self::NONCE_KEY ) && current_user_can( 'manage_options' ) ) {
                if ( $disable ) {
                    set_transient( self::DISABLE_KEY, true, self::DISABLE_LENGTH * 60 );
                    self::$admin_message = sprintf( __( 'Synthesis Security has been disabled for %d minutes' ), self::DISABLE_LENGTH );
                } else {
                    delete_transient( self::DISABLE_KEY );
                    self::$admin_message = sprintf( __( 'Synthesis Security successfully enabled' ) );
                }
                add_action( 'admin_notices', array( __CLASS__, 'show_admin_message' ) );
            }
        }
    }

    // Display the Synthesis Security Settings Page
    public static function settings_page() {
    ?>
        <div class="wrap">
        <?php screen_icon( 'synthesis-management' ); ?>
            <h2><?php _e( 'Synthesis Security Settings' ); ?></h2>
            <p>
                <?php _e( 'Synthesis Security blocks malicious requests to protect your site from attacks. If you need to, you can disable it temporarily' ); ?>
            </p>
            <form method="post" action="">
                <?php wp_nonce_field( self::NONCE_KEY, self::NONCE_KEY ); ?>
                <?php if ( get_transient( self::DISABLE_KEY ) ) { ?>
                <p>
                    <span class="description" style="color: red;"><?php _e( 'Synthesis Security is currently disabled' ); ?></span>
                </p>
                <input type="submit" name="synthesis-enable" class="button-primary" value="<?php _e( 'Enable Synthesis Security' ); ?>" />
                <?php } else { ?>
                <p>
                    <span class="description"><?php _e( 'Synthesis Security is currently enabled' ); ?></span>
                </p>
                <input type="submit" name="synthesis-disable" class="button-primary" value="<?php _e( 'Temporarily Disable Synthesis Security' ); ?>" />
                <?php } ?>
            </form>
        </div>
    <?php
    }

    // Show a notice in the admin interface
    public static function show_admin_message() {
        if ( !empty( self::$admin_message ) ) {
            echo '<div class="updated"><p>' . self::$admin_message . '</p></div>';
        }
    }

    /**
     * Make sure this plugin isn't being loaded directly
     * @static
     * @return void
     */
    public static function check_direct_load() {
        if ( !defined( 'ABSPATH' ) )
            die();
    }

    /**
     * If default options haven't already been saved, do so now
     * @static
     * @return void
     */
    public static function maybe_install() {
        $version = get_option( 'synthesis_security_version' );
        if ( false === $version ) {

            add_option( 'synthesis_security_version', self::VERSION );
            add_option( 'synthesis_whitelisted_variables', array() );
            add_option( 'synthesis_whitelisted_ip', array() );

            // Migrate any existing whitelist settings from WP Firewall 2 or Synthesis Security < 0.1.7
            if ( $firewall_whitelisted_page = get_option( 'WP_firewall_whitelisted_page' ) ) {
                $firewall_whitelisted_page = unserialize( $firewall_whitelisted_page );
                $firewall_whitelisted_variable = unserialize( get_option( 'WP_firewall_whitelisted_variable' ) );
                $updated = array();
                for ( $i = 0; $i < count( $firewall_whitelisted_page ); $i++ ) {
                    $updated[] = array( 'page' => $firewall_whitelisted_page[$i], 'var' => $firewall_whitelisted_variable[$i] );
                }
                update_option( 'synthesis_whitelisted_variables', $updated );
            }
            if ( $synthesis_whitelisted_page = get_option( 'synthesis_whitelisted_page' ) ) {
                $synthesis_whitelisted_page = unserialize( $synthesis_whitelisted_page );
                $synthesis_whitelisted_variable = unserialize( get_option( 'synthesis_whitelisted_variable' ) );
                $updated = array();
                for ( $i = 0; $i < count( $synthesis_whitelisted_page ); $i++ ) {
                    $updated[] = array( 'page' => $synthesis_whitelisted_page[$i], 'var' => $synthesis_whitelisted_variable[$i] );
                }
                update_option( 'synthesis_whitelisted_variables', $updated );
            }
        } elseif ( $version !== self::VERSION ) {
            update_option( 'synthesis_security_version', self::VERSION );
        }
    }

    /**
     * Check whether WP Firewall 2 is enabled. If it is, warn the user that it isn't needed
     * @static
     * @return void
     */
    public static function check_wp_firewall_2() {
        if ( is_plugin_active( 'wp-firewall-2/wp-firewall-2.php' ) ){
            self::show_message( 'WordPress Firewall 2 is active. Synthesis hosting deploys similar functionality via the Must-Use plugin Synthesis Security. Disable and Delete WordPress Firewall 2 to avoid duplicate efforts and performance implications.' );
        }
    }

    /**
     * Display an admin message
     * @static
     * @param string $message The message to display
     * @param bool $error Whether the message is an error
     * @return void
     */
    public static function show_message( $message, $error = false ) {
        $class = $error ? 'error' : 'updated fade';
        echo "<div class=\"$class\"><p><strong>$message</strong></p></div>";
    }

    public static function filter_whitelisted_variables() {
        $whitelisted_variables = get_option( 'synthesis_whitelisted_variables' );

        $variables = self::flatten( $_REQUEST );
        
        // Get the page being requested
        preg_match( '#([^?]+)?.*$#', self::get_request_uri(), $page);
        $page = $page[1];


        $default_whitelisted_variables = array(
            array(
                '.*/wp-comments-post\.php',
                array( 'url', 'comment' )
            ),
            array(
                '.*/(wp-)?admin/.*',
                array( '_wp_original_http_referer', '_wp_http_referer' )
            ),
            array(
                '.*wp-login.php',
                array( 'redirect_to' )
            ),
            array(
                '.*',
                array( 'comment_author_url_.*', '__utmz' )
            ),
            array(
                '.*/(wp-)?admin/options\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/options-general\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/post-new\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/page-new\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/link-add\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/post\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/page\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/nav-menus\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/admin-ajax\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/network/edit\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/network/settings\.php',
                array( '.*' )
            ),
            array(
                '.*/(wp-)?admin/widgets\.php',
                array( '.*' )
            ),
        );

        // Loop through the default whitelisted pages and variables
        foreach ( $default_whitelisted_variables as $var ) {
            $page_regex = $var[0];
            $var_regexes = $var[1];

            if ( preg_match( '#' . $page_regex . '#', $page ) ) {
                foreach ( array_keys( $variables ) as $var ) {
                    foreach ( $var_regexes as $var_regex ) {
                        if ( preg_match( '#' . $var_regex . '#', $var ) ) {
                            unset( $variables[$var] );
                        }
                    }
                }
            }
        }

        // If there are no whitelisted variables, check everything
        if ( !empty ( $whitelisted_variables ) ) {

            // Loop through the whitelisted pages and variables
            foreach ( $whitelisted_variables as $whitelisted_entry ) {
                $wlisted_page = $whitelisted_entry['page'];
                $wlisted_var = $whitelisted_entry['var'];

                // Create a regex from the current whitelisted page
                $page_regex = str_replace( '*', '.*', $wlisted_page );
                $page_regex = '#' . preg_quote( $page_regex, '#' ) . '#';

                // Create a regex from the current whitelisted variable
                $var_regex = str_replace( '*', '.*', $wlisted_var );
                $var_regex = '#' . preg_quote( $var_regex, '#' ) . '#';

                // Check if the page is a match
                if ( preg_match( $page_regex, $page ) ) {
                    foreach ( array_keys( $variables ) as $var ) {
                        // Check if any of the variables are a match
                        if ( preg_match( $var_regex, $var ) ) {
                            // If they are, remove them from the list
                            unset( $variables[$var] );
                        }
                    }
                }
            }
        }
        return $variables;
    }

    /**
     * Check to see if this request violates our intrusion detection rules.
     * Logs and performs a redirect if it does, otherwise does nothing.
     * @static
     * @return void
     */
    public static function maybe_block_request() {
        // Check whether the current visitor has a whitelisted IP
        if( !self::check_whitelisted_ip() ) {
            // Attack Vectors

            $attack_vector_arrays = array(
                'directory-traversal-attack' => array( 'passwd' => 'etc/passwd', 'environ' => 'proc/self/environ', 'up' => '\.\.\/', 'local' => 'usr/local', 'root' => 'root/.*', 'u-root' => '-u\s*root' ),
                'sql-injection-attack' => array( 'concat' => 'concat\s*\(', 'group_concat' => 'group_concat', 'union' => 'union.*select', 'insertion' => '(\\\'|\%27).*(\-\-|\#|\%23)' ),
                'field-truncation-attack' => array( 'spaces' => '\s{49,}', 'nulls' => '\x00' ),
                'cross-site-scripting' => array( 'script' => '(\%3C|\<)(\%73|s)(\%63|c)(\%72|r)(\%69|i)(\%70|p)(\%74|t)[^\>]*(\%3E|\>)' ),
            );

            // These new features can cause false positives
            // If the "disable new features" option is enabled, disable those features
            if ( false === get_option( self::AGGRESSIVE_MODE_KEY, false ) ) {
                unset( $attack_vector_arrays['sql-injection-attack']['insertion'] );
                unset( $attack_vector_arrays['cross-site-scripting']['script'] );
            }

            $attack_vectors = array();
            foreach ( $attack_vector_arrays as $key => $attack_array ) {
                if ( !empty( $attack_array ) ) {
                    $attack_vectors[$key] = '#' . join( '|', $attack_array ) . '#i';
                }
            }

            $attack_categories = array(
                'directory-traversal-attack' => 'Directory Traversal',
                'sql-injection-attack' => 'SQL Injection',
                'field-truncation-attack' => 'Field Truncation',
                'cross-site-scripting' => 'Cross Site Scripting',
            );

            $filtered_request = self::filter_whitelisted_variables();

            foreach ( $filtered_request as $request_variable => $request_value ) {
                foreach( $attack_vectors as $attack_type => $regex ) {
                    if( preg_match( $regex, $request_value ) ) {
                        self::log_message( $request_variable, $request_value, $attack_type, $attack_categories[$attack_type] );
                        self::redirect();
                    }
                }
            }

            // Block unwanted file-extensions from being uploaded
            foreach ($_FILES as $file) {
                $disallowed_file_extensions = array('dll', 'rb', 'py', 'exe', 'php[3-6]?', 'pl', 'perl', 'ph[34]', 'phl', 'phtml?');
                $file_ext_regex = '#\.(' . join( '|', $disallowed_file_extensions ) . ")$#i";
                if ( ! is_array( $file['name'] ) ) {
                    if ( preg_match( $file_ext_regex, $file['name'] ) ) {
                        self::log_message( '$_FILE', $file['name'], 'executable-file-upload-attack', 'Executable File Upload' );
                        self::redirect();
                    }
                }
            }
        }
    }

    /**
     * Check whether the current visitor's IP is whitelisted
     * @static
     * @return bool Whether the current visitor's IP is whitelisted
     */
    public static function check_whitelisted_ip() {
        $current_ip = self::get_request_ip();
        $ips = get_option( 'synthesis_whitelisted_ip' );
        if ( is_array( $ips ) ) {
            foreach ( $ips as $ip ) {
                if ( $current_ip == $ip || $current_ip == gethostbyname( $ip ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function get_request_ip( $default = 'cli' ) {
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return $default;
        }
    }

    public static function get_request_uri() {
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            return $_SERVER['REQUEST_URI'];
        } else {
            // Return a whitelisted page when in CLI mode
            return '/wp-login.php';
        }
    }

    /**
     * Flattens a nested associative array. Nested entries become key[subkey][subsubkey] etc.
     * @static
     * @param array $array
     * @param string $prefix Used for recursive calls. Specifies a prefix for all keys
     * @return array The flattened associative array
     */
    public static function flatten( $array, $prefix = '' ) {
        $result = array();
        foreach ( $array as $key => $value ) {
            $new_prefix = empty( $prefix ) ? $key : "$prefix" . "[$key]";
            if ( is_array( $value ) ) {
                $flattened = self::flatten( $value, $new_prefix );
                $result = array_merge( $result, $flattened );
            } else {
                $result[$new_prefix] = $value;
            }
        }
        return $result;
    }

    /**
     * @param string $username The username used in the invalid login attempt
     * @return string The response from Loggly
     */
    public static function log_authentication_failure( $username ) {
        require_once( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . 'synthesis-blacklist.php' );

        $blog_url = get_bloginfo( 'url' );
        $domain = str_replace( 'http://', '', str_replace( 'https://', '', untrailingslashit( $blog_url ) ) );
        $bad_ip = Synthesis_Blacklist::get_client_ip();

        $args = array(
            'body' => json_encode( array( 'domain' => $domain, 'username' => $username, 'ip' => $bad_ip ) ),
            'headers' => array( 'Content-Type' => 'text/plain' )
        );
        
        if ( get_option( 'sng_level' ) ) {
            return wp_remote_post( 'https://logs-01.loggly.com/inputs/c6367de5-a83f-45bc-b345-8705e2b3fe01/tag/rmkr-wp-login/', $args );
        }

        return wp_remote_post( 'https://logs-01.loggly.com/inputs/e944274e-7924-4aa3-93d2-bdf3d6fc96b2/tag/wp-login/', $args );
    }

    /**
     * Log an attack message. Sends an email and sends a Syslog message
     * @static
     * @param string $bad_variable The variable that was determined to be an attack
     * @param string $bad_value The value of the variable that was determined to be an attack
     * @param string $attack_type The type of the attack
     * @param string $attack_category The category (name) of the attack
     * @return void
     */
     
    public static function log_message( $bad_variable, $bad_value, $attack_type, $attack_category ) {
        $bad_variable = htmlentities( $bad_variable );
        $bad_variable = ( mb_detect_encoding( $bad_variable ) != "UTF-8" ) ? mb_convert_encoding( $bad_variable, "UTF-8" ) : $bad_variable;
        $bad_value = htmlentities( $bad_value );
        $bad_value = ( mb_detect_encoding( $bad_value ) != "UTF-8" ) ? mb_convert_encoding( $bad_value, "UTF-8" ) : $bad_value;
        $bad_ip = self::get_request_ip();
        $blog_url = get_bloginfo( 'url' );
        $domain = str_replace( 'http://', '', str_replace( 'https://', '', untrailingslashit( $blog_url ) ) );

        $args = array(
            'body' => json_encode( array( 'attack' => $attack_type, 'category' => $attack_category, 'domain' => $domain, 'variable' => $bad_variable, 'value' => $bad_value, 'ip' => $bad_ip ) ),
            'headers' => array( 'Content-Type' => 'text/plain' )
        );

        if ( get_option( 'sng_level' ) ) {
            return wp_remote_post( 'https://logs-01.loggly.com/inputs/c6367de5-a83f-45bc-b345-8705e2b3fe01/tag/rmkr-general-security/', $args );
        }

        return wp_remote_post( 'https://logs-01.loggly.com/inputs/8c5a4176-251f-4930-bd4f-e44d61a15496/tag/general-security/', $args );
    }

    /**
     * Redirects the visitor to either the home page, or a "404" page, depending on the REDIRECT_PAGE class constant setting
     * @static
     * @return void
     */
    public static function redirect() {
        if ( '404page' == self::REDIRECT_PAGE ) {
            header( 'Location: ' . home_url( '404' ) );
        } else {
            header( 'Location: ' . home_url() );
        }
        die();
    }

    public static function add_styles( $hook ) {

        if ( $hook == 'settings_page_synthesis-security' )
            wp_enqueue_style( 'synthesis-management', SYNTHESIS_SITE_PLUGIN_URL . 'css/synthesis.css', array(), self::VERSION );

    }
}

Synthesis_Security::start();

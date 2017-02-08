<?php

if ( !class_exists( 'Synthesis_Block_Login_Attempts' ) ) {

   /**
     *  Blocks login attempts from an externally defined blacklist of IPs
     */
    class Synthesis_Block_Login_Attempts {

        // When set to true, this class does nothing
        const DISABLED = false;

        /*
         * Set up the authentication hook so we can block banned IPs from logging in
         */
        public static function start() {
            if ( !self::DISABLED ) {
                add_filter( 'authenticate', array( __CLASS__, 'block_login_attempts' ), 1000, 3 );
            }
        }

        /**
         * Check IPs on authentication to see if they've been banned
         * @static
         * @param string $user
         * @param string $username
         * @param string $password
         * @return WP_User
         */
        public static function block_login_attempts( $user, $username, $password ) {
            if ( !empty( $username ) ) {
                require_once( SYNTHESIS_CHILD_PLUGIN_INCLUDES_DIR . 'synthesis-blacklist.php' );

                // If the incoming IP is blacklisted, block the login.
                if ( Synthesis_Blacklist::is_blacklisted_ip() ) {
                    $user = null;
                }

                Synthesis_Blacklist::increment_logins_since_last_check();
            }
            return $user;
        }
    }

    // Kick things off
    Synthesis_Block_Login_Attempts::start();
}

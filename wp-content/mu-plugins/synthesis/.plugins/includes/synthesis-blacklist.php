<?php

class Synthesis_Blacklist {

    const IP_BLACKLIST_KEY = 'synthesis_block_login_attempts_ip_blacklist';
    const PROXY_RANGE_KEY = 'synthesis_block_login_attempts_ip_exceptions';

    const BLACKLIST_CHECK_PERIOD_KEY = 'synthesis_block_login_attempts_blacklist_check_period';
    const LOGINS_SINCE_LAST_CHECK_KEY = 'synthesis_block_login_attempts_logins_since_last_check';

    const IP_BLACKLIST_URL = 'http://security.websynthesis.com/wpfail.json';
    const PROXY_RANGE_URL = 'http://security.websynthesis.com/wpfailx.json';

    const DEFAULT_BLACKLIST_CHECK_PERIOD = 25;
    const DEFAULT_FAILED_LOGIN_THRESHOLD = 1000;

    const DISABLE_CACHE = false;

    public static function get_ip_blacklist() {
        $cached_ip_blacklist = get_option( self::IP_BLACKLIST_KEY, null );
        if ( is_null( $cached_ip_blacklist ) || self::need_refresh() ) {
            $server_blacklist = self::get_ip_blacklist_from_server();
            if ( !is_null( $server_blacklist ) ) {
                if ( is_null( $cached_ip_blacklist ) ) {
                    add_option( self::IP_BLACKLIST_KEY, $server_blacklist );
                } else {
                    update_option( self::IP_BLACKLIST_KEY, $server_blacklist );
                }
                return $server_blacklist;
            }
        }
        return $cached_ip_blacklist;
    }

    public static function get_ip_blacklist_from_server() {
        $blacklist_string = wp_remote_retrieve_body( wp_remote_get( self::IP_BLACKLIST_URL ) );
        $blacklist = json_decode( $blacklist_string, true );
        if ( is_array( $blacklist ) && isset( $blacklist['json.ip'] ) && is_array( $blacklist['json.ip'] ) ) {
            return self::format_blacklist_from_server( $blacklist['json.ip'] );
        } else {
            return array();
        }
    }

    public static function format_blacklist_from_server( $blacklist ) {
        $new_blacklist = array();
        foreach ( $blacklist as $entry ) {
            $new_blacklist[$entry['term']] = $entry['count'];
        }

        // Uncomment to test blacklist on local machine
        //$new_blacklist['127.0.0.1'] = self::DEFAULT_FAILED_LOGIN_THRESHOLD + 1;

        return $new_blacklist;
    }

    public static function get_proxy_ranges() {
        $cached_proxy_ranges = get_option( self::PROXY_RANGE_KEY, null );
        if ( is_null( $cached_proxy_ranges ) || self::need_refresh() ) {
            $server_ranges = self::get_proxy_ranges_from_server();
            if ( !is_null( $server_ranges ) ) {
                if ( is_null( $cached_proxy_ranges ) ) {
                    add_option( self::PROXY_RANGE_KEY, $server_ranges );
                } else {
                    update_option( self::PROXY_RANGE_KEY, $server_ranges );
                }
                return $server_ranges;
            }
        }
        return $cached_proxy_ranges;
    }

    public static function get_proxy_ranges_from_server() {
        $proxy_ranges_string = wp_remote_retrieve_body( wp_remote_get( self::PROXY_RANGE_URL ) );
        $exceptions = json_decode( $proxy_ranges_string, true );
        if ( is_array( $exceptions ) ) {
            return $exceptions;
        } else {
            // For testing fake responses on localhost
            // return array( array( 'start' => '127.0.0.1', 'end' => '127.0.0.1' ) );
            return null;
        }
    }

    /**
     * Checks to see if an IP is blacklisted. Uses request IP if none or null is provided.
     * @param null $ip IP to check. Uses REMOTE_ADDR, unless this is a known Synthesis proxy, and then it uses HTTP_X_FORWARDED_FOR
     * @return bool True if the IP is blacklisted.
     */
    public static function is_blacklisted_ip( $ip = null ) {
        if ( is_null( $ip ) ) {
            $ip = self::get_client_ip();
        }
        $blacklist = self::get_ip_blacklist();
        if ( is_null( $blacklist ) ) {
            return false;
        }
        if ( isset( $blacklist[$ip] ) && intval( $blacklist[$ip] ) > self::DEFAULT_FAILED_LOGIN_THRESHOLD ) {
            return true;
        }
        return false;
    }

    public static function is_proxy_ip( $ip = null ) {
        if ( is_null( $ip ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $proxy_ranges = self::get_proxy_ranges();

        if ( is_null( $proxy_ranges ) ) {
            return false; // TODO: true means everything is treated as a proxy. False means nothing is
        }

        foreach ( $proxy_ranges as $range ) {
            $range_start = ip2long( $range['start'] );
            $range_end = ip2long( $range['end'] );
            $ip_long = ip2long( $ip );
            if ( $range_start <= $ip_long && $ip_long <= $range_end ) {
                return true;
            }
        }

        return false;
    }

    public static function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];

         if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $X_FORWARDED_FOR = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                if (!empty($X_FORWARDED_FOR)) {
                        $ip = trim($X_FORWARDED_FOR[0]);
                }
        }
        return $ip ;
    }

    public static function need_refresh() {
        if ( self::DISABLE_CACHE ) {
            return true;
        }

        $logins_since_check = self::get_logins_since_last_check();
        $check_period = self::get_check_period();

        return $logins_since_check >= $check_period;
    }

    /**
     * Gets the number of login attempts that have occurred since the latest IP lists were retrieved
     * @static
     * @return int
     */
    public static function get_logins_since_last_check() {
        return intval( get_option( self::LOGINS_SINCE_LAST_CHECK_KEY ) );
    }

    /**
     * Gets the number of user logings allowed between checks for a new blacklist
     * @static
     * @return int
     */
    public static function get_check_period() {
        return intval( get_option( self::BLACKLIST_CHECK_PERIOD_KEY, self::DEFAULT_BLACKLIST_CHECK_PERIOD ) );
    }

    /**
     * Increments the number of logins since the IP blacklist was updated
     * @static
     */
    public static function increment_logins_since_last_check() {
        // Implemented as a direct SQL request because we need to do an atomic action to increment the counter
        /** @var wpdb $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare( "UPDATE $wpdb->options SET option_value = option_value+1 WHERE option_name = %s", self::LOGINS_SINCE_LAST_CHECK_KEY );
        $wpdb->query( $sql );
    }
}

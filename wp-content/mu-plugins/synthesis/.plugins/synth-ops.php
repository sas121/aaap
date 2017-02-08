<?php
function synth_clear_mem_check () {
    delete_transient( 'synthesis_memory_check' );
}
add_action('update_option_active_plugins', 'synth_clear_mem_check');

// If we're running from WP CLI, add our CLI commands.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    class Synthesis_Ops_Commands extends WP_CLI_Command {
        /**
         * @synopsis <wordpress|genesis> <version>
         * @subcommand wphash
         *
         */
        function wordpresshash( $args, $assoc_args ) {
            $filecount = 0;
            $hashes = "";
            $version = $args[1];

            $is_wordpress = false;

            if ( $args[0] == "wordpress" ) {
                WP_CLI::launch( "wget -c -q http://wordpress.org/wordpress-" . $version . ".zip -P /tmp/ || echo -n" );
                $is_wordpress = true;
            } elseif ( $args[0] == "genesis" ) {
                WP_CLI::launch( "wget -c -q http://genesistheme.com/download/sdf98h9p08hsdf009jsdf/genesis." . $version . ".zip -P /tmp/ || echo -n" );
            } else {
                WP_CLI::warning( "command usage: wp synth-ops wphash <wordpress|genesis> <version>" );
                exit();
            }

            $zip_file = ( $is_wordpress ) ? "/tmp/wordpress-" . $version . ".zip" : "/tmp/genesis." . $version . ".zip";

            if ( ! file_exists( $zip_file ) ) {
                WP_CLI::warning( "Wrong " . $args[0] . " version OR server not available at this moment." );
                exit();
            }

            $zip = zip_open( $zip_file );
            if ( is_resource( $zip ) ) {
                while ( $zip_entry = zip_read( $zip ) ) {
                    zip_entry_open( $zip, $zip_entry, "r" );

                    $filename = null;

                    if ( substr( zip_entry_name( $zip_entry ), -1 ) == "/" )
                        continue;

                    if ( preg_match( "/(farbtastic|install)-rtl\.css/", zip_entry_name( $zip_entry ) ) )
                        continue;

                    if ( $is_wordpress && preg_match( "/(wp-admin|wp-includes)\//", zip_entry_name( $zip_entry ) ) ) {
                        $filename = preg_replace( "/^wordpress\//", "", zip_entry_name( $zip_entry ) );
                    } else if ( ! $is_wordpress ) {
                        $filename = zip_entry_name( $zip_entry );
                    }

                    if ( ! is_null($filename) ) {
                        $hashes .= serialize( $filename ) . serialize( md5( zip_entry_read( $zip_entry, zip_entry_filesize( $zip_entry ) ) ) );
                        $filecount++;
                    }

                    zip_entry_close( $zip_entry );
                }

                $hashes = "a:" . $filecount . ":{" . $hashes . "}";
                $prefix = ( $is_wordpress ) ? "wp" : "genesis";

                WP_CLI::launch( "chmod -R 644 " . SYNTHESIS_CHILD_PLUGIN_DIR . "hashfiles/" );
                WP_CLI::launch( "echo '" . $hashes . "' > " . SYNTHESIS_CHILD_PLUGIN_DIR . "hashfiles/" . $prefix . $version . ".php" );

                # uncomment to use the same owner than SYNTHESIS_CHILD_PLUGIN_DIR to the hashfiles directory
                #WP_CLI::launch( "chown -R $(stat -c %U " . SYNTHESIS_CHILD_PLUGIN_DIR . "):$(stat -c %U " . SYNTHESIS_CHILD_PLUGIN_DIR . ") " . SYNTHESIS_CHILD_PLUGIN_DIR . "hashfiles/" );

                WP_CLI::launch( "chmod -R 400 " . SYNTHESIS_CHILD_PLUGIN_DIR . "hashfiles/" );

                WP_CLI::success( $args[0] . " " . $version . " hash file has been generated." );
            }
            zip_close( $zip );
        }

        /**
         * @synopsis --version=<version>
         * @subcommand update-genesis
         *
         */
        function updategenesis( $args, $assoc_args ) {
            $version = $assoc_args['version'];
            $genesis = "genesis." . $version . ".zip";

            WP_CLI::launch( "wget -q http://genesistheme.com/download/sdf98h9p08hsdf009jsdf/" . $genesis . " -P /tmp/ || echo -n" );

            if ( ! file_exists( "/tmp/" . $genesis ) ) {
                WP_CLI::warning( "Wrong Genesis version OR server not available at this moment." );
                exit();
            }

            if ( is_dir( get_theme_root() . "/genesis" ) ) {
                WP_CLI::launch( "rm -rf " . get_theme_root() . "/genesis" );
            }

            WP_CLI::launch( "unzip /tmp/" . $genesis . " -d " . get_theme_root() . " > /dev/null 2>&1" );
            WP_CLI::launch( "chown -R $(stat -c %U " . get_theme_root() . "):$(stat -c %U " . get_theme_root() . ") " . get_theme_root() . "/genesis" );

            if ( is_dir( get_theme_root() . "/genesis" ) ) {
                WP_CLI::success( "Genesis " . $version . " has been reinstalled." );
                WP_CLI::run_command( array( 'synth-ops', 'verify-genesis' ) );
            }
        }

        /**
         * @synopsis [--check] [--default-settings] [--preserve-cdn]
         * @subcommand total-cache
         *
         */
        function w3tc( $args, $assoc_args ) {
            if ( ! file_exists( WP_CONTENT_DIR . "/plugins/w3-total-cache/" ) ) {
                WP_CLI::warning( "W3 Total Cache plugin is not installed into plugins/w3-total-cache/. Skipping." );
                exit();
            }

            if ( ! is_plugin_active( "w3-total-cache/w3-total-cache.php" ) ) {
                WP_CLI::warning( "W3 Total Cache plugin is not enabled. Skipping." );
                exit();
            }

            $w3tc_version = get_plugin_data( WP_CONTENT_DIR . "/plugins/w3-total-cache/w3-total-cache.php" );
            $w3tc_version = $w3tc_version['Version'];

            $w3tc_master = ( $w3tc_version >= "0.9.2.6" ) ? WP_CONTENT_DIR . "/w3tc-config/master.php" : WP_CONTENT_DIR . "/w3-total-cache-config.php";

            if ( ! file_exists( $w3tc_master ) ) {
                WP_CLI::warning( "W3 Total Cache config file can not be found. Skipping." );
                exit();
            }

            if ( isset( $assoc_args['check'] ) ) {
                $file = fopen( $w3tc_master, "r" );

                while ( ! feof( $file ) ) {
                    $line = fgets( $file );

                    if ( preg_match( '/pgcache\.enabled\' => true/', $line ) ) { $pgcache = true; continue; }
                    if ( preg_match( '/pgcache\.engine\' => \'file\'/', $line ) ) { $pgcache_method = "disk: basic"; continue; }
                    if ( preg_match( '/pgcache\.engine\' => \'file_generic\'/', $line ) ) { $pgcache_method = "disk: enhanced"; continue; }
                    if ( preg_match( '/pgcache\.engine\' => \'apc\'/', $line ) ) { $pgcache_method = "opcode: apc"; continue; }
                    if ( preg_match( '/minify\.enabled\' => true/', $line ) ) { $minify = true; continue; }
                    if ( preg_match( '/dbcache\.enabled\' => true/', $line ) ) { $dbcache = true; continue; }
                    if ( preg_match( '/objectcache\.enabled\' => true/', $line ) ) { $objectcache = true; continue; }
                    if ( preg_match( '/objectcache\.engine\' => \'file\'/', $line ) ) { $objectcache_method = "disk"; continue; }
                    if ( preg_match( '/objectcache\.engine\' => \'apc\'/', $line ) ) { $objectcache_method = "apc"; continue; }
                    if ( preg_match( '/fragmentcache\.enabled\' => true/', $line ) ) { $fragmentcache = true; continue; }
                    if ( preg_match( '/fragmentcache\.engine\' => \'file\'/', $line ) ) { $fragmentcache_method = "disk"; continue; }
                    if ( preg_match( '/fragmentcache\.engine\' => \'apc\'/', $line ) ) { $fragmentcache_method = "apc"; continue; }
                    if ( preg_match( '/browsercache\.enabled\' => true/', $line ) ) { $browsercache = true; continue; }
                    if ( preg_match( '/cdn\.enabled\' => true/', $line ) ) { $cdn = true; continue; }
                }

                fclose( $file );

                echo "\033[1;34mW3 Total Cache " . $w3tc_version . " (" . ( ( file_exists( "/var/run/apache2.pid" ) || file_exists( "/run/apache2/apache2.pid" ) ) ? "apache+nginx" : "nginx+php5-fpm" ) . ")\033[0m\n";
                echo "\n\033[34mPage Cache:\033[0m " . ( isset( $pgcache ) ? "enabled (" . $pgcache_method . ")" : "-" );
                echo "\n\033[34mMinify:\033[0m " . ( isset( $minify ) ? "enabled" : "-" );
                echo "\n\033[34mDatabase Cache:\033[0m " . ( isset( $dbcache ) ? "enabled" : "-" );
                echo "\n\033[34mObject Cache:\033[0m " . ( isset( $objectcache ) ? "enabled (" . $objectcache_method . ")" : "-" );
                echo "\n\033[34mFragment Cache:\033[0m " . ( isset( $fragmentcache ) ? "enabled (" . $fragmentcache_method . ")" : "-" );
                echo "\n\033[34mBrowser Cache:\033[0m " . ( isset( $browsercache ) ? "enabled" : "-" );
                echo "\n\033[34mCDN:\033[0m " . ( isset( $cdn ) ? "enabled" : "-" ) . "\n";
            } else if ( isset( $assoc_args['default-settings'] ) ) {
                $siteurl = preg_replace( "/https?:\/\/(www.)?/", "", home_url() );
                if ( strlen( $siteurl ) == 0 || !is_dir( "/var/www/" . $siteurl ) ) {
                    WP_CLI::error( "Unable to find site root. Skipping." );
                    return;
                }
            
                if ( !is_writable( "/var/www/" . $siteurl ) ) {
                    WP_CLI::error( "Site root is not writable. Please check the permissions. Skipping." );
                    return;
                }

                $config = ( file_exists( "/var/run/apache2.pid" ) || file_exists( "/run/apache2/apache2.pid" ) ) ? "master_apache" : "master_nginx";

                if ( file_exists( "/tmp/" . $config ) ) {
                    WP_CLI::launch( "rm /tmp/" . $config );
                }

                if ( file_exists( "/tmp/cdn_settings" ) ) {
                    WP_CLI::launch( "rm /tmp/cdn_settings" );
                }

                $url = "https://tiagohillebrandt.eti.br/w3tc/" . $w3tc_version . "/" . $config;
                WP_CLI::launch( "wget -q " . $url . " -P /tmp/ || echo -n" );

                if ( ! file_exists( "/tmp/" . $config ) ) {
                    WP_CLI::warning( "W3TC version not compatible or server not available at this moment. Please import the settings manually." );
                    exit;
                }

                $old_config = null;
                if ( isset( $assoc_args['preserve-cdn'] ) ) {
                    require_once W3TC_LIB_W3_DIR . "/ConfigData.php";

                    $cdn_url = "https://tiagohillebrandt.eti.br/w3tc/" . $w3tc_version . "/cdn_settings";
                    WP_CLI::launch( "wget -q " . $cdn_url . " -P /tmp/ || echo -n" );

                    if ( ! file_exists( "/tmp/cdn_settings" ) ) {
                        WP_CLI::warning( "Not able to preserve the CDN settings. Skipping." );
                        exit;
                    }

                    $old_config = W3_ConfigData::get_array_from_file( $w3tc_master );
                }

                $backup_file = preg_replace( "/https?:\/\/(www.)?/", "", home_url() );
                $backup_file = $backup_file . ".master.php";
                $backup_file = preg_replace( "/\//", "_", $backup_file );
                WP_CLI::launch( "mv " . $w3tc_master . " /var/www/" . $siteurl . "/." . $backup_file );
                WP_CLI::line( "Current settings backup: /var/www/" . $siteurl . "/." . $backup_file );

                WP_CLI::launch( "rm " . WP_CONTENT_DIR . "/advanced-cache.php 2> /dev/null || echo -n" );
                WP_CLI::launch( "rm " . WP_CONTENT_DIR . "/db.php 2> /dev/null || echo -n" );
                WP_CLI::launch( "rm " . WP_CONTENT_DIR . "/object-cache.php 2> /dev/null || echo -n" );
                WP_CLI::launch( "rm -r " . WP_CONTENT_DIR . "/cache/* 2> /dev/null || echo -n" );
                WP_CLI::launch( "rm -r " . WP_CONTENT_DIR . "/w3tc/* 2> /dev/null || echo -n" );

                WP_CLI::launch( "mv /tmp/" . $config . " " . $w3tc_master );
                WP_CLI::launch( "chown -R $(stat -c %U " . WP_CONTENT_DIR . "):$(stat -c %U " . WP_CONTENT_DIR . ") " . $w3tc_master );

                if ( ! is_null( $old_config ) ) {
                    require_once W3TC_LIB_W3_DIR . "/Config.php";

                    $config = new W3_Config();

                    $cdn = fopen( "/tmp/cdn_settings", "r" );

                    while ( ! feof( $cdn ) ) {
                        $property = trim( fgets( $cdn ) );
                        
                        $config->set( $property, $old_config[$property] );                        
                    }
                    fclose( $cdn );

                    $config->save();
                }

                WP_CLI::success( "W3TC recommended settings have been imported." );
            }
        }

        /**
         * @subcommand flush-transients
         */
        function flushtransients( $args, $assoc_args ) {
            WP_CLI::run_command( array( 'transient', 'delete-all' ) );
        }

        /**
         * @subcommand plugin-check
         *
         */
        function plugincheck( $args, $assoc_args ) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

            WP_CLI::line( "Checking plugins...\n" );

            $plugins_file = preg_replace( "/https?:\/\/(www.)?/", "", home_url() );
            $plugins_file = preg_replace( "/\//", "_", $plugins_file );
            $plugins_file = "/tmp/" . $plugins_file . "-plugins";

            $messages = array(
                'redirect' => 'Redirection plugins are known for using a lot of server resources and should be removed. Redirects should always be implemented at the server level.',
                'security' => 'On Synthesis, security plugins are unnecessary and can actually be a detriment to your site.',
                'backup' => 'We back up your files and database daily. Thus, you really do NOT need a backup plugin with Synthesis.',
                'incompatible' => 'This plugin is not allowed on the Synthesis stack.',
                'exec' => 'The exec and url_fopen PHP functions have been disabled on Synthesis hosting for security reasons.',
                'resource' => 'This plugin is known for using a lot of server resources. Please consider replacing or removing.',
                'spam' => 'This plugin is known for injecting spam in WordPress sites. Please consider replacing or removing.',
                'vulnerability' => 'This plugin has a known security vulnerability and should be removed immediately.',
            );

            $list = array(
                # redirect plugins
                'redirection' => $messages['redirect'],
                'safe-redirect-manager' => $messages['redirect'],
                'simple-301-redirects' => $messages['redirect'],
                'quick-pagepost-redirect-plugin' => $messages['redirect'],
                'pretty-link' => $messages['redirect'],

                # vulnerability plugins
                'fancybox-for-wordpress' => $messages['vulnerability'],

                # incompatible plugins
                'wp-super-cache' => $messages['incompatible'],
                'hyper-cache' => $messages['incompatible'],
                'quick-cache' => $messages['incompatible'],
                'tribe-object-cache' => $messages['incompatible'],
                'wp-file-cache' => $messages['incompatible'],
                'xcache' => $messages['incompatible'],
                'wordpress-gzip-compression' => $messages['incompatible'],
                'wp-fastest-cache' => $messages['incompatible'],
                'batcache' => $messages['incompatible'],
                'db-cache-reloaded-fix' => $messages['incompatible'],
                'aio-cache' => $messages['incompatible'],
                'wp-fast-cache' => $messages['incompatible'],
                'next-level-cache' => $messages['incompatible'],
                'alpha-cache' => $messages['incompatible'],
                'wp-fragment-cache' => $messages['incompatible'],
                'tweet-old-post' => $messages['incompatible'],

                # plugins using exec()
                'ewww-image-optimizer' => $messages['exec'],
                'just-custom-fields' => $messages['exec'],

                # problematic plugins
                'wp-optimize' => $messages['resource'],
                'yet-another-related-posts-plugin' => $messages['resource'],
                'wp-smushit' => $messages['resource'],
                'google-sitemap-generator' => $messages['resource'],
                'google-xml-sitemap' => $messages['resource'],
                'nextgen-gallery' => $messages['resource'],
                'wishlist-member' => $messages['resource'],
                'audit_trail' => $messages['resource'],
                'broken-link-checker' => $messages['resource'],
                'tinymce-advanced' => $messages['resource'],
                'google-analytics-dashboard-for-wp' => $messages['resource'],
                'fuzzy-seo-booster' => $messages['resource'],
                'wp-postviews' => $messages['resource'],
                'dynamic-related-posts' => $messages['resource'],
                'seo-alrp' => $messages['resource'],
                'similar-posts' => $messages['resource'],
                'contextual-related-posts' => $messages['resource'],
                'wp-phpmyadmin' => $messages['resource'],
                'google-xml-sitemaps-with-multisite-support' => $messages['resource'],
                'spyderspanker' => $messages['resource'],
                'spyderspanker-pro' => $messages['resource'],
                'wp-slimstat' => $messages['resource'],
                'yet-another-featured-posts-plugin' => $messages['resource'],
                'social-networks-auto-poster-facebook-twitter-g' => $messages['resource'],
                'floating-social-media-icon' => $messages['resource'],
                'acurax-social-media-widget' => $messages['resource'],
                'seo-automatic-links' => $messages['resource'],
                'social-metrics' => $messages['resource'],
                'si-captcha-for-wordpress' => $messages['resource'],
                'wp-database-optimizer' => $messages['resource'],
                'postie' => $messages['resource'],
                'bwp-minify' => $messages['resource'],
                'featured-posts-grid' => $messages['resource'],
                'crazyegg-heatmap-tracking' => $messages['resource'],

                # backup plugins
                'backupbuddy' => $messages['backup'],
                'wp-dbmanager' => $messages['backup'],
                'backwpup' => $messages['backup'],
                'backupwordpress' => $messages['backup'],
                'simple-backup' => $messages['backup'],
                'wp-db-backup' => $messages['backup'],
                'backup' => $messages['backup'],
                'blogvault' => $messages['backup'],
                'updraftplus' => $messages['backup'],
                'wp-time-machine' => $messages['backup'],
                'wordpress-backup-to-dropbox' => $messages['backup'],
                'pressbackup' => $messages['backup'],
                'updraft' => $messages['backup'],
                'wp-google-drive' => $messages['backup'],
                'duplicator' => $messages['backup'],

                # security plugins
                'better-wp-security' => $messages['security'],
                'all-in-one-wp-security-and-firewall' => $messages['security'],
                'wordfence' => $messages['security'],
                'wp-security-scan' => $messages['security'],
                'vaultpress' => $messages['security'],
                'limit-login-attempts' => $messages['security'],
                'bruteprotect' => $messages['security'],
                'lockdown-wp-admin' => $messages['security'],
                'wp-fail2ban' => $messages['security'],
                'sucuri-scanner' => $messages['security'],
                'bulletproof-security' => $messages['security'],
                'secure-wordpress' => $messages['security'],
                'simple-login-lockdown' => $messages['security'],
                'exploit-scanner' => $messages['security'],
                'security-ninja' => $messages['security'],
                'security-ninja-lite' => $messages['security'],

                # plugins with spam issues
                'social-media-widget' => $messages['spam'],
            );

            $file = array();
            $plugins = get_plugins();
            foreach( $plugins as $name => $data ) {
                $slug = explode( "/", $name );
                $slug = preg_replace( "/\.php/", "", $slug[0] );

                $plugin = array( "name" => $slug, "version" => $data['Version'] );

                $file = array_merge( $file, array( $plugin ) );
            }

            if ( sizeof( $file ) == 0 ) {
                WP_CLI::error( "No plugins were found. Skipping." );
                return;
            }

            foreach ( $file as $plugin_data ) {
                $slug = $plugin_data['name'];
                $current_version = preg_replace( "/[^0-9]/", "", $plugin_data['version'] );

                $api = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

                $version = preg_replace( "/[^0-9]/", "", $api->version );
                $last_update = $api->last_updated;

                $month_diff = -1;
                if ( ! empty( $last_update ) ) {
                    $diff = abs( strtotime( $last_update ) - strtotime( date( 'Y-m-d' ) ) );
                    $years = floor( $diff / ( 365 * 60 * 60 * 24 ) );
                    $month_diff = floor( ( $diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24 ) ) + ( $years * 12 );
                }

                if ( ! empty( $slug ) ) {
                    if ( $version > 0 ) {
                        if ( $current_version < $version ) {
                            echo "\033[32m[UPDATE]\033[0m " . $slug . " plugin is outdated. Please update to the most recent version of the plugin.\n\n";
                        }
                    }

                    $output = "";
                    if ( isset( $list[$slug] ) ) {
                        $output = "\033[31m[REMOVE]\033[0m " . $slug . " plugin. " . $list[$slug];
                    } else {
                        if ( intval( $month_diff ) >= 12 ) {
                            $output = "\033[33m[WARNING]\033[0m " . $slug . " plugin last update was " . $month_diff . " months ago. Please make sure it is compatible with current WordPress version.";
                        }
                    }

                    if ( strlen( $output ) > 0 ) {
                        echo $output . "\n\n";
                    }
                }
            }

            WP_CLI::line( "Success: plugin check has been completed." );
        }

        /**
         * @synopsis [--loggly] [--skip-dns] [--cgi-output]
         * @subcommand healthcheck
         *
         */
        function healthcheck( $args, $assoc_args ) {
            global $wpdb;

            $networkstatus = is_multisite() ? "yes" : "no";
            $blog_url = get_bloginfo( 'url' );
            $blog_version = get_bloginfo ( 'version' );
            $domain_name = str_replace( 'http://', '', str_replace( 'https://', '', untrailingslashit( $blog_url ) ) );
            $count = self::synth_get_plugin_count();

            // Memory Footprint
            if ( false === ( $memusage = get_transient( 'synthesis_memory_check' ) ) ) {
                $memusage = 0;
            }

            // Transients
            $orphan = "SELECT substring(option_name,1,6) as x,count(*) FROM $wpdb->options group by x HAVING x = '_trans' order by count(*) desc";
            $result = $wpdb->get_var( $orphan, 1, 0 );

            // Autoloaded Options
            $autoloaded_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options WHERE autoload = 'yes';" );
            $autoloaded_size = round( $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) / POWER(1024,2) FROM $wpdb->options WHERE autoload = 'yes';" ), 2 );

            // WordPress Cron
            $domain = explode( "/", preg_replace( "/^www\./", "", $domain_name ) );
            $subsite = str_replace( $domain[0], "", preg_replace( "/^www\./", "", $domain_name ) );
            $wpcron = "-";
            if ( file_exists( "/var/log/nginx/". $domain[0] . ".access.log" ) )
                $wpcron = sizeof( preg_grep( "/" . preg_replace( "/\//", "\\/", $subsite ) . "\/wp-cron\.php/", file( "/var/log/nginx/" . $domain[0] . ".access.log" ) ) );

            if ( defined( "DISABLE_WP_CRON" ) ) {
                if ( constant( "DISABLE_WP_CRON" ) ) {
                    $wpcron .= " \033[33m(probably running via crontab)\033[0m";
                }
            }

            // Accesses
            $accesses = array();
            if ( file_exists( "/var/log/nginx/" . $domain[0] . ".access.log" ) ) {
                $accesses['total'] = sizeof( preg_grep( "/-/", file( "/var/log/nginx/" . $domain[0] . ".access.log" ) ) );
                $accesses['wplogin'] = sizeof( preg_grep( "/" . preg_replace( "/\//", "\\/", $subsite ) . "\/wp-login\.php/", file( "/var/log/nginx/" . $domain[0] . ".access.log" ) ) );
                $accesses['xmlrpc'] = sizeof( preg_grep( "/" . preg_replace( "/\//", "\\/", $subsite ) . "\/xmlrpc\.php/", file( "/var/log/nginx/" . $domain[0] . ".access.log" ) ) );
            }

            // Live Site
            $live = null;
            if ( !isset( $assoc_args['skip-dns'] ) ) {
                WP_CLI::launch( "ifconfig | grep inet | cut -d':' -f2 | cut -d' ' -f1 | egrep -v \"^$|127.0.0.1\" > /tmp/server_ips" );

                $file = fopen( "/tmp/server_ips", "r" );
                while( !feof( $file ) ) {
                    $value = fgets( $file );
                    $ip = preg_replace( "/[^0-9\.]/", "", $value );
                    $ip = preg_replace( "/\n/", "", $ip );

                    if ( $ip == "127.0.0.1" || $ip == "::2" )
                        continue;

                    $ips[] = $ip;
                }
                fclose( $file );

                $dns = dns_get_record( $domain[0], DNS_A );

                $live_ip = null;
                $live = false;
                foreach ( $dns as $record ) {
                    foreach ( $ips as $ip ) {
                        if ( $record['ip'] == $ip ) {
                            $live = true;
                            $live_ip = $ip;
                            break 2;
                        }
                    }
                }

                if ( !$live ) {
                    $live_ip = $dns[0]['ip'];
                }

                WP_CLI::launch( "rm /tmp/server_ips" );
            }

            // Caching Method
            $cache = "";
            if ( defined( 'W3TC' ) ) {
                $cache .= "w3-total-cache; ";
            }

            if ( preg_match( "/\.spaccel\.net$/", @gethostbyaddr( $live_ip ) ) ) {
                $cache .= "studiopress-accelerator (" . gethostbyaddr( $live_ip ) . "); ";
            }

            if ( preg_match( "/cache(\d{1,})?\.wsynth\.net$/", @gethostbyaddr( $live_ip ) ) ) {
                $cache .= "cache server (" . gethostbyaddr( $live_ip ) . "); ";
            }

            $nginx_file = "/etc/nginx/sites-enabled/" . $domain[0];
            if ( file_exists( $nginx_file ) && is_readable( $nginx_file ) ) {
                if ( preg_grep( "/proxy_vhost\.inc/", file( $nginx_file ) ) && preg_grep( "/fastcgi_cache/", file( $nginx_file ) ) ) {
                    $cache .= "fastcgi_cache; ";
                }
            }

            $cache = ( empty( $cache ) ) ? "-" : substr( $cache, 0, -2 );

            // Loggly
            $healthdata = array(
                'body' => array(
                    'domain' => $domain_name,
                    'wpversion' => $blog_version,
                    'memusage' => $memusage,
                    'transients' => $result,
                    'plugin_count' => $count,
                    'autoloaded_options' => array(
                        'count' => $autoloaded_count,
                        'size' => $autoloaded_count
                    ),
                    'wpcron' => $wpcron,
                    'is_multisite' => $networkstatus,
                    'is_genesis' => ( defined( 'GENESIS_ADMIN_DIR' ) ? "yes" : "no" ),
                ),
                'headers' => array( 'Content-Type' => 'application/json' )
            );

            if ( sizeof( $accesses ) > 0 ) {
                $healthdata['body']['accesses'] = $accesses;
            }

            $healthdata['body'] = json_encode( $healthdata['body'] );

            // Output
            if ( isset( $assoc_args['cgi-output'] ) ) {
                echo $healthdata['body'];
            } else if ( empty( $assoc_args['loggly'] ) ) {
                echo "\033[34mMemory Usage:\033[0m $memusage";
                echo "\n\033[34mWordPress Version:\033[0m $blog_version";
                echo "\n\033[34mTransient Variables:\033[0m $result";
                echo "\n\033[34mActive Plugins:\033[0m $count";
                echo "\n\033[34mAutoloaded Options Count:\033[0m $autoloaded_count";
                echo "\n\033[34mAutoloaded Options Size:\033[0m $autoloaded_size Mb";
                echo "\n\033[34mWordPress Cron Calls:\033[0m $wpcron";
                echo "\n\033[34mIs Multisite?\033[0m $networkstatus\n";
                echo "\033[34mIs Genesis?\033[0m " . ( defined( 'GENESIS_ADMIN_DIR' ) ? "yes" : "no" ) . "\n";
                echo "--\n";

                echo "\033[34mWeb Server:\033[0m " . ( ( file_exists( "/var/run/apache2.pid" ) || file_exists( "/run/apache2/apache2.pid" ) ) ? "apache+nginx" : "nginx+php5-fpm" ) . "\n";

                if ( !preg_match( "/.rmkr.net$/", gethostname() ) ) {
                    echo "\033[34mCaching Method:\033[0m " . $cache . "\n";
                }

                if ( !is_null( $live ) ) {
                    if ( $live ) {
                        echo "\033[34mDNS Status:\033[0m \033[32msite is live ( $live_ip )\033[0m\n";
                    } else {
                        echo "\033[34mDNS Status:\033[0m \033[31msite not live OR behind a cache/proxy server ";
                        if ( filter_var( $live_ip, FILTER_VALIDATE_IP ) ) {
                            echo "( " . gethostbyaddr( $live_ip ) . " )";
                        }
                        echo "\033[0m\n";
                    }
                }

                if ( sizeof( $accesses ) > 0 ) {
                    echo "--";
                    echo "\n\033[34mToday's Accesses:\033[0m " . $accesses['total'];
                    echo "\n \033[34m* wp-login.php:\033[0m " . $accesses['wplogin'];
                    echo "\n \033[34m* xmlrpc.php:\033[0m " . $accesses['xmlrpc'] . "\n";
                }
            } else {
                wp_remote_post( 'https://logs-01.loggly.com/inputs/11bdfe58-13d0-479c-a81b-b5fa8ee1806b/tag/synthesis-inventory/', $healthdata );
                WP_CLI::success( "Data sent to Loggly" );
            }
        }

        private function synth_get_plugin_count() {
            $count = count( get_option( 'active_plugins'  )  );

            if( is_multisite() )
                $count += count( get_site_option( 'active_sitewide_plugins', array() ) );

            return $count;
        }

        private function get_crc_dir($dir, $tag){
            $files = scandir($dir);
            array_shift($files);
            array_shift($files);

            $dir .= '/';
            $ret = array();

            foreach($files as $f){
                if(is_dir($dir.$f))
                    $ret = array_merge($this->get_crc_dir($dir.$f, $tag.$f.'/'), $ret);
                else
                    $ret["{$tag}{$f}"] = md5_file($dir.$f);
            }

            return $ret;
        }

        /**
         * @subcommand verify-wordpress
         *
         */
        function verifyWordPress( $args, $assoc_args ) {
            WP_CLI::run_command( array( 'core', 'verify-checksums' ) );
        }

        /**
         * @subcommand verify-genesis
         *
         */
        function verify_genesis(){
            $genesis_dir = get_theme_root().'/genesis';

            if(!file_exists($genesis_dir))
                WP_CLI::error( "Genesis directory not found." );

            $genesis_data = wp_get_theme('genesis');
            $config_file = 'genesis' . $genesis_data->get( 'Version' ) . '.php';
            $conf_file_path = SYNTHESIS_CHILD_PLUGIN_DIR . 'hashfiles/' . $config_file;

            if ( file_exists( $conf_file_path ) ) {
                $answers = unserialize( file_get_contents( $conf_file_path ) ) ;
            } else {
                WP_CLI::warning( "Unsupported WordPress Version" );
                return;
            }

            $ret = $this->get_crc_dir($genesis_dir, 'genesis/');

            foreach($answers as $a => $c){
                if($c != $ret[$a]) //it's bad
                    $corrupt[] = $a;
            }

            if(count($corrupt)){
                foreach($corrupt as $f){
                    WP_CLI::warning( "File Mismatch: $f" );
                }
            }

            WP_CLI::success( "Framework Files Verified" );
        }
    }

    WP_CLI::add_command( 'synth-ops', 'Synthesis_Ops_Commands' );
}

<?php

/**
 * Plugin Name: Synthesis Disable Editor
 * Version: 1.0.1
 * Description: Turns off the WordPress editor forcing the use of FTP and a text editor for code changes.
 * Plugin Author: CopyBlogger Media
 * Plugin URL: http://websynthesis.com
 */

if ( ! get_option( 'synthesis_editor' ) && ! defined( 'DISALLOW_FILE_EDIT' ) )
  define('DISALLOW_FILE_EDIT', true);

<?php
/*
Plugin Name: Text & Image
Description: Widget for displaying text with image in the widget.
Version: 1.0
 */

require_once 'Text_image.php';

function register_my_widget() {
    register_widget( 'Text_image' );
}
add_action( 'widgets_init', 'register_my_widget' );

?>
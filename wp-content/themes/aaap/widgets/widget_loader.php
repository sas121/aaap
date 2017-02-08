<?php

$widgets = array(
    array(
        'file' => 'PDF.php',
        'class' => 'PDF_Widget'
    ),
    array(
        'file' => 'Membership.php',
        'class' => 'Membership_Widget'
    )
);




add_action('widgets_init', function() {
    $path = __DIR__;
    global $widgets;
    foreach ($widgets as $widget) {
        require_once $path . '/' .$widget['file'];
        register_widget($widget['class']);
    }
});


<?php
add_action('wp_ajax_update_indexes','update_indexes');

function update_indexes()
{
    $indexes = $_POST['data'];
   
    
    if (isset($indexes) && is_array($indexes)) {
        foreach ($indexes as $index) {
            update_post_meta($index['post_id'], 'home_index', $index['home_index']);
        }
        echo 'success';
        die;
    }

    echo 'false';
    die;
}

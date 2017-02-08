<?php
global $post;
$value = get_post_meta($post->ID, '_annoucment_bottom_content', true);
                                   



?>
<div class="editor_holder">
    <?php wp_editor($value,'annoucment_bottom_content',array('textarea_name'=>'annoucment_bottom_content')); ?>
</div>



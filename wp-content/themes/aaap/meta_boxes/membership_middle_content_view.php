<?php
global $post;
$value = get_post_meta($post->ID, '_membership_middle_content', true);
                                   



?>
<div class="editor_holder">
    <?php wp_editor($value,'membership_middle_content',array('textarea_name'=>'membership_middle_content')); ?>
</div>



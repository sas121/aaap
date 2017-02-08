<?php
global $post;

$postDesc= get_post_meta($post->ID, '_area_resource_post_description', true);
$postCont= get_post_meta($post->ID, '_area_resource_post_content', true);
$postOrder= get_post_meta($post->ID, '_area_resource_order', true);

?>

<div class="area_resource_form">
<div class="editor_holder">
    <label><h3>Post Description</h3></label>
    <?php wp_editor($postDesc,'area_resource_post_description',array('textarea_rows'=>7,'textarea_name'=>'area_resource_post_description')); ?>
</div>

<div class="editor_holder">
    <label><h3>Post Content</h3></label>
    <?php wp_editor($postCont,'area_resource_post',array('textarea_name'=>'area_resource_post_content')); ?>
</div>


</div>
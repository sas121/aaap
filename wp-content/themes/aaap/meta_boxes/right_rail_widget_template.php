<?php
global $wp_registered_sidebars;
global $post;
$value = get_post_meta($post->ID, '_page_right_sidebar', true);
?>
<label>Select Widget</label>
<br />
<select name="page_sidebar">
    <option value="">--Select--</option>
    <?php foreach ($wp_registered_sidebars as $sidebars): ?>
        <option <?php echo $value == $sidebars['id'] ? 'selected="selected"' : '' ?> value="<?php echo $sidebars['id'] ?>"><?php echo $sidebars['name'] ?></option>
    <?php endforeach; ?>
</select>


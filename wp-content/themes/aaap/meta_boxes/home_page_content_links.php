<?php
global $post;
$value = get_post_meta( $post->ID, '_home_content_links', true);
$fields = unserialize($value);
?>
<label>Subscribe to journal link</label>
<br />
<input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['subscribe'] : '' ?>" name="home_content_links[subscribe]" id="middle_box_title" />
<br />
<label>Join now Link</label>
<br />
<input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['join'] : '' ?>" name="home_content_links[join]" id="middle_box_title" />


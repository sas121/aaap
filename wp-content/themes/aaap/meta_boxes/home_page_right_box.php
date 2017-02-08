<?php
global $post;
$value = get_post_meta( $post->ID, '_my_right_box', true);
$fields = unserialize($value);
?>


<label>Box Heading</label>
<br />
<input class="meta_box_link" type="text" name="right_box[box_title]" value="<?php echo ($fields) ? $fields['box_title'] : '' ?>" id="box_1_title" />
<br />
<label>Box Text</label>
<br />
<textarea class="meta_box_textarea" name="right_box[box_text]" id="box_1_text"><?php echo ($fields) ? $fields['box_text'] : '' ?></textarea>
<label>Bottom Link</label>
<br />
<input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_link'] : '' ?>" name="right_box[box_link]" id="box_1_link" />



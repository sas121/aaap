<?php
global $post;
$value = get_post_meta($post->ID, '_membership_right_box', true);
$fields = unserialize($value);
?>

<label>Box Heading</label>
<br />
<input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_title'] : '' ?>" name="membership_right_box[box_title]" id="box_1_title" />
<br /><br />
<label>Box Text</label>
<br />
<textarea name="membership_right_box[box_text]" id="box_1_text"><?php echo ($fields) ? $fields['box_text'] : '' ?></textarea>
 <br /> <br />
<fieldset>
    <b>MAIN LINK</b>
    <br />
    <label>Link Text</label>
    <br />
    <input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_link_text'] : '' ?>" name="membership_right_box[box_link_text]" id="box_1_link" />
    <label>Bottom Link</label>
    <br />
    <input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_link'] : '' ?>" name="membership_right_box[box_link]" id="box_1_link" />
</fieldset>
  <br /> <br />
<fieldset>
    <b>PRICES</b>
    <br />
    <label>Price</label>
    <br />
    <input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['price'] : '' ?>" name="membership_right_box[price]" id="box_1_link" />
    <label>Price Text</label>
    <br />
    <input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['price_text'] : '' ?>" name="membership_right_box[price_text]" id="box_1_link2" />
</fieldset>
  <br /> <br />
<fieldset>
    <b>BOTTOM LINK</b>
    <br />
    <label>Link Text</label>
    <br />
    <input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_bottom_link_text'] : '' ?>" name="membership_right_box[box_bottom_link_text]" id="box_1_link" />
    <label>Link URL</label>
    <br />
    <input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_bottom_link'] : '' ?>" name="membership_right_box[box_bottom_link]" id="box_1_link" />
</fieldset>


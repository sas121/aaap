<?php
global $post;
$value = get_post_meta( $post->ID, '_my_middle_box', true);
$fields = unserialize($value);


$image_src =  get_image_name($fields['image_url'], '244x155');


?>


<label>Box Heading</label>
<br />
<input class="meta_box_link" type="text" value="<?php echo ($fields) ? $fields['box_title'] : '' ?>" name="middle_box[box_title]" id="middle_box_title" />
<br />

<label>Place</label>
<br />
<input class="meta_box_link" value="<?php echo ($fields) ? $fields['place'] : '' ?>" type="text" name="middle_box[place]" id="middle_box_place" />
<label>Date</label>
<br />
<input class="meta_box_link" value="<?php echo ($fields) ? $fields['date'] : '' ?>" type="text" name="middle_box[date]" id="middle_box_date" />
<label>Bottom Link</label>
<br />
<input class="meta_box_link" value="<?php echo ($fields) ? $fields['link'] : '' ?>" type="text" name="middle_box[link]" id="middle_box_link" />
<br />
<?php if($fields['image_url']): ?>
    <br />
    <img src="<?php echo $image_src ?>" class="prev_image" />
<?php endif; ?>
<a href="javascript:void(0)"  id="middle_box_image" class="<?php echo ($fields['image_url']) ? 'remove_middle_box_image' : 'middle_box_image' ?>">
    <?php echo ($fields['image_url']) ? 'Remove Image' : 'Add image' ?>
</a>
<br />
<input type="hidden" value="<?php echo ($fields) ? $fields['image_id'] : '' ?>" name="middle_box[image_id]" id="middle_box_image_id">
<input type="hidden" value="<?php echo ($fields) ? $fields['image_url'] : '' ?>" class="input_inline" name="middle_box[image_url]" id="middle_box_image_url">



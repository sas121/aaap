<?php
global $post;
$value = get_post_meta($post->ID, '_people_custom_post_type', true);
$fields = unserialize($value);


$image_src = $fields['image_url'];
?>


<div class="people_form">
<!--    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['name'] ? 'label_hide' : '' ?>">Name, Degree</label>
        <input value="<?php echo $fields['name'] ?>" autocomplete="off" class="meta_box_input" name="people[name]" type="text"  />
    </div>-->
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['permalink'] ? 'label_hide' : '' ?>">Permalink</label>
        <input value="<?php echo $fields['permalink'] ?>" autocomplete="off" class="meta_box_input" name="people[permalink]" type="text"  />
    </div>
    <?php if ($fields['image_url']): ?>
        <img src="<?php echo $image_src ?>" class="prev_image" />
    <?php endif; ?>

    <button id="people_image" class="button button-primary button-large <?php echo ($fields['image_url']) ? 'remove_people_image' : 'people_image' ?>" type="button">
        <?php echo ($fields['image_url']) ? 'Remove Image' : 'Add image' ?>
    </button>    
    <input type="hidden" value="<?php echo ($fields) ? $fields['image_id'] : '' ?>" name="people[image_id]" id="people_image_id">
    <input type="hidden" value="<?php echo ($fields) ? $fields['image_url'] : '' ?>" class="input_inline" name="people[image_url]" id="people_image_url">
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['title'] ? 'label_hide' : '' ?>">Title (optional)</label>
        <input value="<?php echo $fields['title'] ?>" autocomplete="off" class="meta_box_input" name="people[title]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['email'] ? 'label_hide' : '' ?>">Enter email address or URL (optional)</label>
        <input value="<?php echo $fields['email'] ?>" autocomplete="off" class="meta_box_input" name="people[email]" type="text" />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['institution'] ? 'label_hide' : '' ?>">Institution/Organization (optional)</label>
        <input value="<?php echo $fields['institution'] ?>" autocomplete="off" class="meta_box_input" name="people[institution]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['journal_section'] ? 'label_hide' : '' ?>">Journal Section (Editorial bord only)</label>
        <input value="<?php echo $fields['journal_section'] ?>" autocomplete="off" class="meta_box_input" name="people[journal_section]" type="text" />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['subcategory'] ? 'label_hide' : '' ?>">Subcategory (Editorial bord only - Associate Editors, Editorial Boards, etc.)</label>
        <input value="<?php echo $fields['subcategory'] ?>" autocomplete="off" class="meta_box_input" name="people[subcategory]" type="text" />
    </div>



</div>

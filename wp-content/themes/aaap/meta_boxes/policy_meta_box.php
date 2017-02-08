<?php
global $post;

$image = get_post_meta($post->ID, '_policy_link_image', true);
$desc = get_post_meta($post->ID, '_policy_description', true);
$url = get_post_meta($post->ID, '_policy_link_url', true);
$link_type = get_post_meta($post->ID, '_policy_link_type', true);
$pdf = get_post_meta($post->ID, '_policy_pdf_url', true);

?>

<div class="link_pdf_form">
    <?php if (!empty($image)): ?>
        <img src="<?php echo $image ?>" class="prev_image" />
    <?php endif; ?>
    <label>Add Image</label><br />
    <button id="people_image" class="button button-primary button-large <?php echo ($image) ? 'remove_people_image' : 'people_image' ?>" type="button">
        <?php echo (!empty($image)) ? 'Remove Image' : 'Add image' ?>
    </button>    
    <input type="hidden" value="<?php echo (!empty($image)) ? $image : '' ?>" class="input_inline" name="policy[link_image]" id="people_image_url">




    <div class="input_wrapper">
        <select id="select_type" name="policy[link_type]">
            <option value="">Select Type</option>
            <option  <?php echo ($link_type=='link') ? 'selected="selected"' : '' ?> value="link">Link</option>
            <option <?php echo ($link_type=='pdf') ? 'selected="selected"' : '' ?> value="pdf">PDF</option>
        </select>
    </div>
    <div class="input_wrapper selection link_wrapper" <?php echo ($link_type=='link') ? 'style="display:block;"' : '' ?>>
        <label class="meta_box_label <?php echo (!empty($url)) ? 'label_hide' : '' ?>">Enter URL</label>
        <input type="text" class="meta_box_input" name="policy[link_url]" value="<?php echo $url ?>">
    </div>

    <div class="input_wrapper selection pdf_wrapper" <?php echo ($link_type=='pdf') ? 'style="display:block;"' : '' ?>>
        <?php if((!empty($pdf)) ): ?>
        <img src="<?php echo get_stylesheet_directory_uri() ?>/images/admin/pdf.jpg" alat="pdf_icon" class="prev_image pdf_preview"/>
        <?php endif; ?>
        <button id="pdf" class="button button-primary button-large pdf <?php echo (!empty($pdf)) ? 'remove_pdf_image' : 'people_image' ?>" type="button">
            <?php echo (!empty($pdf)) ? 'Remove PDF' : 'Upload PDF' ?>
        </button>
        <input type="hidden" value="<?php echo (!empty($pdf)) ? $pdf : '' ?>" class="input_inline" name="policy[pdf_url]" id="pdf_url">
        <input type="text" disabled="disabled" value="<?php echo (!empty($pdf)) ? $pdf : '' ?>" class="input_inline" name="policy[pdf_link]" id="pdf_link">
    </div>
    <div class="input_wrapper">
        <?php wp_editor($desc, 'link_deescription', array('textarea_name' => 'policy[description]')); ?>
    </div>
</div>


<?php
global $post;
$value = get_post_meta($post->ID, '_members_referrals_custom_post_type', true);
$fields = unserialize($value);


$image_src = get_image_name($fields['image_url'], '174x173');
?>


<div class="people_form">
    <!--    <div class="input_wrapper">
            <label class="meta_box_label <?php echo $fields['name'] ? 'label_hide' : '' ?>">Name, Degree</label>
            <input value="<?php echo $fields['name'] ?>" autocomplete="off" class="meta_box_input" name="people[name]" type="text"  />
        </div>-->
<!--    <div class="input_wrapper">
        <input value="1" <?php echo $fields['abpn'] ? 'checked="checked"' : '' ?> autocomplete="off" class="meta_box_input" name="members_referrals[abpn]" type="checkbox" />
        <b>Include in Patient referral program</b>
    </div>-->
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['permalink'] ? 'label_hide' : '' ?>">Permalink</label>
        <input value="<?php echo $fields['permalink'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[permalink]" type="text"  />
    </div>
<!--    <?php if ($fields['image_url']): ?>
        <img src="<?php echo $image_src ?>" class="prev_image" />
    <?php endif; ?>

    <button id="people_image" class="button button-primary button-large <?php echo ($fields['image_url']) ? 'remove_people_image' : 'people_image' ?>" type="button">
        <?php echo ($fields['image_url']) ? 'Remove Image' : 'Add image' ?>
    </button>    
    <input type="hidden" value="<?php echo ($fields) ? $fields['image_id'] : '' ?>" name="members_referrals[image_id]" id="people_image_id">
    <input type="hidden" value="<?php echo ($fields) ? $fields['image_url'] : '' ?>" class="input_inline" name="members_referrals[image_url]" id="people_image_url">-->
    <h2><b>General Fields</b></h2>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['title'] ? 'label_hide' : '' ?>">AAAP Title</label>
        <input value="<?php echo $fields['title'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[title]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['practice_name'] ? 'label_hide' : '' ?>">University/Institution/Practice Name</label>
        <input value="<?php echo $fields['practice_name'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[practice_name]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['address1'] ? 'label_hide' : '' ?>">Address1</label>
        <input value="<?php echo $fields['address1'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[address1]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['address2'] ? 'label_hide' : '' ?>">Address2</label>
        <input value="<?php echo $fields['address2'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[address2]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['city'] ? 'label_hide' : '' ?>">City</label>
        <input value="<?php echo $fields['city'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[city]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['locations'] ? 'label_hide' : '' ?>">Locations</label>
        <input value="<?php echo $fields['locations'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[locations]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['zip'] ? 'label_hide' : '' ?>">Zip</label>
        <input value="<?php echo $fields['zip'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[zip]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['phone'] ? 'label_hide' : '' ?>">Phone</label>
        <input value="<?php echo $fields['phone'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[phone]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['fax'] ? 'label_hide' : '' ?>">Fax</label>
        <input value="<?php echo $fields['fax'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[fax]" type="text"  />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['email'] ? 'label_hide' : '' ?>">Email</label>
        <input value="<?php echo $fields['email'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[email]" type="text" />
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['committee'] ? 'label_hide' : '' ?>">Committee</label>
        <input value="<?php echo $fields['committee'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[committee]" type="text" />
    </div>
    <h2><b>Patient referral program</b></h2>
    <div class="input_wrapper">
        <input value="1" <?php echo $fields['abpn'] ? 'checked="checked"' : '' ?> autocomplete="off" class="meta_box_input" name="members_referrals[abpn]" type="checkbox" />
        <b>ABPN Addiction Psychiatry Certification</b>
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['practice_description'] ? 'label_hide' : '' ?>">Practice Description</label>
        <textarea autocomplete="off" class="meta_box_input meta_box_textarea" name="members_referrals[practice_description]"><?php echo $fields['practice_description'] ?></textarea>
    </div>
    <div class="input_wrapper">
        <label class="meta_box_label <?php echo $fields['insurance_types'] ? 'label_hide' : '' ?>">Insurance Types</label>
        <input value="<?php echo $fields['insurance_types'] ?>" autocomplete="off" class="meta_box_input" name="members_referrals[insurance_types]" type="text" />
    </div>



</div>

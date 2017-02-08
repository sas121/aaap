<?php

//$screen = get_current_screen();
//$screen_type = $screen->post_type;
//
//
//switch ($screen_type) {
//    case 'people';
//        $postKey = 'people';
//        $metaKey = '_people_custom_post_type';
//        break;
//    case 'members_referrals';
//        $postKey = 'members_referrals';
//        $metaKey = '_members_referrals_custom_post_type';
//        break;
//}

$metaBoxesPosts = array(
    array(
        'postKey' => 'annucment_middle_box',
        'metaKey' => '_annucment_middle_box',
        'serialize' => true
    ),
    array(
        'postKey' => 'annucment_left_box',
        'metaKey' => '_annucment_left_box',
        'serialize' => true
    ),
    array(
        'postKey' => 'annucment_right_box',
        'metaKey' => '_annucment_right_box',
        'serialize' => true
    ),
    array(
        'postKey' => 'members_referrals',
        'metaKey' => '_members_referrals_custom_post_type',
        'serialize' => true
    ),
    array(
        'postKey' => 'people',
        'metaKey' => '_people_custom_post_type',
        'serialize' => true
    ),
    array(
        'postKey' => 'annoucment_bottom_content',
        'metaKey' => '_annoucment_bottom_content',
        'serialize' => false
    ),
    array(
        'postKey' => 'membership_middle_content',
        'metaKey' => '_membership_middle_content',
        'serialize' => false
    ),
    array(
        'postKey' => 'membership_middle_box',
        'metaKey' => '_membership_middle_box',
        'serialize' => true
    ),
    array(
        'postKey' => 'membership_left_box',
        'metaKey' => '_membership_left_box',
        'serialize' => true
    ),
    array(
        'postKey' => 'membership_right_box',
        'metaKey' => '_membership_right_box',
        'serialize' => true
    ),
);


//Use this when we want to serialize data (There is a issue with editor data and serialization)
foreach ($metaBoxesPosts as $box) {
    $postKey = $box['postKey'];
    $metaKey = $box['metaKey'];
    $serialize = $box['serialize'];
    $value = $_POST[$postKey];
    if (isset($value)) {
        if ($serialize) {
            $data = serialize($value);
        } else {
            $data = $value;
        }
        update_post_meta($post_id, $metaKey, $data);
    }
}




if (isset($_POST['area_resource_post_description'])) {
    update_post_meta($post_id, '_area_resource_post_description', $_POST['area_resource_post_description']);
}

if (isset($_POST['area_resource_post_content'])) {
    update_post_meta($post_id, '_area_resource_post_content', $_POST['area_resource_post_content']);
}

/**
 * Save data from advocacy custom post
 */
if (isset($_POST['advocacy'])) {
    if ($_POST['advocacy']['link_type'] == 'link') {
        $_POST['advocacy']['pdf_url'] = '';
    }
    if ($_POST['advocacy']['link_type'] == 'pdf') {
        $_POST['advocacy']['link_url'] = '';
    }
    $advocacy = $_POST['advocacy'];

    foreach ($advocacy as $key => &$meta) {
        update_post_meta($post_id, '_advocacy_' . $key, $meta);
    }
    update_post_meta($post_id, 'home_index', 0);
}

/**
 * Save data from advocacy custom post
 */
if (isset($_POST['job_board'])) {
    if ($_POST['job_board']['link_type'] == 'link') {
        $_POST['job_board']['pdf_url'] = '';
    }
    if ($_POST['job_board']['link_type'] == 'pdf') {
        $_POST['job_board']['link_url'] = '';
    }
    $job_board = $_POST['job_board'];

    foreach ($job_board as $key => &$meta) {
        update_post_meta($post_id, '_job_board_' . $key, $meta);
    }
    update_post_meta($post_id, 'home_index', 0);
}

/**

 * Save data from adv ocacy custom post
 */
if (isset($_POST['policy'])) {
    if ($_POST['policy']['link_type'] == 'link') {
        $_POST['policy']['pdf_url'] = '';
    }
    if ($_POST['policy']['link_type'] == 'pdf') {
        $_POST['policy']['link_url'] = '';
    }

    $policy = $_POST['policy'];

    foreach ($policy as $key => &$meta) {
        update_post_meta($post_id, '_policy_' . $key, $meta);
    }
    update_post_meta($post_id, 'home_index', 0);
} 
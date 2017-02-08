<?php

require_once 'custom_taxonomies.php';

add_action('init', 'create_post_type');

function create_post_type()
{
    $labels = array(
        'name' => __('People'),
        'singular_name' => __('Peoples'),
        'add_new_item' => __('Add new people'),
        'add_new' => __('Add new people')
    );

    register_post_type('people', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'taxonomies' => array('people'),
        'supports' => array('title', 'thumbnail')
            )
    );
}

add_action('init', 'create_members_referrals_post_type');

function create_members_referrals_post_type()
{
    $labels = array(
        'name' => __('Patients Referrals'),
        'singular_name' => __('Patient Referral'),
        'add_new_item' => __('Add new Patient Referral'),
        'add_new' => __('Add new Patient Referral')
    );

    register_post_type('members_referrals', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'taxonomies' => array('member_referral'),
        'supports' => array('title', 'thumbnail')
            )
    );
}

add_action('init', 'policy_post_type');

function policy_post_type()
{
    $labels = array(
        'name' => __('Policy'),
        'singular_name' => __('Policyss'),
        'add_new_item' => __('Add new Policy'),
        'add_new' => __('Add Policy')
    );

    register_post_type('policy', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'taxonomies' => array('policy'),
        'supports' => array('title', 'excerpt')
            )
    );
}

add_action('init', 'advocacy_post_type');

function advocacy_post_type()
{
    $labels = array(
        'name' => __('Advocacy'),
        'singular_name' => __('Advocacacy'),
        'add_new_item' => __('Add new Advocacy'),
        'add_new' => __('Add Advocacy')
    );

    register_post_type('advocacy', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'taxonomies' => array('advocacy'),
        'supports' => array('title', 'excerpt')
            )
    );
}

add_action('init', 'job_board_post_type');

function job_board_post_type()
{
    $labels = array(
        'name' => __('Job Board'),
        'singular_name' => __('Job Board'),
        'add_new_item' => __('Add new Job Board'),
        'add_new' => __('Add Job Board')
    );

    register_post_type('job_board', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 4,
        'taxonomies' => array('job_board'),
        'supports' => array('title', 'excerpt')
            )
    );
}

add_action('init', 'area_resource_post_type');

function area_resource_post_type()
{
    $labels = array(
        'name' => __('Area Announcements'),
        'singular_name' => __('Area Announcement'),
        'add_new_item' => __('Add new Area Announcement'),
        'add_new' => __('Add Area Announcement')
    );

    register_post_type('area_resource', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 4,
        'taxonomies' => array('area_resource'),
        'supports' => array('title', 'thumbnail')
            )
    );
}

// Register Mentor Custom Post Type
function custom_post_mentor() {

	$labels = array(
		'name'                => _x( 'Mentors', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Mentor', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Mentors', 'text_domain' ),
		'name_admin_bar'      => __( 'Mentor', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
		'all_items'           => __( 'All Mentors', 'text_domain' ),
		'add_new_item'        => __( 'Add New Mentor', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'new_item'            => __( 'New Mentor', 'text_domain' ),
		'edit_item'           => __( 'Edit Mentor', 'text_domain' ),
		'update_item'         => __( 'Update Mentor', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'search_items'        => __( 'Search Mentor', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'Mentor', 'text_domain' ),
		'description'         => __( 'Mentor Custom Post Type', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( 'specialty', 'location' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,		
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'mentor', $args );

}
add_action( 'init', 'custom_post_mentor', 0 );

// Register Specialties Taxonomy
function custom_specialties() {

	$labels = array(
		'name'                       => _x( 'Specialties', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Specialty', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Specialty', 'text_domain' ),
		'all_items'                  => __( 'All Specialties', 'text_domain' ),
		'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Specialty', 'text_domain' ),
		'add_new_item'               => __( 'Add New Specialty', 'text_domain' ),
		'edit_item'                  => __( 'Edit Specialty', 'text_domain' ),
		'update_item'                => __( 'Update Specialty', 'text_domain' ),
		'view_item'                  => __( 'View Specialty', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate Specialties with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove Specialties', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Specialties', 'text_domain' ),
		'search_items'               => __( 'Search Specialties', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'specialty', array( 'mentor' ), $args );

}
add_action( 'init', 'custom_specialties', 0 );

// Register Locations Taxonomy
function custom_locations() {

	$labels = array(
		'name'                       => _x( 'Locations', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Location', 'text_domain' ),
		'all_items'                  => __( 'All Locations', 'text_domain' ),
		'parent_item'                => __( 'Parent Item', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
		'new_item_name'              => __( 'New Location', 'text_domain' ),
		'add_new_item'               => __( 'Add New Location', 'text_domain' ),
		'edit_item'                  => __( 'Edit Location', 'text_domain' ),
		'update_item'                => __( 'Update Location', 'text_domain' ),
		'view_item'                  => __( 'View Location', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate Locations with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove Locations', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Locations', 'text_domain' ),
		'search_items'               => __( 'Search Locations', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'location', array( 'mentor' ), $args );

}
add_action( 'init', 'custom_locations', 0 );

/**
 * Change placeholder for title on post form 
 * 
 * @param string $title
 * @return string
 */
function change_default_title($title)
{
    $screen = get_current_screen();

    if ('people' == $screen->post_type || 'members_referrals' == $screen->post_type) {
        $title = 'Name, Degree';
    }
    return $title;
}

add_filter('enter_title_here', 'change_default_title');

/**
 * Set config for meta boxes
 */
function custom_meta_box()
{
    global $post;

    $template_file = get_post_meta($post->ID, '_wp_page_template', TRUE);



    add_meta_box(
            'people_meta_box', __('Basic Info', 'myplugin_textdomain'), 'people_meta_box_view', 'people', 'normal', 'low'
    );

    add_meta_box(
            'members_referrals_meta_box', __('&nbsp;', 'myplugin_textdomain'), 'members_referrals_box_view', 'members_referrals', 'normal', 'low'
    );

    add_meta_box(
            'advocacy_meta_box', __('Advocacy Settings', 'myplugin_textdomain'), 'advocacy_view', 'advocacy', 'normal', 'high'
    );
	
	add_meta_box(
            'job_board_meta_box', __('Job Board Settings', 'myplugin_textdomain'), 'job_board_view', 'job_board', 'normal', 'high'
    );

    add_meta_box(
            'policy_meta_box', __('Policy Settings', 'myplugin_textdomain'), 'policy_view', 'policy', 'normal', 'high'
    );

    add_meta_box(
            'area_resource_meta_box', __('&nbsp;', 'myplugin_textdomain'), 'area_resource_view', 'area_resource', 'normal', 'low'
    );

    if ($template_file == 'page-annual-meeting.php') {//add metaboxse if page use page-annual-meeting.php template
        add_meta_box(
                'announcment_left_meta_box', __('Announcment Left Box', 'myplugin_textdomain'), 'annoucment_left_view', 'page', 'normal', 'low'
        );
        add_meta_box(
                'announcment_middle_meta_box', __('Announcment Middle Box', 'myplugin_textdomain'), 'annoucment_middle_view', 'page', 'normal', 'low'
        );
        add_meta_box(
                'announcment_right_meta_box', __('Announcment Right Box', 'myplugin_textdomain'), 'annoucment_right_view', 'page', 'normal', 'low'
        );
        add_meta_box(
                'announcment_bottom_text_meta_box', __('Announcment Bottom Content', 'myplugin_textdomain'), 'annoucment_bottom_view', 'page', 'normal', 'low'
        );
    }

    if ($template_file == 'page-membership.php') {//add metaboxse if page page-membership.php template. It should be membership page
        add_meta_box(
                'membership_middle_content_meta_box', __('Middle Content', 'myplugin_textdomain'), 'membership_middle_content_view', 'page', 'normal', 'low'
        );
        add_meta_box(
                'membership_left_meta_box', __('Left Box', 'myplugin_textdomain'), 'membership_left_box_view', 'page', 'normal', 'low'
        );
        add_meta_box(
                'membership_middle_meta_box', __('Middle Box', 'myplugin_textdomain'), 'membership_middle_box_view', 'page', 'normal', 'low'
        );
        add_meta_box(
                'membership_right_meta_box', __('Right Box', 'myplugin_textdomain'), 'membership_right_box_view', 'page', 'normal', 'low'
        );
    }
}

add_action('add_meta_boxes', 'custom_meta_box');

/**
 * Template file for people form
 */
function people_meta_box_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/people_meta_box.php';
}

/**
 * Template file for members_referrals CMS form
 */
function members_referrals_box_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/members_referrals_meta_box.php';
}

/**
 * Template file for policy CMS form
 */
function policy_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/policy_meta_box.php';
}

/**
 * Template file for advocacy CMS form
 */
function advocacy_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/advocacy_meta_box.php';
}

/**
 * Template file for job board CMS form
 */
function job_board_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/job_board_meta_box.php';
}

/**
 * Template file for area CMS form
 */
function area_resource_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/area_resource_meta_box.php';
}

/**
 * Template file for annoucment left box
 */
function annoucment_left_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/annoucment_left_meta_box.php';
}

/**
 * Template file for annoucment middle box
 */
function annoucment_middle_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/annoucment_middle_meta_box.php';
}

/**
 * Template file for annoucment right box
 */
function annoucment_right_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/annoucment_right_meta_box.php';
}

/**
 * Template file for annoucment bottom content
 */
function annoucment_bottom_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/annoucment_bottom_content_view.php';
}

/**
 * Template file for annoucment bottom content
 */
function membership_middle_content_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/membership_middle_content_view.php';
}
/**
 * Template file for membership left box
 */
function membership_left_box_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/membership_left_box.php';
}
/**
 * Template file for membership middle box
 */
function membership_middle_box_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/membership_middle_box.php';
}
/**
 * Template file for membership right box
 */
function membership_right_box_view()
{
    $base_path = dirname(__DIR__);
    require_once $base_path . '/meta_boxes/membership_right_box.php';
}

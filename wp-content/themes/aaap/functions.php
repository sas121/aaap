<?php

require_once 'custom_post_types/custom_post_types.php';
require_once 'extras/meta_box_taxonomy_saves.php';
require_once 'extras/ajax.php';
require_once 'widgets/widget_loader.php';

$host = $_SERVER['HTTP_HOST'];

$pageID = 250;
$areaDirectorPageID = 301;
if ($host == 'localhost') {
    $pageID = 544;
    $areaDirectorPageID = 783;
}

global $term;

/**
 * Define css for admin pages
 */
function admin_style()
{
    wp_enqueue_style('admin-style', dirname(get_stylesheet_uri()) . '/css/admin_style.css');
}

add_action('admin_print_styles', 'admin_style');

/**
 * Define JS for admin pages
 */
function meta_boxes_scripts()
{
    $url = get_permalink();
    $base_url = site_url() . '/';
    $screen = get_current_screen();
    $showJS = array('people', 'members_referrals', 'advocacy', 'job_board', 'policy', 'link_pdf', 'area_resource'); //include js in these custom post types

    if ($url == $base_url || in_array($screen->post_type, $showJS)) {
        wp_enqueue_media();
        echo '<script type="text/javascript"> var base_url = "' . get_stylesheet_directory_uri() . '";</script>';
        wp_enqueue_script('meta-box-javascript', dirname(get_stylesheet_uri()) . '/meta_boxes/js/main.js');
    }
    wp_enqueue_script('meta-box-validation-script', dirname(get_stylesheet_uri()) . '/js/jquery.validate.js');
    wp_enqueue_script('meta-box-jquery-ui', dirname(get_stylesheet_uri()) . '/js/jquery-ui-1.10.4.custom.js');
}

add_action('admin_enqueue_scripts', 'meta_boxes_scripts');




/*
 * Register meta box for home page
 */

function home_page_boxes()
{
    $screen = get_current_screen();
    $url = get_permalink();
    $base_url = site_url() . '/';
    if ($url == $base_url) {
        add_meta_box(
                'home_page_left_box', __('Left Box', 'myplugin_textdomain'), 'home_page_left_box', 'page', 'side', 'low'
        );
        add_meta_box(
                'home_page_middle_box', __('Middle Box', 'myplugin_textdomain'), 'home_page_middle_box', 'page', 'side', 'low'
        );
        add_meta_box(
                'home_page_right_box', __('Right Box', 'myplugin_textdomain'), 'home_page_right_box', 'page', 'side', 'low'
        );

        add_meta_box(
                'home_page_content_links', __('Content Links', 'myplugin_textdomain'), 'home_page_content_links', 'page', 'normal', 'low'
        );
    }

    $post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'];
    $template_file = get_post_meta($post_id, '_wp_page_template', TRUE);

    if ($template_file == 'page-content.php') {//Add metabox only on pages with page-conten.php file selected as template. This is main content template file
        add_meta_box(
                'right_sidebar_widget_select_box', __('Right rail widtget template', 'myplugin_textdomain'), 'right_rail_widget_template', 'page', 'side', 'high'
        );
    }
}

add_action('add_meta_boxes', 'home_page_boxes');

function home_page_left_box()
{
    include_once 'meta_boxes/home_page_left_box.php';
}

function home_page_right_box()
{
    include_once 'meta_boxes/home_page_right_box.php';
}

function home_page_middle_box()
{
    include_once 'meta_boxes/home_page_middle_box.php';
}

function home_page_content_links()
{
    include_once 'meta_boxes/home_page_content_links.php';
}

function right_rail_widget_template()
{
    include_once 'meta_boxes/right_rail_widget_template.php';
}

function save_home_page_boxes($post_id)
{
    $url = get_permalink();
    $base_url = site_url() . '/';
    $right_sidebar = $_POST['page_sidebar'];

    if ($url == $base_url) {
        $left_box = serialize($_POST['left_box']);
        $middle_box = serialize($_POST['middle_box']);
        $right_box = serialize($_POST['right_box']);
        $content_links = serialize($_POST['home_content_links']);
        update_post_meta($post_id, '_my_left_box', $left_box);
        update_post_meta($post_id, '_my_middle_box', $middle_box);
        update_post_meta($post_id, '_my_right_box', $right_box);
        update_post_meta($post_id, '_home_content_links', $content_links);
    }

    if (!empty($right_sidebar)) {
        update_post_meta($post_id, '_page_right_sidebar', $right_sidebar);
    }

    require_once 'extras/meta_box_saves.php';
}

add_action('save_post', 'save_home_page_boxes');

/**
 * Get data from metaboxes
 */
function get_meta_values($meta_key)
{
    global $post;
    $value_string = get_post_meta($post->ID, $meta_key, true);
    $value = unserialize($value_string);
    return $value;
}

/**
 * Image resize
 */
add_image_size('home_page_box', '244', '155', true);
add_image_size('people_images', '174', '173', true);
add_image_size('membership_images', '975', '340', true);

/**
 * Get proper image name for custom resize
 */
function get_image_name($image, $dimensions)
{
    $img_parts = explode('.', $image);
    $img_name = preg_replace('/(\.[a-zA-Z]{3,4}$)/', '-' . $dimensions . '$1', $image);
    return $img_name;
}

/**
 * Register footer menu
 */
register_nav_menu('footer', 'Footer menu');
/**
 * Register small top menu
 */
register_nav_menu('top_menu', 'Small top menu');
/**
 * Register small top highlight menu
 */
register_nav_menu('highlight_menu', 'Small top highlight menu');

/**
 * Custom header menu
 */
function get_header_menu_items()
{
    $menu_name = 'primary';
    if (( $locations = get_nav_menu_locations() ) && isset($locations[$menu_name])) {

        $headerMenu = wp_get_nav_menu_object($locations[$menu_name]);

        $menu_items = wp_get_nav_menu_items($headerMenu->term_id, array('order' => 'DESC'));
        return $menu_items;
    }
}

function create_menu_html()
{
    $menu = get_header_menu_items();
    $page_id = get_the_ID();

    // print_r($page_id);
    //print_r($menu);

    $parents = get_post_ancestors($page_id);
    $oldest = end($parents);
    $dis = array();


    $main_menu_items = array();
    $submenu_count = 1;
    $loop = 0;
    $m = '<ul id="menu-header-menu" class="nav-menu">';
    foreach ($menu as $key => $item) {


        if ($page_id == $item->object_id) {
            $currentPage = 'current_page ';
        } else if ($oldest == $item->object_id) {
            $currentPage = 'current_page ';
        } else {
            $currentPage = '';
        }
        if (in_array($item->menu_item_parent, $dis)) {
            if ($menu[$key + 1]->menu_item_parent == 0) {            
                $m .= '</ul></td>';
            }
            continue;  
        }


        if ($item->menu_item_parent == 0) {//It's a main menu item
            if ($key > 0) {
                $m .='</tr></table></div>';
            }
            $m .= '<li><a class="' . $currentPage . 'main_menu_link" href="' . $item->url . '">' . $item->title . '</a><div><table><tr>';
        } else {
            array_push($dis, $item->ID);
            if ($submenu_count == 1) {
                $m .= '<td><ul class="sub-menu">';

                $m .= '<li class="a"><a href="' . $item->url . '">' . $item->title . '</a></li>';

                if ($menu[$key + 1]->menu_item_parent == 0 && isset($menu[$key + 1])) {
                    $m .= '</ul></td><!--test-->';
                    $submenu_count = 0;
                }
            }
            if ($submenu_count == 2) {

                $m .= '<li class="b"><a href="' . $item->url . '">' . $item->title . '</a></li>';

                if ($menu[$key + 1]->menu_item_parent == 0 && isset($menu[$key + 1])) {
                    $m .= '</ul></td><!--test2-->';
                    $submenu_count = 0;
                }
            }
            if ($submenu_count == 3) {
                $m .= '<li class="c"><a href="' . $item->url . '">' . $item->title . '</a></li>';
                $m .= '</ul></td><!--test3-->';
                $submenu_count = 0;
            }

            $submenu_count++;
        }

        if ($key + 1 == count($menu)) {
            $m .='</ul></td></tr></table></div>';
        }
    }
    $m .= '</ul>';

    

    return $m;
}

/**
 * Add JS files to front
 */
function add_js()
{
    wp_enqueue_script('custom-js-select', get_stylesheet_directory_uri() . '/js/select2.js');
    wp_enqueue_script('script-name', get_stylesheet_directory_uri() . '/js/main.js');
    wp_enqueue_script('form-validation', dirname(get_stylesheet_uri()) . '/js/jquery.validate.js');
    wp_enqueue_script('custom-form-script', get_stylesheet_directory_uri() . '/js/custom-form-element.js');
    wp_enqueue_script('modal', get_stylesheet_directory_uri() . '/js/jquery.easyModal.js');
}

add_action('wp_enqueue_scripts', 'add_js');

function add_css_to_front()
{
    wp_enqueue_style('custom-select-style', get_stylesheet_directory_uri() . '/css/select2.css');
    if (check_os('Mac')) {
        wp_enqueue_style('mac-style', get_stylesheet_directory_uri() . '/css/mac.css');
    }
}

add_action('wp_enqueue_scripts', 'add_css_to_front');

/**
 * Get latest posts
 */
function latest_posts($limit = 3)
{
    $cat_id = get_cat_ID('news');

    $args = array(
        'posts_per_page' => $limit,
        'offset' => 0,
        'category' => $cat_id,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_status' => 'publish',
        'suppress_filters' => true
    );
    $posts_array = get_posts($args);
	wp_reset_postdata(); 

    return $posts_array;
}

/**
 * Fetch all posts from people custom post type 
 * that belongs to one category
 * 
 * @param string $catagory One of categories in people custom post types
 * @return array
 */
function get_people_pages_by_category($catagory)
{
    $args = array(
		'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'people_jobs',
                'field' => 'slug',
                'terms' => $catagory
            )
        )
    );
    $query = new WP_Query($args);

    return $query->posts;
}

/**
 * Fetch all posts for some category
 * 
 * @param string $category_name Custom name of the slug in taxonomy
 * @param string $current_category Name of category
 * @return type
 */
function get_posts_by_category($category_name, $current_category)
{
    $respons = query_posts(array($category_name => $current_category));
    return $respons;
}

/**
 * Fetch all posts for selected custom post type
 * 
 * @param string $custom_post_type Name of custom post type
 * @return object
 */
function get_custom_post_type_posts($custom_post_type)
{
    $args = array(
        'orderby' => 'post_date',
		'posts_per_page' => -1,
        'order' => 'DESC',
        'post_type' => $custom_post_type,
        'post_status' => 'publish',
    );
    $custom_posts = get_posts($args);
    return $custom_posts;
}

function create_location_filter_list()
{
    global $wpdb;
    $europe = $wpdb->get_results("SELECT * FROM europe");
    $usa = $wpdb->get_results("SELECT * FROM usa_states");

    return array(
        'europe' => $europe,
        'usa_states' => $usa,
    );
}

function remove_sidebars()
{
    unregister_sidebar('sidebar-1');
    unregister_sidebar('sidebar-2');
    unregister_sidebar('sidebar-3');
}

add_action('init', 'remove_sidebars');

function remove_widgets()
{
    unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Archives');
    unregister_widget('WP_Widget_Links');
    unregister_widget('WP_Widget_Meta');
    unregister_widget('WP_Widget_Search');
    unregister_widget('WP_Widget_Categories');
    unregister_widget('WP_Widget_Recent_Posts');
    unregister_widget('WP_Widget_Recent_Comments');
    unregister_widget('WP_Widget_RSS');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('WP_Nav_Menu_Widget');
    unregister_widget('Twenty_Fourteen_Ephemera_Widget');
}

add_action('widgets_init', 'remove_widgets', 11);

function add_sidebars()
{
    register_sidebar(array(
        'name' => __('Left Sidebar Template One', 'theme_text_domain'),
        'id' => 'left-sidebar',
        'description' => 'Right side bar template one',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    register_sidebar(array(
        'name' => __('Right Sidebar Template One', 'theme_text_domain'),
        'id' => 'right-sidebar-1',
        'description' => 'Right side bar template one',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    register_sidebar(array(
        'name' => __('Right Sidebar Template Two', 'theme_text_domain'),
        'id' => 'right-sidebar-2',
        'description' => 'Right side bar template two',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    register_sidebar(array(
        'name' => __('Right Sidebar Template Three', 'theme_text_domain'),
        'id' => 'right-sidebar-3',
        'description' => 'Right side bar template three',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));

    register_sidebar(array(
        'name' => __('Default Right Sidebar', 'theme_text_domain'),
        'id' => 'default-right-sidebar',
        'description' => 'Default Right Sidebar',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    
    register_sidebar(array(
        'name' => __('Custom Sidebar', 'theme_text_domain'),
        'id' => 'custom-sidebar',
        'description' => 'Custom Sidebar',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    
    register_sidebar(array(
        'name' => __('Left Sidebar 2', 'theme_text_domain'),
        'id' => 'left-sidebar-2',
        'description' => 'Left Sidebar 2',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    
    register_sidebar(array(
        'name' => __('Quote Sidebar', 'theme_text_domain'),
        'id' => 'quote-sidebar',
        'description' => 'Quote Sidebar',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
    
    register_sidebar(array(
        'name' => __('Homepage Widget', 'theme_text_domain'),
        'id' => 'homepage-widget',
        'description' => 'Homepage Widget',
        'class' => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>'
    ));
}

add_action('widgets_init', 'add_sidebars', 12);

/**
 * Add new buttons to wordpress editor
 * 
 * @param array $buttons
 * @return string
 */
function add_more_buttons($buttons)
{
    $buttons[] = 'hr';
    $buttons[] = 'fontsizeselect';
    $buttons[] = 'fontselect';
    $buttons[] = 'styleselect';
    return $buttons;
}

add_filter("mce_buttons_3", "add_more_buttons");

// Callback function to filter the MCE settings
function my_mce_before_init_insert_formats($init_array)
{

    // Define the style_formats array
    $style_formats = array(
        // Each array child is a format with it's own settings
        array(
            'title' => 'Price One',
            'inline' => 'span',
            'classes' => 'price_one',
            'wrapper' => false,
        ),
        array(
            'title' => 'Price Two',
            'inline' => 'span',
            'classes' => 'price_two',
            'wrapper' => false,
        ),
        array(
            'title' => 'Price Three',
            'inline' => 'span',
            'classes' => 'price_three',
            'wrapper' => false,
        ),
        array(
            'title' => 'Price One Title',
            'inline' => 'span',
            'classes' => 'price_one_title',
            'wrapper' => false,
        ),
        array(
            'title' => 'Price Two Title',
            'inline' => 'span',
            'classes' => 'price_two_title',
            'wrapper' => false,
        ),
        array(
            'title' => 'Price Three Title',
            'inline' => 'span',
            'classes' => 'price_three_title',
            'wrapper' => false,
        ),
        array(
            'title' => 'Price Text',
            'inline' => 'span',
            'classes' => 'price_text',
            'wrapper' => false,
        ),
        array(
            'title' => 'Footnote',
            'inline' => 'span',
            'classes' => 'footnote',
            'wrapper' => false,
        ),
        array(
            'title' => 'Custom Link',
            'classes' => 'box_link',
            'selector' => 'a',
        ),
        array(
            'title' => 'Blue Text Mark',
            'classes' => 'blue_text',
            'inline' => 'span',
        ),
    );
    // Insert the array, JSON ENCODED, into 'style_formats'
    $init_array['style_formats'] = json_encode($style_formats);
    $init_array['fontsize_formats'] = "10px 11px 12px 13px 14px 15px 16px 17px 18px 19px 20px 21px 22px 23px 24px 25px 26px 27px 28px 29px 30px 32px 48px";
    $init_array['font_formats'] = 'ChronicleDisplay = ChronicleDisplay-Roman2;' .
            'Gotham-Light = Gotham-Light2;' .
            'Gotham-Medium = Gotham-Medium2;' .
            'Gotham-Bold = Gotham-Bold2' .
            ''
    ;

    return $init_array;
}

// Attach callback to 'tiny_mce_before_init' 
add_filter('tiny_mce_before_init', 'my_mce_before_init_insert_formats');

function my_theme_add_editor_styles()
{
    add_editor_style(get_stylesheet_directory_uri() . '/css/editor_style.css');
}

add_action('init', 'my_theme_add_editor_styles');

/**
 * Add new query vars
 * 
 * @param array $aVars
 * @return array
 */
function add_query_vars($aVars)
{
    $aVars[] = "area_cat"; // represents the name of the category shown in URL
    $aVars[] = "area_dir"; // represents the name of the category shown in URL

    return $aVars;
}

// hook add_query_vars function into query_vars
add_filter('query_vars', 'add_query_vars');

function add_rewrite_rules($aRules)
{
    global $pageID;
    global $areaDirectorPageID;
    $aNewRules = array('practitioner-resources/area-resources/area-announcements/([^/]+)/?$' => 'index.php?page_id=' . $pageID . '&area_cat=$matches[1]');
    $aNewRules2 = array('practitioner-resources/area-resources/area-director/([^/]+)/?$' => 'index.php?page_id=' . $areaDirectorPageID . '&area_dir=$matches[1]');
    $aRules = $aNewRules + $aNewRules2 + $aRules;
    return $aRules;
}

// hook add_rewrite_rules function into rewrite_rules_array
add_filter('rewrite_rules_array', 'add_rewrite_rules');

/**
 * Hide br tags in wpcf7
 */
if (!defined('WPCF7_AUTOP')) {
    define('WPCF7_AUTOP', false);
}

/**
 * Add meta box to area resource category 
 */
function area_resource_add_category_meta_box()
{
    include_once 'meta_boxes/area_resource_category_meta_box.php';
}

add_action('area_resource_category_add_form_fields', 'area_resource_add_category_meta_box', 10, 2);

function area_resource_edit_category_meta_box($term)
{
    include_once 'meta_boxes/area_resource_edit_category_meta_box.php';
}

add_action('area_resource_category_edit_form_fields', 'area_resource_edit_category_meta_box', 10, 2);

/**
 * Change order of posts
 * 
 * @param object $wp_query Wordpress Object for queries
 */
function set_post_order_in_admin($wp_query)
{

    $available_sort_posts = array(//custom post slugs
        'advocacy',
        'policy',
		'job_board',
		'area_resource'
    );
    if (is_admin()) {
        $screen = get_current_screen();
        $post_type = $screen->post_type;
        if (in_array($post_type, $available_sort_posts)) {
            $wp_query->set('orderby', 'meta_value_num');
            $wp_query->set('meta_key', 'home_index');
            $wp_query->set('order', 'ASC');
        }
    }
}

add_filter('pre_get_posts', 'set_post_order_in_admin');

/**
 * 
 * @param string $os Operating system (Win,Mac) 
 * @return boolean
 */
function check_os($os)
{
    $user_agent = getenv("HTTP_USER_AGENT");
    if (strpos($user_agent, $os) !== FALSE) {
        return true;
    }
}

/**
 * Fetch data from contact form and change recepient email
 * so we can sand email to address from database
 * 
 * @param object $wpcf Object created by wpcf7 plugin
 */
function set_area_director_email(&$wpcf)
{
    $data = $wpcf->posted_data;


    $slug = $data['area'];

    $term = get_term_by('slug', $slug, 'area_resource_category');

    $t_id = $term->term_taxonomy_id;

    $term_meta = get_option("taxonomy_$t_id");

    $areaDirectorEmail = $term_meta['area_director_email'];

    $wpcf->mail['recipient'] = $areaDirectorEmail;
}

//add_action("wpcf7_before_send_mail", "set_area_director_email");

/**
 * Add new shortcode tag to form
 */
function wpcf7_add_shortcode_getparam()
{
    if (function_exists('wpcf7_add_shortcode')) {
        wpcf7_add_shortcode('area-name', 'wpcf7_getparam_shortcode_handler', true);
    }
}

add_action('wpcf7_init', 'wpcf7_add_shortcode_getparam');

/**
 * Get new tag in html form
 * 
 * @return string
 */
function wpcf7_getparam_shortcode_handler()
{
    $slug = get_query_var('area_dir');
    $html = '';
    if ($slug) {
        $html = '<input name="area-name" type="hidden" class="zlatko" value="' . $slug . '" >';
    }
    return $html;
}

add_filter('wpcf7_special_mail_tags', 'add_tag_to_email', 10, 3);

/**
 * Replace tag ,[area-name], from email with real area from URL
 * 
 * @param string $output
 * @param string $name
 * @param string $html
 * @return string
 */
function add_tag_to_email($output, $name, $html)
{
    $actual_link = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $array = explode('/', $actual_link);
    $length = count($array);



    if ($array[$length - 3] == 'area-director') {
        $area = $array[$length - 2];
    }

    if ('area-name' == $name) {
        if ($area) {
            $output = ucwords(str_ireplace('-', ' ', $area));
        } else {
            $output = ' ';
        }
    }
    return $output;
}

// apply tags to attachments
function wptp_add_tags_to_attachments()
{
    register_taxonomy_for_object_type('post_tag', 'attachment');
}

add_action('init', 'wptp_add_tags_to_attachments');

// Add tag metabox to page
function add_tags_to_page()
{

    register_taxonomy_for_object_type('post_tag', 'page');
}

// Add to the admin_init hook of your theme functions.php file 
add_action('admin_init', 'add_tags_to_page');

// search filter
function search_filter($query)
{
    if (!$query->is_admin && $query->is_search) {
        $query->set('post_type', array('post', 'page', 'people', 'members_referrals', 'policy', 'advocacy', 'area_resource')); // id of page or post
    }
    return $query;
}
add_filter('pre_get_posts', 'search_filter');


//Check form data on Patient Referral Program page 
if (isset($_POST['declimer_confirm'])) {
    if (empty($_POST['year']) || empty($_POST['month']) || empty($_POST['day']) || empty($_POST['agree'])) {
        header('Location:' . site_url() . '/patient-resources/find-a-specialist/');
    } else {
		$add_anchor = ( isset($_GET['sid']) ) ? '#'.$_GET['sid'] : '';
        header('Location:' . site_url() . '/patient-resources/find-a-specialist-list/'.$add_anchor);
    }
    die();
}

// get link for post to display in search page
function get_post_link_in_search($res){
	
	switch( $res->post_type ){
		case 'people':
			$terms = wp_get_post_terms( $res->ID, 'people_jobs' );
			return site_url().'/about/'.$terms[0]->slug.'#'.$res->ID;
		break;
		
		case 'members_referrals':
			return site_url().'/patient-resources/find-a-specialist';
		break;
		
		case 'policy':
			return site_url().'/about/policy-statements#'.$res->ID;
		break;
		
		case 'advocacy':
			return site_url().'/about/advocacy#'.$res->ID;
		break;
		
		case 'area_resource':
			return site_url().'/practitioner-resources/area-resources/';
		break;
		
		
		default:
			return $res->guid;
		break;
	}
	
}

function modify_tax_labels($label){

    return "Filter by $label";
}

//add_filter('beautiful_filters_taxonomy_label', 'modify_tax_labels', 10, 1);


function custom_tribe_bar_datepicker_caption() {
	$caption = 'Date';
	
	if ( tribe_is_month() ) {
		$caption = 'Find events in';
	} elseif ( tribe_is_list_view() ) {
		$caption = 'Events From';
	} elseif ( tribe_is_day() ) {
		$caption = 'Day Of';
	}
	
	return $caption;
}
add_filter( 'tribe_bar_datepicker_caption', 'custom_tribe_bar_datepicker_caption' );

add_filter( 'tribe_get_events_title', 'change_month_title_wording' );

function change_month_title_wording( $title ) {
	return str_replace( 'Events for', '', $title );
}

/*
Plugin Name: The Events Calendar Remove Events Archive from Yoast Page Title
Plugin URI: https://gist.github.com/geoffgraham/041c048aca979de714273314ae039ce7
Description: The Events Calendar - Yoast SEO - Prevent Yoast from changing Event Title Tags for Event Views (Month, List, Etc,)
*/
add_action( 'pre_get_posts', 'tribe_remove_wpseo_title_rewrite', 20 );
function tribe_remove_wpseo_title_rewrite() {
    if ( class_exists( 'Tribe__Events__Main' ) && class_exists( 'Tribe__Events__Pro__Main' ) ) {
        if( tribe_is_month() || tribe_is_upcoming() || tribe_is_past() || tribe_is_day() || tribe_is_map() || tribe_is_photo() || tribe_is_week() ) {
            $wpseo_front = WPSEO_Frontend::get_instance();
            remove_filter( 'wp_title', array( $wpseo_front, 'title' ), 15 );
            remove_filter( 'pre_get_document_title', array( $wpseo_front, 'title' ), 15 );
        }
    } elseif ( class_exists( 'Tribe__Events__Main' ) && !class_exists( 'Tribe__Events__Pro__Main' ) ) {
        if( tribe_is_month() || tribe_is_upcoming() || tribe_is_past() || tribe_is_day() ) {
            $wpseo_front = WPSEO_Frontend::get_instance();
            remove_filter( 'wp_title', array( $wpseo_front, 'title' ), 15 );
            remove_filter( 'pre_get_document_title', array( $wpseo_front, 'title' ), 15 );
        }
    }
	

};







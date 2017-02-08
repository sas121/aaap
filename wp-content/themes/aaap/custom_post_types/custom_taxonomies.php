<?php

function people_hierarchical_taxonomy()
{
    $labels = array(
        'name' => _x('People Jobs', 'taxonomy general name'),
        'singular_name' => _x('People Job', 'taxonomy singular name'),
        'search_items' => __('Search People Jobs'),
        'all_items' => __('All People Jobs'),
        'parent_item' => __('Parent People Jobs'),
        'parent_item_colon' => __('Parent People Jobs:'),
        'edit_item' => __('Edit People Jobs'),
        'update_item' => __('Update People Jobs'),
        'add_new_item' => __('Add People Jobs'),
        'new_item_name' => __('New People Jobs'),
        'menu_name' => __('People Jobs'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'people_jobs'),
    );

    register_taxonomy('people_jobs', 'people', $args);
}

function people_nonhierarchical_taxonomy()
{
    $labels = array(
        'name' => _x('Tags', 'taxonomy general name'),
        'singular_name' => _x('Tag', 'taxonomy singular name'),
        'search_items' => __('Search Tags'),
        'popular_items' => __('Popular Tags'),
        'all_items' => __('All Tags'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Tag'),
        'update_item' => __('Update Tag'),
        'add_new_item' => __('Add New Tag'),
        'new_item_name' => __('New Tag'),
        'separate_items_with_commas' => __('Separate tags with commas'),
        'add_or_remove_items' => __('Add or remove tags'),
        'choose_from_most_used' => __('Choose from the most used tags'),
        'not_found' => __('No tags found.'),
        'menu_name' => __('Tags'),
    );

    $args = array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array('slug' => 'people_tags'),
    );

    register_taxonomy('tags', 'people', $args);
}

function link_pdf_category()
{
    $labels = array(
        'name' => _x('Category', 'taxonomy general name'),
        'singular_name' => _x('Category', 'taxonomy singular name'),
        'search_items' => __('Search Category'),
        'all_items' => __('All Categories'),
        'parent_item' => __('Parent Category'),
        'parent_item_colon' => __('Parent Category:'),
        'edit_item' => __('Edit Category'),
        'update_item' => __('Update Category'),
        'add_new_item' => __('Add Category'),
        'new_item_name' => __('New Category'),
        'menu_name' => __('Category'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'link_pdf_category'),
    );

    register_taxonomy('link_pdf_category', 'link_pdf', $args);
}

function link_pdf_tags()
{
    $labels = array(
        'name' => _x('Tags', 'taxonomy general name'),
        'singular_name' => _x('Tag', 'taxonomy singular name'),
        'search_items' => __('Search Tags'),
        'popular_items' => __('Popular Tags'),
        'all_items' => __('All Tags'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Tag'),
        'update_item' => __('Update Tag'),
        'add_new_item' => __('Add New Tag'),
        'new_item_name' => __('New Tag'),
        'separate_items_with_commas' => __('Separate tags with commas'),
        'add_or_remove_items' => __('Add or remove tags'),
        'choose_from_most_used' => __('Choose from the most used tags'),
        'not_found' => __('No tags found.'),
        'menu_name' => __('Tags'),
    );

    $args = array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array('slug' => 'link_pdf_tags'),
    );

    register_taxonomy('link_pdf_tags', 'link_pdf', $args);
}

function area_resource_category()
{
    $labels = array(
        'name' => _x('Area', 'taxonomy general name'),
        'singular_name' => _x('Area', 'taxonomy singular name'),
        'search_items' => __('Search Areas'),
        'all_items' => __('All Areas'),
        'parent_item' => __('Parent Area'),
        'parent_item_colon' => __('Parent Area:'),
        'edit_item' => __('Edit Area'),
        'update_item' => __('Update Area'),
        'add_new_item' => __('Add Area'),
        'new_item_name' => __('New Area'),
        'menu_name' => __('Area'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'area_resource_category'),
    );

    register_taxonomy('area_resource_category', 'area_resource', $args);
}

function area_resource_tags()
{
    $labels = array(
        'name' => _x('Tags', 'taxonomy general name'),
        'singular_name' => _x('Tag', 'taxonomy singular name'),
        'search_items' => __('Search Tags'),
        'popular_items' => __('Popular Tags'),
        'all_items' => __('All Tags'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Tag'),
        'update_item' => __('Update Tag'),
        'add_new_item' => __('Add New Tag'),
        'new_item_name' => __('New Tag'),
        'separate_items_with_commas' => __('Separate tags with commas'),
        'add_or_remove_items' => __('Add or remove tags'),
        'choose_from_most_used' => __('Choose from the most used tags'),
        'not_found' => __('No tags found.'),
        'menu_name' => __('Tags'),
    );

    $args = array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array('slug' => 'area_resource_tags'),
    );

    register_taxonomy('area_resource_tags', 'area_resource', $args);
}


/**
 * Add taxonomies
 */
add_action('init', 'create_people_taxonomies', 0);
function create_people_taxonomies()
{
    people_hierarchical_taxonomy();
    people_nonhierarchical_taxonomy();
    link_pdf_category();
//    link_pdf_tags();
    area_resource_category();
    area_resource_tags();
    
}

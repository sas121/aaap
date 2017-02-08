<?php
/**
 * Template Name: AAAP Gravity Form Page w/ Custom Sidebar */
get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID,'_page_right_sidebar');
$right_sidebar_id =  current($meta);
$content = apply_filters('the_content', $page->post_content);
?>

<div class="headline">
    <div class="headline_wrapper">
            <h1><?php echo $parent_page_title; ?></h1>
    </div>
</div>
<div class="page_wrapper">
    <div class="page_content">
    
        <div class="aaap_application_sidebar">
            <div class="app-side-container">
            <?php the_field('custom_sidebar_content'); ?>
                   <?php

// check if the repeater field has rows of data
if( have_rows('additional_sidebar_content') ):

 	// loop through the rows of data
    while ( have_rows('additional_sidebar_content') ) : the_row();
    ?>
		<hr class="app-side-hr"></hr>
        <h3 class="blue-bold"><?php echo the_sub_field('header'); ?></h3>
        <p class="app-side-reg"><?php echo the_sub_field('description'); ?></p>
        <a class="box_link" href="<?php echo the_sub_field('link_url'); ?>"><?php echo the_sub_field('link'); ?></a>
<?php
    endwhile;

else :

    // no rows found

endif;

?>
            </div>
        </div>
        <div class="application_main_content">
            <div><?php echo $content; ?></div>
        </div>   
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();
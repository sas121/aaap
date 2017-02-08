<?php
/**
 * Template Name: Mentoring Program Directory
 */
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
    
        <div class="mentoring-program-directory">
            <div class="directory-side-container">
            <h2 class="boldest">Get Support from Seasoned Professionals</h2>
            <p class="app-side-reg">The goal of the PCSS-O colleague support program is to provide individualized support for healthcare providers who do not have access to clinical experts in this area.</p>
            <p class="app-side-ital">The Mentoring Program is for educational purposes only. Participants agree that they will not rely on this information while treating patients or providing other professional services</p>
            <hr class="app-side-hr"></hr>
            <h3 class="blue-bold">Ask a Colleague Instead</h3>
             <p class="app-side-reg">Designed to answer a question posted on a moderated listserv. The person posting the question can either ask to remain anonymous OR they can provide their name. AAAP staff will monitor the questions and post. Mentors will be asked to comment and provide advice/suggestions.</p>
                   <a class="box_link" href="http://aaap.org/">ASK A COLLEAGUE</a>
            </div>
        </div>
        
        <div class="directory_main_content">
            <h3 class="page_title"><?php echo get_the_title($post->ID) ?></h3>
            <div class="mentory-query-results">
            // WP_Query arguments
<?php 
$args = array (
	'post_type'              => array( 'mentor' ),
);

// The Query
$query = new WP_Query( $args );
?>            
            
            
            
            </div>
        </div>   
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();


<?php
/**
 * Template Name: Custom AAAP Application Form for Doctors*/
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
            <h2 class="boldest">If you would like to obtain a mentor, please complete the following form.</h2>
            <p class="app-side-reg">Once received, we will match you up with a mentor that best suits your needs. Please feel free to contact us at 401-524-3074 if you have any questions.</p>
            <p class="app-side-ital">The Mentoring Program is for educational purposes only. Participants agree that they will not rely on this information while treating patients or providing other professional services</p>
            <hr class="app-side-hr"></hr>
            <h3 class="blue-bold">Ask a Colleague Instead</h3>
             <p class="app-side-reg">Designed to answer a question posted on a moderated listserv. The person posting the question can either ask to remain anonymous OR they can provide their name. AAAP staff will monitor the questions and post. Mentors will be asked to comment and provide advice/suggestions.</p>
                   <a class="box_link" href="http://www.aaap.org/ask-a-colleague/">ASK A COLLEAGUE</a>
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
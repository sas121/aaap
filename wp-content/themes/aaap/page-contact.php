<?php
/**
 * Template Name: Contact Page
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
        <div class="menu_sidebar">
            <?php include_once 'left-sidebar.php'; ?>
        </div>
        <div class="page_main_content main_template">
            <h3 class="page_title"><?php echo get_the_title($post->ID) ?></h3>
            <div><?php echo $content; ?></div>
			
			<div id="thank-you">
				<h3>Message sent</h3>
				<p>
					Your message has been successfully sent.<br>
					Thank you for contacting us.
				</p>
				<a href="#" id="close-btn">OK</a>
			</div>
			
			<script type="text/javascript">
				$(function() {
					$('#thank-you').easyModal({
						top: 300,
						overlayOpacity: 0.5,
						overlayColor: "#000"
					});
					
					$('#close-btn').click(function(){
						$('#thank-you').trigger('closeModal');
					});
				});
			</script>
        </div>   
        <div class="right_sidebar">
            <?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();
<?php

get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID,'_page_right_sidebar');
$right_sidebar_id =  current($meta);
$content = apply_filters('the_content', get_post_meta($post->ID, '_advocacy_description', true));




?>
<div class="headline">
    <div class="headline_wrapper">
		<h1><?php echo  strtoupper($post->post_type);?></h1>
    </div>
</div>

<div class="page_wrapper">
    <div class="page_content">
        <div class="menu_sidebar">
            <?php //include_once 'left-sidebar.php'; ?>
			<div class="sidebar_content">
				<?php echo do_shortcode('[custom_menu_wizard menu="header menu" children_of="about " include="root" menu_class="menu-widget-left" wrap_link="div class=li-cont"]'); ?>
			</div>
			<script type="text/javascript">
				$('.menu-widget-left li').each(function(){
					if($(this).find('a').html() == 'Advocacy Statements')
						$(this).addClass('current-menu-item');
				});
			</script>
        </div>
        <div class="page_main_content main_template">
            <h3 class="page_title"><?php echo get_the_title($post->ID) ?></h3>
            <div><?php echo $content; ?></div>
        </div>   
        <div class="right_sidebar">
            <?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();

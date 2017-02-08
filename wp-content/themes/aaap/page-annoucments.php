<?php
/**
 * Template Name: Annoucments Template
 */
$current_category = get_query_var('area_cat');
$categoryPosts = get_posts_by_category('area_resource_category', $current_category);
$categoryDescription = category_description(get_category_by_slug($current_category)->term_id);
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$cat = get_term_by('slug', $current_category, 'area_resource_category');

get_header();
?>
<div class="headline">
    <div class="headline_wrapper">
        <h1><?php echo $parent_page_title; ?></h1>
    </div>
</div>

<div class="page_wrapper">
    <div class="page_content">
        <div class="menu_sidebar">
            <?php //include_once 'left-sidebar.php'; ?>
			<div class="sidebar_content">
				<?php echo do_shortcode('[custom_menu_wizard menu="header menu" children_of="practitioner resources" include="root" menu_class="menu-widget-left" wrap_link="div class=li-cont"]'); ?>
			</div>
			<script type="text/javascript">
				$('.menu-widget-left li').each(function(){
					if($(this).find('a').html() == 'Area Resources')
						$(this).addClass('current-menu-item');
				});
			</script>
        </div>
        <div class="page_main_content main_template">
            <h3 class="page_title"><?php echo $cat->name; ?></h3>
            <?php echo $categoryDescription; ?>
            <div class="areas">
                <?php foreach ($categoryPosts as $post): ?>
                    <?php $postDesc = get_post_meta($post->ID, '_area_resource_post_description', true); ?>
                    <div class="area_info">
                        <div class="name"><a href="<?php echo $post->guid ?>"><?php echo $post->post_title ?></a></div>
                        <div class="description"><?php echo $postDesc; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>   
        <div class="right_sidebar">
            <?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();






<?php
get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID, '_page_right_sidebar');
$right_sidebar_id = current($meta);
$cat = get_the_category();




$args = array(
    'posts_per_page' => 10,
    'offset' => 0,
    'category' => 5,
    'orderby' => 'post_date',
    'order' => 'DESC',
    'post_type' => 'post',
    'post_mime_type' => '',
    'post_parent' => '',
    'post_status' => 'publish',
);

$posts = get_posts($args);


//print_r($posts);

?>
<div class="headline">
    <div class="headline_wrapper">
        <h1><?php echo $cat[0]->name ?></h1>
    </div>
</div>

<div class="page_wrapper">
    <div class="page_content">
        <div class="menu_sidebar">
            <div class="sidebar_content">
				<li class="widget widget_custom_menu_wizard">
					<div class="menu-header-menu-container">
						<ul id="menu-header-menu" class="menu-widget-left">
							<?php

							$cats = get_categories();

							foreach($cats as $cat1):

								$current = ($cat[0]->cat_ID == $cat1->cat_ID) ? 'current-menu-item' : '';

								if( $cat1->slug != 'uncategorized'): ?>
									<li class="menu-item <?php echo $current ?>">
										<div class="li-cont">
											<a href="<?php echo get_category_link( $cat1->cat_ID ) ?>"><?php echo $cat1->name ?></a>
										</div>
									</li>
							<?php
								endif;
							endforeach; ?>
						</ul>
					</div>
				</li>
			</div>
        </div>
        <div class="page_main_content main_template posts_list">
            <?php foreach ($posts as $single): ?>
            <h3 class="page_title"><?php echo get_the_title($single->ID) ?></h3>
            <div class="single_post_content">
                <?php echo $single->post_excerpt; ?>
            <a class='box_link' href='<?php echo $single->guid; ?>'>Read More</a>
            <div class='clear'></div>
            </div>

            <?php endforeach; ?>
        </div>
        <div class="right_sidebar">
            <?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();

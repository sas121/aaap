<?php

get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID,'_page_right_sidebar');
$right_sidebar_id =  current($meta);
$cat = get_the_category();



query_posts($query_string . '&cat=' . $cat[0]->cat_ID);

 

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
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post();  ?>
        <div class="page_main_content main_template">
            <h3 class="page_title"><?php the_title(); ?></h3>
            <div class="single_post_content"><?php  the_content(); ?></div>
            <?php 
            if($cat[0]->slug == 'news'):
                $archive_link = get_category_link($cat[0]->cat_ID);
            ?>
            <div class='single_navigation'>
            <?php previous_post_link( '%link', 'Previous', true ); ?>
                <a href='<?php echo $archive_link ?>'>View All <?php echo ucfirst($cat[0]->name) ?></a>
            <?php next_post_link( '%link', 'Next', true ); ?>
            <div class='clear'></div>
            </div>
            <?php endif; ?>
            </div>   
       <?php
        endwhile;
        endif;
        ?>
          
       
        <div class="right_sidebar">
            <?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();

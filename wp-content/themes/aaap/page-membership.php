<?php
/**
 * Template Name: Membership Page
 */
get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID, '_page_right_sidebar');
$right_sidebar_id = current($meta);
$content = apply_filters('the_content', $page->post_content);
$image = get_the_post_thumbnail($page->ID, 'full');

//Content of the left box
$leftContent = get_post_meta($post->ID, '_membership_left_box', true);
$leftContentValue = unserialize($leftContent);

//Content of the middle box
$middleContent = get_post_meta($post->ID, '_membership_middle_box', true);
$middleContentValue = unserialize($middleContent);

//Content of the right box
$rightContent = get_post_meta($post->ID, '_membership_right_box', true);
$rightContentValue = unserialize($rightContent);


//Bottom content of the page. HTML from wp editor
$middleContent = apply_filters('the_content',get_post_meta($page->ID, '_membership_middle_content', true));
?>
<div class="headline">
    <div class="headline_wrapper">
            <h1><?php echo $parent_page_title; ?></h1>
    </div>
</div>

<div class="page_wrapper membership">
    <div class="page_content">
        <div class="page_main_content main_template">
            <div class="page_top">
                <?php echo $image; ?>
                <div class="sub_image">
                    <?php echo $content; ?>
                </div>
            </div>
            <div class="middle_content">
                <img class="membership_image" src="<?php echo get_stylesheet_directory_uri() ?>/images/membership.png" alt="membership" />
                <div class="membership_content">
                    <?php echo $middleContent; ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="three_box_holder">
                <?php if( $rightContentValue['box_title'] ): ?>
                <div class="boxes">
	            <?php else: ?>
	            <div class="boxes two-box">
		        <?php endif; ?>
                    <div class="content_wrapper">
                        <div class="box_content">
                            <h2><?php echo $leftContentValue['box_title'] ?></h2>
                            <div><?php echo $leftContentValue['box_text'] ?></div>
                            <div class="price_holder">
                                <h3><?php echo $leftContentValue['price'] ?></h3>
                                 <?php echo $leftContentValue['price_text'] ?>
                            </div>
                        </div>
                        <div class="dotted_bottom_line"></div>
                        <div><a class="box_link" href="<?php echo $leftContentValue['box_link'] ?>"><?php echo $leftContentValue['box_link_text'] ?></a></div>
                    </div>
                    <a href="<?php echo $leftContentValue['box_bottom_link'] ?>" class="blue_button"><?php echo $leftContentValue['box_bottom_link_text'] ?><span>&raquo;</span></a>
                </div>
                <?php if( $rightContentValue['box_title'] ): ?>
                <div class="boxes">
	            <?php else: ?>
	            <div class="boxes two-box">
		        <?php endif; ?>
                    <div class="content_wrapper">
                        <div class="box_content">
                            <h2><?php echo $middleContentValue['box_title'] ?></h2>
                            <div><?php echo $middleContentValue['box_text'] ?></div>
                            <div class="price_holder">
                                <h3><?php echo $middleContentValue['price'] ?></h3>
                                 <?php echo $middleContentValue['price_text'] ?>
                            </div>
                        </div>
                        <div class="dotted_bottom_line"></div>
                        <div><a class="box_link" href="<?php echo $middleContentValue['box_link'] ?>"><?php echo $middleContentValue['box_link_text'] ?></a></div>
                    </div>
                    <a href="<?php echo $middleContentValue['box_bottom_link'] ?>" class="blue_button greenbuttons"><?php echo $middleContentValue['box_bottom_link_text'] ?><span>&raquo;</span></a>
                </div>
                <?php if( $rightContentValue['box_title'] ): ?>
                <div class="boxes last">
                    <div class="content_wrapper">
                        <div class="box_content">
                            <h2><?php echo $rightContentValue['box_title'] ?></h2>
                            <div><?php echo $rightContentValue['box_text'] ?></div>
                            <div class="price_holder">
                                <h3><?php echo $rightContentValue['price'] ?></h3>
                                <?php echo $rightContentValue['price_text'] ?>
                            </div>
                        </div>
                        <div class="dotted_bottom_line"></div>
                        <div><a class="box_link" href="<?php echo $middleContentValue['box_link'] ?>"><?php echo $middleContentValue['box_link_text'] ?></a></div>
                    </div>
                    <a href="<?php echo $middleContentValue['box_bottom_link'] ?>" class="blue_button greenbuttons"><?php echo $middleContentValue['box_bottom_link_text'] ?><span>&raquo;</span></a>
                </div>
                <?php endif; ?>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
            
        </div>

    </div>
</div>
<?php
get_footer();


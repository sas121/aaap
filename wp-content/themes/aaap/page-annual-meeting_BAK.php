<?php
/**
 * Template Name: Annual Meeting
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
$leftContent = get_post_meta($post->ID, '_annucment_left_box', true);
$leftContentValue = unserialize($leftContent);

//Content of the middle box
$middleContent = get_post_meta($post->ID, '_annucment_middle_box', true);
$middleContentValue = unserialize($middleContent);

//Content of the right box
$rightContent = get_post_meta($post->ID, '_annucment_right_box', true);
$rightContentValue = unserialize($rightContent);


//Bottom content of the page. HTML from wp editor
$bottomContent = get_post_meta($page->ID, '_annoucment_bottom_content', true);
?>

<div class="headline">
    <div class="headline_wrapper">
        <h1><?php echo $cat[0]->name ?></h1>
    </div>
</div>

<div class="page_wrapper annual_meeting double-sidebar">
    <div class="page_content">
        <div class="menu_sidebar">
            <?php include_once 'left-sidebar.php'; ?>
        </div>
        <div class="page_main_content main_template">
            <div class="page_top">
                <?php echo $image; ?>
                <div class="sub_image">
                    <?php echo $content; ?>
                </div>
            </div>
            <div class="three_box_holder">
                <div class="boxes">
                    <div class="content_wrapper">
                        <div class="box_content">
                            <h2><?php echo $leftContentValue['box_title'] ?></h2>
                            <div><?php echo $leftContentValue['box_text'] ?></div> 
                        </div>
                        <div class="dotted_bottom_line"></div>
                        <div><a class="box_link" href="<?php echo $leftContentValue['box_link'] ?>"><?php echo $leftContentValue['box_link_text'] ?></a></div>
                    </div>
                    <a href="<?php echo $leftContentValue['box_bottom_link'] ?>" class="blue_button"><?php echo $leftContentValue['box_bottom_link_text'] ?><span>&raquo;</span></a>
                </div>
                <div class="boxes">
                    <div class="content_wrapper">
                        <div class="box_content">
                            <h2><?php echo $middleContentValue['box_title'] ?></h2>
                            <div><?php echo $middleContentValue['box_text'] ?></div> 
                        </div>
                        <div class="dotted_bottom_line"></div>
                        <div><a class="box_link" href="<?php echo $middleContentValue['box_link'] ?>"><?php echo $middleContentValue['box_link_text'] ?></a></div>
                    </div>
                    <a href="<?php echo $middleContentValue['box_bottom_link'] ?>" class="blue_button"><?php echo $middleContentValue['box_bottom_link_text'] ?><span>&raquo;</span></a>
                </div>
                <div class="boxes last">
                    <div class="content_wrapper">
                        <div class="box_content">
                            <h2><?php echo $rightContentValue['box_title'] ?></h2>
                            <div><?php echo $rightContentValue['box_text'] ?></div> 
                        </div>
                        <div class="dotted_bottom_line"></div>
                    <div><a class="box_link" href="<?php echo $rightContentValue['box_link'] ?>"><?php echo $rightContentValue['box_link_text'] ?></a></div>
                    </div>
                    <a href="<?php echo $rightContentValue['box_bottom_link'] ?>" class="blue_button"><?php echo $rightContentValue['box_bottom_link_text'] ?><span>&raquo;</span></a>
                </div>
                <div class="clear"></div>
            </div>
			
            <div class="clear"></div>
            <div class="bottom_boxes">
                <?php echo $bottomContent; ?>
                <div class="clear"></div>
            </div>
			
			<script type="text/javascript">
			 //FIX eeducation-training bottom boxes
			 var max_height = 0;
			 var title_height = 0;
			 // find highest element
			 $('.bottom_boxes .small_boxes').each(function(){
				var cur_height = $(this).find('.small_boxes_text').height();
				if( cur_height > max_height ){
					max_height = cur_height;
					title_height = $(this).find('h3').height();
				}
			 });
			 
			 $('.bottom_boxes .small_boxes').each(function(){
				var title = $(this).find('h3').height();
				var box = $(this).find('.small_boxes_text').height();
				
				if( title == title_height ){
					$(this).find('.small_boxes_text').css('height', max_height);
				}else{
					$(this).find('.small_boxes_text').css('height', title_height-title + max_height);
				}
			 });
			</script>
        </div>
        
        <div class="right_sidebar">
            <?php include_once 'custom-sidebar.php'; ?>
        </div>

    </div>
</div>
<?php
get_footer();





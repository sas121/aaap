<?php
/*
  Template Name: Home Page v2
 */

get_header();

$leftBox = get_meta_values('_my_left_box');
$middleBox = get_meta_values('_my_middle_box');
$rightBox = get_meta_values('_my_right_box');
$contentLinks = get_meta_values('_home_content_links');
global $post;

$page = get_page($post->ID);

//putRevSlider( "home_page" );
//var_dump($middleBox);
//print_r($page);


$image_src = get_image_name($middleBox['image_url'], '244x155');

?>


<div class="slider_wrapper">
	<div style="clear:both"></div>
	<?php
	echo do_shortcode('[royalslider id="2"]');
	?>
</div>
<div class="three_box_wrapper">
	<div class="projects_box_holder">
		<?php dynamic_sidebar('homepage-widget'); ?>
	</div>
	<div class="projects_box_holder">
		<div class="projects_box_double">
			<div class="projects_box_content">
                <h2>Two Projects. One Mission.<br />Helping to end the opioid overdose epidemic.</h2>
                <a class="request-button" href="http://pcssprojects.org" target="_blank">Go to pcssprojects.org</a>
            </div>
		</div>
		<div class="projects_box_single">
            <a href="http://pcssmat.org" target="_blank"><img src="http://www.aaap.org/wp-content/uploads/2016/03/AAAP-PCSSMAT-Logo-OnWhite-RGB.png" /></a>
            <p><a href="http://pcssmat.org" target="_blank">Learn More ></a></p>
            <a href="http://pcss-o.org" target="_blank"><img src="http://www.aaap.org/wp-content/uploads/2016/03/PCSSO-Logo-OnWhite-RGB.png" /></a>
            <p><a href="http://pcss-o.org" target="_blank">Learn More ></a></p>
            <p>Our mission is to promote high quality evidence-based screening, assessment and treatment for substance use and co-occurring mental disorders.</p>
        </div>
	</div>
    <div class="three_box_holder">
        <div class="boxes">
            <div class="box_content">
                <h2><?php echo $leftBox['box_title']; ?></h2>
                <div><?php echo $leftBox['box_text']; ?></div>
            </div>
            <div class="dotted_bottom_line"></div>
            <div><a class="box_link" href="<?php echo $leftBox['box_link'] ?>">Join us today</a></div>
        </div>
        <div class="boxes">
            <div class="box_content">
                <h2><?php echo $middleBox['box_title']; ?></h2>
                <img src="<?php echo $image_src ?>" alt="" />
                <div class="places"><?php echo $middleBox['place']; ?></div>
                <div class="date"><?php echo $middleBox['date']; ?></div>
            </div>
            <div class="dotted_bottom_line"></div>
             <div><a class="box_link" href="<?php echo $middleBox['link'] ?>">Learn More</a></div>
        </div>
        <div class="boxes">
            <div class="box_content">
                <h2><?php echo $rightBox['box_title']; ?></h2>
                <div><?php echo $rightBox['box_text']; ?></div>
            </div>
            <div class="dotted_bottom_line"></div>
            <div><a class="box_link" href="<?php echo $rightBox['box_link'] ?>">Learn More</a></div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<div class="home_content_wrapper">
    <div class="home_content">
        <div class="image_holder"><?php echo get_the_post_thumbnail($post->ID, 'full'); ?></div>
        <div class="content_hodler">
            <div class="content"><?php echo $page->post_content; ?></div>
            <div class="content_links">
                <div><a class="box_link" href="<?php echo $contentLinks['subscribe'] ?>">Learn More</a></div>
                <div><a class="box_link" href="<?php echo $contentLinks['join'] ?>">Join AAAP Today</a></div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php get_footer(); ?>
<?php
/**
 * Template Name: Area Resource Template
 */
get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID, '_page_right_sidebar');
$right_sidebar_id = current($meta);
$content = apply_filters('the_content', $page->post_content);
$areas = get_categories(array(
    'type' => 'post',
    'hide_empty' => 0,
    'hierarchical' => 1,
    'taxonomy' => 'area_resource_category',
    'pad_counts' => false
));

//$my_wp_query = new WP_Query();
//$all_wp_pages = $my_wp_query->query(array('post_type' => 'page'));
//$c = get_page_children($page->ID,$all_wp_pages);

uasort($areas, compare);

function compare($a, $b){
	$a = convert_roman_to_int( trim(str_replace('Area ', '', $a->name) ));
	$b = convert_roman_to_int( trim(str_replace('Area ', '', $b->name) ));
	if ($a == $b)
		return 0;
	
	return ($a < $b) ? -1 : 1;
}


function convert_roman_to_int($roman) {
	
	$romans = array(
		'M' => 1000, 	'CM' => 900, 	'D' => 500, 	'CD' => 400, 	'C' => 100,
		'XC' => 90,		'L' => 50,		'XL' => 40,		'X' => 10,		'IX' => 9,
		'V' => 5,		'IV' => 4,		'I' => 1
	);
	
	$result = 0;
	
	foreach ($romans as $key => $value) {
		while (strpos($roman, $key) === 0) {
			$result += $value;
			$roman = substr($roman, strlen($key));
		}
	}
	return $result;
}


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
            <h3 class="page_title"><?php echo get_the_title($post->ID) ?></h3>
            <p class="page_text"><?php echo $content; ?></p>
            <div class="areas">
                <?php foreach ($areas as $area): ?>
                <div class="area_info">
					<a name="<?php echo $area->term_id ?>"></a>
                    <div class="name"><?php echo $area->name ?></div>
                    <div class="description"><?php echo $area->description ?></div>
                    <div><a class="box_link" href="<?php echo get_permalink($areaDirectorPageID) . $area->slug ?>">Contact your Area Director</a></div>
                    <?php if($area->category_count != 0): ?>
                    <div><a class="box_link announcment" href="<?php echo get_permalink($pageID) . $area->slug ?>">Area Announcements</a></div>
                    <?php endif; ?>
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


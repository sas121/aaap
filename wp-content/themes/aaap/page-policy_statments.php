<?php
/**
 * Template Name: Policy Statements
 */
get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID, '_page_right_sidebar');
$right_sidebar_id = current($meta);
$content = apply_filters('the_content', $page->post_content);

$pageposts = get_posts(array(
    'post_type' => array('policy'),
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => "DESC"
));

/*
FOR REORDER IN ADMIN
    'orderby' => 'meta_value_num',
    'meta_key' => 'home_index',
*/




//$querystr = "
//    SELECT DISTINCT($wpdb->posts.post_title),$wpdb->posts.post_content,$wpdb->posts.ID,$wpdb->posts.post_excerpt,$wpdb->posts.post_date,$wpdb->terms.name,$wpdb->posts.guid
//    FROM $wpdb->posts, $wpdb->postmeta,$wpdb->term_relationships,$wpdb->terms
//    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
//    AND  $wpdb->posts.ID = $wpdb->term_relationships.object_id
//    AND  $wpdb->term_relationships.term_taxonomy_id = $wpdb->terms.term_id   
//    AND  (($wpdb->posts.post_type='policy' AND $wpdb->postmeta.meta_key = 'home_index') OR ($wpdb->posts.post_type = 'post' AND $wpdb->terms.name='News')) 
//    AND $wpdb->posts.post_status = 'publish' 
//    AND $wpdb->posts.post_date < NOW()
//    ORDER BY $wpdb->postmeta.meta_value ASC,$wpdb->posts.post_date DESC
// ";
//
//$pageposts = $wpdb->get_results($querystr, OBJECT);
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
        <div class="page_main_content policy">
            <h3 class="page_title"><?php echo get_the_title($post->ID) ?></h3>
            <p class="page_text"><?php echo $page->post_content ?></p>
            <?php foreach ($pageposts as $pagepost): ?>
				<a name="<?php echo $pagepost->ID ?>"></a>
                <?php
				
                $linkType = get_post_meta($pagepost->ID, '_policy_link_type', true);
                $linkUrl = get_post_meta($pagepost->ID, '_policy_link_url', true);
                $pdfURL = get_post_meta($pagepost->ID, '_policy_pdf_url', true);
                if ($pagepost->name == 'News') {
                    $imageData = wp_get_attachment_image_src(get_post_thumbnail_id($pagepost->ID), 'medium');
                    $image = $imageData['0'];
                    $description = $pagepost->post_excerpt;
                    $link = $pagepost->guid;
                    $target = '';
                } else {
                    $image = get_post_meta($pagepost->ID, '_policy_link_image', true);
//                    $description = get_post_meta($pagepost->ID, '_policy_description', true);
                    $description = $pagepost->post_excerpt;
                    if ($linkType == 'link') {
                        $link = $linkUrl;
                         $target = 'target="_blank"';
                    } else if ($linkType == 'pdf') {
                        $link = $pdfURL;
                         $target = 'target="_blank"';
                    } else {
                        $link = $pagepost->guid;
                        $target = '';
                    }
                  
                }
                $date = date('F j, Y', strtotime($pagepost->post_date));
                ?>
                <div class="people_info">
                    <?php if ($image): ?>
                        <img src="<?php echo $image ?>" />
                    <?php endif; ?>
                    <div class="info">
                        <h3><?php echo $pagepost->post_title; ?></h3>
                        <div class="title date-no-uppercase"><?php echo $date; ?></div>
                        <div class="description">
                            <?php echo $description; ?>
							<?php if( ! empty($linkType) ): ?>
                            <a <?php echo $target; ?> href="<?php echo $link; ?>" class="box_link">View Full Policy</a>
							<?php endif; ?>
                        </div>
                    </div>
                    <div class="clear"></div>

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

<?php
/*
  Template Name: Staff template
 */

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$category = get_term_by('slug', 'staff', 'people_jobs');


$custom_posts = get_people_pages_by_category('staff');



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
            <?php include_once 'left-sidebar.php'; ?>
        </div>
        <div class="page_main_content">
            <h3><?php echo get_the_title($post->ID) ?></h3>
            <p class="page_text"><?php echo $page->post_content; ?></p>
<!--            <select class="styled">
                <option value="">Sort By</option>
                <option value="">State</option>
                <option value="">Alphabetical</option>
                <option value="">ABPN Addiction psychiatry certification</option>
            </select>-->

            <?php foreach ($custom_posts as $custom_post): ?>
                <?php $value = get_post_meta($custom_post->ID, '_people_custom_post_type', true);
                $fields = unserialize($value); 
                //print_r($fields);
                ?>
                <div class="people_info">
                    <?php if ($fields['image_url']): ?>
                        <img src="<?php echo $fields['image_url']; ?>" />
                        <?php endif; ?>
                    <div class="info">
                        <h3><?php echo $custom_post->post_title; ?></h3>
                        <?php if($fields['title']): ?>
                        <div class="title"><?php echo $fields['title']; ?></div>
                        <?php endif; ?>
                        <?php if($fields['permalink']): ?>
                        <div class="permalink"><a target="_blank" href="<?php echo $fields['permalink']; ?>"><?php echo str_ireplace(array('http://','www.'), '', $fields['permalink']); ?></a></div>
                        <?php endif; ?>
                        <?php if($fields['email']): ?>
                        <div class="permalink"><a href="mailto:<?php echo $fields['email']; ?>"><?php echo $fields['email']; ?></a></div>
                        <?php endif; ?>
                        <?php if($fields['institution']): ?>
                        <div class="permalink"><?php echo  $fields['institution']; ?></div>
                        <?php endif; ?>
                        <?php if($fields['journal_section']): ?>
                        <div class="permalink"><?php echo  $fields['journal_section']; ?></div>
                        <?php endif; ?>
                        <?php if($fields['subcategory']): ?>
                        <div class="permalink"><?php echo  $fields['subcategory']; ?></div>
                        <?php endif; ?>
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
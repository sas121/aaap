<?php
/*
  Template Name: Find a specialist
 * 
 * 
 */
//error_reporting(E_ALL);
$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);

$page = get_page($post->ID);

$filter = create_location_filter_list();

$custom_posts = get_custom_post_type_posts('members_referrals');

$tmp = array();
foreach ($custom_posts as &$sortColumn) {
    $titleArray = explode(' ', $sortColumn->post_title);
    $tmp[] = $titleArray[1]; 
}
array_multisort($tmp, $custom_posts);




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
            <?php //include('left-sidebar.php') ?>
            <div class="sidebar_content">
                <?php echo do_shortcode('[custom_menu_wizard menu="header menu" children_of="patient resources" include="root" menu_class="menu-widget-left" wrap_link="div class=li-cont"]'); ?>
            </div>
            <script type="text/javascript">
                $('.menu-widget-left li').each(function() {
                    if ($(this).find('a').html() == 'Find a specialist')
                        $(this).addClass('current-menu-item');
                });
            </script>
        </div>
        <div class="page_main_content find-a-specialist">
            <h3><?php echo get_the_title($post->ID) ?></h3>
            <p class="page_text"><?php echo $page->post_content; ?></p>
            <form method="post" id="filter_form">
                <select id="filter_member">
                    <option value="">Location</option>
                    <optgroup label="USA">
                        <?php foreach ($filter['usa_states'] as $state): ?>
                            <option value="<?php echo $state->full_state; ?>"><?php echo $state->full_state; ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Europe">
                        <?php foreach ($filter['europe'] as $country): ?>
                            <option value="<?php echo $country->country ?>"><?php echo $country->country; ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Argentina">
                        <option value="argentina">Argentina</option>
                    </optgroup>
                    <optgroup label="Canada">
                        <option value="canada">Canada</option>
                    </optgroup>
                    <optgroup label="Costa Rica">
                        <option value="costa rica">Costa Rica</option>
                    </optgroup>
                    <optgroup label="India">
                        <option value="India">India</option>
                    </optgroup>
                    <optgroup label="Puerto Rico">
                        <option value="puerto rico">Puerto Rico</option>
                    </optgroup>
                    <optgroup label="Venezuela">
                        <option value="venezuela">Venezuela</option>
                    </optgroup>
                </select>
            </form>

            <?php foreach ($custom_posts as $custom_post): ?>
                <?php
                $value = get_post_meta($custom_post->ID, '_members_referrals_custom_post_type', true);
                $fields = unserialize($value);
                //print_r($fields);
                ?>
                <div class="people_info <?php echo strtolower(str_ireplace(' ', '_', trim($fields['locations']))) ?>">
                    <a name="<?php echo $custom_post->ID ?>"></a>
                    <?php if ($fields['image_url']): ?>
                        <img src="<?php echo get_image_name($fields['image_url'], '174x173'); ?>" />
                    <?php endif; ?>
                    <div class="info">
                        <h3><?php echo $custom_post->post_title; ?></h3>
                        <?php if ($fields['practice_name']): ?>
                            <div class="title"><?php echo $fields['practice_name']; ?></div>
                        <?php endif; ?>

                        <div class="specialist_list_left">    
                            <?php if ($fields['address1']): ?>
                                <div style="float: left"><?php echo $fields['address1']; ?></div>
                            <?php endif; ?>
                            <?php if ($fields['address2']): ?>
                                <div style="float: left"><?php echo $fields['address2']; ?></div>
                            <?php endif; ?>
                            <div class="clear"></div>
                            <?php if ($fields['city']): ?>
                                <div><?php echo $fields['city']; ?></div>
                            <?php endif; ?>
                            <?php if ($fields['locations']): ?>
                                <div><?php echo $fields['locations']; ?></div>
                            <?php endif; ?>
                            <?php if ($fields['zip']): ?>
                                <div><?php echo $fields['zip']; ?></div>
                            <?php endif; ?>
                            <?php if ($fields['phone']): ?>
                                <div><?php echo $fields['phone']; ?></div>
                            <?php endif; ?>
                            <?php if ($fields['fax']): ?>
                                <div><?php echo $fields['fax']; ?></div>
                            <?php endif; ?>
                            <?php if ($fields['email']): ?>
                                <div class="permalink"><a href="mailto:<?php echo $fields['email']; ?>">Email</a></div>
                            <?php endif; ?>
                            <?php if ($fields['abpn']): ?>
                                <div>ABPN Board Certified in Addiction Psychiatry</div>
                            <?php endif; ?>   
                        </div>
                        <div class="specialist_list_right">    

                            <?php //if ($fields['permalink']): ?>
            <!--                                <div class="permalink"><a target="_blank" href="<?php // echo $fields['permalink'];    ?>"><?php //echo str_ireplace(array('http://', 'www.'), '', $fields['permalink']);    ?></a></div>-->
                            <?php // endif; ?>
                            <?php // if ($fields['practice_name']): ?>
            <!--                                <div class="permalink"><?php // echo $fields['practice_name'];    ?></div>-->
                            <?php // endif; ?>
                            <?php if ($fields['practice_description']): ?>
                                <div class="practice_description">
                                    <div>
                                        <span>Description</span>
                                        <p><?php echo $fields['practice_description']; ?></p>
                                    </div>
                                    <div>
                                        <span>Insurance Accepted</span>
                                        <p><?php echo $fields['insurance_types']; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <div class="clear"></div>


                        </div>
                        <div class="clear"></div>
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

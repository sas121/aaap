<?php
/**
 * Template Name: Patient Referral Program
 */

get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID, '_page_right_sidebar');
$right_sidebar_id = current($meta);
$content = apply_filters('the_content', $page->post_content);
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
        <div class="page_main_content main_template">
            <h3 class="page_title"><?php echo get_the_title($post->ID) ?></h3>
            <div><?php echo $content; ?></div>
            <form id="disclaimer" action="" id="new_post" name="new_post" method="post">
				<input type="hidden" name="specialistID" value="<?php echo (isset($_GET['sid'])) ? $_GET['sid'] : ''; ?>"/>
                <label>       
                    <input class="required" type="radio" name="agree" value="1" id="agree" value="">
                    <span></span>
                </label>

                <span>By checking this box I attest that I have read and understand the Disclaimer of Warranties and Liabilities statement above and agree to all the terms therein.</span>

                <p>
                    <br />
                    <select name="year" id="year" class="disclaimer">
                        <option value="">Year</option>
                        <?php
                        $startdate = 1960;
                        $enddate = date("Y");
                        $years = range($enddate, $startdate);
                        foreach ($years as $year):
                            ?>
                            <option><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="month" id="month" class="disclaimer">
                        <option value="">Month</option>
                        <?php
                        for ($month = 1; $month <= 12; $month++):
                            ?>
                            <option><?php echo $month; ?></option>
                        <?php endfor; ?>
                    </select>

                    <select name="day" id="day" class="disclaimer">
                        <option value="">Day</option>
                        <?php
                        for ($day = 1; $day <= 31; $day++):
                            ?>
                            <option><?php echo $day; ?></option>
                        <?php endfor; ?>
                    </select>
                </p>
                <p class="error_placment">
                   
                </p>
                
                <button name="declimer_confirm" type="submit" class="box_link">Submit</button>
            </form>
        </div>   
        <div class="right_sidebar">
<?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();

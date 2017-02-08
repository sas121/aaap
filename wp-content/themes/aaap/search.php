<?php
global $query_string;

$query_args = explode("&", $query_string);
$search_query = array();

foreach($query_args as $key => $string) {
	$query_split = explode("=", $string);
	$search_query[$query_split[0]] = urldecode($query_split[1]);
} // foreach

$search = new WP_Query($search_query);

$search_result = $search->posts;


get_header();
?>



<div class="headline">
    <div class="headline_wrapper">
            <h1>Search Page</h1>
    </div>
</div>
<div class="page_wrapper">
    <div class="page_content">

        <div class="menu_sidebar">
            <div class="sidebar_content">
				<li class="widget widget_custom_menu_wizard">
					<div class="menu-header-menu-container">
						<ul id="menu-header-menu" class="menu-widget-left">
							<li class="menu-item current-menu-item">
								<div class="li-cont">
									<a href="#">Search</a>
								</div>
							</li>
						</ul>
					</div>
				</li>
			</div>
        </div>
        <div class="page_main_content search_results">
            <?php if($search_result): ?>
            <h3 class="page_title">Search results for: <?php echo implode(' ', $search_query); ?></h3>
            <?php foreach ($search_result as $res): ?>
                <div class="people_info">
                    <div class="info">
					<?php //var_dump($res); ?>
                        <h3><a href="<?php echo get_post_link_in_search($res).'?sid='.$res->ID; ?>"><?php echo $res->post_title; ?></a></h3>
                        <div class="post_date"><?php echo date('F d, Y',strtotime($res->post_date)); ?></div>
                    </div>
                        <div class="clear"></div>
                </div>
                <?php endforeach; ?>
            
            <?php else: ?>       
            <h3>There is no results for: <?php echo implode(' ', $search_query); ?></h3>
            <div class="primary-navigation">
                <div class="search_wrapper"><?php echo get_search_form(); ?></div>
            </div>
            
            <?php endif; ?>

        </div>   
        <div class="right_sidebar">
            <?php include_once 'right-sidebar.php'; ?>
        </div>
        <div class="clear"></div>


    </div>
</div>




<?php
get_footer();



<?php
/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=980">
        <title><?php wp_title('|', true, 'right'); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
        <link href='http://fonts.googleapis.com/css?family=Arapey:400italic,400' rel='stylesheet' type='text/css'>
		<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" />
        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
        <![endif]-->
        <?php wp_head(); ?>
        <script>$ = jQuery.noConflict();</script>
    </head>

    <body <?php body_class(); ?>>
        <div id="page" class="hfeed site">
            <?php if (get_header_image()) : ?>
                <div id="site-header">
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                        <img src="<?php header_image(); ?>" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="">
                    </a>
                </div>
            <?php endif; ?>

            <header id="masthead" class="site-header" role="banner">




                <div class="header-main">
                    <div class="logo_wrapper">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><img src="<?php echo get_stylesheet_directory_uri() . '/images/logo.png' ?>" /></a>
                    </div>
                    <div class="header_right">
                        <div class="latest_news">
                            <h4>Latest news from aaap</h4>
                            <div class="news_holder">
                                <?php $latest_posts = latest_posts(5); ?>
                                <?php foreach ($latest_posts as $key=>$one_post): ?>
                                <div class="<?php echo $key==0 ? 'show_news' : 'hide_news' ?> news">
                                    <a href="<?php echo $one_post->guid ?>"><?php echo wp_trim_words($one_post->post_title,'5','') ?><span>&raquo;</span></a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="icon_menu_wrapper">
                            <div class="social_icons">
                                <a href="https://twitter.com/AAAP1985" target="_blank" class="twitter"></a>
                                <a href="https://www.facebook.com/AAAP1985" target="_blank" class="facebook"></a>
                                <?php /*<a href="http://www.aaap.org/rss.xml" class="rss"></a>*/ ?>
                            </div>
                            <div class="small_menu">
                                <?php echo wp_nav_menu(array('theme_location' => 'top_menu', 'menu_class' => 'small-top-menu')); ?>
                            </div>
                            <div class="clear"></div>
							<div class="top-highlight-menu">
	                            <?php echo wp_nav_menu(array('theme_location' => 'highlight_menu', 'menu_class' => 'small-top-menu')); ?>
                            </div>
                        </div>

                        <div class="clear"></div>

                    </div>
                    <div class="clear"></div>
                    <nav id="primary-navigation" class="site-navigation primary-navigation" role="navigation">
                        
                        <a class="screen-reader-text skip-link" href="#content"><?php _e('Skip to content', 'twentyfourteen'); ?></a>
                        <?php //wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'nav-menu')); ?>
                        <div class="menu-header-menu-container"><?php echo create_menu_html() ?></div>
                        <div class="search_wrapper"><?php echo get_search_form(); ?></div>
                        <div class="clear"></div>
                    </nav>
                </div>
                <?php /*<div class="menu_shadow"></div>*/ ?>
                

                <div id="search-container" class="search-box-wrapper hide">
                    <div class="search-box">
                        <?php get_search_form(); ?>
                    </div>
                </div>
            </header><!-- #masthead -->

            <div id="main" class="site-main">
                



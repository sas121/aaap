<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?>

</div><!-- #main -->


</div><!-- #page -->

<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="footer-wrapper">

        <?php get_sidebar('footer'); ?>

        <div class="site-info">
        	<p class="addFooter">400 Massasoit Avenue <span class="divider">/</span> Suite 307 <span class="divider">/</span> East Providence, RI 02914 <span class="divider">/</span> (401) 524-3076 <span class="divider">/</span> <a class="contactLink" href="/contact/">Contact Us ›</a></p>
            <hr />
            <p class="disclaimer">This content on this site is intended solely to inform and educate medical professionals. This site shall not be used for medical advice and is not a substitute for the advice or treatment of a qualified medical professional.</p>
            <p class="copyright">©<?php echo date("Y"); ?> American Academy of Addiction Psychiatry. All Rights Reserved. AAAP and American Academy of Addiction Psychiatry are registered trademarks of the American Academy of Addiction Psychiatry ("AAAP"). All trademarks, service marks and logos displayed on this website are registered, unregistered and/or service trademarks of AAAP or other trademark owners, and may not be copied, reproduced, used or displayed.</p>
        </div><!-- .site-info -->

        <div class="footer_links">
        
        	<div class="social_icons">
               <a href="https://twitter.com/AAAP1985" target="_blank" class="twitter"></a>
               <a href="https://www.facebook.com/AAAP1985" target="_blank" class="facebook"></a>
               <?php /*<a href="http://www.aaap.org/rss.xml" class="rss"></a>*/ ?>
            </div>
            <?php echo wp_nav_menu(array('theme_location' => 'footer', 'menu_class' => 'footer-menu')); ?>
                            
            <div class="AAAP_Logo"><img src="/wp-content/themes/aaap/images/AAAP_PNG.png" alt="American Academy of Addiction Psychiatry" />
            <p class="logoText">Translating Science.<br />
            Transforming Lives.</p></div>
        </div>
        <div class="clear"></div>
    </div>
</footer><!-- #colophon -->

<?php
if (function_exists('yoast_analytics')) {

    yoast_analytics();
}
?>

<?php wp_footer(); ?>
</body>
</html> 

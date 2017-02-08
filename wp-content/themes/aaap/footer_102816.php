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
            <p class="disclaimer">This content on this site is intended solely to inform and educate medical professionals. This site shall not be used for medical advice and is not a substitute for the advice or treatment of a qualified medical professional.</p>
            <p>©2015 American Academy of Addiction Psychiatry. All Rights Reserved. AAAP and American Academy of Addiction Psychiatry are registered trademarks of the American Academy of Addiction Psychiatry ("AAAP"). All trademarks, service marks and logos displayed on this website are registered, unregistered and/or service trademarks of AAAP or other trademark owners, and may not be copied, reproduced, used or displayed.</p>
        </div><!-- .site-info -->

        <div class="footer_links">
            <?php echo wp_nav_menu(array('theme_location' => 'footer', 'menu_class' => 'footer-menu')); ?>
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

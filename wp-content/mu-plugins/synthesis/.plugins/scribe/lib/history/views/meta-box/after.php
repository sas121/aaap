<?php
global $post;
if( isset( $post->ID ) ) {
	$score = $this->getSeoScoreForPost( $post->ID );
	$primaryKeywords = $this->getSeoPrimaryKeywordsForPost($post->ID);
} else {
	$score = 0;
	$keywords = array();
}
?>
<input type="hidden" name="serialized-ecordia-results" value="<?php echo esc_attr( get_post_meta($pid, $this->_meta_seoInfo, true) ); ?>" />
<div>
	<div id="ecordia-review-score">
		<p><strong><?php esc_html_e( 'Content Score' , 'scribeseo'); ?></strong></p>
		<p><strong id="ecordia-review-score-number" class="<?php echo sanitize_html_class( $this->getSeoScoreClassForPost( $score ) ); ?>"><?php printf( esc_html__( '%1$d%%' , 'scribeseo'), $score ); ?></strong></p>
	</div>
	<div id="ecordia-review-keywords">
		<p><strong><?php esc_html_e( 'Primary Keywords' , 'scribeseo'); ?></strong></p>
		<?php if( empty( $primaryKeywords ) ) { ?>
		<p class="ecordia-error"><?php esc_html_e( 'No Primary Keywords Found.', 'scribeseo'); ?></p>
		<?php } else { ?>
		<ul style="margin-left: 6px;">
			<?php foreach( $primaryKeywords as $primary ) { ?>
			<li class="<?php echo sanitize_html_class( $this->getSeoScoreClassForPost( 100 ) ); ?>"><strong><?php echo esc_html( $primary ); ?></strong></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
	<br class="clear" />
</div>
<?php if ( $score ) { ?>
<div class="ecordia-analyze-action">
	<div class="alignleft">
		<p>
			<a href="<?php echo esc_url( admin_url( 'media-upload.php?tab=ecordia-score&type=ecordia-score&post=' . $post->ID . '&TB_iframe=true' ) ); ?>" id="ecordia-seo-analysis-review-button" class="button"><?php esc_html_e( 'Review' , 'scribeseo'); ?></a>
		</p>
	</div>
	<br class="clear" />
</div>
<?php }

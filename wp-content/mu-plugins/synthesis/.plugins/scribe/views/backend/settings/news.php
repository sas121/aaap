<div class="wrap">
	<?php screen_icon(); ?>
	<h2 id="scribe-seo-news-headline">
		<?php echo wp_kses( __('SEO News from the best - <a href="http://searchengineland.com">Search Engine Land</a> and <a href="http://www.searchenginejournal.com">Search Engine Journal</a>', 'scribeseo'), Scribe_SEO::formatting_allowedtags() ); ?>
	</h2>
	
	<?php foreach($feed->get_items() as $item) {
		$creators = $item->get_item_tags(SIMPLEPIE_NAMESPACE_DC_11, 'creator');
		$creator_string = is_array($creators) ? sprintf('<em>by</em> <span class="scribe-seo-news-item-creator">%s</span>', esc_html( $creators[0]['data'] ) ) : ''; 
		$content = $item->get_content();
		$content_formatted = wpautop(substr($content, 0, strpos($content, '<p>')));
		?>
	<div class="scribe-seo-news-item">
		<h3><a href="<?php echo esc_url( $item->get_link() ); ?>"><?php echo esc_html( $item->get_title() ); ?></a></h3>
		<p><?php printf('<span class="scribe-seo-news-item-date">%s</span> %s', $item->get_date(get_option('date_format')), $creator_string ); ?></p>
		<?php echo $content_formatted; ?>
	</div>
	<?php } ?>

</div>
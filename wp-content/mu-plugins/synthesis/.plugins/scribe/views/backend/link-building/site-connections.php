<div class="wrap scribe-metaboxes">
		<?php screen_icon(); ?>
		<h2>
			<?php _e( 'Scribe - Site Connections', 'scribeseo' ); ?>
		</h2>
			<div class="scribe-site-connections-tip-visible scribe-metabox">
			<p>A key part of online marketing is creating connections with others who are authoritative in their space. And with the Scribe Site Connection tool, we make it easy for you to find them.</p>
			<p>Simply enter a keyword below and we will not only find external sites and social media users for that term, we will also show you a score of how your site compares to other authoritative sites.</p>
			</div>

		<div class="metabox-holder">
			<?php settings_errors(); ?>
				
			<div id="main-sortables" class="meta-box-sortables">
				
				<?php do_meta_boxes( 'scribe-connections', 'normal', $social_activity ); ?>
				
			</div>
		</div>
		
</div>
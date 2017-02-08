<?php

/**
 *
 * Please see single-event.php in this directory for detailed instructions on how to use and modify these templates.
 *
 */

?>

<?php global $cfs; ?>

<script type="text/html" id="tribe_tmpl_tooltip">
	<div id="tribe-events-tooltip-[[=eventId]]" class="tribe-events-tooltip">
		<h4 class="entry-title summary"><a target="_blank" <a href="[[=permalink]]">[[=raw title]]</a></h4>

		<div class="tribe-events-event-body">
			<div class="duration">
				<abbr class="tribe-events-abbr updated published dtstart">[[=startTime]]</abbr>
				[[ if(endTime.length) { ]] - <abbr class="tribe-events-abbr dtend"> [[=endTime]] [[=timeZone]]</abbr>
				[[ } ]]
				
			</div>
			[[ if(imageTooltipSrc.length) { ]]
			<div class="tribe-events-event-thumb">
				<img src="[[=imageTooltipSrc]]" alt="[[=title]]" />
			</div>
			[[ } ]]
			[[ if(excerpt.length) { ]]
			<p class="moreDetails"><a target="_blank" <a href="[[=permalink]]"><?php esc_html_e( 'Find out more', 'the-events-calendar' ) ?> &raquo;</a></p>
			[[ } ]]
			<span class="tribe-events-arrow"></span>
		</div>
	</div>
</script>
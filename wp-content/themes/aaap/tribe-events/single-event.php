<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$event_id = get_the_ID();

$tribe_add_presenter = tribe_get_custom_field('Presenter'); 
$additional_values['presenter'] = $tribe_add_presenter;
	
$tribe_add_presenterdetails = tribe_get_custom_field('Presenter Details'); 
$additional_values['presenterDetails'] = $tribe_add_presenterdetails;

$tribe_add_presenter2 = tribe_get_custom_field('Presenter 2'); 
$additional_values['presenter2'] = $tribe_add_presenter2;
	
$tribe_add_presenter2details = tribe_get_custom_field('Presenter 2 Details'); 
$additional_values['presenter2Details'] = $tribe_add_presenter2details;

$tribe_add_timezone = tribe_get_custom_field('Time Zone'); 
$additional_values['timeZone'] = $tribe_add_timezone;

$tribe_add_registrationlink = tribe_get_custom_field('Registration Link'); 
$additional_values['registrationLink'] = $tribe_add_registrationlink;

$tribe_add_webinarslideslink = tribe_get_custom_field('Webinar Slides Link'); 
$additional_values['webinarSlidesLink'] = $tribe_add_webinarslideslink;

$tribe_add_withcmelink = tribe_get_custom_field('With CME Link'); 
$additional_values['withCmeLink'] = $tribe_add_withcmelink;

$tribe_add_withoutcmelink = tribe_get_custom_field('Without CME Link'); 
$additional_values['withoutCmeLink'] = $tribe_add_withoutcmelink;

$tribe_add_watchnow = tribe_get_custom_field('Watch Now'); 
$additional_values['watchNow'] = $tribe_add_watchnow;

?>

<div id="tribe-events-content" class="tribe-events-single vevent hentry">

	<p class="tribe-events-back">
		<a href="<?php echo tribe_get_events_link() ?>"> <?php _e( '&laquo; All Events', 'tribe-events-calendar' ) ?></a>
	</p>

	<!-- Notices -->
	<?php tribe_events_the_notices() ?>

	<?php the_title( '<h2 class="tribe-events-single-event-title summary entry-title">', '</h2>' ); ?>

	<div class="tribe-events-schedule updated published tribe-clearfix">
		<?php echo tribe_events_event_schedule_details( $event_id, '<h3>', '</h3>' ); ?>
		<h3><?php echo $tribe_add_timezone ?></h3>
		<?php if ( tribe_get_cost() ) : ?>
			<span class="tribe-events-divider">|</span>
			<span class="tribe-events-cost"><?php echo tribe_get_cost( null, true ) ?></span>
		<?php endif; ?>
	</div>

	<!-- Event header -->
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>
		<!-- Navigation -->
		<h3 class="tribe-events-visuallyhidden"><?php _e( 'Event Navigation', 'tribe-events-calendar' ) ?></h3>
		<ul class="tribe-events-sub-nav">
			<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '<span>&laquo;</span> %title%' ) ?></li>
			<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( '%title% <span>&raquo;</span>' ) ?></li>
		</ul>
		<!-- .tribe-events-sub-nav -->
	</div>
	<!-- #tribe-events-header -->

	<?php while ( have_posts() ) :  the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<!-- Event featured image, but exclude link -->
			<?php echo tribe_event_featured_image( $event_id, 'full', false ); ?>

			<!-- Event content -->
			<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
			<div class="tribe-events-single-event-description tribe-events-content entry-content description">
				<?php if (!empty($tribe_add_registrationlink)): ?>
					<div class="mk-button-align left">
						<a style="margin-bottom: 25px" href="<?php echo $tribe_add_registrationlink ?>" target="_blank"  class="mk-button dark light-color mk-shortcode three-dimension large ">Register Here</a>
					</div>
				<?php endif ?>
				
				<?php if (!empty($tribe_add_watchnow)): ?>
					<div class="mk-button-align left">
						<a style="margin-bottom: 25px" href="<?php echo $tribe_add_watchnow ?>" target="_blank"  class="mk-button dark light-color mk-shortcode three-dimension large ">Watch Now</a>
					</div>
				<?php endif ?>
				
				<?php if (!empty($tribe_add_webinarslideslink)): ?>
					<div class="mk-button-align left">
						<a style="margin-bottom: 25px" href="<?php echo $tribe_add_webinarslideslink ?>" target="_blank"  class="mk-button dark light-color mk-shortcode three-dimension large ">View Webinar Slides</a>
					</div>
				<?php endif ?>
				
				<?php if (!empty($tribe_add_withcmelink)): ?>
					<div class="mk-button-align left">
						<a style="margin-bottom: 25px" href="<?php echo $tribe_add_withcmelink ?>" target="_blank"  class="mk-button dark light-color mk-shortcode three-dimension large ">Training with CME Credit</a>
					</div>
				<?php endif ?>
				
				<?php if (!empty($tribe_add_withoutcmelink)): ?>
					<div class="mk-button-align left">
						<a style="margin-bottom: 25px" href="<?php echo $tribe_add_withoutcmelink ?>" target="_blank"  class="mk-button dark light-color mk-shortcode three-dimension large ">Training without CME Credit</a>
					</div>
				<?php endif ?>
				
				<?php if (!empty($tribe_add_presenter)): ?>
					<p><strong><?php echo $tribe_add_presenter ?></strong><?php endif ?><?php if (!empty($tribe_add_presenterdetails) && !empty($tribe_add_presenter)): ?> | <?php echo $tribe_add_presenterdetails ?></p>
				<?php endif ?>
				
				<?php if (!empty($tribe_add_presenter2)): ?>
					<p><strong><?php echo $tribe_add_presenter2 ?></strong><?php endif ?><?php if (!empty($tribe_add_presenter2details) && !empty($tribe_add_presenter2)): ?> | <?php echo $tribe_add_presenter2details ?></p>
				<?php endif ?>
				<?php the_content(); ?>
			</div>
			<!-- .tribe-events-single-event-description -->
			<?php do_action( 'tribe_events_single_event_after_the_content' ) ?>

			<!-- Event meta -->
			<?php do_action( 'tribe_events_single_event_before_the_meta' ) ?>
			<?php
			/**
			 * The tribe_events_single_event_meta() function has been deprecated and has been
			 * left in place only to help customers with existing meta factory customizations
			 * to transition: if you are one of those users, please review the new meta templates
			 * and make the switch!
			 */
			if ( ! apply_filters( 'tribe_events_single_event_meta_legacy_mode', false ) ) {
				tribe_get_template_part( 'modules/meta' );
			} else {
				echo tribe_events_single_event_meta();
			}
			?>
			<?php do_action( 'tribe_events_single_event_after_the_meta' ) ?>
		</div> <!-- #post-x -->
		<?php if ( get_post_type() == TribeEvents::POSTTYPE && tribe_get_option( 'showComments', false ) ) comments_template() ?>
	<?php endwhile; ?>

	<!-- Event footer -->
	<div id="tribe-events-footer">
		<!-- Navigation -->
		<!-- Navigation
-->		<h3 class="tribe-events-visuallyhidden"><?php _e( 'Event Navigation', 'tribe-events-calendar' ) ?></h3>
		<ul class="tribe-events-sub-nav">
			<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '<span>&laquo;</span> Previous' ) ?></li>
			<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( 'Next <span>&raquo;</span>' ) ?></li>
		</ul>
	</div>
	<!-- #tribe-events-footer -->

</div><!-- #tribe-events-content -->

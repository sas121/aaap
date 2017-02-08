<?php
get_header();
?>



<div class="headline">
    <div class="headline_wrapper">
            <h1>Mentoring Program Directory</h1>
    </div>
    </div>
    
<div class="page_wrapper">
    <div class="page_content">

<div class="mentoring-program-directory">
 <div class="directory-side-container">
            <h2 class="boldest">Get Support from Experts</h2>
            <p class="app-side-reg">The goal of the AAAP mentoring program is to provide ongoing individualized support for Addiction Psychiatrists.</p>
            <hr class="program-dir-hr">
                  <?php do_action('show_beautiful_filters'); ?>
             <hr class="program-dir-hr">
                   <a class="box_link" href="http://www.aaap.org/mentoring-details/">MORE DETAILS</a>
                   <a class="box_link" href="http://www.aaap.org/mentee-application/">APPLY NOW</a>
            </div>
        </div>


<div class="mentors">
<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>	
		<div class="mentor-listing">
			<div class="mentor">
<h2 class="mentor-name"><?php echo get_the_title(); ?></h2>

<?php
$specialty_terms = wp_get_object_terms( $post->ID,  'specialty' );
if ( ! empty( $specialty_terms ) ) {
	if ( ! is_wp_error( $specialty_terms ) ) {
			foreach( $specialty_terms as $term ) {
				echo '<span class="mentor-specialties">' . esc_html( $term->name ) . '</span>'; 
			}
			echo implode(',', $out);
	}
}
?>

<p class"mentor-specialties"><?php get_the_terms( $specialty ); ?></p>

<div class="mentor-left">
<?php if( get_field('profile_image') ): ?>
<img class="mentor-profpic" src="<?php the_field('profile_image'); ?>" />
<?php endif; ?>
</div>

<div class="mentor-info">
<p class="mentor-info-line-first"><strong>Fellowship:</strong> <?php the_field('fellowship'); ?></p>
<p class="mentor-info-line"><strong>Board Certification:</strong> <?php the_field('board_certification'); ?></p>
<p class="mentor-info-line"><strong>Academic Affiliation:</strong> <?php the_field('academic__organization_affiliations'); ?></p>
<p class="mentor-info-line"><strong>Current Clinical Position:</strong> <?php the_field('current_clinical_position'); ?></p>
<p class="mentor-info-line"><strong>Clinical/Research Interest:</strong> <?php the_field('clinical__research_interest'); ?></p>
<p class="mentor-info-line"><strong>Areas of Expertise:</strong> <?php the_field('areas_of_expertise'); ?></p>
</div>  
 
		</div>
		<a class="request-button" href="http://www.aaap.org/mentee-application/?mentor_name=<?php echo esc_attr( urlencode( get_the_title() ) ); ?>&type=new">Request <?php echo get_the_title(); ?> »</a>
		</div>
		
	<?php endwhile; ?>
	<!-- show pagination here -->
<?php else : ?>
	<!-- show 404 error here -->
<?php endif; ?>
</div>
</div>
</div>
</div>
<?php
get_footer();
?>
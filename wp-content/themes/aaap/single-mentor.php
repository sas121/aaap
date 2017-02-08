<?php

get_header();

$post;
$parent_page_title = empty($post->post_parent) ? get_the_title($post->ID) : get_the_title($post->post_parent);
$page = get_page($post->ID);
$meta = get_post_meta($page->ID,'_page_right_sidebar');
$right_sidebar_id =  current($meta);
$cat = get_the_category();

 

?>
<div class="headline">
    <div class="headline_wrapper">
    </div>
</div>
<div class="page_wrapper">
    <div class="page_content">
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
<div class="request-button">Request Mentor »</div>
</div>

<div class="mentor-info">
<p class="mentor-info-line"><strong>Fellowship:</strong> <?php the_field('fellowship'); ?></p>
<p class="mentor-info-line"><strong>Board Certification:</strong> <?php the_field('board_certification'); ?></p>
<p class="mentor-info-line"><strong>Academic/Organization Affiliations:</strong> <?php the_field('academic__organization_affiliations'); ?></p>
<p class="mentor-info-line"><strong>Current Clinical Position:</strong> <?php the_field('current_clinical_position'); ?></p>
<p class="mentor-info-line"><strong>Clinical/Research Interest:</strong> <?php the_field('clinical__research_interest'); ?></p>
<p class="mentor-info-line"><strong>Areas of Expertise:</strong> <?php the_field('areas_of_expertise'); ?></p>
   </div>    
   </div>
   </div>
       
        <div class="clear"></div>
    </div>
</div>
<?php
get_footer();

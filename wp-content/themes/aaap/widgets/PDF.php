<?php

class PDF_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
                'pdf_widget', // Base ID
                __('PDF Page Widget'), // Name
                array('description' => __('Download Page Content in PDF format')) // Args
        );
    }

    public function widget($args, $instance)
    {
		$where = '';
        $tags = get_the_tags();
        global $wpdb;
        if ($tags) {
            $where = '(';
            foreach ($tags as $key => $tag) {
                if ($key == 0) {
                    $where .= "wp_terms.name = '" . $tag->name . "'";
                } else {
                    $where .= " OR wp_terms.name = '" . $tag->name . "'";
                }
            }
            $where .= ') AND ';
        }

        $links = $wpdb->get_results(
                "SELECT guid FROM wp_term_relationships 
                        JOIN wp_posts ON wp_posts.ID=wp_term_relationships.object_id
                        JOIN wp_terms ON wp_terms.term_id = wp_term_relationships.term_taxonomy_id
                        WHERE " . $where . " wp_posts.post_type='attachment'"
        );

        $title = apply_filters('widget_title', $instance['title']);
        if (count($links) > 0) {
            echo $args['before_widget'];
            echo $args['before_title'] . $title . $args['after_title'];
            foreach ($links as $link) {
                echo '<a class="pdf_link_widget" href="' . $link->guid . '" target="_blank"><img src="' . get_stylesheet_directory_uri() . '/images/pdf.jpg" alt="pdf icon"></a>';
            }
            echo $args['after_widget'];
        }
    }

    public function form($instance)
    {
        global $newInstance;
        $newInstance = $instance;
        include 'html/pdf_widget_form.php';
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }

}

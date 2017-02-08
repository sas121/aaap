<?php

class Text_image extends WP_Widget {

    function __construct() {
        parent::__construct(
            'text_image', // Base ID
            'Text & Image', // Name
            array( 'description' => 'Widget for displaying text with image in the widget.' ) // Args
        );

        add_action('admin_enqueue_scripts', array($this, 'enq_files'));
    }
	
	
	public function enq_files(){
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('upload_media_widget', plugin_dir_url(__FILE__) . 'upload-media.js');
		wp_enqueue_style('thickbox');
	}
    
    /*
     * generate widget
     */
    public function widget( $args, $instance ) {
        $image = $instance['image'];
        $text = $instance['text'];
        $name = $instance['name'];
        $role = $instance['role'];

        echo $args['before_widget'];
        
		echo '<img src="'.$image.'">';
        if (!empty($text)) { echo '<p>'.$text.'</p>'; }
        if (!empty($name)) { echo '<h2>'.$name.'</h2>'; }
        if (!empty($role)) { echo '<h3>'.$role.'</h3>'; }
        
        echo $args['after_widget'];
    }

    /*
     * create form for admin
     */
    public function form( $instance ) {
        $image = ( isset( $instance[ 'image' ] ) ) ? $instance[ 'image' ] : '';
        $text = ( isset( $instance[ 'text' ] ) ) ? $instance[ 'text' ] : '';
        $name = ( isset( $instance[ 'name' ] ) ) ? $instance[ 'name' ] : '';
        $role = ( isset( $instance[ 'role' ] ) ) ? $instance[ 'role' ] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'text' ); ?>">Text:</label> 
            <textarea class="widefat" style="height:100px" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_attr( $text ); ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'name' ); ?>">Name:</label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" type="text" value="<?php echo esc_attr( $name ); ?>">
        </p>
        <p>
            <input class="widefat" id="<?php echo $this->get_field_id( 'role' ); ?>" name="<?php echo $this->get_field_name( 'role' ); ?>" type="text" value="<?php echo esc_attr( $role ); ?>">
        </p>
		<p>			
			<label>Image:</label>
            <input name="<?php echo $this->get_field_name( 'image' ); ?>" id="<?php echo $this->get_field_id( 'image' ); ?>" class="widefat" type="text" size="36"  value="<?php echo esc_url( $image ); ?>" />
            <input class="upload_image_button button button-primary" type="button" value="Upload Image" /> ( 197 px wide for sidebars / 980 px wide for full width )
		</p>
        <?php
    }

    /*
     * insert new data to DB
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['image'] = ( ! empty( $new_instance['image'] ) ) ? strip_tags( $new_instance['image'] ) : '';
        $instance['text'] = ( ! empty( $new_instance['text'] ) ) ? strip_tags( $new_instance['text'] ) : '';
        $instance['name'] = ( ! empty( $new_instance['name'] ) ) ? strip_tags( $new_instance['name'] ) : '';
        $instance['role'] = ( ! empty( $new_instance['role'] ) ) ? strip_tags( $new_instance['role'] ) : '';
        
        return $instance;
    }
}



?>
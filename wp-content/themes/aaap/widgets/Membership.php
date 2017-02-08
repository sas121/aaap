<?php

class Membership_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
                'membership_widget', // Base ID
                __('Membership Widget'), // Name
                array('description' => __('Display Membership Logo and Link To "Become Member Page"')) // Args
        );
    }

    public function widget($args, $instance)
    {
        global $newInstance;
        $newInstance = $instance;
        $title = apply_filters('widget_title', $instance['title']);

        echo $args['before_widget'];
        include 'html/membership_widget_front.php';
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        global $newInstance;
        $newInstance = $instance;
        include 'html/membership_widget_form.php';
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        $instance['description'] = (!empty($new_instance['description']) ) ? strip_tags($new_instance['description']) : '';
        $instance['link_text'] = (!empty($new_instance['link_text']) ) ? strip_tags($new_instance['link_text']) : '';
        $instance['link_url'] = (!empty($new_instance['link_url']) ) ? strip_tags($new_instance['link_url']) : '';

        return $instance;
    }

}

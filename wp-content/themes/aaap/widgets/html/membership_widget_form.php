<?php
if (isset($newInstance['title'])) {
    $title = $newInstance['title'];
} else {
    $title = __('New title', 'text_domain');
}

if (isset($newInstance['description'])) {
    $description = $newInstance['description'];
} else {
    $description = __('Enter Description', 'text_domain');
}
if (isset($newInstance['link_text'])) {
    $linkText = $newInstance['link_text'];
} else {
    $linkText = __('Enter Link Text', 'text_domain');
}
if (isset($newInstance['link_url'])) {
    $linkUrl = $newInstance['link_url'];
} else {
    $linkUrl = __('Enter Link Url', 'text_domain');
}
?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
</p>
<p>
    <label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description:'); ?></label> 
    <textarea class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><?php echo esc_attr($description); ?></textarea>
</p>
<p>
    <label for="<?php echo $this->get_field_id('link_text'); ?>"><?php _e('Link Text:'); ?></label> 
     <input class="widefat" id="<?php echo $this->get_field_id('link_text'); ?>" name="<?php echo $this->get_field_name('link_text'); ?>" type="text" value="<?php echo esc_attr($linkText); ?>">
</p>
<p>
    <label for="<?php echo $this->get_field_id('link_url'); ?>"><?php _e('Link Url:'); ?></label> 
     <input class="widefat" id="<?php echo $this->get_field_id('link_url'); ?>" name="<?php echo $this->get_field_name('link_url'); ?>" type="text" value="<?php echo esc_attr($linkUrl); ?>">
</p>







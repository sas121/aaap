<form action="<?php echo site_url() ?>" method="get">
    <img src="<?php echo get_stylesheet_directory_uri() ?>/images/magnify_glass.png" alt="magnify glass" />
    <input placeholder="Search" type="text" name="s" id="search" value="<?php the_search_query(); ?>" />
    
</form>


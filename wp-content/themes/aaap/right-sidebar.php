<div class="sidebar_content">
    <ul class="widget_holder">
        <?php
        if ($right_sidebar_id) {
            dynamic_sidebar($right_sidebar_id);
        } else {
            dynamic_sidebar('default-right-sidebar');
        }
        ?>
    </ul>
</div>
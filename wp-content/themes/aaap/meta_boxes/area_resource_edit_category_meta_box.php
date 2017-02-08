<?php
$t_id = $term->term_id;

// retrieve the existing value(s) for this meta field. This returns an array
$term_meta = get_option("taxonomy_$t_id");

?>
<tr class="form-field">
    <th scope="row" valign="top"><label for="area_director_email"><?php _e('Area Director Email'); ?></label></th>
    <td>
        <input type="text" name="term_meta[area_director_email]" id="area_director_email" value="<?php echo esc_attr($term_meta['area_director_email']) ? esc_attr($term_meta['area_director_email']) : ''; ?>">
        <p class="description"><?php _e('Enter email of director for selected area'); ?></p>
    </td>
</tr>


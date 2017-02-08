<?php
    $categories = get_categories(array(
        'taxonomy'=>'link_pdf_category'
    ));
?>

<select name="link_pdf_category">
    <option value="">Categories</option>
    <?php foreach ($categories as $category): ?>
    <option <?php echo strtolower($_GET['link_pdf_category'])==strtolower($category->name) ? 'selected="selected"' : '' ?> value="<?php echo strtolower($category->name) ?>"><?php echo $category->name ?></option>
    <?php endforeach; ?>
</select>
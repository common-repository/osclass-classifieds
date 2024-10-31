<?php

function osclass_oc_category_select($categories, $item = array()) {
    $catId = isset($item['fk_i_category_id']) ? $item['fk_i_category_id'] : null;
?>
    <select id="catId" name="catId" class="uk-select uk-100">
        <option value=""><?php _e('Category', 'osclass-classifieds'); ?></option>
        <?php foreach($categories as $category) { ?>
            <optgroup value="<?php echo esc_attr($category['id']); ?>" label="<?php echo esc_attr($category['name']); ?>">
            <?php if( isset($category['categories']) ) {
                foreach($category['categories'] as $sub) { ?>
            <option value="<?php echo esc_attr($sub['id']); ?>" <?php echo ($catId==$sub['id']) ? "selected" : ''; ?>><?php echo esc_html($sub['name']); ?></option>
            <?php } ?>
            </optgroup>
        <?php }
        } ?>
    </select>
<?php
}
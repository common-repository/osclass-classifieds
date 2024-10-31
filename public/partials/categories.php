<?php
$endpoint = osclass_oc_api_endpoint().'categories';

$client = new GuzzleHttp\Client();
$res = $client->request('GET', $endpoint, []);
$categories = json_decode($res->getBody(), true);
$show_category_count = get_option('osclass_categories')['osclass_categories_show_count'];

?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<?php // display latest listings
if (count($res) > 0) { $key = 0; ?>
<div class="osclass-categories">
    <?php foreach($categories as $_key => $category) {
        if( osclass_oc_show_empty_categories() || (!osclass_oc_show_empty_categories() && $category['count'] > 0 ) ) { $key++; ?>
    <div class="category-column">
        <h3><a class="osc-category" href="<?php echo osclass_oc_search_url(array('catId' => $category['id'])); ?>"><?php echo $category['name']; ?>
        <?php if(osclass_oc_show_category_count()) { echo "&nbsp;(".$category['count'].")"; }?></a></h3>
        <?php if(is_array($category['categories']) && count($category['categories'])>0 ) {
        foreach($category['categories'] as $subcategory) { ?>
        <?php if( osclass_oc_show_empty_categories() || (!osclass_oc_show_empty_categories() && $subcategory['count']>0 ) ) { ?>
        <a class="osc-subcategory" href="<?php echo osclass_oc_search_url(array('catId' => $subcategory['id'])); ?>"><?php echo $subcategory['name']; ?><?php if(osclass_oc_show_category_count()) { echo "&nbsp;(".$subcategory['count'].")"; }?></a>
        <?php } ?>
        <?php }
        } ?>
    </div>
    <?php } ?>
    <?php if($key%3===0 && $key>0) { ?>
    <div style="clear:both"></div>
    <?php } ?>
<?php   } ?>
</div>
<?php } ?>

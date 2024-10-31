<?php

$fm_error = osclass_oc_get_fm_error();
$fm_ok = osclass_oc_get_fm_ok();
// client ----------
$client = new GuzzleHttp\Client();
// --------------------------

// get listings ----------
$page = 1;
$endpoint = osclass_oc_api_endpoint().'items';
$res    = $client->request('GET', $endpoint, array('query' =>
    array(
        'page' => $page-1,
        'offset' => osclass_oc_items_per_page(),
        'category' => null,
        'order' => 'dt_pub_date',
        'order_type' => 'desc'
        )
    )
);
$items = json_decode($res->getBody(), true);
// -----------------------

// get count -------------
$endpoint = osclass_oc_api_endpoint().'items';
$res = $client->request('GET', $endpoint, array('query' =>
    array(
        'count' => true
    )
));
$totalItems = json_decode($res->getBody(), true);

$itemsPerPage = osclass_oc_items_per_page();
$to = $page * $itemsPerPage;
if($to>$totalItems) { $to = $totalItems; }

$search_number['from']  = 1;
$search_number['to']    = $to;
$search_number['of']    = $totalItems;
?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>
<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_ok; ?></div>
<?php } ?>

<?php // display latest listings
if ($totalItems > 0) { ?>
<h2><?php _e('Latest listings', 'osclass-classifieds'); ?></h2>
<div class="osclass-results">
    <p><?php printf(__('%1$d - %2$d of %3$d listings', 'osclass-classifieds'), $search_number['from'], $search_number['to'], $search_number['of']); ?></p>
    <p>
<?php foreach($items as $item) { 
    osclass_oc_show_listing($item);
} ?>
</div>
<?php if($totalItems>osclass_oc_items_per_page()) { ?>
<form action="<?php echo osclass_oc_search_url(); ?>" method="get" class="form-btn-center">
    <input type="hidden" name="paged" value="2"/>
    <button class="btn-center"><?php _e('See all listings', 'osclass-classifieds'); ?></button>
</form>
<?php } ?>

<?php } else { ?>
<p><?php _e('No listings found', 'osclass-classifieds'); ?></p>
<?php } ?>

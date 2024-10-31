<?php
$fm_ok = $fm_error = $error = false;
$fm_ok = osclass_oc_get_fm_ok();
// create endpoint ----------
$endpoint = osclass_oc_api_endpoint().'items';
$client = new GuzzleHttp\Client();
// --------------------------

$filtered_search = false;

$validateError = array();
// validate 
$page = get_query_var('paged', 1);
if(!is_numeric($page)) {
    $page = 1;
} else {
    if($page===0) { $page = 1; }
}

$itemsPerPage = osclass_oc_items_per_page();
if(isset($_REQUEST['itemsPerPage']) && is_numeric($_REQUEST['itemsPerPage']) && (int)$_REQUEST['itemsPerPage']>0) { 
    $itemsPerPage = (int)$_REQUEST['itemsPerPage'];
    if($itemsPerPage===0) { $itemsPerPage = osclass_oc_items_per_page(); }
}

$pattern = get_query_var('pattern', null);
if(!is_null($pattern) && sanitize_text_field($pattern)!='' ) {
    $pattern = sanitize_text_field($pattern);
    $filtered_search = true;
}

// category_slug or category_id are accepted
$catId = isset($_REQUEST['catId']) ? ($_REQUEST['catId']) : null;
if(!is_null($catId)) {
    if( strlen(sanitize_text_field($catId))==0 ) {
        $catId = null;
    } else {
        $catId = sanitize_text_field($catId);
        $filtered_search = true;
    }
}

$min_price = get_query_var('min_price', null);
if(!is_null($min_price) ) {
    if(!is_numeric($min_price) ){
        $min_price = null;
    } else {
        $filtered_search = true;
    }
}
$max_price = get_query_var('max_price', null);
if(!is_null($max_price) ) {
    if(!is_numeric($max_price) ){
        $max_price = null;
    } else {
        $filtered_search = true;
    }
}

$country = get_query_var('countryId', null);
if(!is_null($country) && is_numeric($country) ) {
    $country = (int)$country;
    $filtered_search = true;
}
$region = get_query_var('regionId', null);
if(!is_null($region) && is_numeric($region)) {
    $region = (int)$region;
    $filtered_search = true;
}
$city = get_query_var('cityId', null);
if(!is_null($city) && is_numeric($city)) {
    $city = (int)$city;
    $filtered_search = true;
}

$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : null;
if(!is_null($order) && !in_array($order, ['dt_pub_date', 'i_price']) ) {
    $order = null;
}

$order_type = isset($_REQUEST['order_type']) ? sanitize_text_field($_REQUEST['order_type']) : 'desc';
if(!is_null($order_type) && !in_array($order_type, ['desc', 'asc']) ) {
    $order_type = null;
}

// --------------------------

// get listings ----------
$res    = $client->request('GET', $endpoint, array('query' =>
    array(
        'page' => $page-1,
        'offset' => $itemsPerPage,
        'pattern' => $pattern,
        'category' => $catId,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'country' => $country,
        'region' => $region,
        'city' => $city,
        'order' => $order,
        'order_type' => $order_type
        )
    )
);
$items = json_decode($res->getBody(), true);
// -----------------------

// get count -------------
$endpoint = osclass_oc_api_endpoint().'items';
$res = $client->request('GET', $endpoint, array('query' =>
    array(
        'count' => true,
        'pattern' => $pattern,
        'category' => $catId,
        'min_price' => $min_price,
        'max_price' => $max_price,
        'country' => $country,
        'region' => $region,
        'city' => $city,
    )
));
$totalItems = json_decode($res->getBody(), true);
// -----------------------

$totalPages     = 1;
if($totalItems>$itemsPerPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
}
$to = $page * $itemsPerPage;
if($to>$totalItems) { $to = $totalItems; }

$search_number['from']  = (($page-1) * $itemsPerPage) + 1;
$search_number['to']    = $to;
$search_number['of']    = $totalItems;

?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>
<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-success"><?php echo $fm_ok; ?></div>
<?php } ?>

<?php
// order links
$request = remove_query_arg( 'order' );
$request = remove_query_arg( 'order_type', $request );

$home_root = parse_url(home_url());
$home_root = ( isset($home_root['path']) ) ? $home_root['path'] : '';
$home_root = preg_quote( $home_root, '|' );

$request = preg_replace('|^'. $home_root . '|i', '', $request);
$request = preg_replace('|^/+|', '', $request);

$base = trailingslashit( get_bloginfo( 'url' ) );
?>

<?php if($filtered_search) { ?>
    <div style="float:right;"><a href="<?php echo osclass_oc_search_url(); ?>"><?php _e('Clear filters', 'osclass-classifieds'); ?></a></div>
<?php } ?>
<?php // display latest listings
if ($totalItems > 0) { ?>
<div class="osclass-results">
    <p><?php printf(__('%1$d - %2$d of %3$d listings', 'osclass-classifieds'), $search_number['from'], $search_number['to'], $search_number['of']); ?></p>
    <p>
    <select id="sortby" name="order">
        <option value="dt_pub_date" <?php echo ($order=='dt_pub_date') ? 'selected': '';?> data-href="<?php echo add_query_arg( array('order' => 'dt_pub_date', 'order_type' => 'desc'), $base . $request ); ?>"><?php _e('New first', 'osclass-classifieds'); ?></option>
        <option value="higher_price" <?php echo ($order=='i_price' && $order_type=='desc') ? 'selected': '';?> data-href="<?php echo add_query_arg( array('order' => 'i_price', 'order_type' => 'desc'), $base . $request ); ?>"><?php _e('Higher price first', 'osclass-classifieds'); ?></option>
        <option value="lower_price" <?php echo ($order=='i_price' && $order_type=='asc') ? 'selected': '';?> data-href="<?php echo add_query_arg( array('order' => 'i_price', 'order_type' => 'asc'), $base . $request ); ?>"><?php _e('Lower price first', 'osclass-classifieds'); ?></option>
     </select>
    </p>
<?php foreach($items as $item) { 
    osclass_oc_show_listing($item);
} ?>
<?php 
global $wp_query;
$big = 999999999; // need an unlikely integer
echo paginate_links( array(
    'base' => str_replace( $big, '%#%', html_entity_decode( get_pagenum_link( $big ) ) ),
    'format' => 'paged=%#%',
    'current' => $page,
    'total' => $totalPages
) ); ?>
</div>

<script>
(function( $ ) {
	 $('#sortby').change(function(){
         var selected_url = $(this).find('option:selected').data('href'); 
         document.location.href = selected_url;
	 });
})( jQuery );
</script>
<?php } else { ?>
<?php _e('No items found', 'osclass-classifieds'); ?>
<?php } 
<?php 
$fm_ok = $fm_error = $error = false;
$fm_ok = osclass_oc_get_fm_ok();
$fm_error = osclass_oc_get_fm_error();
// create endpoint ----------
$endpoint = osclass_oc_api_endpoint().'users/items';
$client = new GuzzleHttp\Client();


$validateError = array();
// validate 
$page = get_query_var('paged', 1);
if(!is_numeric($page)) {
    $page = 1;
} else {
    if($page===0) { $page = 1; }
}

$itemsPerPage = 10;
if(isset($_REQUEST['itemsPerPage']) && is_numeric($_REQUEST['itemsPerPage']) && (int)$_REQUEST['itemsPerPage']>0) { 
    $itemsPerPage = (int)$_REQUEST['itemsPerPage'];
    if($itemsPerPage===0) { $itemsPerPage = 10; }
}

// get listings ----------
$res    = $client->request('POST', $endpoint, array('json' =>
    array(
        'jwttoken' => osclass_oc_get_jwt(),
        'page' => $page,
        'itemsPerPage' => $itemsPerPage
        )
    )
);
$result = json_decode($res->getBody(), true);

$totalItems = isset($result['total_items']) ? (int)$result['total_items']: 0;
$totalPages     = 1;
$itemsPerPage   = isset($result['itemsPerPage']) ? $result['itemsPerPage']: 10;
if($totalItems>$itemsPerPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
} 

$to = $page * $itemsPerPage;
if($to>$totalItems) { $to = $totalItems; }

$search_number['from']  = (($page-1) * $itemsPerPage) + 1;
$search_number['to']    = $to;
$search_number['of']    = $result['total_items'];
// -----------------------
?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>
<hr/>
<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/my_account_actions.php' ); ?>

<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-success"><?php echo $fm_ok; ?></div>
<?php } ?>
<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>




<?php // display latest listings
if ($totalItems > 0) { ?>
<div><p><?php printf(__('%1$d - %2$d of %3$d listings', 'osclass-classifieds'), $search_number['from'], $search_number['to'], $search_number['of']); ?></p></div>
<div class="osclass-results">
<?php foreach($result['items'] as $item) { 
    osclass_oc_show_listing($item);
} ?>
<div class="fixfloat"></div>
<?php  
global $wp_query;
$big = 999999999; // need an unlikely integer
echo paginate_links( array(
    'base' => str_replace( $big, '%#%', html_entity_decode( get_pagenum_link( $big ) ) ),
    'format' => 'paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $totalPages
) ); ?>
</div>
<?php } else { ?>
<?php _e('You don\'t have items', 'osclass-classifieds'); ?>
<?php } ?>
<div class="fixfloat"></div>
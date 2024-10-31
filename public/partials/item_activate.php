<?php
use GuzzleHttp\Exception\ClientException;

$fm_ok = $fm_error = $error = false;
$item_id    = get_query_var('item_id', null);
$secret     = get_query_var('secret', null);

$validateError = array();
// validate
if(!is_null($item_id) && !is_numeric($item_id) ) {
    $validateError[] = __('Sorry, the link is not valid.', 'osclass-classifieds');
}

if(!isset($secret) && strlen($secret)==0 ) {
    $validateError[] = __('Sorry, the link is not valid.', 'osclass-classifieds');
}

if(!empty($validateError)) {
    osclass_oc_set_fm_error(__('Sorry, the link is not valid.', 'osclass-classifieds'));
    osclass_oc_redirect(osclass_oc_home_url());
}

$endpoint = osclass_oc_api_endpoint().'admin/item/activate';
$data = array( 'json' => array(
        'id' =>  $item_id,
        'secret' =>  $secret,
        'admin_user' =>  osclass_oc_api_admin_username(),
        'admin_password' => osclass_oc_api_admin_password()
        )
    );

$client = new GuzzleHttp\Client();

try {
    $_response = $client->request('POST', $endpoint, $data);

    $response = json_decode($_response->getBody(), true);
    if($response['success']) {
        if(isset($response['message'])) {
            $fm_ok = $response['message'];
        }
    } else {
        if(isset($response['message'])) {
            $fm_error = $response['message'];
        }
    }
} catch (ClientException $e) {
    $body = $e->getResponse()->getBody(true);
    $error = json_decode($body, true);
    $fm_error = $error['error']['message'];
}
?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>
<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-success"><?php echo $fm_ok; ?></div>
<?php } ?>
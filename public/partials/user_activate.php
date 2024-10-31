<?php
$fm_ok = $fm_error = $error = false;
$user_id    = get_query_var('user_id', null);
$secret     = get_query_var('secret', null);


$validateError = array();
// validate
if(!is_null($user_id) && !is_numeric($user_id) ) {
    $validateError[] = __('Sorry, the link is not valid.', 'osclass-classifieds');
}

if(!isset($secret) && strlen($secret)==0 ) {
    $validateError[] = __('Sorry, the link is not valid.', 'osclass-classifieds');
}

if(!empty($validateError)) {
    osclass_oc_set_fm_error(__('Sorry, the link is not valid.', 'osclass-classifieds'));
    osclass_oc_redirect(osclass_oc_home_url());
}

$endpoint = osclass_oc_api_endpoint().'admin/users/validate';
$data = array( 'json' => array(
        'id' =>  $user_id,
        'secret' =>  $secret,
        'admin_user' =>  osclass_oc_api_admin_username(),
        'admin_password' => osclass_oc_api_admin_password()
        )
    );

$client = new GuzzleHttp\Client();
$response = $client->request('POST', $endpoint, $data);
$user_response = json_decode($response->getBody(), true);

if($user_response['success']) {
    $user = $user_response['user'];
    // send welcome email
    if(osclass_oc_get_send_user_welcome_notification()) {
        osclass_oc_send_user_welcome_notification($user['s_email']);
    }
    // send admin email - new user created
    if(osclass_oc_get_send_admin_user_created_notification()) {
        osclass_oc_send_admin_user_welcome_notification($user['s_email']);
    }
    if(isset($user_response['message'])) {
        $fm_ok = $user_response['message'];
    }
} else {
    if(isset($user_response['message'])) {
        $fm_error = $user_response['message'];
    }
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
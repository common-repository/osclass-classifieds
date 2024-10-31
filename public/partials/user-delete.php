<?php
$fm_ok = $fm_error = $error = false;
if ( isset($_REQUEST['user_id']) && isset($_REQUEST['secret']) && is_numeric($_REQUEST['user_id']) && $_REQUEST['user_id']!='' && $_REQUEST['secret']!='') {
  
  $user_id = (int)$_REQUEST['user_id'];
  $secret = sanitize_text_field($_REQUEST['secret']);
  
  $endpoint = osclass_oc_api_endpoint().'users/delete/'.$user_id.'/'.$secret;
  $client = new GuzzleHttp\Client();
  $data = array( 'json' => array(
      'jwttoken' => osclass_oc_get_jwt()
    )
  );
  $response = $client->request('POST', $endpoint, $data);
  $delete_response = json_decode($response->getBody(), true);

  if($delete_response['success']) {
    $fm_ok = __("Your account have been deleted", 'osclass-classifieds');
    // do logout
    do_action('osclass_oc_logout');
  } else {
    $fm_error = __("Oops! you can not do that", 'osclass-classifieds');
  }
} else {
  $fm_error = __("Oops! you can not do that", 'osclass-classifieds');
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

<?php
$fm_ok = $fm_error = $error = false;
if ( isset($_REQUEST['userId']) && $_REQUEST['userId']!='' && is_numeric($_REQUEST['userId']) &&
     isset($_REQUEST['code']) && $_REQUEST['code']!='') {

  $user_id = (int)$_REQUEST['userId'];
  $endpoint = osclass_oc_api_endpoint().'users/change-email-confirm/'.$user_id.'/'.$_REQUEST['code'];
  $client = new GuzzleHttp\Client();
  $response = $client->request('GET', $endpoint);
  $confirm_response = json_decode($response->getBody(), true);

  if($confirm_response['success']) {
    // change email !
    osclass_oc_set_logged_user_email($confirm_response['email']);
    $fm_ok = __("Your email has been changed successfully", 'osclass-classifieds');
  } else {
    $fm_error = __("Sorry, the link is not valid", 'osclass-classifieds');
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

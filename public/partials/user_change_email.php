<?php
$fm_ok = $fm_error = $error = false;
if ( ! empty( $_POST ) ) {
  $validateError = array();
  // validate 
  if(isset($_POST['email']) && strlen(sanitize_email($_POST['email'])) == 0 && !is_email($_POST['email']) ) {
      $validateError[] = __('Email invalid', 'osclass-classifieds');
  }

  if(!empty($validateError)) {
      $fm_error = implode("<br>", $validateError);
  } else {
    $endpoint = osclass_oc_api_endpoint().'users/change-email/'.osclass_oc_get_logged_user_id() ;
    $data = array( 'json' => array(
        'new_email' =>  $_POST['new_email'],
        'url' => osclass_oc_change_user_email_confirm_url(),
        'jwttoken' => osclass_oc_get_jwt()
      )
    );
    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $register_response = json_decode($response->getBody(), true);

    if($register_response['success']) {
      // send confirmation email
      if(osclass_oc_send_new_email($_POST['new_email'], $register_response['message'])) {
        $fm_ok = __("We've sent you an e-mail. Follow its instructions to validate the changes", 'osclass-classifieds');
      } else {
        $fm_error = __("We tried to sent you an e-mail, but it failed. Please, contact an administrator", 'osclass-classifieds');
      }
    } else {
        $fm_error = __("Some error occurred, try again later", 'osclass-classifieds');
    }
  }
}

?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<h1><?php _e('My account', 'osclass-classifieds'); ?></h1>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/my_account_actions.php' ); ?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>
<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-success"><?php echo $fm_ok; ?></div>
<?php } ?>

<form id="new" method="post" class="uk-form-stacked">
    <div class="uk-margin"><!--current email-->
    <label class="uk-form-label" for="email"><?php _e('Current email', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <?php echo osclass_oc_get_logged_user_email(); ?>
    </div>
  </div>
  <div class="uk-margin"><!--new email-->
    <label class="uk-form-label" for="new_email"><?php _e('New email', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input  name="new_email" type="email" class="uk-input"/>
    </div>
  </div>
  <div>
    <br>
    <p><button type="submit" class="uk-button"><?php _e('Update', 'osclass-classifieds'); ?></button></p>
  </div>
</form>
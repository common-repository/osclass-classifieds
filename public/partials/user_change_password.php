<?php
$fm_ok = $fm_error = $error = false;
if ( ! empty( $_POST ) ) {
  $validateError = array();
  // validate 
  if( isset($_POST['password']) && strlen(sanitize_text_field($_POST['password'])) == 0 ||
      isset($_POST['new_password']) && strlen(sanitize_text_field($_POST['new_password'])) == 0 ||
      isset($_POST['new_password2']) && strlen(sanitize_text_field($_POST['new_password2'])) == 0) {
      $validateError[] = __('Passwords cannot be empty', 'osclass-classifieds');
  } else {
      if($_POST['new_password']!=$_POST['new_password2']) {
          $validateError[] = __('Passwords don\'t match', 'osclass-classifieds');
      }
  }

  $password = $_POST['password'];
  $new_password = $_POST['new_password'];
  $new_password2 = $_POST['new_password2'];
  
  if(!empty($validateError)) {
      $fm_error = implode("<br>", $validateError);
  } else {
    $endpoint = osclass_oc_api_endpoint().'users/change-password/'.osclass_oc_get_logged_user_id() ;
    $data = array( 'json' => array(
        'password' => $password,
        'new_password' => $new_password,
        'new_password2' => $new_password2,
        'jwttoken' => osclass_oc_get_jwt()
      )
    );
    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $change_response = json_decode($response->getBody(), true);
    if($change_response['success']) {
      $fm_ok = $change_response['message'];
    } else {
      $fm_error = $change_response['message'];
    }
  }
}
?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<h1><?php _e('Change password', 'osclass-classifieds'); ?></h1>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/my_account_actions.php' ); ?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>
<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-success"><?php echo $fm_ok; ?></div>
<?php } ?>

<form id="new" method="post" class="uk-form-stacked">
  <div class="uk-margin"><!--password-->
    <label class="uk-form-label" for="password"><?php _e('Current password', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input name="password" type="password" class="uk-input"/>
    </div>
  </div>
  <div class="uk-margin"><!--new_password-->
    <label class="uk-form-label" for="new_password"><?php _e('New password', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input name="new_password" type="password" class="uk-input"/>
    </div>
  </div>
  <div class="uk-margin"><!--new_password2-->
    <label class="uk-form-label" for="new_password2"><?php _e('Repeat new password', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input name="new_password2" type="password" class="uk-input"/>
    </div>
  </div>
  <div>
    <br>
    <p><button type="submit" class="uk-button"><?php _e('Update', 'osclass-classifieds'); ?></button></p>
  </div>
</form>
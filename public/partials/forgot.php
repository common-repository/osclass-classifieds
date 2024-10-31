<?php
$redirect = false;
$fm_error = false;


$validateError = array();
// validate 
if(!isset($_REQUEST['userId']) && !is_numeric($_REQUEST['userId']) ) {
    $validateError[] = __('Sorry, the link is not valid.', 'osclass-classifieds');
}
if(!isset($_REQUEST['code']) && strlen($_REQUEST['code'])==0 ) {
    $validateError[] = __('Sorry, the link is not valid.', 'osclass-classifieds');
}
if(!empty($validateError)) {
    osclass_oc_set_fm_error(__('Sorry, the link is not valid.', 'osclass-classifieds'));
    osclass_oc_redirect(osclass_oc_home_url());
}

if ( ! empty( $_POST ) ) {

    if( isset($_POST['new_password']) && strlen(sanitize_text_field($_POST['new_password'])) == 0 ||
        isset($_POST['new_password2']) && strlen(sanitize_text_field($_POST['new_password2'])) == 0) {
        $validateError[] = __('Passwords cannot be empty', 'osclass-classifieds');
    } else {
        if($_POST['new_password']!=$_POST['new_password2']) {
            $validateError[] = __('Passwords don\'t match', 'osclass-classifieds');
        }
    }

    if(!empty($validateError)) {
        $fm_error = implode("<br>", $validateError);
    } else {
        $code = sanitize_text_field($_POST['code']);
        $user_id = (int)$_POST['userId'];
        $new_password = $_POST['new_password'];
        $new_password2 = $_POST['new_password2'];

        $endpoint = osclass_oc_api_endpoint().'users/forgot';
        $data = array( 'json' => array(
            'code' => $code,
            'user_id' => $user_id,
            'new_password' => $new_password,
            'new_password2' => $new_password2,
            )
        );

        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', $endpoint, $data);
        $forgot_response = json_decode($response->getBody(), true);

        if($forgot_response['success']) {
            osclass_oc_set_fm_ok(__('Password has been successfully updated.', 'osclass-classifieds'));
            osclass_oc_redirect(osclass_oc_home_url());
        } else {
            $fm_error = $forgot_response['message'];
        }
    }
}
?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<form id="forgot" method="POST" class="uk-form-stacked">
  <input type="hidden" name="userId" value="<?php echo get_query_var('userId'); ?>"/>
  <input type="hidden" name="code" value="<?php echo get_query_var('code'); ?>"/>
  <div class="uk-margin"><!--password-->
      <label class="uk-form-label" for="password"><?php _e('New password', 'osclass-classifieds'); ?></label>
      <div class="uk-form-controls">
          <input  name="new_password" type="password" class="uk-input"/>
      </div>
  </div>
  <div class="uk-margin"><!--password2-->
      <label class="uk-form-label" for="password2"><?php _e('Repeat new password', 'osclass-classifieds'); ?></label>
      <div class="uk-form-controls">
          <input  name="new_password2" type="password" class="uk-input"/>
      </div>
  </div>
  <div>
      <br>
      <p><button type="submit" class="uk-button"><?php _e('Register', 'osclass-classifieds'); ?></button></p>
  </div>
</form>

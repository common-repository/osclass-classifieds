<?php
$fm_ok = $fm_error = $error = false;
if ( ! empty( $_POST ) ) {

    $validateError = array();
    // validate 
    if(isset($_POST['email']) && strlen(sanitize_email($_POST['email'])) == 0 && !is_email($_POST['email']) ) {
        $validateError[] = __('Email invalid', 'osclass-classifieds');
    }
    
    $email = sanitize_email($_POST['email']);

    if(!empty($validateError)) {
        $fm_error = implode("<br>", $validateError);
    } else {
        $endpoint = osclass_oc_api_endpoint().'admin/users/recover';
        $data = array(
            'json' => array(
                'url' => osclass_oc_forgot_password_url(),
                'email' => $email,
                'admin_user' =>  osclass_oc_api_admin_username(),
                'admin_password' => osclass_oc_api_admin_password()
            )
        );

        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', $endpoint, $data);
        $recover_response = json_decode($response->getBody(), true);

        if($recover_response['success']===true) {
            if(osclass_oc_send_user_forgot_password($email, $recover_response['url']) ) {
                $fm_ok = __('We have sent you an email with the instructions to reset your password', 'osclass-classifieds');
            } else { $error = true; }
        } else { $error = true; }

        if($error) {
            $fm_error = __('We were not able to identify you given the information provided', 'osclass-classifieds');
        }
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

<form id="recover" method="post" class="uk-form-stacked">
    <div class="uk-margin"><!--email-->
    <label class="uk-form-label" for="email"><?php _e('Email', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input name="email" type="email" placeholder="address@example.com"/>
    </div>
  </div>
  <div>
  <?php if( function_exists( 'gglcptch_display' ) ) { echo gglcptch_display(); } ; ?>
  <br>
  <p><button type="submit" class="uk-button"><?php _e('Recover password', 'osclass-classifieds'); ?></button></p>
  </div>
</form>
<?php
$fm_ok = $fm_error = $error = false;
$fm_error = osclass_oc_get_fm_error();

$name = "";
$email = "";
if ( ! empty( $_POST ) ) {
    $validateError = array();
    // validate 
    if(isset($_POST['username']) && strlen(sanitize_text_field($_POST['username'])) == 0 ) {
        $validateError[] = __('User name cannot be empty', 'osclass-classifieds');
    }
    if(isset($_POST['email']) && strlen(sanitize_email($_POST['email'])) == 0 && !is_email($_POST['email']) ) {
        $validateError[] = __('Email invalid', 'osclass-classifieds');
    }
    if( isset($_POST['password']) && strlen(sanitize_text_field($_POST['password'])) == 0 ||
        isset($_POST['password2']) && strlen(sanitize_text_field($_POST['password2'])) == 0) {
        $validateError[] = __('Passwords cannot be empty', 'osclass-classifieds');
    } else {
        if($_POST['password']!=$_POST['password2']) {
            $validateError[] = __('Passwords don\'t match', 'osclass-classifieds');
        }
    }

    if(!empty($validateError)) {
        $fm_error = implode("<br>", $validateError);
    } else {
        $name = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $password2 = $_POST['password2'];

        $data = array( 'json' => array(
                's_name' =>  $name,
                's_email' =>  $email,
                's_password' => $password,
                's_password2' => $password2,
            )
        );
        
        $endpoint = osclass_oc_api_endpoint().'users';
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', $endpoint, $data);
        $register_response = json_decode($response->getBody(), true);

        // USER_NEED_VALIDATION
        // USER_CREATED
        // USER_FAILED
        if($register_response['success'] && $register_response['code']=='USER_CREATED') {
            // ADD FLASH MESSAGE
            osclass_oc_loggin_user($email, $password);
            // send welcome email
            if(osclass_oc_get_send_user_welcome_notification()) {
                osclass_oc_send_user_welcome_notification($email);
            }
            // send admin email - new user created
            if(osclass_oc_get_send_admin_user_created_notification()) {
                osclass_oc_send_admin_user_welcome_notification($email);
            }
            wp_redirect(osclass_oc_home_url());
        } else if($register_response['success'] && $register_response['code']=='USER_NEED_VALIDATION') {
            // ADD FLASH MESSAGE
            // send validation email
            osclass_oc_send_user_validation_email($email);
            $fm_ok = __('The user has been created. An activation email has been sent', 'osclass-classifieds');
        } else {
            // ADD FLASH MESSAGE
            $fm_error = $register_response['message'];
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

<p><?php echo sprintf(__('Already registered? <a href="%s">Log in</a>', 'osclass-classifieds'), osclass_oc_login_url() ); ?></p>
<form id="new" method="POST" action="<?php echo osclass_oc_register_url(); ?>" class="uk-form-stacked">
  <div class="uk-margin"><!--email-->
      <label class="uk-form-label" for="email"><?php _e('Username', 'osclass-classifieds'); ?></label>
      <div class="uk-form-controls">
          <input type="text"  name="username" placeholder="username" class="uk-input" value="<?php echo esc_attr($name); ?>"/>
      </div>
  </div>
  <div class="uk-margin"><!--email-->
      <label class="uk-form-label" for="email"><?php _e('Email', 'osclass-classifieds'); ?></label>
      <div class="uk-form-controls">
          <input type="email" name="email" placeholder="address@example.com" class="uk-input" value="<?php echo esc_attr($email); ?>"/>
      </div>
  </div>
  <div class="uk-margin"><!--password-->
      <label class="uk-form-label" for="password"><?php _e('Password', 'osclass-classifieds'); ?></label>
      <div class="uk-form-controls">
          <input  name="password" type="password" class="uk-input"/>
      </div>
  </div>
  <div class="uk-margin"><!--password2-->
      <label class="uk-form-label" for="password2"><?php _e('Repeat password', 'osclass-classifieds'); ?></label>
      <div class="uk-form-controls">
          <input  name="password2" type="password" class="uk-input"/>
      </div>
  </div>
  <?php if( function_exists( 'gglcptch_display' ) ) { echo gglcptch_display(); } ; ?>
  <div>
    <button type="submit" class="uk-button"><?php _e('Register', 'osclass-classifieds'); ?></button>
    <div style="clear:both"></div>
    <p><small><?php printf(__('By clicking Register, I agree to the <a href="%s" target="_blank">Privacy Policy</a> and <a href="%s" target="_blank">Terms and conditions</a>.', 'osclass-classifieds'),
    'https://osclass.org/page/privacy-policy', 
    'https://osclass.org/page/legal-note' ); ?></small></p>
  </div>  
</form>

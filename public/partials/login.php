<?php
// if POST form
$fm_error = false;
if ( ! empty( $_POST ) ) {
  $validateError = array();
  // validate
  if(!isset($_POST['email']) || isset($_POST['email']) && strlen(sanitize_email($_POST['email'])) == 0 && !is_email($_POST['email']) ) {
    $validateError[] = __('Email invalid', 'osclass-classifieds');
  }
  if( !isset($_POST['password']) || isset($_POST['password']) && strlen(sanitize_text_field($_POST['password'])) == 0) {
    $validateError[] = __('Password cannot be empty', 'osclass-classifieds');
  }

  if(!empty($validateError)) {
    $fm_error = implode("<br>", $validateError);
  } else {
    $email = sanitize_email( $_POST['email'] );
    $password = $_POST['password'];

    $result = osclass_oc_loggin_user($email, $password);
    if(isset($result['success']) && $result['success']===false ) {
      $fm_error = $result['message'];
    } else {
      osclass_oc_redirect(osclass_oc_search_url());
    }
  }
} ?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>

<form id="new" method="post" class="uk-form-stacked">
  <div class="uk-margin"><!--email-->
    <label class="uk-form-label" for="email"><?php _e('Email', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input name="email" type="email" placeholder="address@example.com"/>
    </div>
  </div>
  <div class="uk-margin"><!--password-->
    <label class="uk-form-label" for="password"><?php _e('Password', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input  name="password" type="password" class="uk-input"/>
    </div>
  </div>
  <?php if( function_exists( 'gglcptch_display' ) ) { echo gglcptch_display(); } ; ?>
  <div>
    <button type="submit" class="uk-button"><?php _e('Login', 'osclass-classifieds'); ?></button>
    <div style="clear:both"></div>
    <p><small><?php printf(__('By clicking Login, I agree to the <a href="%s" target="_blank">Privacy Policy</a> and <a href="%s" target="_blank">Terms and conditions</a>.', 'osclass-classifieds'),
    'https://osclass.org/page/privacy-policy', 
    'https://osclass.org/page/legal-note' ); ?></small></p>
  </div>
  <div>
    <p><?php _e('Don\'t you have an account?', 'osclass-classifieds'); ?> <a href="<?php echo osclass_oc_register_url(); ?>"><?php _e('Register now', 'osclass-classifieds'); ?></a></p>
    <p><?php _e('Forgot your password?', 'osclass-classifieds'); ?> <a href="<?php echo osclass_oc_recover_url(); ?>"><?php _e('Recover password', 'osclass-classifieds'); ?></a></p>
  </div>
</form>
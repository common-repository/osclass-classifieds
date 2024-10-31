<?php
$fm_ok = $fm_error = $error = $form_error = false;
$_yourname = $_youremail = $_message = '';

$item_id    = get_query_var('item_id', 0);
if(is_numeric($item_id) && (int)$item_id>0) {
    $item_id = (int)$item_id;
    if( !osclass_oc_is_web_user_logged_in() ){
      osclass_oc_set_fm_error( sprintf(__('You can\'t contact the seller, only registered users can, <a href="%s">Log in</a>', 'osclass-classifieds'), osclass_oc_login_url(array()) ) );
      wp_redirect( osclass_oc_item_url($item_id) );
    } 
    // create endpoint ----------
    $endpoint = osclass_oc_api_endpoint().'items/'.$item_id;
    $client = new GuzzleHttp\Client();

    $res    = $client->request('GET', $endpoint);
    $result = json_decode($res->getBody(), true);
    if(isset($result[0]) ) {
        $item = $result[0];
        if ( ! empty( $_POST ) ) {
          // validate
          $_error = false;
          $aError = array();
          if(!isset($_POST['yourname']) || strlen(sanitize_text_field($_POST['yourname'])) == 0) {
            $_error = true;
            $aError[] = __('Your name is required', 'osclass-classifieds');
          } else {
            $_yourname = sanitize_text_field($_POST['yourname']);
          }

          if(!isset($_POST['youremail']) || (strlen(sanitize_email($_POST['youremail'])) == 0 || !is_email($_POST['youremail']) ) ) {
            $_error = true;
            $aError[] = __('Your email is required', 'osclass-classifieds');
          } else {
            $_youremail = sanitize_email($_POST['youremail']);
          }
          if(!isset($_POST['message']) || strlen(sanitize_text_field($_POST['message'])) == 0) {
            $_error = true;
            $aError[] = __('Message is required', 'osclass-classifieds');
          } else {
            $_message = sanitize_text_field($_POST['message']);
          }
          if(!$_error) {
            $_item_id = (int)$item['pk_i_id'];
            $data = array(
              'url' => osclass_oc_item_url($_item_id),
              'title' => osclass_oc_item_url($item['s_title']),
              'message' => $_message,
              'youremail' => $_youremail,
              'yourname' => $_yourname
            );

            $email_sent = osclass_oc_send_contact_item_form(
              $item['s_contact_email'],
              $item['s_contact_name'],
              $data
            );
            if($email_sent) {
              $fm_ok = __('We\'ve just sent an e-mail to the seller.', 'osclass-classifieds');
            } else {
              $fm_error = __('Sorry, we could not deliver your message.', 'osclass-classifieds');
            }
          } else {
            $form_error = implode('<br>', $aError);
          }
        }
    }
}
?>

<?php if($fm_error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
<?php } ?>
<?php if($fm_ok!==false) { ?>
<div class="uk-alert uk-alert-success"><?php echo $fm_ok; ?></div>
<?php } ?>

<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

<?php if(is_array($item) && isset($item['pk_i_id'])) { ?>
    <div class="osc-item-contact">
      <h1><?php _e('Contact publisher', 'osclass-classifieds'); ?></h1>
      <?php if($form_error!==false) { ?>
      <div class="uk-alert uk-alert-danger"><?php echo $form_error; ?></div>
      <?php } ?>
      <div class="osc-header">
          <h3 style="display:inline-block;margin:0px;"><?php echo esc_html($item['s_title']); ?></h1>
          <div class="price">
              <span><?php echo esc_html($item['formated_price']); ?></span>
          </div>
      </div>

      <form id="new" method="post" class="uk-form-stacked">
        <input type="hidden" name="item_id" value="<?php echo esc_attr($item['pk_i_id']);?>"/>
        <div class="uk-margin"><!--name-->
          <label class="uk-form-label" for="yourname"><?php _e('Your name', 'osclass-classifieds'); ?>*</label>
          <div class="uk-form-controls">
            <input name="yourname" type="text" class="uk-input uk-input-title" placeholder="" value="<?php echo esc_attr($_yourname); ?>"/>
          </div>
        </div>
        <div class="uk-margin"><!--email-->
          <label class="uk-form-label" for="youremail"><?php _e('Your Email', 'osclass-classifieds'); ?>*</label>
          <div class="uk-form-controls">
            <input name="youremail" type="email" placeholder="" class="uk-input uk-input-title" value="<?php echo esc_attr($_youremail); ?>"/>
          </div>
        </div>
        <div class="uk-margin"><!--message-->
          <label class="uk-form-label" for="message"><?php _e('Message', 'osclass-classifieds'); ?>*</label>
          <div class="uk-form-controls">
            <textarea name="message" class="uk-textarea uk-textarea-description"><?php echo esc_html($_message); ?></textarea>
          </div>
        </div>
        <div>
          <br>
          <p><button type="submit" class="uk-button"><?php _e('Send', 'osclass-classifieds'); ?></button></p>
        </div>

      </form>
    </div>
<?php } else { ?>
  <?php _e('The listing your are looking for does not exist.', 'osclass-classifieds'); ?>
<?php } ?>
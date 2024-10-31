<?php 
$_user = array();
if(osclass_oc_is_web_user_logged_in()) {
    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  osclass_oc_get_logged_user_email(),
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);

    if($user_response['success']) {
        $user =  $user_response['user'];
?>

<div class="osclass-actions my-account-actions">
    <div class="osclass-action"><a href="<?php echo osclass_oc_my_account_url(); ?>"><?php _e('My listings', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action"><a href="<?php echo osclass_oc_user_change_email_url(); ?>"><?php _e('Change email', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action"><a href="<?php echo osclass_oc_user_change_password_url(); ?>"><?php _e('Change password', 'osclass-classifieds'); ?></a></div>
    <div class="osclass-action">
        <a onclick="javascript:return confirm('<?php echo esc_js(__('This action can not be undone. Are you sure you want to continue?', 'osclass-classifieds')); ?>')" href="<?php echo osclass_oc_user_delete_url($user['pk_i_id'], $user['s_secret']);?>" ><?php _e('Remove account', 'osclass-classifieds'); ?></a>
    </div>
</div>
<?php
    }
}
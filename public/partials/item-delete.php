<?php
$fm_ok = $fm_error = $error = false;
if ( isset($_REQUEST['item_id']) && 
      $_REQUEST['item_id']!='' && 
      is_numeric($_REQUEST['item_id']) &&
      isset($_REQUEST['secret']) && 
      $_REQUEST['secret']!='') {

  $item_id = (int)$_REQUEST['item_id'];
  $secret = sanitize_text_field($_REQUEST['secret']);

  $endpoint = osclass_oc_api_endpoint().'items/delete/'.$item_id.'/'.$secret;
  $client = new GuzzleHttp\Client();
  $response = $client->request('GET', $endpoint);
  $delete_response = json_decode($response->getBody(), true);

  if($delete_response['success']) {
    $fm_ok = __("Your listing has been deleted", 'osclass-classifieds');
    osclass_oc_set_fm_ok( $fm_ok );
  } else {
    $fm_error = __("The listing you are trying to delete couldn't be deleted", 'osclass-classifieds');
    osclass_oc_set_fm_error( $fm_error );
  }
  osclass_oc_redirect( osclass_oc_my_account_url() );
} else {
  osclass_oc_set_fm_error(__('Sorry, the link is not valid.', 'osclass-classifieds'));
  osclass_oc_redirect( osclass_oc_my_account_url() );
}
?>
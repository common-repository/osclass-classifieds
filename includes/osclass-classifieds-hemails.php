<?php


function osclass_oc_send_mail($to, $subject, $body, $headers = array('Content-Type: text/html; charset=UTF-8')) {

    if($to!=='' && $subject!=='' && $body!=='' && is_array($headers)) {
        return wp_mail( $to, $subject, $body, $headers );
    }
    return false;
}

/**
 * Send item contact email
 */
function osclass_oc_send_contact_item_form($to_email, $to_name, $data) {
    $body = '<div>';
    $body .= '<p>Hi '.$to_name.'</p>';
    $body .= '<p>'.$data['yourname'].', '.$data['youremail'].'  left you a message about your listing.</p>';
    $body .= '<p><a href="'.$data['url'].'">'.$data['title'].'</a></p>';
    $body .= '<p>'.$data['message'].'</p>';
    $body .= '<p>Regards</p>';
    $body .= '</div>';
    return osclass_oc_send_mail($to_email, 'Someone has a question about your listing', $body);
}

function osclass_oc_send_new_email($to_email, $url) {
    if(osclass_oc_get_logged_user_email()=='') {
        return false;
    }

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
        $subject = "You requested an email change";
        $link = '<a href="'.$url.'">Validate your new email</a>';

        $body = '<p>Hi '.$user['s_name'].'</p>';
        $body .= "<p>You're receiving this e-mail because you requested an e-mail change. Please confirm this new e-mail address by clicking on the following validation link: $link</p>";
        $body .= "<p>Regards,</p>";
        return osclass_oc_send_mail($to_email, $subject, $body);
    }
    return false;
}

/**
 * Send user validate account
 */
function osclass_oc_send_user_validation_email($email) {
    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  $email,
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);

    if($user_response['success']) {
        $user =  $user_response['user'];
        $subject = 'Please validate your account';
        $validation_url = osclass_oc_user_activate_url($user['pk_i_id'], $user['s_secret']);
        $validation_link = '<a href="'.$validation_url.'">Validate account</a>';

        $body = '<div>';
        $body .= '<p>Hi '.$user['s_name'].'</p>';
        $body .= '<p>Please validate your registration by clicking on the following link: '.$validation_link.'</p>';
        $body .= '<p>Thank you!</p>';
        $body .= '<p>Regards</p>';
        $body .= '</div>';
        return osclass_oc_send_mail($email, $subject, $body);
    }
    return false;
}


function osclass_oc_send_email_item_validation($email, $item_id) {
    $endpoint = osclass_oc_api_endpoint().'admin/items/'.$item_id;
    $data = array( 'json' => array(
        'email' =>  $email,
        'item_id' => $item_id,
        'admin_user' =>  osclass_oc_api_admin_username(),
        'admin_password' => osclass_oc_api_admin_password()
        )
    );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $item_response = json_decode($response->getBody(), true);

    if($item_response['success']) {

        $item =  $item_response['item'];
        $secret =  $item['s_secret'];

        $subject = 'Please validate your listing';
        $validation_url = osclass_oc_item_activate_url($item['pk_i_id'], $secret);
        $validation_link = '<a href="'.$validation_url.'">'.$validation_url.'</a>';

        $body = '<div>';
        $body .= '<p>Hi '.$item['s_contact_name'].'</p>';
        $body .= '<p>You\'re receiving this e-mail because a listing has been published at .';
        $body .= 'Please validate this listing by clicking on the following link: '.$validation_url.'. ';
        $body .= 'If you didn\'t publish this listing, please ignore this email.'.$validation_link.'</p>';
        $body .= '<p>Listing details:</p>';
        $body .= '<p>Contact name: '.$item['s_contact_name'].'<br />';
        $body .= 'Contact e-mail: '.$item['s_contact_email'].'</p>';
        $body .= '<p>'.$item['s_description'].'</p>';
        $body .= '<p>Url: '.osclass_oc_item_url($item['pk_i_id']).'</p>';
        $body .= '<p>Regards</p>';
        $body .= '</div>';
        $return  = osclass_oc_send_mail($email, $subject, $body);
    }
    return false;
}

/**
 * Send notification email - new ad 
 */
function osclass_oc_send_admin_ad_created_notification($item_id) {

    $endpoint = osclass_oc_api_endpoint().'admin/items/'.$item_id;
    $data = array( 'json' => array(
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $item = array();
    $to_email = '';
    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $item_response = json_decode($response->getBody(), true);
    if($item_response['success']) {
        $item = $item_response['item'];
        if(!isset($item['pk_i_id']) ) {
            return false;
        }
        $to_email = $item['s_contact_email'];
    }

    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  $to_email,
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);
    if($user_response['success']) {
        $user =  $user_response['user'];
        $web_title = osclass_oc_get_home_title();

        $body = '<div>';
        $body .= '<p>Hi '.$user['s_name'].'</p>';
        $body .= '<p>You\'re receiving this email because a listing has been published at <a href="'.osclass_oc_home_url().'">'.$web_title.'</a>.</p>';
        $body .= '<p>Listing details:</p>';
        $body .= '<p>';
        $body .= 'Name: '.$user['s_name'].'<br>';
        $body .= 'Email: '.$user['s_email'].'<br>';
        $body .= '</p>';
        $body .= '<p>Url: <a href="'.osclass_oc_item_url($item['pk_i_id']).'">'.$item['s_title'].'</a></p>';
        $body .= '<p>Thank you!</p>';
        $body .= '<p>Regards</p>';
        $body .= '</div>';

        $admin_email = get_option( 'admin_email' );
        return osclass_oc_send_mail($admin_email, $web_title . ' - A new listing has been published', $body);
    }
}

/**
 * Send notification email - new user
 */
function osclass_oc_send_user_ad_created_notification($to_email) {
    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  $to_email,
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);
    if($user_response['success']) {
        $user =  $user_response['user'];
        $web_title = osclass_oc_get_home_title();

        $body = '<div>';
        $body .= '<p>Hi '.$user['s_name'].'</p>';
        $body .= '<p>You\'ve successfully created a new listing at <a href="'.osclass_oc_home_url().'">'.$web_title.'</a>.</p>';
        $body .= '<p>Thank you!</p>';
        $body .= '<p>Regards</p>';
        $body .= '</div>';

        return osclass_oc_send_mail($to_email, $web_title . ' - Listing successfully created', $body);
    }
}

/**
 * Send ADMIN notification email - new user
 */
function osclass_oc_send_admin_user_welcome_notification($to_email) {
    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  $to_email,
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);
    if($user_response['success']) {
        $user =  $user_response['user'];
        $web_title = __('Classifieds', 'osclass-classifieds');
        // get home title
        $a = array(
            'name'        => 'osclass-classifieds',
            'post_type'   => 'page',
            'post_status' => 'publish'
        );
        $my_posts = get_posts($a);
        if( $my_posts ) {
            $web_title = get_the_title($my_posts[0]->ID);
        }
        $body = '<div>';
        $body .= '<p>Hi admin</p>';
        $body .= '<p>You\'re receiving this email because a new user has been created at <a href="'.osclass_oc_home_url().'">'.$web_title.'</a>.</p>';
        $body .= '<p>User details:</p>';
        $body .= '<p>';
        $body .= 'Name: '.$user['s_name'].'<br>';
        $body .= 'Email: '.$user['s_email'].'<br>';
        $body .= '</p>';
        $body .= '<p>Regards</p>';
        $body .= '</div>';

        $admin_email = get_option( 'admin_email' );
        return osclass_oc_send_mail($admin_email, 'A new user has registered', $body);
    }
}

/**
 * Send user welcome notification
 */
function osclass_oc_send_user_welcome_notification($to_email) {
    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  $to_email,
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);
    if($user_response['success']) {
        $user =  $user_response['user'];
        $web_title = __('Classifieds', 'osclass-classifieds');
        // get home title
        $a = array(
            'name'        => 'osclass-classifieds',
            'post_type'   => 'page',
            'post_status' => 'publish'
        );
        $my_posts = get_posts($a);
        if( $my_posts ) {
            $web_title = get_the_title($my_posts[0]->ID);
        }
        $body = '<div>';
        $body .= '<p>Hi '.$user['s_name'].'</p>';
        $body .= '<p>You\'ve successfully registered for <a href="'.osclass_oc_home_url().'">'.$web_title.'</a>.</p>';
        $body .= '<p>Thank you!</p>';
        $body .= '<p>Regards</p>';
        $body .= '</div>';
        return osclass_oc_send_mail($to_email, 'Registration successful!', $body);
    }
}

/**
 *
 */
function osclass_oc_send_user_forgot_password($to_email, $forgot_url) {
    $endpoint = osclass_oc_api_endpoint().'admin/users/byemail';
    $data = array( 'json' => array(
            'email' =>  $to_email,
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $user_response = json_decode($response->getBody(), true);
    if($user_response['success']) {
        $user =  $user_response['user'];

        $body  = '<p>Hi '.$user['s_name'].'</p>';
        $body .= "<p>We've sent you this e-mail because you've requested a password reminder. Follow this link to recover it: <a href=\"$forgot_url\">Change password</a></p>";
        $body .= "<p>The link will be deactivated in 24 hours.</p>";
        $body .= "<p>If you didn't request a password reminder, please ignore this message. This request was made from IP ".osclass_oc_get_ip()." on ".date("Y-m-d H:m:i")."</p>";
        $body .= "<p>Regards,</p>";

        return osclass_oc_send_mail($to_email, 'Recover your password', $body);
    }
    return false;
}

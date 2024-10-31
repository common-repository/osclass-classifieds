<?php
use \Firebase\JWT\JWT;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

function osclass_oc_is_web_user_logged_in() {
    if(isset($_SESSION['userId']) && isset($_SESSION['userId'])!='') {
        return true;
    }
    return false;
}

function osclass_oc_logged_user_id() {
    return isset($_SESSION['userId']) ? $_SESSION['userId'] : '';
}

function osclass_oc_loggin_user($email, $password) {
    $endpoint = osclass_oc_api_endpoint().'login';
    $data = array( 'json' => array(
            'email' =>  $email,
            'password' => $password
            )
        );

    $client = new GuzzleHttp\Client();
    try {
        $response = $client->request('POST', $endpoint, $data);
        $login_response = json_decode($response->getBody(), true);

        if(isset($login_response['resp']['jwt'])) {
            // destroy session
            do_action('osclass_oc_login');
            // osclass_oc_check_token($login_response['resp']['jwt']);
            osclass_oc_set_info_token($login_response['resp']['jwt']);
        }
        return $login_response;
    } catch (RequestException $e) {
    }
    return false;
}

/**
 * returns true if token is valid, false otherwise
 */
function osclass_oc_check_token($jwt='') {
    if($jwt==='') {
        $jwt = osclass_oc_get_jwt();
    }
    try {
        $secret = base64_decode(strtr(OSCLASS_OC_SECRET, '-_', '+/'));
        JWT::$leeway = 60; // $leeway in seconds
        $decoded = JWT::decode($jwt, $secret, array(OSCLASS_OC_ALGORITHM));
        return true;
    } catch(Exception $e) {
        if(isset($_SESSION['isLogged'])) {
            do_action('osclass_oc_logout');
        }
        return false;
    }
}

function osclass_oc_set_info_token($jwt='') {
    if($jwt==='') {
        $jwt = osclass_oc_get_jwt();
    }
    try {
        $secret = base64_decode(strtr(OSCLASS_OC_SECRET, '-_', '+/'));
        JWT::$leeway = 60; // $leeway in seconds
        $decoded = JWT::decode($jwt, $secret, array(OSCLASS_OC_ALGORITHM));
        $_SESSION['isLogged'] = true;
        $_SESSION['jwttoken'] = $jwt;
        $_SESSION['userId'] = $decoded->data->id;
        $_SESSION['userEmail'] = $decoded->data->email;
        return true;
    } catch(Exception $e) {
        if(isset($_SESSION['isLogged'])) {
            do_action('osclass_oc_logout');
        }
        return false;
    }
}
function osclass_oc_get_jwt() {
    return isset($_SESSION['jwttoken']) ?  $_SESSION['jwttoken'] : '';
}
function osclass_oc_get_logged_user_email() {
    return isset($_SESSION['userEmail']) ?  $_SESSION['userEmail'] : '';
}
function osclass_oc_set_logged_user_email($email) {
    $_SESSION['userEmail'] = $email;
}
function osclass_oc_get_logged_user_id() {
    return isset($_SESSION['userId']) ?  $_SESSION['userId'] : '';
}

function osclass_oc_get_home_title() {
    $web_title = _e('Classifieds', 'osclass-classifieds');
    // get home title
    $a = array(
        'name'        => 'osclass-classifieds',
        'post_type'   => 'page',
        'post_status' => 'publish'
    );
    $my_posts = get_posts($a);
    if( $my_posts ) {
        $web_title = $my_posts[0]->post_title;
    }
    return $web_title;
}

function osclass_oc_get_item_by_id($item_id) {
    $item = array();
    $endpoint = osclass_oc_api_endpoint().'admin/items/'.$item_id;
    $data = array( 'json' => array(
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password()
            )
        );

    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $item_response = json_decode($response->getBody(), true);
    if($item_response['success']) {
        $item = $item_response['item'];
    }
    return $item;
}

function osclass_oc_current_url() {
    return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function osclass_oc_params_blacklist() {
    // we don't need all this in our URLs, do we?
    return array(
        'action2', 'action', // action and bulk actions
        'selected', // selected rows for bulk actions
        '_wpnonce',
        '_wp_http_referer'
    );
}

class OscUrlHelper {
    var $params;
    public function __construct() {
        wp_parse_str($_SERVER['QUERY_STRING'], $_params);
        $blacklist = osclass_oc_params_blacklist();
        $this->params = array_diff_key($_params, array_combine($blacklist, $blacklist));
    }

    public function url($params, $base = false) {
        $blacklist = osclass_oc_params_blacklist();
        $params = array_merge($this->params, $params);

        $url = remove_query_arg($blacklist, $base ? $base : osclass_oc_current_url());
        $url = add_query_arg( urlencode_deep( $params ), $url );

        return $url;
    }
}

function osclass_oc_get_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}


function osclass_oc_show_listing($item) {
    global $wp_query;
    $post = $wp_query->get_queried_object();
    if ( isset($post->post_name) ) {
        $pagename = $post->post_name;
    }
    $image = isset($item['resources'][0]) ? $item['resources'][0]['thumbnail'] : plugins_url('public/assets/images/no_photo.gif', OSCLASS_OC_DIR .'/public/');  ?>
    <div class="osc-listing" style="">
        <div style="float: left;width: 100px;height: 84px;position: absolute;top: 50%;margin-top: -42px;background: whitesmoke;">
            <a class="osc-listing-primary-image-listing-link" href="<?php echo osclass_oc_item_url($item['pk_i_id']); ?>">
            <img alt="" src="<?php echo $image; ?>" style="width: 100px;display: block;margin: 0 auto;transition: all .2s ease-in-out;opacity: 1;cursor: pointer;box-shadow: none !important;border-radius: 0px !important;">
            </a>
        </div>
        <div style="margin-left: 110px; clear: none;">
            <span style="display: block;overflow: hidden; text-decoration: none;color: #21759b;height: auto; font-weight: bold;"><?php echo $item['s_title']; ?></span>
            <?php if($pagename!='osc-my-account') { ?>
            <a href="<?php echo osclass_oc_item_url($item['pk_i_id']); ?>" title="<?php echo esc_html($item['s_title']); ?>" class="osc-link-wrap" style="overflow: initial; height: auto;color: transparent;"></a>
            <?php } ?>
            <?php echo wp_trim_words($item['s_description'], osclass_oc_words_in_excerpt()); ?>
        </div>
        <div class="listing-info">
        <?php if(osclass_oc_is_web_user_logged_in() && $pagename=='osc-my-account') { ?>
            <a href="<?php echo osclass_oc_item_edit_url($item['pk_i_id']); ?>"><?php _e('Edit', 'osclass-classifieds'); ?></a>&nbsp;
            <a onclick="javascript:return confirm('<?php echo esc_js(__('This action can not be undone. Are you sure you want to continue?', 'osclass-classifieds')); ?>')" href="<?php echo osclass_oc_item_delete_url($item['pk_i_id'], $item['s_secret']);?>" ><?php _e('Delete', 'osclass-classifieds'); ?></a>
        <?php } else { 
            $item_location_html = isset($item['s_country']) ? '&nbsp;|&nbsp;<i class="fa fa-map-marker" aria-hidden="true"></i>&nbsp;'.$item['s_country'] : '';
            ?>
            <span class="listing-date"><?php echo date_format(date_create($item['dt_pub_date']), 'M d, Y'); ?> <?php echo $item_location_html; ?></span>
        <?php } ?>
            <div class="listing-price"><?php echo $item['formated_price']; ?></div>
        </div>
    </div>
<?php }


function osclass_oc_redirect($url = false)
  {
    if(headers_sent())
    {
      $destination = ($url == false ? 'location.reload();' : 'window.location.href="' . $url . '";');
      echo die('<script>' . $destination . '</script>');
    }
    else
    {
      $destination = ($url == false ? $_SERVER['REQUEST_URI'] : $url);
      header('Location: ' . $destination);
      die();
    }
  }

  function osclass_oc_set_fm_ok($fm) {
      $_SESSION['front_fm_ok'] = $fm;
  }
  function osclass_oc_set_fm_error($fm) {
      $_SESSION['front_fm_error'] = $fm;
  }
  function osclass_oc_get_fm_ok() {
      $r = false;
      if(isset($_SESSION['front_fm_ok']) ) {
        $r = $_SESSION['front_fm_ok'];
        unset($_SESSION['front_fm_ok']);
      }
      return $r;
  }
  function osclass_oc_get_fm_error() {
      $r = false;
      if(isset($_SESSION['front_fm_error']) ) {
        $r = $_SESSION['front_fm_error'];
        unset($_SESSION['front_fm_error']);
      }
      return $r;
  }
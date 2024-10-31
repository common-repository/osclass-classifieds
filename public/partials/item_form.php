<?php
$fm_ok = $fm_error = $error = false;

$action = isset($args['action']) ? 'edit' : 'add';
$item_id = get_query_var('item_id', null);

// if POST form
if ( ! empty( $_POST ) && isset($_POST['action']) ) {

    $endpoint = osclass_oc_api_endpoint().'items';
    if($_POST['action'] == 'edit' && $item_id!==null && $item_id>0) {
      $endpoint = osclass_oc_api_endpoint().'items/'.$item_id;
    }
    $post = array();
    foreach($_POST as $key => $value) {
      if(!is_array($value)) {
        $post[] = array('name' => $key, 'contents' => $value);
      } else {
        foreach($value as $_key => $_value) {
          $newkey = $key."[".$_key."]";
          $post[] = array('name' => $newkey, 'contents' => $_value);

        }
      }
    }
    $post[] = array('name' => 'jwttoken', 'contents' => osclass_oc_get_jwt());
    $data = array(
        'multipart' => $post
    );
    $client = new GuzzleHttp\Client();
    $response = $client->request('POST', $endpoint, $data);
    $itemform_response = json_decode($response->getBody(), true);
    if($itemform_response['success']) {
      
      if($action=='add') {
        if(isset($itemform_response['id'])) {
          $item_id = $itemform_response['id'];
        }
      }

      $files = array();
      if(isset($item_id)) {
        if(isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
          for($i = 0; $i < count($_FILES['file']['name']); $i++ ) {
            if($_FILES['file']['name'][$i]!='') {
              $files[] = array(
                'name' => 'file[]',
                'contents' => fopen($_FILES['file']['tmp_name'][$i], 'r')
              );
            }
          }
        }

        if(is_array($files) && count($files)>0 ) {
          $files[] = array('name' => 'item_id', 'contents' => $itemform_response['id']);
          $files[] = array('name' => 'jwttoken', 'contents' => osclass_oc_get_jwt());

          $data = array(
              'multipart' => $files
          );
          $endpoint = osclass_oc_api_endpoint().'resources';
          $client = new GuzzleHttp\Client();
          $response = $client->request('POST', $endpoint, $data);
          $resources_response = json_decode($response->getBody(), true);
        }
        // send email to user - new listing created
        if(osclass_oc_get_send_user_ad_created_notification()) {
          osclass_oc_send_user_ad_created_notification(osclass_oc_get_logged_user_email());
        }
        // send email to admin - new listing created
        if(osclass_oc_get_send_admin_ad_created_notification()) {
          osclass_oc_send_admin_ad_created_notification($itemform_response['id']);
        }

        // send validation email
        if(isset($itemform_response['action']) && $itemform_response['action']=='email_item_validation') {
          osclass_oc_send_email_item_validation(osclass_oc_get_logged_user_email(), $itemform_response['id']);
        }
        osclass_oc_set_fm_ok(__($itemform_response['msg'], 'osclass-classifieds'));
        osclass_oc_redirect( osclass_oc_my_account_url() );
      }
    } else {
      $fm_error = nl2br($itemform_response['msg']);
    }
}

// load categories API REQUEST
$endpoint = osclass_oc_api_endpoint().'categories';
$client = new GuzzleHttp\Client();
$res = $client->request('GET', $endpoint, []);
$categories = json_decode($res->getBody(), true);

// load countries API REQUEST
$endpoint = osclass_oc_api_endpoint().'countries';
$client = new GuzzleHttp\Client();
$res = $client->request('GET', $endpoint, []);
$countries = json_decode($res->getBody(), true);
$regions = array();
$cities = array();

if(count($countries)==1) {
  // fk_c_country_code is a string (US, SP, FR, ... )
  $item['fk_c_country_code'] = sanitize_text_field($countries[0]['id']);
}
if(isset($_REQUEST['catId']) && is_numeric($_REQUEST['catId']) ) {
  $item['fk_i_category_id'] = (int)$_REQUEST['catId'];
}
if(isset($_REQUEST['title']['en_US'])) {
  $item['s_title'] = sanitize_text_field($_REQUEST['title']['en_US']);
}
if(isset($_REQUEST['description']['en_US'])) {
  $item['s_description'] = sanitize_text_field($_REQUEST['description']['en_US']);
}
if(isset($_REQUEST['price']) && preg_match('/[0-0|\.|,]/', $_REQUEST['price']) ) {
  $_price = (float)$_REQUEST['price'];
  $item['i_price'] = (int)$_price*1000000;
}
if(isset($_REQUEST['countryId']) && is_numeric($_REQUEST['countryId']) ) {
  $item['fk_c_country_code'] = (int)$_REQUEST['countryId'];
}
if(isset($_REQUEST['regionId']) && is_numeric($_REQUEST['regionId'])) {
  $item['fk_i_region_id'] = (int)$_REQUEST['regionId'];
}
if(isset($_REQUEST['cityId']) && is_numeric($_REQUEST['cityId'])) {
  $item['fk_i_city_id'] = (int)$_REQUEST['cityId'];
}

$resources_count = 0;
/*
 * UPDATE ITEM
 */
if($item_id!==null && $item_id!='') {
  // check if item author is logged
  $item = osclass_oc_get_item_by_id($item_id);
  $resources_count = isset($item['resources']) ? count($item['resources']) : 0;
  if(!isset($item['fk_i_user_id']) ){
    echo "Listing does not exist";
    return;
  }
  if($item['fk_i_user_id']!=osclass_oc_logged_user_id()) {
    echo "Not your listing";
    return;
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
<?php if($action == 'add') { ?>
<h1><?php _e('Add Item', 'osclass-classifieds'); ?></h1>
<?php } else { ?>
<h1><?php _e('Edit Item', 'osclass-classifieds'); ?></h1>
<?php } ?>
<form id="new" name="new" method="POST" class="osclass-item-form" role="form" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>"/>
      <?php if(isset($item['s_secret'])) { ?>
      <input type="hidden" name="secret" value="<?php echo esc_attr($item['s_secret']); ?>"/>
      <?php } ?>
      <h3><?php _e('Basic information', 'osclass-classifieds'); ?></h3>
      <div class="uk-margin"><!--category-->
        <label class="uk-form-label" for="catId"><?php _e('Category', 'osclass-classifieds'); ?></label>
        <div class="uk-form-controls">
          <?php osclass_oc_category_select($categories, $item); ?>
        </div>
      </div>
      <div class="uk-margin"><!--title-->
        <label class="uk-form-label" for="title"><?php _e('Title', 'osclass-classifieds'); ?> (<span class="count-title"><?php echo osclass_oc_max_characters_per_title(); ?></span> <?php _e('characters remaining', 'osclass-classifieds'); ?>)</label>
        <div class="uk-form-controls">
          <input type="text" name="title[en_US]" placeholder="Iphone 6, ..." class="uk-input uk-input-title" value="<?php echo (isset($item['s_title'])) ? esc_attr($item['s_title']): ''; ?>"/>
        </div>
      </div>
      <div class="uk-margin"><!--description-->
        <label class="uk-form-label" for="description"><?php _e('Description', 'osclass-classifieds'); ?> (<span class="count-description"><?php echo osclass_oc_max_characters_per_description(); ?></span> <?php _e('characters remaining', 'osclass-classifieds'); ?>)</label>
        <div class="uk-form-controls">
          <textarea name="description[en_US]" class="uk-textarea uk-textarea-description"><?php echo (isset($item['s_description'])) ? esc_html($item['s_description']): '';?></textarea>
        </div>
      </div>
      <div class="uk-margin"><!--price-->
        <label class="uk-form-label" for="price"><?php _e('Price', 'osclass-classifieds'); ?></label>
        <div class="uk-form-controls">
          <input type="text" name="price" class="uk-input" value="<?php echo (isset($item['i_price']) && $item['i_price']>0) ? ($item['i_price']/1000000): ''; ?>"/>
        </div>
      </div>
      <h3><?php _e('Location information', 'osclass-classifieds'); ?></h3>
      <div class="uk-margin"><!--country-->
        <label class="uk-form-label" for="countryId"><?php _e('Country', 'osclass-classifieds'); ?></label>
        <div class="uk-form-controls">
          <?php if(count($countries)==1) { ?>
            <input v-model="countryId" name="countryId" type="hidden" value="<?php echo esc_attr($countries[0]['id']); ?>"/> 
            <?php echo esc_html($countries[0]['name']); ?>
          <?php } else { ?>
          <select id="countryId" name="countryId" v-model="countryId" class="uk-select">
            <option value=""><?php _e('Country', 'osclass-classifieds'); ?></option>
            <?php foreach($countries as $country) { ?>
              <option value="<?php echo esc_attr($country['id']); ?>"><?php echo esc_html($country['name']); ?></option>
            <?php } ?>
          </select>
          <?php } ?>
        </div>
      </div>
      <div class="uk-margin"><!--region-->
        <label class="uk-form-label" for="regionId"><?php _e('Region', 'osclass-classifieds'); ?></label>
        <div class="uk-form-controls">
          <select id="regionId" name="regionId" v-model="regionId" class="uk-select">
            <option value=""><?php _e('Region', 'osclass-classifieds'); ?></option>
            <template v-for="region in regions">
              <option :value="region.id">{{ region.name }}</option>
            </template>
          </select>
        </div>
      </div>
      <div class="uk-margin"><!--city-->
        <label class="uk-form-label" for="regionId"><?php _e('City', 'osclass-classifieds'); ?></label>
        <div class="uk-form-controls">
          <select id="cityId" name="cityId" v-model="cityId" class="uk-select">
            <option value=""><?php _e('City', 'osclass-classifieds'); ?></option>
            <template v-for="city in cities">
              <option :value="city.id">{{ city.name }}</option>
            </template>
          </select>
        </div>
      </div>
      <h3><?php _e('Upload images', 'osclass-classifieds'); ?></h3>
        <div class="uk-margin">
          <div class="uk-form-custom">
            <?php if(!isset($item['resources'][0]['id']) ) { ?>
            <div class="uk-form-file uk-margin">
              <input type="file" name="file[]">
            </div>
            <?php } else { ?>
            <img id="resource_<?php echo esc_attr($item['resources'][0]['id']); ?>" src="<?php echo esc_attr($item['resources'][0]['thumbnail']); ?>"/>
            <input type="file" name="file[]" v-if="resourcesAvailable==1">
            <button id="btn_resource_<?php echo esc_attr($item['resources'][0]['id']); ?>" v-on:click.prevent="deleteResource(<?php echo esc_attr($item['pk_i_id']).', '.esc_attr($item['resources'][0]['id']).', \''.esc_js($item['resources'][0]['code']); ?>')"><?php _e('Remove image', 'osclass-classifieds'); ?></button>
            <?php } ?>
            <br/>
          </div>
        </div>
      <div>
        <button id="upload" type="submit"><?php _e('Submit', 'osclass-classifieds'); ?></button>
        <p><small><?php printf(__('By clicking Submit, I agree to the <a href="%s" target="_blank">Privacy Policy</a> and <a href="%s" target="_blank">Terms and conditions</a>.', 'osclass-classifieds'),
   'https://osclass.org/page/privacy-policy', 
   'https://osclass.org/page/legal-note' ); ?></small></p>
      </div>
    </form>

    <script>
        var app = new Vue({
            el: '#new',
            data: {
                resourcesAvailable: <?php echo osclass_oc_get_max_resources()-count($resources_count) ; ?>,
                countryId: '',
                regionId: '',
                regions: [],
                cityId: '',
                cities: []
            },
            watch: {
                countryId: function (c, aa) {
                this.getRegions(c)
                },
                regionId: function (r, aa) {
                this.getCities(r)
                }
            },
            created: function() {
                this.countryId = '<?php echo isset($item['fk_c_country_code']) ? esc_js($item['fk_c_country_code']): ''; ?>';
                this.regionId = '<?php echo isset($item['fk_i_region_id']) ? esc_js($item['fk_i_region_id']): ''; ?>';
                this.cityId = '<?php echo isset($item['fk_i_city_id']) ? esc_js($item['fk_i_city_id']): ''; ?>';
            },
            methods: {
                deleteResource (item_id, resource_id, code) {
                  if(confirm('<?php echo esc_js(__('This action cannot be undone, Are you sure that you want to delete the image?', 'osclass-classifieds')); ?>')) {
                    var _data = new FormData()
                    _data.append('jwttoken', '<?php echo osclass_oc_get_jwt(); ?>');

                    axios.post(`<?php echo osclass_oc_api_endpoint(); ?>items/deleteResource/` + item_id + '/' + resource_id + '/' + code, _data).then(response => {
                        if (response.data.success=='true') {
                          console.log("removed resource " + resource_id);
                          var resource = document.getElementById( 'resource_' + resource_id );
                          resource.parentNode.removeChild( resource );
                          var btn_resource = document.getElementById( 'btn_resource_' + resource_id );
                          btn_resource.parentNode.removeChild( btn_resource );

                          this.resourcesAvailable++;
                        } else {
                          console.log('none' + response);
                        }
                    })
                  }
                },
                getRegions (countryCode) {
                    console.log('<?php echo osclass_oc_api_endpoint(); ?>regions/' + countryCode);
                    axios.get(`<?php echo osclass_oc_api_endpoint(); ?>regions/` + countryCode).then(response => {
                        if (response.data.length > 0) {
                          this.regions = response.data
                        } else {
                          this.regions = []
                          this.regionId = ''
                        }
                    })
                },
                getCities (regionId) {
                    axios.get(`<?php echo osclass_oc_api_endpoint(); ?>cities/` + regionId).then(response => {
                    if (response.data.length > 0) {
                        this.cities = response.data
                    } else {
                        this.cities = []
                        this.cities = ''
                    }
                    })
                }
            }
        });

        (function( $ ) {
            // javascript code here. i.e.: $(document).ready( function(){} );
            $('body').on('focus keypress', 'input[name*="title"]', function (e) {
                var $this = $(this);
                var msgSpan = $this.parents('body').find('.count-title');
                var ml = <?php echo osclass_oc_max_characters_per_title(); ?>;
                var length = this.value.length;
                var msg = ml - length;
                msgSpan.css('color', 'inherit');
                if(msg<0) {
                    msgSpan.css('color', 'red');
                }
                msgSpan.html(msg);
            });
            $('body').on('focus keypress', 'textarea[name*="description"]', function (e) {
                var $this = $(this);
                var msgSpan = $this.parents('body').find('.count-description');
                var ml = <?php echo osclass_oc_max_characters_per_description(); ?>;
                var length = this.value.length;
                var msg = ml - length;
                msgSpan.css('color', 'inherit');
                if(msg<0) {
                    msgSpan.css('color', 'red');
                }
                msgSpan.html(msg);
            });
            $('input[name*="title"]').focus();
            $('textarea[name*="description"]').focus();


        })(jQuery);
</script>
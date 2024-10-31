<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// update_option('osclass_setttings', false);
$domain = "osclass.org";


$fm_ok = false;
$fm_error = false;
// create new site
$reponse  = array();
if(isset($_REQUEST['submit']) ) {
    check_admin_referer('osclass-classifieds-create-site');
    if(isset($_REQUEST['ho_country']) && preg_match('/^[A-Z]{2}$/', strtoupper(sanitize_text_field($_REQUEST['ho_country'])) ) ) {
        $country = sanitize_text_field($_REQUEST['ho_country']);
        $post_data = array(
            'email' => get_option('admin_email'),
            'password' => wp_generate_password(8),
            'site' => 'wpsite'.wp_generate_password(8, false, false),
            'country' => $country,
            'site_title' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'language' => 'en_US',
            'theme' => 'TH-BDRBLU',
            'plan' => "HO-FREE",
            'from' => get_home_url()
        );

        $url = 'https://'.$domain.'/hosted/ajax?paction=create_site';
        $response = wp_remote_post( $url, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'body' => $post_data,
            )
        );
        $json = $response['body']; 
        // parse
        preg_match('/(\{"error".*\})/', $json, $m);
        if(isset($m[0])) {
            $json = $m[0];
        }

        $ret = json_decode($json, true);
        if($ret['error']>0 && isset($ret['msg']) ) {
            $fm_error = $ret['msg'];

        }
        if($ret['error']==0 && isset($ret['msg']) ) {
            osclass_oc_set_api_settings(array(
                'osclass_api_url' => 'https://'.$post_data['site'].'.osclass.org/api/v1.0/',
                'osclass_api_admin_username' => $post_data['email'],
                'osclass_api_admin_password' => $post_data['password'],
                )
            );
            $fm_ok = sprintf(__("We are creating your classifieds page, please validate your account in the e-mail that we have sent to %s", "osclass-classifieds"),
                get_option( 'admin_email' ) 
            );
        }
    } else {
        $fm_error = __("Country is required.", "osclass-classifieds");
    }


}

$ho_countries = array();
$countries_url = 'http://geo.osclass.org/newgeo.services.php?action=countries';
$countries_response = wp_remote_get($countries_url,  
    array(
        'timeout'     => 20 
    )
);

$ho_countries = array();
if ( is_array( $countries_response ) ) {
    $ho_countries = json_decode( substr($countries_response['body'], 1, -1), true);
}

?>
<style>
.osc-label {
    display: block;
    margin-bottom: 5px;
    color: #333333;
}
.osc-select {
    width: 300px;
}
.osc-controls {
    margin-bottom: .5em;
}
</style>

<div id="installapp" style="margin: 10px 20px 0 2px;">
    <h1 style="font-size: 23px;font-weight: 400;margin: 0;padding: 9px 0 4px;line-height: 29px; padding-bottom: 12px;">Osclass classifieds installation</h1>

    <template v-if="fmerror">
        <div class="uk-alert uk-alert-danger" >
            <span v-html="fmerror"></span>
        </div>
        <div class="postbox">
            <div class="inside">
                <p><?php _e('If your problem persist, feel free to contact us.', 'osclass-classifieds'); ?></p>
                <p><?php printf(__('<b>Include the email address used on the message (%s).</b>', 'osclass-classifieds'), get_option('admin_email')); ?></p>
                <p><?php printf(__('Just follow the link <a href="%s" target="_blank">contact</a>', 'osclass-classifieds'), 'https://osclass.org/contact'); ?></p>
            </div>
        </div>
    </template>

    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <h3 class="stuffbox"><span class="">{{ title }}</span></h3>
                <?php if(is_array($ho_countries) && count($ho_countries) ) { ?>
                <div class="inside">
                    <template v-if=" fmok !== '' ">
                    <div class="uk-alert uk-alert-success"  style="font-size: 1.2em;">{{ fmok }}</div>
                    <p><a href="?page=osclass-admin" class="button button-primary button-hero">Continue</a></p>
                    </template>
                    <form v-if=" fmok === '' " id="ho_form_10" action="<?php echo admin_url("admin.php"); ?>" v-on:submit="submitForm">
                        <input type="hidden" name="page" value="osclass-admin"/>
                        <input type="hidden" name="submit" value="1"/>
                        <input type="hidden" name="install" value="1"/>
                        <?php wp_nonce_field('osclass-classifieds-create-site'); ?>
                        <div>
                            <p style="font-size: 16px; font-weight: 300; line-height: 23px;"><?php _e('Please, tell us which country you want to use for your classifieds page.', 'osclass-classifieds'); ?></p>
                            <p style="font-weight: 500; line-height: 23px;"><?php _e('This information can not be changed once installed.', 'osclass-classifieds'); ?></p>
                            <p>
                                <select v-model="countryId" name="ho_country" id="ho_country" class="osc-select">
                                    <option value=""><?php _e('Select a country', 'hostedorg'); ?></option>
                                    <?php foreach($ho_countries as $c) {
                                        echo '<option value="' . $c['code'] . '">' . $c['s_name'] . '</option>';
                                    } ?>
                                </select>
                            </p>
                        </div>
                        <div class='clear'></div>
                        <div class="control-group center">
                            <div class="controls">
                                <button v-bind:disabled="!isValid" id="ho_continue_10" type="submit" class="button button-primary button-hero"><?php _e('CREATE A FREE SITE', 'hostedorg'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php } else { ?>
                <div class="inside">
                    <div class="uk-alert uk-alert-success"  style="font-size: 1.2em;"><?php _e('Timeout, please reload the page.'); ?></div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
    var app = new Vue({
        el: '#installapp',
        data: {
            title: '<?php echo ($fm_ok!==false) ? esc_js(__("Successfully installed", "osclass-classifieds")) : esc_js(__('Install - Select country location', 'osclass-classifieds')); ?>',
            countryId: '',
            regionId: '',
            regions: [],
            cityId: '',
            cities: [],
            fmerror: '<?php echo ($fm_error!==false) ? $fm_error : ''; ?>',
            fmok: '<?php echo ($fm_ok!==false) ? $fm_ok : ''; ?>'
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
        },
        computed: {
          isValid: function () {
            return this.countryId != ''
          }
        },
        methods: {
            getRegions (countryCode) {
                var url = "https://geo.osclass.org/newgeo.services.php?callback=?&action=regions&country="+countryCode;
                that = this;
                JSONP({
                    url: url,
                    success: function(data) {
                        if (data.length > 0) {
                            that.regions = data
                        } else {
                            that.regions = []
                            that.regionId = ''
                        }
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });
            },
            getCities (regionId) {
                var url = "https://geo.osclass.org/newgeo.services.php?callback=?&action=cities&region="+regionId;
                that = this;
                JSONP({
                    url: url,
                    success: function(data) {
                        if (data.length > 0) {
                            that.cities = data
                        } else {
                            that.cities = []
                            that.cityId = ''
                        }
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });
            },
            submitForm (e) {
                if(this.countryId != '') {
                    this.fmerror = '';
                } else {
                    e.preventDefault();
                    this.fmerror = 'Select that country you want to use for your classifieds page.';
                }
            }
        }
    });
</script>
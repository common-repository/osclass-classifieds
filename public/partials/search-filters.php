<?php
$error = false;
try {
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
} catch(Exception $e) {
    $error = $e->getMessage();
}

if($error!==false) { ?>
<div class="uk-alert uk-alert-danger"><?php echo $error; ?></div>
<?php return; 
} ?>


<?php // load header actions
load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>


<h1><?php _e('Search ads', 'osclass-classifieds'); ?></h1>

<form method="get" id="search">
<input type="hidden" name="page_id" value="<?php echo osclass_oc_search_url_id(); ?>"/>
  <div class="uk-margin">
    <label class="uk-form-label" for="pattern"><?php _e('Your search', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <input name="pattern" type="text" class="osc-100" placeholder=""/>
    </div>
  </div>

  <div class="uk-margin">
    <label class="uk-form-label" for="password"><?php _e('Category', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
      <?php osclass_oc_category_select($categories); ?>
    </div>
  </div>

  <div class="uk-margin"><!--price-->
    <label class="uk-form-label" for="price"><?php _e('Price', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
        <div class="price_range"><?php _e('Min', 'osclass-classifieds'); ?><input type="text" name="min_price" class="uk-input"/></div>
        <div class="price_range"><?php _e('Max', 'osclass-classifieds'); ?><input type="text" name="max_price" class="uk-input"/></div>
    </div>
  </div>

  <div class="uk-margin"><!--country-->
    <label class="uk-form-label" for="countryId"><?php _e('Country', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
        <select id="countryId" name="countryId" v-model="countryId" class="uk-select uk-100">
        <option value=""><?php _e('Country', 'osclass-classifieds'); ?></option>
        <?php foreach($countries as $country) { ?>
            <option value="<?php echo $country['id'] ?>"><?php echo $country['name'] ?></option>
        <?php } ?>
        </select>
    </div>
    </div>
    <div class="uk-margin"><!--region-->
    <label class="uk-form-label" for="regionId"><?php _e('Region', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
        <select id="regionId" name="regionId" v-model="regionId" class="uk-select uk-100">
        <option value=""><?php _e('Region', 'osclass-classifieds'); ?></option>
        <template v-for="region in regions">
            <option :value="region.id">{{ region.name }}</option>
        </template>
        </select>
    </div>
    </div>
    <div class="uk-margin"><!--city-->
    <label class="uk-form-label" for="cityId"><?php _e('City', 'osclass-classifieds'); ?></label>
    <div class="uk-form-controls">
        <select id="cityId" name="cityId" v-model="cityId" class="uk-select uk-100">
        <option value=""><?php _e('City', 'osclass-classifieds'); ?></option>
        <template v-for="city in cities">
            <option :value="city.id">{{ city.name }}</option>
        </template>
        </select>
    </div>
  </div>

  <div>
    <p><button type="submit" class="uk-button"><?php _e('Search', 'osclass-classifieds'); ?></button></p>
  </div>
</form>

<script>
    var app = new Vue({
        el: '#search',
        data: {
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
        methods: {
            getRegions (countryCode) {
                // @todo  FIX api url 
                axios.get(`http://docker.local:80/api/v1.0/regions/` + countryCode).then(response => {
                    if (response.data.length > 0) {
                    this.regions = response.data
                    } else {
                    this.regions = []
                    this.regionId = ''
                    }
                })
            },
            getCities (regionId) {
                axios.get(`http://docker.local/api/v1.0/cities/` + regionId).then(response => {
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
</script>
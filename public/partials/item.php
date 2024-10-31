<?php
$fm_error = osclass_oc_get_fm_error();
$item       = array();
$item_id    = get_query_var('item_id', 0);
if(is_numeric($item_id) && $item_id>0) {
    // create endpoint ----------
    $endpoint = osclass_oc_api_endpoint().'items/'.$item_id;
    $client = new GuzzleHttp\Client();

    $res    = $client->request('GET', $endpoint);
    $result = json_decode($res->getBody(), true);
    if(isset($result[0]) ) {
        $item = $result[0];
    } 
}

?>
<?php if(isset($item['resources']) && count($item['resources']) <= 1) { ?> 
<style>
    .bx-wrapper {
        margin-bottom:0px;
    }
</style>
<?php } ?>
<div class="osc-item">
    <?php // load header actions
    load_template( OSCLASS_OC_DIR. '/public/partials/actions.php' ); ?>

    <?php if($fm_error!==false) { ?>
    <div class="uk-alert uk-alert-danger"><?php echo $fm_error; ?></div>
    <?php } ?>

    <?php if($item==array()) { ?>
    <div class="uk-alert uk-alert-danger">
        <?php _e('The listing your are looking for does not exist', 'osclass-classifieds'); ?>
    </div>
    <?php } else { ?>

    <h1><?php echo esc_html($item['s_title']); ?></h1>

    <?php if(isset($item['resources'])) { ?>
    <div class="bx-wrapper photos">
        <ul class="bxslider" style="height:250px;">
            <?php foreach($item['resources'] as $image) { ?>
            <li>
                <img width="100%" src="<?php echo $image['preview']; ?>" alt="<?php echo esc_attr( $item['s_title'] );?>"/>
            </li>
            <?php } ?>
        </ul>
        <?php if(count($item['resources']) > 1) { ?>
        <div id="bx-pager" class="text-left">
            <?php foreach($item['resources'] as $key => $image) { ?>
            <a class="bx-pager-item" data-slide-index="<?php echo $key; ?>" href=""><img src="<?php echo $image['thumbnail']; ?>" /></a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <?php } ?>
    <div>
        <div class="info">
            <?php $no_image_url = plugin_dir_url(OSCLASS_OC_DIR). 'osclass-classifieds/public/images/user_default.gif'; ?>
            <?php $no_image_url = 'https://osclass.org/oc-content/themes/osclass_org/images/user_default.gif'; ?>
            <div class="avatar"><img src="http://www.gravatar.com/avatar/<?php echo md5( strtolower( trim( $item['s_contact_email'] ) ) ); ?>?s=80&d=<?php echo $no_image_url; ?>" /></div>
            <div class="by"><?php _e('by', 'osclass-classfieds'); ?>&nbsp;<b><?php echo esc_html($item['s_contact_name']); ?></b></div>
            <div class="pub-date"><?php _e('Published:', 'osclass-classfieds'); ?>&nbsp;<b><?php echo esc_html($item['dt_pub_date']); ?></b></div>
            <div class="price">
                <span><?php echo esc_html($item['formated_price']); ?></span>
            </div>
        </div>
        <div class="clear"></div>
        <?php if(isset($item['s_category_name'])) { ?>
        <div class="meta">
            <label><?php _e('Category', 'osclass-classifieds'); ?></label>
            <a href="<?php echo osclass_oc_search_url(array('catId' => $item['fk_i_category_id'])); ?>" class=""><?php echo esc_html($item['s_category_name']); ?></a>
        </div>
        <?php } ?>
        <?php if(isset($item['s_country']) && $item['s_country']!='') { ?>
        <div class="meta meta-last">
            <label><?php _e('Location', 'osclass-classifieds'); ?></label>
            <span><?php echo esc_html($item['s_country']);  ?><?php echo ($item['s_region']!='') ? esc_html(', '.$item['s_region']) : ''; ?></span>
        </div>
        <?php } ?>
    </div>
    <div class="item-description">
        <?php echo esc_attr(@$item['s_description']); ?>
    </div>
    <br>
    <button onclick="location.href='<?php echo osclass_oc_contact_item_url($item['pk_i_id']); ?>'"><?php _e('Contact seller', 'osclass-classifieds'); ?></button>
    </form>
</div>

<script>
    var bxSlider;
    jQuery(document).ready(function($) {
        bxSlider = $('.bxslider').bxSlider({
            pagerCustom: '#bx-pager',
            responsive : true,
            adaptiveHeight: true,
            mode: 'fade'
        });
    });
</script>
<?php } ?>
<?php

function osclass_oc_get_max_resources() {
    return 1;
}
/*  Api  */
function osclass_oc_api_endpoint() {
    return get_option('osclass_settings')['osclass_api_url'];
}

function osclass_oc_api_admin_username() {
    return get_option('osclass_settings')['osclass_api_admin_username'];
}

function osclass_oc_api_admin_password() {
    return get_option('osclass_settings')['osclass_api_admin_password'];
}

function osclass_oc_set_api_settings($array) {
    update_option('osclass_settings', $array);
}

/*  Categories  */
function osclass_oc_show_category_count() {
    $show_category_count = get_option('osclass_categories')['osclass_categories_show_count'];
    return $show_category_count;
}

function osclass_oc_show_empty_categories() {
    $hide_empty_categories = isset(get_option('osclass_categories')['osclass_hide_empty_categories']) ? false: true;
    return $hide_empty_categories;
}

/*  Listings  */
function osclass_oc_reg_user_can_contact() {
    $reg_user_can_contact = isset(get_option('osclass_listings')['reg_user_can_contact']) ? true: false;
    return $reg_user_can_contact;
}

function osclass_oc_words_in_excerpt() {
    // return get_option('osclass_listings')['words_in_excerpt'];
    $words_in_excerpt = get_option('osclass_listings')['words_in_excerpt'];
    if(is_numeric($words_in_excerpt) && $words_in_excerpt>0) {
        return $words_in_excerpt;
    }
    return 15;
}

// function osclass_oc_order_ad_listings() {
//     return get_option('osclass_listings')['order_ad_listings'];
// }

function osclass_oc_items_per_page() {
    $itemsperpage = get_option('osclass_listings')['items_per_page'];
    if(is_numeric($itemsperpage) && $itemsperpage>0) {
        return $itemsperpage;
    }
    return 10;
}

function osclass_oc_show_ad_views() {
    return isset(get_option('osclass_listings')['show_ad_views']) ? true: false;
}

/*  Notification  */
function osclass_oc_get_send_admin_ad_created_notification() {
    return isset(get_option('osclass_notifications')['send_admin_ad_created_notification']) ? true: false;
}

function osclass_oc_get_send_user_ad_created_notification() {
    return isset(get_option('osclass_notifications')['send_user_ad_created_notification']) ? true: false;
}

function osclass_oc_get_send_admin_user_created_notification() {
    return isset(get_option('osclass_notifications')['send_admin_user_created_notification']) ? true: false;
}

function osclass_oc_get_send_user_welcome_notification() {
    return isset(get_option('osclass_notifications')['send_user_welcome_notification']) ? true: false;
}

/*  more  */
function osclass_oc_max_characters_per_title() {
    return 100;
}

function osclass_oc_max_characters_per_description() {
    return 500;
}
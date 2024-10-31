<?php


function osclass_oc_search_url($args = array()) {
    return osclass_oc_get_page_by_slug('osc-search', $args);
}
function osclass_oc_search_url_id() {
    return osclass_oc_get_page_id_by_slug('osc-search');
}

function osclass_oc_search_filter_url($args = array()) {
    return osclass_oc_get_page_by_slug('osc-advanced-search', $args);
}

function osclass_oc_item_url($id = null) {
    return osclass_oc_get_page_by_slug('osc-item', array('item_id' => $id));
}

function osclass_oc_item_new_url() {
    return osclass_oc_get_page_by_slug('osc-item-form');
}

function osclass_oc_categories_url() {
    return osclass_oc_get_page_by_slug('osc-categories');
}

function osclass_oc_item_edit_url($id = null) {
    return osclass_oc_get_page_by_slug('osc-item-form-edit', array('item_id' => $id));
}

function osclass_oc_my_account_url() {
    return osclass_oc_get_page_by_slug('osc-my-account');
}

function osclass_oc_home_url() {
    return osclass_oc_get_page_by_slug('osc-classifieds');
}

function osclass_oc_forgot_password_url() {
    return osclass_oc_get_page_by_slug('osc-forgot', array(
        'userId' => '{userId}',
        'code' => '{code}'
        ));
}

function osclass_oc_change_user_email_confirm_url() {
    return osclass_oc_get_page_by_slug('osc-change-user-email-confirm', array(
        'userId' => '{userId}',
        'code' => '{code}'
    ));
}

function osclass_oc_user_activate_url($id, $secret) {
    return osclass_oc_get_page_by_slug('osc-user-activate', array(
        'user_id' => $id,
        'secret' => $secret
        ));
}

function osclass_oc_item_activate_url($id, $secret) {
    return osclass_oc_get_page_by_slug('osc-item-activate', array(
        'item_id' => $id,
        'secret' => $secret
        ));
}

function osclass_oc_item_delete_url($id, $secret) {
    return osclass_oc_get_page_by_slug('osc-item-delete', array(
        'item_id' => $id,
        'secret' => $secret
        ));
}

function osclass_oc_user_delete_url($id, $secret) {
    return osclass_oc_get_page_by_slug('osc-user-delete', array(
        'user_id' => $id,
        'secret' => $secret
        ));
}

function osclass_oc_contact_item_url($id = null) {
    if($id==null) {
        return osclass_oc_get_page_by_slug('osc-item-contact');
    }
    return osclass_oc_get_page_by_slug('osc-item-contact', array(
        'item_id' => $id
    ));
}

function osclass_oc_login_url() {
    return osclass_oc_get_page_by_slug('osc-login');
}

function osclass_oc_recover_url() {
    return osclass_oc_get_page_by_slug('osc-recover');
}

function osclass_oc_logout_url() {
    return osclass_oc_get_page_by_slug('osc-logout');
}

function osclass_oc_register_url() {
    return osclass_oc_get_page_by_slug('osc-register');
}

function osclass_oc_user_change_email_url() {
    return osclass_oc_get_page_by_slug('osc-user-change-email');
}

function osclass_oc_user_change_password_url() {
    return osclass_oc_get_page_by_slug('osc-user-change-password');
}

function osclass_oc_get_page_by_slug($the_slug, $args = array()) {
    $a = array(
    'name'        => $the_slug,
    'post_type'   => 'page',
    'post_status' => 'publish'
    );
    $my_posts = get_posts($a);
    if( $my_posts ) :
        $url = get_permalink($my_posts[0]->ID);
        if(build_query($args)!=='') {
            if ( get_option('permalink_structure') ) {
                return $url . '?' . build_query($args);
            } else {
                return $url . '&' . build_query($args);
            }
        }
        return $url;
    endif;
    return '';
}

function osclass_oc_get_page_id_by_slug($the_slug) {
    $a = array(
        'name'        => $the_slug,
        'post_type'   => 'page',
        'post_status' => 'publish'
        );
        $my_posts = get_posts($a);
        if( $my_posts ) :
            return $my_posts[0]->ID;
        endif;
        return '';
}
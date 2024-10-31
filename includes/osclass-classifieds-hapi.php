<?php 
function osclass_oc_api_get_categories_stats() {
    $endpoint = osclass_oc_api_endpoint().'admin/categories/stats';

    $client = new GuzzleHttp\Client();
    $res    = $client->request('POST', $endpoint, array('json' =>
        array(
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password(),
            )
        )
    );
    $stats = json_decode($res->getBody(), true);
    return $stats;
}

function osclass_oc_api_get_items_stats() {
    $endpoint = osclass_oc_api_endpoint().'admin/items_stats';

    $client = new GuzzleHttp\Client();
    $res    = $client->request('POST', $endpoint, array('json' =>
        array(
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password(),
            )
        )
    );
    $stats = json_decode($res->getBody(), true);
    return $stats;
}

function osclass_oc_api_get_users_stats() {
    $endpoint = osclass_oc_api_endpoint().'admin/users_stats';

    $client = new GuzzleHttp\Client();
    $res    = $client->request('POST', $endpoint, array('json' =>
        array(
            'admin_user' =>  osclass_oc_api_admin_username(),
            'admin_password' => osclass_oc_api_admin_password(),
            )
        )
    );
    $stats = json_decode($res->getBody(), true);
    return $stats;
}
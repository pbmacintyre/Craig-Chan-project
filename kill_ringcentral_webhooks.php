<?php
/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 *
 */

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-db-functions.inc');
require_once('includes/ringcentral-curl-functions.inc');
require_once('includes/ringcentral-php-functions.inc');

require('includes/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/includes");
$dotenv->load();

show_errors();
$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];

/* get the access token */
$table = "clients";
$columns_data = array("access", "refresh");
$where_info = array("account", "3058829020");
$db_result = db_record_select($table, $columns_data, $where_info);
$accessToken = $db_result[0]['access'];
$refreshToken = $db_result[0]['refresh'];
//echo_spaces("access token", $accessToken);
//echo_spaces("refresh token", $refreshToken);

$endpoint_url = "https://platform.ringcentral.com/restapi/v1.0/subscription";

$subscription_ch = curl_init();

// Set cURL options
curl_setopt_array($subscription_ch, [
    CURLOPT_URL => $endpoint_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $accessToken",
        "Accept: application/json"
    ],
]);

$subscription_response = curl_exec($subscription_ch);
curl_close($subscription_ch);
$subscriptions = json_decode($subscription_response, true);

//echo_spaces("Subscription response", $subscriptions);

if ($subscriptions['errorCode'] == "TokenInvalid") {
    echo_spaces("New access token needed");
    $accessToken = get_new_access_token($refreshToken);

    $subscription_ch = curl_init();

// Set cURL options
    curl_setopt_array($subscription_ch, [
        CURLOPT_URL => $endpoint_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            "Accept: application/json"
        ],
    ]);

    $subscription_response = curl_exec($subscription_ch);
    curl_close($subscription_ch);
    $subscriptions = json_decode($subscription_response, true);
}

//echo_spaces("Listing subscriptions", $subscriptions);

foreach ($subscriptions['records'] as $subscription) {
    $subscription_id = $subscription['id'];
    // echo_spaces("Individual Subscription array", $subscription);
    echo_spaces("Subscription ID", $subscription_id);
    echo_spaces("Creation Time", $subscription['creationTime']);
    // do a for each next line if needed.
    foreach ($subscription['eventFilters'] as $key => $filter) {
        echo_spaces("Event Filter URI $key", $subscription['eventFilters'][$key]);
    }
    echo_spaces("Webhook URI", $subscription['deliveryMode']['address']);
    echo_spaces("Webhook transport type", $subscription['deliveryMode']['transportType'], 2);

    if ($subscription_id == "46b00ea7-c60c-4bb9-bf9d-3b43583cd492") {
        $endpoint_del_url = "https://platform.ringcentral.com/restapi/v1.0/subscription/$subscription_id";
        $subscription_del_ch = curl_init();

// Set cURL options
        curl_setopt_array($subscription_del_ch, [
            CURLOPT_URL => $endpoint_del_url,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Accept: application/json"
            ],
        ]);

        $subscription_del_response = curl_exec($subscription_del_ch);
        curl_close($subscription_del_ch);
        echo_spaces("Subscription ID Deleted", $subscription_id, 2);
    }
}


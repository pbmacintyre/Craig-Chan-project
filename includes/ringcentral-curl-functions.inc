<?php
/** Copyright (C) 2019-2024 Paladin Business Solutions */

/* ================= */
/* Generic functions */
/* ================= */

use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;

function get_new_access_token($refreshToken) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $client_id = $_ENV['RC_APP_CLIENT_ID'];
    $client_secret = $_ENV['RC_APP_CLIENT_SECRET'];
    $url = 'https://platform.ringcentral.com/restapi/oauth/token';

    $refresh_params = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
    ];
    $refresh_headers = [
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        'Content-Type: application/x-www-form-urlencoded'
    ];
    $new_ch = curl_init();
    curl_setopt($new_ch, CURLOPT_URL, $url);
    curl_setopt($new_ch, CURLOPT_POST, true);
    curl_setopt($new_ch, CURLOPT_POSTFIELDS, http_build_query($refresh_params));
    curl_setopt($new_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($new_ch, CURLOPT_HTTPHEADER, $refresh_headers);

    $new_response = curl_exec($new_ch);
    curl_close($new_ch);
    $new_data = json_decode($new_response, true);
    $newAccessToken = $new_data['access_token'];
    $newRefreshToken = $new_data['refresh_token'];
    // save back to DB
    $table = "tokens";
    $where_col = "account";
    $where_data = "3058829020";
    $fields_data = $fields_data = array(
        "access" => $newAccessToken,
        "refresh" => $newRefreshToken,
    );
    db_record_update ($table, $fields_data, $where_col, $where_data);

//    echo_spaces("new access token", $newAccessToken, 2);

     return $newAccessToken;
}

function get_access_token() {

    /* get the access token */
    $table = "tokens";
    $columns_data = array("access", "refresh");
    $where_info = array("account", "3058829020");
    $db_result = db_record_select($table, $columns_data, $where_info);
    return $db_result[0]['access'];
}
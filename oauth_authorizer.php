<?php

require_once('includes/ringcentral-db-functions.inc');

require('includes/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/includes");
$dotenv->load();

$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];

if (isset($_GET['code'])) {

    $redirect_uri = $_ENV['RC_REDIRECT_URL'];
    $url = 'https://platform.ringcentral.com/restapi/oauth/token';

    $params = [
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri,
    ];

    $headers = [
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        'Content-Type: application/x-www-form-urlencoded'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    $accessToken = $data['access_token'];
    $refreshToken = $data['refresh_token'];

    /* ====== get account number ==== */
    $account_url = "https://platform.ringcentral.com/restapi/v1.0/account/~";

    $account_ch = curl_init();

    // Set cURL options
    curl_setopt_array($account_ch, [
        CURLOPT_URL => $account_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            "Accept: application/json"
        ],
    ]);

    $account_response = curl_exec($account_ch);
    curl_close($account_ch);
    $account_data = json_decode($account_response, true);
    $accountId = $account_data['id'];

    $table = "tokens";
    // check if this account has already been authorized
    $columns_data = array("token_id");
    $where_info = array("account", $accountId);
    $db_result = db_record_select($table, $columns_data, $where_info);
    if (empty($db_result)) {
        // not in DB, not already authorized, so save it
        $columns_data = array(
            "account" => $accountId,
            "access" => $accessToken,
            "refresh" => $refreshToken,);
        db_record_insert($table, $columns_data);
        header("Location: authorized.php?authorized=1");
    } else {
        header("Location: authorized.php?authorized=0");
    }

}
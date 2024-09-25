<?php
require('includes/vendor/autoload.php');

//require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/includes");
$dotenv->load();

$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];
$url = 'https://platform.ringcentral.com/restapi/oauth/token';

$table = "tokens";
$columns_data = array("*");
$db_result = db_record_select($table, $columns_data);

foreach ($db_result as $row) {
//    echo_spaces("existing access token", $row['access'], 2);
//    echo_spaces("existing refresh token", $row['refresh'], 2);

    // refresh the token
    $refresh_params = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $row['refresh']
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
    $refresh_data = json_decode($new_response, true);
    $accessToken = $refresh_data['access_token'];
    $refreshToken = $refresh_data['refresh_token'];
//    echo_spaces("new access token", $accessToken, 2);
//    echo_spaces("new refresh token", $refreshToken, 2);

    // save newly created tokens
    $table = "tokens";
    $where_col = "account";
    $where_data = $row['account'];
    $fields_data = $fields_data = array(
        "access" => $accessToken,
        "refresh" => $refreshToken,
    );
    // db_record_update($table, $fields_data, $where_col, $where_data) ;
}
//echo "all done";

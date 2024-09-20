<?php
ob_start();
session_start();

require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');
require_once('includes/ringcentral-functions.inc');

show_errors();

$table = "ringcentral_control";
$columns_data = array ("client_id", "client_secret", );
$where_info = array ("ringcentral_control_id", 1);
$db_result = db_record_select($table, $columns_data, $where_info = "", $condition = "" );

$client_id      = $db_result[0]['client_id'];
$client_secret  = $db_result[0]['client_secret'];

if (isset($_GET['code'])) {

    $redirect_uri = 'https://paladin-bs.com/craig_chan_project/oauth_sample.php';
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
    echo_spaces("data object", $data);

    $accessToken    = $data['access_token'];
    $refreshToken    = $data['refresh_token'];

    $table = "ringcentral_control";
    $where_col = "ringcentral_control_id";
    $where_data = 1;
    $fields_data = $fields_data = array(
        "access_token" => $accessToken,
        "refresh_token" => $refreshToken,
    );
    db_record_update($table, $fields_data, $where_col, $where_data ) ;

    /* ======================== */

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
    $account_data = json_decode($account_response, true);

    echo_spaces("account data response object", $account_data, 2);

    /* ========================

    $action_url = 'https://platform.ringcentral.com/restapi/v1.0/account/~/extension/~/sms';
    $action_headers = [
        'Authorization: Bearer ' . $accessToken,
        "Content-Type: application/json"
    ];
    $action_data = [
        'from' => array('phoneNumber' => '+16502950182'),  // my account phone #
        'to' => array(array('phoneNumber' => '+19029405827')),
        'text' => "This is a test SMS from the Craig Chan app original access token",
    ];

    $sms_ch = curl_init();
    curl_setopt($sms_ch, CURLOPT_URL, $action_url);
    curl_setopt($sms_ch, CURLOPT_POST, true);
    curl_setopt($sms_ch, CURLOPT_POSTFIELDS, json_encode($action_data));
    curl_setopt($sms_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($sms_ch, CURLOPT_HTTPHEADER, $action_headers);

    $sms_response = curl_exec($sms_ch);
    // Check if there were any errors with the request
    if (curl_errno($sms_ch)) {
        echo 'Error:' . curl_error($sms_ch);
    } else {
        // Print the API response
        echo_spaces("SMS response object", $sms_response, 2);
    }
    curl_close($sms_ch);
*/
}

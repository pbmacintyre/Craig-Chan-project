<?php
ob_start();
session_start();

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
show_errors();

$client_id      = '5aVrxx9dB3gfhHhtY0fEgo';
$client_secret  = 'YXhSxLVDFGdb5gz72ehncGWKbp30awtEzd7UkjjfQGWU';

echo_spaces("Client ID", $client_id);
echo_spaces("Secret", $client_secret,1);

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

    echo_spaces("access token", $data['access_token'],2);
    echo_spaces("data object", $data);

    $accessToken = $data['access_token'];

    $api_url = 'https://platform.ringcentral.com/restapi/v1.0/account/~/extension/~/sms';

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        "Content-Type: application/json"
    ];

//    $message = "This is a test SMS from the Craig Chan app";

    $sms_data = [
        'from' => array('phoneNumber' => '+16502950182'),  // my account phone #
//        'from' => array('phoneNumber' => '+16506662168'),  //main company #
        'to' => array(array('phoneNumber' => '+19029405827')),
        'text' => 'This is a test SMS from the Craig Chan app',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    // Check if there were any errors with the request
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Print the API response
        echo_spaces("response object", $response, 2);
    }
    curl_close($ch);


    /* =============================  */
/*
    $message = "This is a test SMS from the Craig Chan app";

    try {
        $apiResponse = $platform->post('/account/~/extension/~/sms',
            array('from' => array('phoneNumber' => '+16502950182'),
                'to' => array(array('phoneNumber' => '+19029405827')),
                'text' => $message,));
        echo_spaces("SMS Sent",'',1);
    }
    catch (\RingCentral\SDK\Http\APIException $e) {
        // dump out the message object contents
        error_log($e->getMessage());
        var_dump($e->getMessage());
    }

    */
}

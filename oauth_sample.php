<?php
ob_start();
session_start();

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
show_errors();

$client_id      = '5aVrxx9dB3gfhHhtY0fEgo';
$client_secret  = 'YXhSxLVDFGdb5gz72ehncGWKbp30awtEzd7UkjjfQGWU';

echo_spaces("Client ID", $client_id);
echo_spaces("Secret", $client_secret,2);
//echo_spaces("GET Code", $_GET['code'], 2);

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

    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];
        $refresh_token = $data['refresh_token'];
        // Save tokens securely
        echo_spaces("Access Token", $access_token, 2) ;
        echo_spaces("Refresh Token", $refresh_token,2);
    } else {
        // Handle errors
        echo "Error getting access token";
    }
}

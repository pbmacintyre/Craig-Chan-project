<?php

if (!$accessToken) {

    // refresh the token
    $refresh_params = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $_SESSION['refresh_token']
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
    echo_spaces("new access token", $accessToken, 2);
    echo_spaces("new data object", $refresh_data, 2);
    $message = "This is a test SMS from the Craig Chan app with refresh token";
}
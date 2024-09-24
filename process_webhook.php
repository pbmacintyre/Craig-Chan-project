<?php

/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 *
 */

// runs when a RingCentral event is triggered... send SMS on certain events.

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

$hvt = isset($_SERVER['HTTP_VALIDATION_TOKEN']) ? $_SERVER['HTTP_VALIDATION_TOKEN'] : '';
if (strlen($hvt) > 0) {
    header("Validation-Token: {$hvt}");
}

$incoming = file_get_contents("php://input");

// use following to send incoming event data to a file for visual review
file_put_contents("received_EVENT_payload.log", $incoming);

if (empty($incoming)) {
    http_response_code(200);
    echo json_encode(array('responseType'=>'error', 'responseDescription'=>'No data provided Check SMS payload.'));
    exit();
}

$incoming_data = json_decode($incoming);

if (!$incoming_data) {
    http_response_code(200);
    echo json_encode(array('responseType'=>'error', 'responseDescription'=>'Media type not supported.  Please use JSON.'));
    exit();
}

echo_spaces("incoming payload", $incoming_data);

/*

$table = "ringcentral_control";
$columns_data = array ("from_number",);
$where_info = array("ringcentral_control_id", 1);
$db_result = db_record_select($table, $columns_data, $where_info);

//echo_spaces("testing output", $db_result);

$from_number = $db_result[0]['from_number'] ;
$to_number = "+19029405827" ;
$message = "this is a test message";

$sdk->platform()->post('/account/~/extension/~/sms',
         array('from' => array('phoneNumber' => $from_number),
                'to' => array(array('phoneNumber' => $to_number)),
                'text' => "$message"));


/* ============================== */
/* ============================== */
/* ============================== */
/* ============================== */
/* ==== get admin records ======= */
/* ============================== */
/* ============================== */
/* ============================== */
/* ============================== */

// now get all admin users related to this account.
/*
$admins_url = "https://platform.ringcentral.com/restapi/v1.0/account/~/directory/entries";

$admins_params = array(
    'showFederated' => 'true',
    'type' => 'User',
    'typeGroup' => '',
    'page' => 1,
    'perPage' => 'all',
    'siteId' => '',
);
$admins_headers = [
    "Authorization: Bearer $accessToken",
    "Accept: application/json",
];
// Set cURL options
$admins_ch = curl_init();
curl_setopt($admins_ch, CURLOPT_URL, $admins_url . '?' . http_build_query($admins_params));
curl_setopt($admins_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($admins_ch, CURLOPT_HTTPHEADER, $admins_headers);

$admins_response = curl_exec($admins_ch);

curl_close($admins_ch);
$admins_data = json_decode($admins_response, true);

$users = $admins_data['records'];
//    echo_spaces("ALL users", $users, 2);

/* ==================================================== */
/* ==== trim user records to only admin records ======= */
/* ==================================================== */
/*
$i = 1;
//
foreach ($users as $value) {
//        echo_spaces("Value array", $value);
    if ($value['type'] == 'User' && $value['status'] == 'Enabled' && $value['phoneNumbers'][0]['phoneNumber']) {

        // if they are an enabled user type with a phone number
        $single_user_params = array(
            'extensionNumber' => $value['extensionNumber'],
        );
//            $accountID = $value->account['id'];
        //sleep(1);
        try {
            // get extension information to see if they are admin
            $single_user_endpoint = "/restapi/v1.0/account/~/extension?" . http_build_query($single_user_params);
            $single_user_headers = [
                "Authorization: Bearer $accessToken",
                "Accept: application/json",
            ];


            $single_user_ch = curl_init();
            curl_setopt($single_user_ch, CURLOPT_URL, $single_user_endpoint);
            curl_setopt($single_user_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($single_user_ch, CURLOPT_HTTPHEADER, $single_user_headers);

            $single_user_response = curl_exec($single_user_ch);

            curl_close($single_user_ch);
            $single_user_data = json_decode($single_user_response, true);

            echo_spaces("single User admin record", $single_user_data, 2);

            /* ====================== */

//                foreach ($resp2->json()->records as $value2) {
//                    if ($value2->permissions->admin->enabled) {
//                        // if they are admin level
//                        echo_spaces("Counter", $i++);
//                        echo_spaces("ID", $value->id);
//                        echo_spaces("first Name", $value->firstName);
//                        echo_spaces("last Name", $value->lastName);
//                        echo_spaces("Extension #", $value->extensionNumber);
//                        echo_spaces("email", $value->email);
//                        echo_spaces("phone #", $value->phoneNumbers[0]->phoneNumber);
//                        echo_spaces("Account ID", $value->account->id);
//                        echo_spaces("Admin Permission", $value2->permissions->admin->enabled, 1);
//                    }
//                }
//          echo_spaces("Response Object", $resp2->json()->records,1);
/*        }
        catch (Exception $e) {
            echo_spaces("Error Message", $e->getMessage());
        }
    }
}

/*

$table = "ringcentral_control";
$where_col = "ringcentral_control_id";
$where_data = 1;
$fields_data = $fields_data = array(
    "access_token" => $accessToken,
    "refresh_token" => $refreshToken,
);
db_record_update($table, $fields_data, $where_col, $where_data ) ;



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


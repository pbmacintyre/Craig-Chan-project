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
// file_put_contents("received_EVENT_payload.log", $incoming);

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

//connect to the SDK
$sdk = ringcentral_sdk();

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



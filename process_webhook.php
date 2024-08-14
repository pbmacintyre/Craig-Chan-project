<?php

/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 *
 */

// runs when an SMS message comes in from a client for a specific shop
// to process STOP / HELP / START requests

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

$hvt = isset($_SERVER['HTTP_VALIDATION_TOKEN']) ? $_SERVER['HTTP_VALIDATION_TOKEN'] : '';
if (strlen($hvt) > 0) {
    header("Validation-Token: {$hvt}");
}

$incoming = file_get_contents("php://input");

//file_put_contents("received_SMS_payload.log", $incoming);

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

// parse out the incoming information
$incoming_sms = $incoming_data->body->subject;
// the shops mobile number
$incoming_shop_mobile_number = $incoming_data->body->to[0]->phoneNumber;
// the customers mobile number
$incoming_customer_mobile_number = $incoming_data->body->from->phoneNumber;

// find out what shop table the number is in and get the JWT key, if it exists
$shop_info = get_shop_table("mobile", $incoming_shop_mobile_number);
$jwt_key = decryptData($shop_info['jwt_key']);

// use the ringCentral function for the specific SDK based on the shop type and its JWT and therefore the shops SMS active number
if ($shop_info["name"] == "shopify_shops") {
    $sdk = ringcentral_shopify_sdk($jwt_key);
    $sms_ok = check_shopify_customer_sms ($incoming_shop_mobile_number, $incoming_customer_mobile_number);
} elseif ($shop_info["name"] == "bigcomm_shops") {
    $sdk = ringcentral_bigcomm_sdk($jwt_key);
    // the following function also checks that they are not in our stops table
    $sms_ok = check_bigcomm_customer_sms($incoming_shop_mobile_number, $incoming_customer_mobile_number);
}

// use shop information to determine the following information, in Big Comm we cannot "process" the STOP request
// as the API for "accepts_marketing" is read-only
if ($sms_ok) {
    if (preg_match('/^(STOP)|(END)|(CANCEL)|(UNSUBSCRIBE)|(QUIT)$/i', $incoming_sms)) {

        if ($shop_info["name"] == "shopify_shops") {
            // send message to Shopify via API for SMS opt OUT
            Send_Unsub_to_shopify_customer($incoming_shop_mobile_number, $incoming_customer_mobile_number);
        }
        if ($shop_info["name"] == "bigcomm_shops") {
            // store STOP status in our STOPS db
            record_bigcomm_stops ($incoming_customer_mobile_number, $shop_info['store_hash']) ;
        }
        // use the shops SDK connection to send out the SMS
        $sdk->platform()->post('/account/~/extension/~/sms',
            array('from' => array('phoneNumber' => $incoming_shop_mobile_number),
                'to' => array(array('phoneNumber' => $incoming_customer_mobile_number)),
                'text' => 'You have been successfully unsubscribed. '));
    } elseif (preg_match('/^(HELP)|(INFO)$/i', $incoming_sms)) {
        $message = 'Please call ' . $incoming_shop_mobile_number . ' for more information, or text STOP to stop these messages. 
        Msg & Data rates may apply. ';
        $sdk->platform()->post('/account/~/extension/~/sms',
            array('from' => array('phoneNumber' => $incoming_shop_mobile_number),
                'to' => array(array('phoneNumber' => $incoming_customer_mobile_number)),
                'text' => $message));
    }
}
// if a START request comes in from Big Comm
if (preg_match('/^(START)$/i', $incoming_sms)) {
    if ($shop_info["name"] == "bigcomm_shops") {
        reverse_bigcomm_stops ($incoming_customer_mobile_number, $shop_info['store_hash']) ;
        // use the shops SDK connection to send out the SMS
        $sdk->platform()->post('/account/~/extension/~/sms',
            array('from' => array('phoneNumber' => $incoming_shop_mobile_number),
                'to' => array(array('phoneNumber' => $incoming_customer_mobile_number)),
                'text' => 'You have been successfully subscribed. You may have to reactivate your settings on the store as well. '));
    }
}


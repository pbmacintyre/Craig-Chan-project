<?php

/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 *
 */

// runs when a RingCentral SMS event is triggered...

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-curl-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

require('includes/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/includes");
$dotenv->load();

show_errors();

$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];

$hvt = isset($_SERVER['HTTP_VALIDATION_TOKEN']) ? $_SERVER['HTTP_VALIDATION_TOKEN'] : '';
if (strlen($hvt) > 0) {
    header("Validation-Token: {$hvt}");
}

$incoming = file_get_contents("php://input");

// use following to send incoming event data to a file for visual review
file_put_contents("received_SMS_EVENT_payload.log", $incoming);

if (empty($incoming)) {
    http_response_code(200);
    echo json_encode(array('responseType' => 'error', 'responseDescription' => 'No data provided Check SMS payload.'));
    exit();
}

$incoming_data = json_decode($incoming);

if (!$incoming_data) {
    http_response_code(200);
    echo json_encode(array('responseType' => 'error', 'responseDescription' => 'Media type not supported.  Please use JSON.'));
    exit();
}

//echo_spaces("incoming payload account #", $incoming_data->body->contacts['0']->account->id);

// parse out the incoming information
$incoming_sms = $incoming_data->body->subject;

// the shops mobile number
$toNumber = $incoming_data->body->to[0]->phoneNumber;
// the customers mobile number
$fromNumber = $incoming_data->body->from->phoneNumber;

//echo_spaces("SMS Subject", $incoming_sms,1);
//echo_spaces("To Number", $toNumber,1);
//echo_spaces("From Number", $fromNumber,1);

if (preg_match('/^(TODAY)$/i', $incoming_sms)) {
//TODO change this line to react to STOP when LIVE
    //if (preg_match('/^(STOP)|(END)|(CANCEL)|(UNSUBSCRIBE)|(QUIT)$/i', $incoming_sms)) {
    send_stop_sms($fromNumber, $toNumber);
    // remove numbers from the clients table.
    kill_sms_webhook($fromNumber, $toNumber);
}


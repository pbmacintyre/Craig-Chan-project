<?php

/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 *
 */

// runs when a RingCentral event is triggered... send SMS on certain events.

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
file_put_contents("received_EVENT_payload.log", $incoming);

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

$accountId = $incoming_data->body->contacts['0']->account->id;
$timeStamp = $incoming_data->timestamp;

//echo_spaces("incoming time stamp", $timeStamp);

// with the account id
// [1] get the access token
// [2] get the audit trail information
// [3] find all admin users
// [4] send events from last 15 minutes to admins via SMS and
// [5] post the event to a TM group

/* === [1] get the access token  === */
$table = "tokens";
$columns_data = array("access", "refresh");
$where_info = array("account", $accountId);
$db_result = db_record_select($table, $columns_data, $where_info);
$accessToken = $db_result[0]['access'];
$refreshToken = $db_result[0]['refresh'];

/* === [2] get the audit trail information  === */
$audit_data = get_audit_data($accessToken, $timeStamp);
//echo_spaces("audit array", $audit_data);

// [3] find all admin users
$allAdmins = get_admins($accessToken);
//echo_spaces("admin array", $allAdmins);

// [4] send event from 5 minutes either side of the event date stamp to admins via SMS
send_admin_sms ($allAdmins, $audit_data, $accessToken);

// [5] post the event to a TM group
send_team_message ($audit_data, $accessToken);


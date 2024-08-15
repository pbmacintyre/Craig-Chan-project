<?php

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

show_errors();

$sdk = ringcentral_sdk();
//echo_spaces("SDK object", $sdk);

$endpoint = "/restapi/v1.0/account/~/directory/entries";
//$endpoint = "/restapi/v1.0/account/~/extension/~/assigned-role";

$params = array(
    'showFederated' => 'true',
    'type' => 'User',
    'typeGroup' => '',
    'page' => 1,
    'perPage' => 'all',
    'siteId' => ''
);

//$params = null ;
//echo_spaces("params", $params);

try {
//    $resp = $sdk->platform()->get($endpoint);
    $resp = $sdk->platform()->get($endpoint, $params);
    // echo_spaces("Response Object", $resp->json()->records);
} catch (Exception $e) {
    echo_spaces("Error Message", $e->getMessage());
}

foreach ($resp->json()->records as $value) {
    if ($value->extensionNumber == '12001') {
        echo_spaces("ID", $value->id);
        echo_spaces("first Name", $value->firstName);
        echo_spaces("last Name", $value->lastName);
        echo_spaces("email", $value->email);
        echo_spaces("Account ID", $value->account->id,1);
    }
}

//echo_spaces("Response Object", $resp->json()->records);

//$table = "ringcentral_control";
//$columns_data = "*";
//$where_info = array("ringcentral_control_id", 1);
//$db_result = db_record_select($table, $columns_data, $where_info);
//
//echo_spaces("testing output", $db_result);
//
//echo_spaces("from #", $db_result[0]['from_number']) ;





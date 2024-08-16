<?php

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

show_errors();

$sdk = ringcentral_sdk();

$endpoint = "/restapi/v1.0/account/~/directory/entries";

$params = array(
    'showFederated' => 'true',
    'type' => 'User',
    'typeGroup' => '',
    'page' => 1,
    'perPage' => 'all',
    'siteId' => '',
);

try {
    $resp = $sdk->platform()->get($endpoint, $params);
//     echo_spaces("Response Object", $resp->json()->records);
}
catch (Exception $e) {
    echo_spaces("Error Message 1", $e->getMessage());
}

$i = 1;
//
foreach ($resp->json()->records as $value) {
    if ($value->type == 'User' && $value->status == 'Enabled' && $value->phoneNumbers[0]->phoneNumber ) {
        // if they are enabled user types with a phone number
        $params2 = array(
            'extensionNumber' => $value->extensionNumber,
        );
        $accountID = '3058829020';
        sleep(1) ;
        try {
            $endpoint2 = "/restapi/v1.0/account/$accountID/extension";
            $resp2 = $sdk->platform()->get($endpoint2, $params2);
            foreach ($resp2->json()->records as $value2) {
                if ($value2->permissions->admin->enabled) {
                    // if they are admin level
                    echo_spaces("Counter", $i++);
                    echo_spaces("ID", $value->id);
                    echo_spaces("first Name", $value->firstName);
                    echo_spaces("last Name", $value->lastName);
                    echo_spaces("Extension #", $value->extensionNumber);
                    echo_spaces("email", $value->email);
                    echo_spaces("phone #", $value->phoneNumbers[0]->phoneNumber);
                    echo_spaces("Account ID", $value->account->id);
                    echo_spaces("Admin Permission", $value2->permissions->admin->enabled, 1);
                }
            }
//          echo_spaces("Response Object", $resp2->json()->records,1);
        }
        catch (Exception $e) {
            echo_spaces("Error Message resp2", $e->getMessage());
        }
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





<?php

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

show_errors();

$sdk = ringcentral_sdk ();
//echo_spaces("SDK object", $sdk);

$response = $sdk->platform()->get("/subscription");

$subscriptions = $response->json()->records;

//echo_spaces("Listing subscriptions", $subscriptions);

foreach ($subscriptions as $subscription) {
    echo_spaces("Subscription ID", $subscription->id);
    echo_spaces("Creation Time", $subscription->creationTime);
    echo_spaces("Event Filter URI", $subscription->eventFilters[0]);
    echo_spaces("Webhook URI", $subscription->deliveryMode->address);
    echo_spaces("Webhook transport type", $subscription->deliveryMode->transportType, 2);

//    if ($subscription->id == "5e64753f-d14f-437c-af83-c43621e654af") {
//        $response = $sdk->platform()->delete("/restapi/v1.0/subscription/{$subscription->id}");
//        echo_spaces("Subscription ID Deleted", $subscription->id);
//    }
}


//$table = "ringcentral_control";
//$columns_data = "*";
//$where_info = array("ringcentral_control_id", 1);
//$db_result = db_record_select($table, $columns_data, $where_info);
//
//echo_spaces("testing output", $db_result);
//
//echo_spaces("from #", $db_result[0]['from_number']) ;





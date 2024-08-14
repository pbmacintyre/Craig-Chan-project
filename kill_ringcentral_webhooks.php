<?php
require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-db-functions.inc');
require_once('includes/ringcentral-php-functions.inc');

//show_errors();

$sdk = ringcentral_sdk ();

// list subscriptions then delete the one we don't need.

$response = $sdk->platform()->get("/subscription");
$subscriptions = $response->json()->records;

foreach ($subscriptions as $subscription) {
    echo_spaces("Subscription ID", $subscription->id);
    echo_spaces("Creation Time", $subscription->creationTime);
    echo_spaces("Event Filter URI", $subscription->eventFilters[0]);
    echo_spaces("Webhook URI", $subscription->deliveryMode->address);
    echo_spaces("Webhook transport type", $subscription->deliveryMode->transportType, 2);

    if ($subscription->id == "81041574-5c2f-457c-93ea-162b80c09dfe") {
        $response = $sdk->platform()->delete("/restapi/v1.0/subscription/{$subscription->id}");
        echo_spaces("Subscription ID Deleted", $subscription->id);
    }
//        if ($subscription->id == "367d863a-1a09-413d-924e-8d41f9c7d3db") {
//        $response = $sdk->platform()->delete("/restapi/v1.0/subscription/{$subscription->id}");
//        echo_spaces("Subscription ID Deleted", $subscription->id);
//    }
}

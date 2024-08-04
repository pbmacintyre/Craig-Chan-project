<?php

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-shopify-functions.inc');
show_errors();

 $jwt = "eyJraWQiOiI4NzYyZjU5OGQwNTk0NGRiODZiZjVjYTk3ODA0NzYwOCIsInR5cCI6IkpXVCIsImFsZyI6IlJTMjU2In0.eyJhdWQiOiJodHRwczovL3BsYXRmb3JtLnJpbmdjZW50cmFsLmNvbS9yZXN0YXBpL29hdXRoL3Rva2VuIiwic3ViIjoiNjIxOTk0ODYwMTYiLCJpc3MiOiJodHRwczovL3BsYXRmb3JtLnJpbmdjZW50cmFsLmNvbSIsImV4cCI6Mzg2MDc4MjQyNCwiaWF0IjoxNzEzMjk4Nzc3LCJqdGkiOiJIakVKMzl3T1RWTzR3eERaUm9Gc1BBIn0.fOZiP51xW8uhN3gdGN8h30ar9rVJnGJwlIEuKwqjVXLahaL8gGGwyNz5g8BIVomYFdmX5dFOEo7_AwFz9EkylOSqCXofUr15poqhnUjMssDWSgEr4GAt-1IA6KUm28FuQ4SRiyND5rxXY8Dn7cFfmIOT5PbhmE03Lijy1QS6W9Glzbtby2hd0Xeq1XttDA0YNBf_k8SRn7HWvMvZj0VbHXCpDScJn2mwmv-2Z-fmxy1NAQGI56Xel6O4WtS_zcDCdhKFQo1bOeHwvetRG8cSyjkTANhmqU7SFhlRWCYmGxitsdZpj1jQa5Rk4meJ5jdmWDizPkjFHCtdWA2v0Leojw";
//$jwt = "eyJraWQiOiI4NzYyZjU5OGQwNTk0NGRiODZiZjVjYTk3ODA0NzYwOCIsInR5cCI6IkpXVCIsImFsZyI6IlJTMjU2In0.eyJhdWQiOiJodHRwczovL3BsYXRmb3JtLnJpbmdjZW50cmFsLmNvbS9yZXN0YXBpL29hdXRoL3Rva2VuIiwic3ViIjoiNjIxOTk0ODYwMTYiLCJpc3MiOiJodHRwczovL3BsYXRmb3JtLnJpbmdjZW50cmFsLmNvbSIsImV4cCI6Mzg2NTg1NDU4MSwiaWF0IjoxNzE4MzcwOTM0LCJqdGkiOiJuQ3ZvZUtXVVFzV2d6VnQyTEJvaVJnIn0.DII0BeTtNEMAUit_LnxyNdmjTIPE8-XvwhnZAz1TNF4eruWnpPry-AghsG7_1a5fCbqvzigS7WRVXBe12hE9wKSsoe3iBhvQT8ka1l4V1lR3cP1hSVxGUfZVR2vE0g9sy-7XcMVaN84cWh9vkBR1ZkdHF9lHO3T1Py36Pt_BrZBGf0Ug_c_WU_IGlpfcLU2DGop7v6FheLUxjLBoJubpQ7J1h4flUamveBVYtbiVZDIA7DnXV46zyIuYHOxp-K-mAGj2tb5Ec9XE9jW0_Wl2bKjOzUwVz_inr12jHmZpIu5iC_vMGPoBItzKTSIzQTHVs8_rjh_sXvK1mpZnvhmQMA";

$sdk = ringcentral_shopify_sdk($jwt);

echo_spaces("RingCentral Subscriptions", "", 1);

//ringcentral_create_webhook_subscription($jwt, "shopify_shops") ;

$response = $sdk->platform()->get("/subscription");

$subscriptions = $response->json()->records;

foreach ($subscriptions as $subscription) {
    echo_spaces("Subscription ID", $subscription->id);
    echo_spaces("Creation Time", $subscription->creationTime);
    echo_spaces("Event Filter URI", $subscription->eventFilters[0]);
    echo_spaces("Webhook URI", $subscription->deliveryMode->address);
    echo_spaces("Webhook transport type", $subscription->deliveryMode->transportType, 2);

    if ($subscription->id == "5e64753f-d14f-437c-af83-c43621e654af") {
        $response = $sdk->platform()->delete("/restapi/v1.0/subscription/{$subscription->id}");
        echo_spaces("Subscription ID Deleted", $subscription->id);
    }
}



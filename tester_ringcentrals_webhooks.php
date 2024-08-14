<?php
require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

show_errors();

$sdk = ringcentral_sdk();

$webhookId = ringcentral_create_webhook_subscription($sdk) ;

echo_spaces("Webhook ID", $webhookId);



<?php

require_once(__DIR__ . '/includes/ringcentral-php-functions.inc');
require_once(__DIR__ . '/includes/ringcentral-db-functions.inc');
require_once(__DIR__ . '/includes/ringcentral-functions.inc');
require_once(__DIR__ . '/includes/ringcentral-curl-functions.inc');

require(__DIR__ . '/includes/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/includes");
$dotenv->load();

$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];
$accountId = $_ENV['RC_APP_ACCOUNT_ID'];

$table = "clients";
$columns_data = array("*");
$db_result = db_record_select($table, $columns_data);

foreach ($db_result as $row) {
    $clients = refresh_tokens($row['refresh'], $client_id, $client_secret);

    // save newly created client information
    $table = "clients";
    $where_info = array ("account", $row['account']);
    $fields_data = $fields_data = array(
        "access" => $clients['accessToken'],
        "refresh" => $clients['refreshToken'],
    );

    db_record_update($table, $fields_data, $where_info);
}

$message = "CRON runs every 30 minutes";
echo_spaces("tokens refreshed");
//send_basic_sms ($clients['accessToken'], $message);

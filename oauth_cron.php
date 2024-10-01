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

$table = "tokens";
$columns_data = array("*");
$db_result = db_record_select($table, $columns_data);

foreach ($db_result as $row) {
    $tokens = refresh_tokens($row['refresh'], $client_id, $client_secret);

    // save newly created tokens
    $table = "tokens";
    $where_col = "account";
    $where_data = $row['account'];
    $fields_data = $fields_data = array(
        "access" => $tokens['accessToken'],
        "refresh" => $tokens['refreshToken'],
    );

    db_record_update($table, $fields_data, $where_col, $where_data);
}
// now do the control table.
$table = "ringcentral_control";
$columns_data = array("refresh");
$where_info = array("ringcentral_control_id", 1);
$db_result = db_record_select($table, $columns_data, $where_info);

$tokens = refresh_tokens($db_result[0]['refresh'], $client_id, $client_secret);

// save newly created tokens
$table = "ringcentral_control";
$where_col = "ringcentral_control_id";
$where_data = 1;
$fields_data = $fields_data = array(
    "access" => $tokens['accessToken'],
    "refresh" => $tokens['refreshToken'],
);
db_record_update($table, $fields_data, $where_col, $where_data);

//echo "all done";
$message = "CRON runs every 30 minutes";
//send_basic_sms ($tokens['accessToken'], $message);

?>
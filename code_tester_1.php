<?php
ob_start();


require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-db-functions.inc');

show_errors();

require_once('includes/vendor/autoload.php');

use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
session_start();

$rcsdk = new RingCentral\SDK\SDK(
    $_ENV['RC_APP_CLIENT_ID'],
    $_ENV['RC_APP_CLIENT_SECRET'],
    $_ENV['RC_SERVER_URL']);
$platform = $rcsdk->platform();




ob_end_flush();
?>
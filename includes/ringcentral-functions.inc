<?php
/** Copyright (C) 2019-2024 Paladin Business Solutions */

/* ================= */
/* Generic functions */
/* ================= */

use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;

function prod_test() {
    return 0 ;  // 1 for production & 0 for test environment
}

function app_name () {
    echo "Audit Trail Notifications App";
    return "Audit Trail Notifications App";
}
/* ================== */
/* Get RingCental SDK */
/* ================== */
function ringcentral_sdk () {
    // Include Libraries
    require('includes/vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $jwt_key = $_ENV['RC_USER_JWT'] ;

    $sdk = new RingCentral\SDK\SDK(
        $_ENV['RC_APP_CLIENT_ID'],
        $_ENV['RC_APP_CLIENT_SECRET'],
        $_ENV['RC_SERVER_URL']);

    $platform = $sdk->platform();

    // Login via API
    if (!$sdk->platform()->loggedIn()) {
        try {
            $platform->login(["jwt" => $jwt_key]);
        }
        catch (\RingCentral\SDK\Http\ApiException $e) {
            $sdk = 0;
            // exit("<br/><br/>Unable to authenticate to platform. Check your RingCentral credentials. <br/><br/>") ;
        }
    }
    $controller = array('SDK' => $sdk, 'platform' => $platform);
    return $controller;
}
/* ======================== */
/* Get RingCental oAuth SDK */
/* ======================== */
function ringcentral_oAuth_sdk () {
    // Include Libraries
    require_once('includes/vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $sdk = new RingCentral\SDK\SDK(
        $_ENV['RC_APP_CLIENT_ID'],
        $_ENV['RC_APP_CLIENT_SECRET'],
        $_ENV['RC_SERVER_URL']);

    return $sdk->platform();
}

/* ============================== */
/* send out SMS to Shopify client */
/* ============================== */
function ringcentral_send_mobile ($payload, $shop_type) {

    if ($shop_type == "shopify") {
        $sdk = ringcentral_shopify_sdk($payload['JWT']);
    } elseif ($shop_type == "BigComm") {
        $sdk = ringcentral_bigcomm_sdk($payload['JWT']);
    }

    try {
        $apiResponse = $sdk->platform()->post('/account/~/extension/~/sms',
            array('from' => array('phoneNumber' => $payload["from_mobile"]),
                'to' => array(array('phoneNumber' => substr($payload["to_mobile"], 2))),
                'text' => $payload["message"]));
    }
    catch (\RingCentral\SDK\Http\APIException $e) {
        // dump out the message object contents
        error_log($e->getMessage());
        var_dump($e->getMessage());
    }
}

/* ======================================== */
/* get from phone number from control table */
/* it is stored in the user_name field      */
/* ======================================== */
function ringcentral_get_from_phone () {
    $table = "ringcentral_control";
    $columns_data = array("from_number");
    $where_info = array("ringcentral_control_id", 1);
    $db_result = db_record_select($table, $columns_data, $where_info);

    return $db_result[0]["from_number"];
}

function ringcentral_gen_and_send_six_digit_code ($to) {

    $six_digit_code = rand(100000, 999999);
    $_SESSION['six_digit_code'] = $six_digit_code;
    $_SESSION['last_four'] = substr($to, -4);

    $message = "You or another person has requested to use this number for two-factor authentication. 
        To authenticate 2FA please enter the following code on the validation page: $six_digit_code";

    // connect to SDK with credentials in the DB
    $sdk = ringcentral_sdk();
    $from = ringcentral_get_from_phone();

    try {
        $apiResponse = $sdk->platform()->post('/account/~/extension/~/sms',
            array('from' => array('phoneNumber' => $from),
                'to' => array(array('phoneNumber' => $to)),
                'text' => $message),
        );
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        // craft a friendly message here.
        $return_message = "There was an error sending the validation code, Please try again later <br/>" . $e;
    }
}

/* =================== */
/* Build ReCaptcha top */
/* =================== */
function ringcentral_build_recaptcha_v3_top () {
    $table = "ringcentral_control";
    $columns_data = array("grc_site_key");
    $where_info = array("ringcentral_control_id", 1);
    $db_result = db_record_select($table, $columns_data, $where_info);

    $site_key = $db_result[0]['grc_site_key'];

    echo "<script src='https://www.google.com/recaptcha/api.js?render=$site_key'></script>";
}

/* ========================= */
/* Verify ReCaptcha response */
/* ========================= */
function ringcentral_verify_v3_recaptcha ($action) {
    $table = "ringcentral_control";
    $columns_data = array("grc_secret_key");
    $where_info = array("ringcentral_control_id", 1);
    $db_result = db_record_select($table, $columns_data, $where_info);

    $secret_key = $db_result[0]['grc_secret_key'];

    $token = $_POST['g-recaptcha-response'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $secret_key,
        'response' => $token,
        'remoteip' => $ip
    );

    $options = array(
        'http' => array(
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $reCaptchaResult = json_decode($response);

// Check if reCAPTCHA is successful
    if ($reCaptchaResult->success) {
        // reCAPTCHA validation succeeded
        echo "reCAPTCHA verification passed!";
        // Further processing goes here
    } else {
        // reCAPTCHA validation failed
        echo "reCAPTCHA verification failed!";
        // Redirect or display an error message as needed
    }

}

function ringcentral_build_help ($field) {
    $image_source = 'images/question_mark.png';

    $table = "ringcentral_help";
    $columns_data = array("ringcentral_help_help");
    $where_info = array("ringcentral_help_field", $field);
    $db_result = db_record_select($table, $columns_data, $where_info);

    $help_text = $db_result[0]['ringcentral_help_help'];

    return "<img src='$image_source' title='" . $help_text . "' />";
}

function ringcentral_create_webhook_subscription ($sdk) {
    // create watching subscription for audit events

    $subscription_url = "https://" . $_SERVER['HTTP_HOST'] ;
    if (prod_test() == 0 ) {
        $subscription_url .= "/craig_chan_project/process_webhook.php" ;
    } elseif (prod_test() == 1 ) {
        $subscription_url .= "/process_webhook.php" ;
    }

    try {
        $api_call = $sdk->platform()->post('/subscription',
            array(
                "eventFilters" => array(
                    "/restapi/v1.0/account/~/directory/entries",
                ),
                "expiresIn" => "315360000",
                "deliveryMode" => array(
                    "transportType" => "WebHook",
                    // need full URL for this to work as well
                    "address" => $subscription_url,
                )
            )
        );
        $webhook_id = $api_call->json()->id;
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        echo_spaces("API Exception", $e->getMessage());
        exit();
    }
    return $webhook_id;
}

function ringcentral_kill_webhook_subscription ($existing_rc_jwt_key, $shop_type) {
    // coming in decrypted
    if ($shop_type == "shopify_shops") {
        $sdk = ringcentral_shopify_sdk($existing_rc_jwt_key);
    } elseif ($shop_type == "bigcomm_shops") {
        $sdk = ringcentral_bigcomm_sdk($existing_rc_jwt_key);
    }

    $table = $shop_type;
    $columns_data = array("rc_webhook_token");
    $where_info = array("jwt_code", encryptData($existing_rc_jwt_key));
    $db_result = db_record_select($table, $columns_data, $where_info);

    $webhook_token = $db_result[0]['rc_webhook_token'];
    $sdk->platform()->delete("/subscription/{$webhook_token}");
}

// setup Mail Gun class for use
require_once('includes/vendor/autoload.php');
use Mailgun\Mailgun;

function send_mailgun_email ($email, $subject, $body) {

    $table = "ringcentral_control";
    $columns_data = array("mailgun_key");
    $where_info = array("ringcentral_control_id", 1);
    $db_result = db_record_select($table, $columns_data, $where_info);

    $mailgun_key = $db_result[0]['mailgun_key'];

    $mgClient = Mailgun::create($mailgun_key);

    try {
        $mgClient->messages()->send('ecommerce.labs.gamechanging.dev' , [
            "from"      => 'ecommerce labs<admin@ecommerce.labs.gamechanging.dev>',
            "to"        => "$email",
            "subject"   => "$subject",
            "text"      => "$body",
            'html'      => "<html>$body</html>",
        ]);
        $result = true ;
    } catch (Exception $e){
        echo_spaces("Catch exception Message", $e->getMessage(), 2);
        $result = false ;
    }
    return $result;
}
// =====================================================
// ======= GET / POST Functions are below ==============
// =====================================================
function callAccountRequest ($endpoint, $params) {
    global $platform;
    try {
        $resp = $platform->get($endpoint, $params);
        echo_spaces("Object detail", $resp->json()->id);
//        echo_spaces("Object detail", $resp->json()->serviceInfo->brand);
        echo_spaces("Object", $resp->json());
//        echo $resp->text();
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        print 'Expected HTTP Error: ' . $e->getMessage() . PHP_EOL;
    }
}

function callPostRequest ($platform, $endpoint, $params=null) {
//    echo_spaces("SDK object", $sdk);
    echo_spaces("Session", $_SESSION);
    $i = 1 ;
    try {
        $resp = $platform->post($endpoint, $params);
//        foreach ($resp->json()->records as $value) {
//            //if ($value->eventType == "UPDATE") {
//                echo_spaces("Counter", $i++);
//                echo_spaces("Event Time", date("F j, Y, g:i a", strtotime($value->eventTime)));
//                echo_spaces("Event Type", $value->eventType);
//                echo_spaces("Action", $value->actionId);
//                echo_spaces("Account Id", $value->accountId);
//                echo_spaces("Initiator Id", $value->initiator->extensionId);
//                echo_spaces("Initiator Name", $value->initiator->name);
//                echo_spaces("Initiator Role", $value->initiator->role);
//
//                echo_spaces("Element affected", $value->details->parameters[0]->value);
//                echo_spaces("Old value", $value->details->parameters[1]->value);
//                echo_spaces("New value", $value->details->parameters[2]->value, 2);
////            }
//        }
        echo_spaces("Object", $resp->json());
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        print 'Expected HTTP Error: ' . $e->getMessage() . PHP_EOL;
    }
}

// helper function for calling API and printing results
function callGetRequest ($endpoint, $params) {
    global $platform;
    try {
        $resp = $platform->get($endpoint, $params);
        header("Content-type: text/plain");
        echo $resp->text();
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        print 'Expected HTTP Error: ' . $e->getMessage() . PHP_EOL;
    }
}

function callUserRollRequest ($endpoint, $params) {
    global $platform;
    try {
        $resp = $platform->get($endpoint, $params);

        $i = 1;
        foreach ($resp->json()->records as $value) {
//            echo_spaces("admins", $value->displayName);
//            if ($value->description) { echo_spaces("desc", $value->description); }
            if (stripos($value->displayName, "admin") == true) {
                // build output
                $outstring = $value->displayName;
                if ($value->description) {
                    $outstring .= " - " . $value->description;
                }
                echo_spaces($i++ . " Admin User Role", $outstring);
            }
        }
//        echo_spaces("result", $resp->json()->records);

    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        print 'Expected HTTP Error: ' . $e->getMessage() . PHP_EOL;
    }
}

function base64url_encode ($plainText) {
    $base64 = base64_encode($plainText);
    $base64 = trim($base64, "=");
    $base64url = strtr($base64, '+/', '-_');
    return ($base64url);
}
/* ============================================= */
/* Get list of Team Messaging Group Chats (Teams) */
/* ============================================= */
function list_tm_teams () {

    $controller = ringcentral_sdk();
    $platform = $controller['platform'];
    $sdk = $controller['SDK'];

    $endpoint = "/team-messaging/v1/chats";

    $params = array(
//        'type' => array('Team'),
        'type' => array( 'Everyone', 'Group', 'Personal', 'Direct', 'Team' ),
        //'recordCount' => 2,
    );

    try {
        $resp = $platform->get($endpoint, $params);
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        //echo_spaces("Error message", $e->getMessage());
        $resp = $e->getMessage() ;
    }

    return $resp;
}

/* =============================================== */
/* Post text to Team Messaging Group Chats (Teams) */
/* =============================================== */
function post_tm_chat ($rcsdk, $chatId, $team_chat) {

    $platform = $rcsdk->platform();

    $body = array(
        'text' => $team_chat,
        // use options below if you are ever going to send attachments to the chat as well.
        // 'attachments' => array(
        //	array(
        //		'id' => '<ENTER VALUE>',
        //		'type' => '<ENTER VALUE>'
        //	)
    );

    try {
        $platform->post("/restapi/v1.0/glip/chats/{$chatId}/posts", $body);
        $result = true;
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        // echo "<br/><br/>Failed to add text to selected Team chat -><br/>" . $e ;
        $result = false;
    }
    return $result;
}
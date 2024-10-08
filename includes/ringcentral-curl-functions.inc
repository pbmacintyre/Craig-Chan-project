<?php
/** Copyright (C) 2019-2024 Paladin Business Solutions */

/* ================= */
/* Generic functions */
/* ================= */

function get_audit_data ($accessToken, $timeStamp) {
    // set date range for 5 minutes on either side of the event timestamp
    $dateTime = new DateTime($timeStamp, new DateTimeZone('AST'));
    $startDateTime = $dateTime->modify('-5 minutes')->format('Y-m-d\TH:i:s.v\Z');
//    $startDateTime = '2024-08-01T00:00:00.000Z';

    $dateTime = new DateTime($timeStamp, new DateTimeZone('AST'));
    $endDateTime = $dateTime->modify('+5 minutes')->format('Y-m-d\TH:i:s.v\Z');
//    $endDateTime = '2024-08-22T00:00:00.000Z';

//    echo_spaces("start date", $startDateTime);
//    echo_spaces("end date", $endDateTime, 1);

    $url = "https://platform.ringcentral.com/restapi/v1.0/account/~/audit-trail/search";

    $params = [
        'eventTimeFrom' => $startDateTime,
        'eventTimeTo' => $endDateTime,
        'page' => 1,
        'perPage' => 100,
        'includeAdmins' => True,
        'includeHidden' => True,
        // only bring back these types of events from the audit trail
        "actionIds" => ['CHANGE_USER_INFO:USER'],
    ];

    $headers = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json",
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    //echo_spaces("data object", $data);
    $audit_data = array();

    foreach ($data['records'] as $value)
        // build an array of events that are applicable to sending to Admins
        $audit_data = [
            "eventType" => $value['eventType'],
            "eventTime" => $value['eventTime'],
            // "actionId" => $value['actionId'],
            "Element Affected" => $value['details']['parameters'][0]['value'],
            "old value" => $value['details']['parameters'][1]['value'],
            "new value" => $value['details']['parameters'][2]['value'],
            "initiator name" => $value['initiator']['name'],
            "initiator extensionId" => $value['initiator']['extensionId'],
            "initiator extensionNumber" => $value['initiator']['extensionNumber'],
            "target name" => $value['target']['name'],
            "target extensionId" => $value['target']['objectId'],
            "target extensionNumber" => $value['target']['extensionNumber'],
            // "actionId" => $data['records']['accountID'],
        ];
    return $audit_data;
}

function get_admins ($accessToken) {
    $endpoint = "https://platform.ringcentral.com/restapi/v1.0/account/~/directory/entries";

    $params = array(
        'showFederated' => 'true',
        'type' => 'User',
        'typeGroup' => '',
        'page' => 1,
        'perPage' => 'all',
        'siteId' => '',
    );

    $endpoint .= '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

//    echo_spaces("data object", $data);
    $admin_data = array();
    $counter2 = 1;
    foreach ($data['records'] as $value) {
        if ($value['type'] == 'User' && $value['status'] == 'Enabled' && $value['phoneNumbers'][0]['phoneNumber']) {
            // if they are enabled user types with a phone number
            $params2 = array(
                'extensionNumber' => $value['extensionNumber'],
            );
            // need to sleep for a while to allow for repeated access to the API without timing out.
            sleep(1);
            try {
                $endpoint2 = "https://platform.ringcentral.com/restapi/v1.0/account/~/extension";
                $endpoint2 .= '?' . http_build_query($params2);

                $ch2 = curl_init();
                curl_setopt_array($ch2, [
                    CURLOPT_URL => $endpoint2,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $accessToken,
                        'Content-Type: application/json'
                    ]
                ]);

                $resp2 = curl_exec($ch2);
                curl_close($ch2);
                $data2 = json_decode($resp2, true);
//                echo_spaces("data 2 object", $data2);

                foreach ($data2['records'] as $value2) {
                    if ($value2['permissions']['admin']['enabled']) {
                        // if they are admin level
                        $admin_data[$counter2] = [
                            "first_name" => $value['firstName'],
                            "last_name" => $value['lastName'],
                            "phone_number" => $value['phoneNumbers'][0]['phoneNumber'],
                            "Admin Permission" => $value2['permissions']['admin']['enabled'],
                        ];
                        $counter2++;
//                        echo_spaces("ID", $value['id']);
//                        echo_spaces("first Name", $value['firstName']);
//                        echo_spaces("last Name", $value['lastName']);
//                        echo_spaces("Extension #", $value['extensionNumber']);
//                        echo_spaces("email", $value['email']);
//                        echo_spaces("phone #", $value['phoneNumbers'][0]['phoneNumber']);
//                        echo_spaces("Account ID", $value['account']['id']);
//                        echo_spaces("Admin Permission", $value2['permissions']['admin']['enabled'], 1);
                    }

                }
            }
            catch (Exception $e) {
                echo_spaces("Error Message resp2", $e->getMessage());
            }
        }
    }
    return $admin_data;
}

function send_admin_sms ($allAdmins, $audit_data, $accessToken) {

    $endpoint = 'https://platform.ringcentral.com/restapi/v1.0/account/~/extension/~/sms';

    $fromNumber = ringcentral_get_from_phone();

    $sms_headers = [
        'Authorization: Bearer ' . $accessToken,
        "Content-Type: application/json"
    ];

    foreach ($allAdmins as $admin) {
        $settingChanged = $audit_data['Element Affected'];
        $oldValue = $audit_data['old value'];
        $newValue = $audit_data['new value'];
        $initiator = $audit_data['initiator name'];

        $message = "The setting '$settingChanged' was changed from '$oldValue' to '$newValue' by: $initiator";
//        echo_spaces("Text message $count", $message);

        $sms_data = [
            'from' => array('phoneNumber' => $fromNumber),
            'to' => array(array('phoneNumber' => '+19029405827')),
            // activate this line when going live and comment out the line above
            // 'to' => array(array('phoneNumber' => $admin['phone_number'])),
            'text' => $message,
        ];

        $sms_ch = curl_init();
        curl_setopt($sms_ch, CURLOPT_URL, $endpoint);
        curl_setopt($sms_ch, CURLOPT_POST, true);
        curl_setopt($sms_ch, CURLOPT_POSTFIELDS, json_encode($sms_data));
        curl_setopt($sms_ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($sms_ch, CURLOPT_HTTPHEADER, $sms_headers);

        $sms_response = curl_exec($sms_ch);
        curl_close($sms_ch);
    }
}

function send_team_message ($audit_data, $accessToken) {

    // this chat id will have to be dynamic or come from the DB... still to be determined
    $chatId = '138271293446';  // "Admin Audit Messages"
    $endpoint = "https://platform.ringcentral.com/team-messaging/v1/chats/$chatId/posts";

    $settingChanged = $audit_data['Element Affected'];
    $oldValue = $audit_data['old value'];
    $newValue = $audit_data['new value'];
    $initiator = $audit_data['initiator name'];

    $message = "The setting '$settingChanged' was changed from '$oldValue' to '$newValue' by: $initiator";

    $data = [
        'text' => $message,
    ];

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);
}

function refresh_tokens ($refreshToken, $client_id, $client_secret) {
    $endpoint = 'https://platform.ringcentral.com/restapi/oauth/token';

    $refresh_params = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken
    ];

    $refresh_headers = [
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        'Content-Type: application/x-www-form-urlencoded'
    ];

    $new_ch = curl_init();
    curl_setopt($new_ch, CURLOPT_URL, $endpoint);
    curl_setopt($new_ch, CURLOPT_POST, true);
    curl_setopt($new_ch, CURLOPT_POSTFIELDS, http_build_query($refresh_params));
    curl_setopt($new_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($new_ch, CURLOPT_HTTPHEADER, $refresh_headers);

    $new_response = curl_exec($new_ch);
    curl_close($new_ch);
    $refresh_data = json_decode($new_response, true);
    $tokens['accessToken'] = $refresh_data['access_token'];
    $tokens['refreshToken'] = $refresh_data['refresh_token'];

    return $tokens;
}

function get_new_access_token ($refreshToken) {
    // only used in testing code. New access tokens are generated
    // in the CRON every 30 minutes

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $client_id = $_ENV['RC_APP_CLIENT_ID'];
    $client_secret = $_ENV['RC_APP_CLIENT_SECRET'];
    $url = 'https://platform.ringcentral.com/restapi/oauth/token';

    $refresh_params = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
    ];
    $refresh_headers = [
        'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret),
        'Content-Type: application/x-www-form-urlencoded'
    ];
    $new_ch = curl_init();
    curl_setopt($new_ch, CURLOPT_URL, $url);
    curl_setopt($new_ch, CURLOPT_POST, true);
    curl_setopt($new_ch, CURLOPT_POSTFIELDS, http_build_query($refresh_params));
    curl_setopt($new_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($new_ch, CURLOPT_HTTPHEADER, $refresh_headers);

    $new_response = curl_exec($new_ch);
    curl_close($new_ch);
    $new_data = json_decode($new_response, true);
    $newAccessToken = $new_data['access_token'];
    $newRefreshToken = $new_data['refresh_token'];
    // save back to DB
    $table = "clients";
    $where_col = "account";
    $where_data = "3058829020";
    $fields_data = $fields_data = array(
        "access" => $newAccessToken,
        "refresh" => $newRefreshToken,
    );
    db_record_update($table, $fields_data, $where_col, $where_data);

//    echo_spaces("new access token", $newAccessToken, 2);

    return $newAccessToken;
}

function send_basic_sms ($accessToken, $message) {

    $endpoint = 'https://platform.ringcentral.com/restapi/v1.0/account/~/extension/~/sms';

    $fromNumber = ringcentral_get_from_phone();
    $sms_data = [
        'from' => array('phoneNumber' => $fromNumber),
        'to' => array(array('phoneNumber' => '+19029405827')),
        'text' => $message,
    ];

    $sms_headers = [
        'Authorization: Bearer ' . $accessToken,
        "Content-Type: application/json"
    ];

    $sms_ch = curl_init();
    curl_setopt($sms_ch, CURLOPT_URL, $endpoint);
    curl_setopt($sms_ch, CURLOPT_POST, true);
    curl_setopt($sms_ch, CURLOPT_POSTFIELDS, json_encode($sms_data));
    curl_setopt($sms_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($sms_ch, CURLOPT_HTTPHEADER, $sms_headers);

    curl_exec($sms_ch);
    curl_close($sms_ch);
}

function is_admin ($accessToken, $accountId, $extensionId) {
    $admin = false;
    try {
        $endpoint = "https://platform.ringcentral.com/restapi/v1.0/account/$accountId/extension/$extensionId";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]
        ]);

        $resp = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($resp, true);

        if ($data['permissions']['admin']['enabled']) {
            $admin = true;
        }

    }
    catch (Exception $e) {
        echo_spaces("Error Message resp", $e->getMessage());
    }

    return $admin;
}
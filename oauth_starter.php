<?php

$client_id = '24pu9Cwlu1fcAtmSh5osBv';
$authorization_url = "https://platform.ringcentral.com/restapi/oauth/authorize?response_type=code&client_id={$client_id}";

header("Location: $authorization_url");
exit();

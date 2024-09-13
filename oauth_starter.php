<?php

$client_id = '5aVrxx9dB3gfhHhtY0fEgo';
$redirect_uri = 'https://paladin-bs.com/craig_chan_project/oauth_sample.php';
$authorization_url = "https://platform.ringcentral.com/restapi/oauth/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&state=your_custom_state";

header("Location: $authorization_url");
exit();


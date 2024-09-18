<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RingCentral Authorization Code Flow Authentication</title>
</head>
<body>
<div align="justify">
    <div style="width:500px">
        <p>
            <b>Important!</b> You need to enable pop-up for this web site in order to login your RingCentral via this Web app.
        </p>
    </div>
    <h2>Login / Authorize your Account</h2></br>
    <?php
    $client_id = '5aVrxx9dB3gfhHhtY0fEgo';
    $authorization_url = "https://platform.ringcentral.com/restapi/oauth/authorize?response_type=code&client_id={$client_id}";
    ?>
    <div>
        <a href='<?= $authorization_url ?>'>Authorize your account</a>&nbsp;&nbsp;&nbsp;

    </div>
</div>
</body>
</html>

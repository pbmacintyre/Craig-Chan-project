<?php
/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 */
ob_start();
session_start();

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-db-functions.inc');
require_once('includes/ringcentral-php-functions.inc');

show_errors();

page_header(1);  // set back to 1 when recaptchas are set in the .ENV file

function show_form ($message, $label = "", $print_again = false) { ?>

    <form action="" method="post">
        <input type="hidden" name="form_token" value="<?php echo generate_form_token(); ?>">
        <table class="CustomTable">
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <img src="images/rc-logo.png"/>
                    <h2><?php app_name(); ?></h2>
                    <?php
                    if ($print_again == true) {
//                        echo "<p class='msg_bad'>" . $message . "</strong></font>";
                        echo_plain_text($message, "red", "large");
                    } else {
//                        echo "<p class='msg_good'>" . $message . "</p>";
                        echo_plain_text($message, "#008EC2", "large");
                    } ?>
                </td>
            </tr>
            <tr class="CustomTable">
                <td class="CustomTableFullCol">
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <input type="submit" class="submit_button" value="   Authorize   " name="authorize">
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <hr>
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <?php app_version(); ?>
                </td>
            </tr>
        </table>
    </form>
    <?php
}

/* ============= */
/*  --- MAIN --- */
/* ============= */
if (isset($_POST['authorize'])) {
    require(__DIR__ . '/includes/vendor/autoload.php');
    $dotenv = Dotenv\Dotenv::createImmutable('includes');
    $dotenv->load();
    $client_id = $_ENV['RC_APP_CLIENT_ID'];
    $authorization_url = "https://platform.ringcentral.com/restapi/oauth/authorize?response_type=code&client_id={$client_id}";

    header("Location: $authorization_url");
} else {
    $_SESSION['login_action'] = false;
    $message = "Please authorize your account. <br/>";
    show_form($message);
}

ob_end_flush();
page_footer();

<?php
/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 */
ob_start();
session_start();
$_SESSION['login_good'] = 0;

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-db-functions.inc');
require_once('includes/ringcentral-php-functions.inc');

show_errors();

page_header(1);

function show_form ($message, $label = "", $print_again = false) {    ?>
    <script>
        <?php
        $table = "ringcentral_control";
        $columns_data = array("grc_site_key");
        $where_info = array("ringcentral_control_id", 1);
        $db_result = db_record_select($table, $columns_data, $where_info);

        $site_key = $db_result[0]['grc_site_key'];  ?>
        grecaptcha.ready(function () {
            grecaptcha.execute('<?= $site_key ?>', {action: 'submit'}).then(function (token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    </script>

    <form action="" method="post">
        <input type="hidden" name="form_token" value="<?php echo generate_form_token(); ?>">
        <table class="CustomTable" >
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <img src="images/rc-logo.png"/>
                    <h2><?php app_name(); ?></h2>
                    <?php
                    if ($print_again == true) {
//                        echo "<p class='msg_bad'>" . $message . "</strong></font>";
                        echo_plain_text($message, "red", "large" );
                    } else {
//                        echo "<p class='msg_good'>" . $message . "</p>";
                        echo_plain_text($message, "#008EC2", "large" );
                    } ?>
                    <hr>
                </td>
            </tr>
            <tr class="CustomTable">
                <td class="left_col">
                    <p style='display: inline; <?php if ($label == "user_email") echo "color:red"; ?>'>Email address:</p>
                </td>
                <td class="right_col">
                    <input type="text" name="user_email" >
                </td>
            </tr>
            <tr class="CustomTable">
                <td class="left_col">
                    <p style='display: inline; <?php if ($label == "user_pass") echo "color:red"; ?>'>Password:</p>
                </td>
                <td class="right_col">
                    <input type="password" name="user_pass" size="5" >
                </td>
            </tr>
            <tr class="CustomTable">
                <td class="CustomTableFullCol">
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                </td>
                <td >
                    <input type="submit" class="submit_button" value="   Login   " name="login">
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol"><hr></td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <a class="links" href="lost_password.php">Lost password</a>
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <?php echo_plain_text("Version 0.1", "grey", "small"); ?>
                </td>
            </tr>
        </table>
    </form>
    <?php

}

function check_form () {

    $print_again = false;
    $label = "";
    $message = "";

    /* ======================================================================== */
    /* check the form token and then if good, process the rest of the form data */
    /* ======================================================================== */
    if (isset($_SESSION['form_token']) && $_POST['form_token'] == $_SESSION['form_token']) {

        $clean_form_data = array(
            "user_email" => strip_tags($_POST['user_email']),
            "user_pass" => strip_tags($_POST['user_pass']),
        );
        /* ====================================== */
        /* ====== data integrity checks ========= */
        /* ====================================== */
        // find out what shop table the email address is in. Then check the password
        $shop_info = get_shop_table("user_email", $clean_form_data['user_email']);
        $table = $shop_info["name"];
        $columns_data = array("user_pass", "mobile");
        $where_info = array("id", $shop_info["id"]);
        $db_result = db_record_select($table, $columns_data, $where_info);

        if (!password_verify($clean_form_data["user_pass"], $db_result[0]['user_pass'])) {
            $print_again = true;
            $label = "";
            $message = "The provided email address or password are invalid,<br/> please check and try again.";
        }
        if ($print_again == true) {
            $_SESSION['login_action'] = false;
            show_form($message, $label, $print_again);
        } else {
            // put user id in the session
            $_SESSION['user_id'] = $shop_info["id"];
            $_SESSION['shop_name'] = $shop_info["name"];
            $_SESSION['shop_type'] = $shop_info["shop_type"];
            //Gen 6 digit code then send it, store it in the session, and validate it
            ringcentral_gen_and_send_six_digit_code($db_result[0]['mobile']);
            header("Location: login_validator.php");
        }
    } else {
        // if failed token testing then just go back to home page
        $_SESSION['form_token'] = "";
        header("Location: index.php");
    }
}

/* ============= */
/*  --- MAIN --- */
/* ============= */
if (isset($_POST['login'])) {
    $_SESSION['login_action'] = true;
    check_form();
} else {
    $_SESSION['login_action'] = false;
//    if ($_GET['pwd'] == 1) {
//        $message = "Your password has been successfully updated";
//    } elseif ($_GET['pwd_reset'] == 1) { {
//        $message = "If we have your email on file we have sent you an email with a link to reset your password.";
//    }
//    } else {
        $message = "Please provide credentials to login to your account. <br/>";
       // $message .= "If you are new here, please \"Register for a Platform\".";
//    }
    show_form($message);
}

ob_end_flush();
page_footer();
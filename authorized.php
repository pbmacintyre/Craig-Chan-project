<?php
/**
 * Copyright (C) 2019-2024 Paladin Business Solutions
 */
ob_start();
session_start();

require_once('includes/ringcentral-functions.inc');
require_once('includes/ringcentral-db-functions.inc');
require_once('includes/ringcentral-php-functions.inc');
require_once('includes/ringcentral-curl-functions.inc');

show_errors();

page_header(0);  // set back to 1 when recaptchas are set in the DB

function show_form ($message, $auth, $label = "", $print_again = false, $color = "#008EC2") {
    $accessToken = $_SESSION['access_token'];
    ?>
    <form action="" method="post">
        <table class="CustomTable">
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <img src="images/rc-logo.png"/>
                    <h2><?php app_name(); ?></h2>
                    <?php
                    echo_plain_text($message, $color, "large");
                    ?>
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <hr>
                </td>
            </tr>
            <?php if ($auth == 1) { ?>
                <tr>
                    <td class="addform_left_col">
                        <p style='display: inline; <?php if ($label == "from_number") echo "color:red"; ?>'>From
                            Number:</p>
                        <?php required_field(); ?>
                    </td>
                    <td class="addform_right_col">
                        <input type="text" name="from_number" value="<?php
                        if ($print_again) {
                            echo strip_tags($_POST['from_number']);
                        }
                        ?>">
                    </td>
                </tr>
                <tr>
                    <td class="addform_left_col">
                        <p style='display: inline; <?php if ($label == "to_number") echo "color:red"; ?>'>To Number:</p>
                        <?php required_field(); ?>
                    </td>
                    <td class="addform_right_col">
                        <input type="text" name="to_number" value="<?php
                        if ($print_again) {
                            echo strip_tags($_POST['to_number']);
                        }
                        ?>">
                    </td>
                </tr>
                <?php
                $response = list_tm_teams($accessToken);
                //        echo_spaces("Chat Groups", $response);
                ?>
                <tr>
                    <td class="addform_left_col">
                        <p style='display: inline; <?php if ($label == "chat_id") echo "color:red"; ?>'>Available Group
                            Chats:</p>
                        <?php required_field(); ?>
                    </td>
                    <td class="addform_right_col">
                        <?php

                        if (!$response) {
                            echo "<font color='red'>No Team Chats are currently available</font>";
                        } else { ?>
                            <select name="chat_id">
                                <option selected value="-1">Choose Team to Post Chat into</option>
                                <?php
                                foreach ($response['records'] as $record) { ?>
                                    <option value="<?php echo $record['id']; ?>"><?php echo $record['name']; ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="CustomTable">
                    <td colspan="2" class="CustomTableFullCol">
                        <input type="submit" class="submit_button" value="   Save   " name="save">
                    </td>
                </tr>
            <?php } ?>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <?php echo_plain_text("Version 0.1", "grey", "small"); ?>
                </td>
            </tr>
        </table>
    </form>
    <?php
}

function check_form ($auth) {
    $print_again = false;
    $label = "";
    $message = "";
    $accessToken = $_SESSION['access_token'];
    // check the formatting of the mobile # == +19991234567
    $pattern = '/^\+\d{11}$/'; // Assumes 11 digits after the '+'

    $from_number = strip_tags($_POST['from_number']);
    $to_number = strip_tags($_POST['to_number']);
    $chat_id = strip_tags($_POST['chat_id']);

    if ($from_number == "") {
        $print_again = true;
        $label = "from_number";
        $message = "Please provide a valid mobile number to send out messages.";
    }
    if (!preg_match($pattern, $from_number)) {
        $print_again = true;
        $label = "from_number";
        $message = "The mobile FROM number is not in the correct format of +19991234567";
    }
    if ($to_number == "") {
        $print_again = true;
        $label = "to_number";
        $message = "Please provide a valid mobile number to receive messages.";
    }
    if (!preg_match($pattern, $to_number)) {
        $print_again = true;
        $label = "to_number";
        $message = "The mobile TO number is not in the correct format of +19991234567";
    }
    if ($chat_id == "-1") {
        $print_again = true;
        $label = "chat_id";
        $message = "Please select a Group Chat to post to.";
    }
    // end edit checks
    if ($print_again == true) {
        $color = "red";
        show_form($message, $auth, $label, $print_again, $color);
    } else {
        // update the record with validated information
        echo_spaces("Ready to save data");

        $table = "clients";



    }
}

/* ============= */
/*  --- MAIN --- */
/* ============= */
$auth = $_GET['authorized'];
if (isset($_POST['save'])) {
    check_form($auth);
} elseif ($auth == 1) {
    $message = "Your account has been authorized. <br/> Please provide the following additional information";
    show_form($message, $auth);
} else {
    $message = "Your account has already been authorized <br/> or it is not an admin level account. <br/>";
    show_form($message, $auth);
}

ob_end_flush();
page_footer();

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

page_header(0);  // set back to 1 when recaptchas are set in the DB

function show_form ($message, $label = "", $print_again = false) { ?>

        <table class="CustomTable">
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <img src="images/rc-logo.png"/>
                    <h2><?php app_name(); ?></h2>
                    <?php
                        echo_plain_text($message, "#008EC2", "large");
                    ?>
                </td>
            </tr>
            <tr class="CustomTable">
                <td colspan="2" class="CustomTableFullCol">
                    <hr>
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

/* ============= */
/*  --- MAIN --- */
/* ============= */
$auth = $_GET['authorized'] ;
if ($auth == 1) {
    $message = "Your account has been authorized. <br/>";
} else {
    $message = "Your account has already been authorized <br/>or it is not an admin level account. <br/>";
}
show_form($message);

ob_end_flush();
page_footer();

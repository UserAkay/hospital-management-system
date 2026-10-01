<?php

/* SESSION TIMEOUT (20 minutes) */
$timeout = 1200; // seconds

if (isset($_SESSION['LAST_ACTIVITY'])) {

    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];

    if ($inactiveTime > $timeout) {

        session_unset();
        session_destroy();

        header("Location: login.php?error=Session expired. Please login again.");
        exit();

    }
}

/* UPDATE LAST ACTIVITY TIME */

$_SESSION['LAST_ACTIVITY'] = time();
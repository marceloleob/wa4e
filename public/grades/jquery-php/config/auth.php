<?php
session_start();

$env = 'local';
$timeout = 1800; // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

$userLogged = false;
$userLoggedId = null;
$userLoggedName = '';

if (isset($_SESSION['user_id'])) {
    $userLogged = true;
    $userLoggedId = $_SESSION['user_id'];
    $userLoggedName = $_SESSION['name'];
}

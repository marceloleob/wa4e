<?php
include '../config/auth.php';
include '../config/db.php';
include '../includes/header.php';
?>

<div class="d-flex justify-content-center align-items-center my-5">
    <h1 class="h3 mb-2 fw-normal">See you soon!</h1>
</div>

<?php
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>
<?php

session_start();

// check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // if not logged in, redirect sa login page
    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'];

?>
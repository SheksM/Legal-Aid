<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    log_action($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
}

session_destroy();
header('Location: login.php');
exit();
?>

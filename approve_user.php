<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$user_id = (int)$_GET['id'];
$action = $_GET['action'];

if (!in_array($action, ['approve', 'reject'])) {
    header('Location: admin.php');
    exit();
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_approved = 0 AND role != 'admin'");
$stmt->execute([$user_id]);
$user_to_approve = $stmt->fetch();

if (!$user_to_approve) {
    header('Location: admin.php');
    exit();
}

if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        log_action($pdo, $_SESSION['user_id'], 'user_approved', "User {$user_to_approve['username']} ({$user_to_approve['role']}) approved");
        $_SESSION['message'] = "User {$user_to_approve['first_name']} {$user_to_approve['last_name']} has been approved.";
        $_SESSION['message_type'] = 'success';
    }
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        log_action($pdo, $_SESSION['user_id'], 'user_rejected', "User {$user_to_approve['username']} ({$user_to_approve['role']}) rejected and deleted");
        $_SESSION['message'] = "User {$user_to_approve['first_name']} {$user_to_approve['last_name']} has been rejected and removed.";
        $_SESSION['message_type'] = 'success';
    }
}

header('Location: admin.php');
exit();
?>

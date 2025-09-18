<?php
// Common functions for the Legal Aid platform

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function check_role($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function check_multiple_roles($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function log_action($pdo, $user_id, $action, $description = null, $ip_address = null) {
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip_address]);
}

function get_user_by_id($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_prison_by_id($pdo, $prison_id) {
    $stmt = $pdo->prepare("SELECT * FROM prisons WHERE id = ?");
    $stmt->execute([$prison_id]);
    return $stmt->fetch();
}

function format_date($date) {
    return date('M j, Y g:i A', strtotime($date));
}

function get_case_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending Verification</span>',
        'verified' => '<span class="badge badge-info">Verified</span>',
        'assigned' => '<span class="badge badge-primary">Assigned</span>',
        'in_progress' => '<span class="badge badge-success">In Progress</span>',
        'completed' => '<span class="badge badge-dark">Completed</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function get_urgency_badge($urgency) {
    $badges = [
        'low' => '<span class="badge badge-success">Low</span>',
        'medium' => '<span class="badge badge-warning">Medium</span>',
        'high' => '<span class="badge badge-danger">High</span>',
        'critical' => '<span class="badge badge-dark">Critical</span>'
    ];
    return $badges[$urgency] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function send_notification($pdo, $user_id, $message, $type = 'info') {
    // Simple notification system - could be expanded to email/SMS
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $message, $type]);
}
?>

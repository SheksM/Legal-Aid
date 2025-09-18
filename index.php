<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect to landing page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Redirect based on user role
switch ($_SESSION['role']) {
    case 'client':
        header('Location: dashboard/client.php');
        break;
    case 'warden':
        header('Location: dashboard/warden.php');
        break;
    case 'lawyer':
        header('Location: dashboard/lawyer.php');
        break;
    case 'admin':
        header('Location: dashboard/admin.php');
        break;
    default:
        header('Location: auth/logout.php');
        break;
}
exit();
?>

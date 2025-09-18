<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['lawyer', 'warden', 'admin'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Get document ID
$document_id = (int)$_GET['id'];
if (!$document_id) {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid document ID');
}

// Get document details with case information
$stmt = $pdo->prepare("
    SELECT cd.*, c.id as case_id, c.prison_id, c.lawyer_id, c.warden_id, c.status
    FROM case_documents cd
    JOIN cases c ON cd.case_id = c.id
    WHERE cd.id = ?
");
$stmt->execute([$document_id]);
$document = $stmt->fetch();

if (!$document) {
    header('HTTP/1.0 404 Not Found');
    exit('Document not found');
}

// Check access permissions
$has_access = false;

if ($_SESSION['role'] === 'admin') {
    $has_access = true;
} elseif ($_SESSION['role'] === 'lawyer') {
    // Lawyers can only download documents from cases assigned to them
    $has_access = ($document['lawyer_id'] == $_SESSION['user_id']);
} elseif ($_SESSION['role'] === 'warden') {
    // Wardens can download documents from cases in their prison
    $stmt = $pdo->prepare("SELECT prison_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $warden_prison = $stmt->fetchColumn();
    
    $has_access = ($document['prison_id'] == $warden_prison);
}

if (!$has_access) {
    header('HTTP/1.0 403 Forbidden');
    exit('You do not have permission to download this document');
}

// Check if file exists
$file_path = $document['document_path'];
if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found on server');
}

// Log the download action
log_action($pdo, $_SESSION['user_id'], 'document_downloaded', "Downloaded document: {$document['document_name']} from case #{$document['case_id']}");

// Set headers for file download
$file_size = filesize($file_path);
$file_name = $document['document_name'];

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($file_path);
exit();
?>

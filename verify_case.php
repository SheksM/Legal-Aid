<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('warden');

$case_id = (int)$_GET['id'];
$error = '';
$success = '';

// Get case details
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           u.first_name as client_first_name, u.last_name as client_last_name, u.email as client_email
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    WHERE c.id = ? AND c.status = 'pending'
");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    header('Location: warden.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $warden_notes = sanitize_input($_POST['warden_notes']);
    
    if ($action === 'verify') {
        $stmt = $pdo->prepare("UPDATE cases SET status = 'verified', warden_id = ?, warden_notes = ?, verified_at = NOW() WHERE id = ?");
        if ($stmt->execute([$_SESSION['user_id'], $warden_notes, $case_id])) {
            log_action($pdo, $_SESSION['user_id'], 'case_verified', "Case #$case_id verified");
            $success = 'Case has been verified and is now available to lawyers.';
        } else {
            $error = 'Error verifying case.';
        }
    } elseif ($action === 'reject') {
        if (empty($warden_notes)) {
            $error = 'Please provide a reason for rejection.';
        } else {
            $stmt = $pdo->prepare("UPDATE cases SET status = 'rejected', warden_id = ?, warden_notes = ? WHERE id = ?");
            if ($stmt->execute([$_SESSION['user_id'], $warden_notes, $case_id])) {
                log_action($pdo, $_SESSION['user_id'], 'case_rejected', "Case #$case_id rejected");
                $success = 'Case has been rejected.';
            } else {
                $error = 'Error rejecting case.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Case - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="warden.php">Dashboard</a></li>
                <li><a href="../legal/resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Case Verification</h1>
            <a href="warden.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <div class="mt-2">
                    <a href="warden.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        <?php else: ?>

        <!-- Case Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Case Details</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <p><strong>Case Title:</strong> <?php echo htmlspecialchars($case['case_title']); ?></p>
                    <p><strong>Client:</strong> <?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($case['client_email']); ?></p>
                    <p><strong>Case Type:</strong> <?php echo ucfirst($case['case_type']); ?></p>
                </div>
                <div>
                    <p><strong>Prison:</strong> <?php echo htmlspecialchars($case['prison_name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($case['prison_location']); ?></p>
                    <p><strong>Urgency:</strong> <?php echo get_urgency_badge($case['urgency_level']); ?></p>
                    <p><strong>Submitted:</strong> <?php echo format_date($case['created_at']); ?></p>
                </div>
            </div>
            
            <div class="mt-3">
                <h4>Case Description:</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--secondary-color);">
                    <?php echo nl2br(htmlspecialchars($case['case_description'])); ?>
                </div>
            </div>
            
            <?php
            // Get case documents
            $doc_stmt = $pdo->prepare("SELECT * FROM case_documents WHERE case_id = ? ORDER BY uploaded_at DESC");
            $doc_stmt->execute([$case_id]);
            $documents = $doc_stmt->fetchAll();
            ?>
            
            <?php if (!empty($documents)): ?>
            <div class="mt-3">
                <h4>Case Documents:</h4>
                <div style="background: #f0f8ff; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--primary-color);">
                    <?php foreach ($documents as $doc): ?>
                        <div style="display: flex; justify-content: between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #e0e0e0;">
                            <div>
                                <strong><?php echo htmlspecialchars($doc['document_name']); ?></strong>
                                <small style="display: block; color: #666;">
                                    Uploaded: <?php echo format_date($doc['uploaded_at']); ?>
                                </small>
                            </div>
                            <a href="download_document.php?id=<?php echo $doc['id']; ?>" 
                               class="btn btn-sm btn-outline" 
                               style="margin-left: auto;">
                                📥 Download
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Verification Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Warden Verification</h3>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="warden_notes" class="form-label">Verification Notes</label>
                    <textarea id="warden_notes" name="warden_notes" class="form-control" rows="4" 
                              placeholder="Add any notes about the verification process, additional context, or recommendations for lawyers..."></textarea>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Important:</strong> Please carefully review the case details and verify that the information is accurate and the request is legitimate before approving.
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" name="action" value="reject" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to reject this case?')">
                        Reject Case
                    </button>
                    <button type="submit" name="action" value="verify" class="btn btn-success" 
                            onclick="return confirm('Are you sure you want to verify this case?')">
                        Verify & Approve Case
                    </button>
                </div>
            </form>
        </div>

        <?php endif; ?>
    </div>
</body>
</html>

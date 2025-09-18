<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('lawyer');

$case_id = (int)$_GET['id'];
$error = '';
$success = '';

// Get case details
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           u.first_name as client_first_name, u.last_name as client_last_name, u.email as client_email, u.phone as client_phone
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    WHERE c.id = ? AND c.lawyer_id = ?
");
$stmt->execute([$case_id, $_SESSION['user_id']]);
$case = $stmt->fetch();

if (!$case) {
    header('Location: lawyer.php');
    exit();
}

// Check if just assigned
$just_assigned = isset($_GET['assigned']) && $_GET['assigned'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $lawyer_notes = sanitize_input($_POST['lawyer_notes']);
    
    if ($action === 'update_progress') {
        $stmt = $pdo->prepare("UPDATE cases SET status = 'in_progress', lawyer_notes = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$lawyer_notes, $case_id])) {
            log_action($pdo, $_SESSION['user_id'], 'case_progress_updated', "Case #$case_id progress updated");
            $success = 'Case progress updated successfully.';
            $case['status'] = 'in_progress';
            $case['lawyer_notes'] = $lawyer_notes;
        } else {
            $error = 'Error updating case progress.';
        }
    } elseif ($action === 'complete_case') {
        $stmt = $pdo->prepare("UPDATE cases SET status = 'completed', lawyer_notes = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$lawyer_notes, $case_id])) {
            log_action($pdo, $_SESSION['user_id'], 'case_completed', "Case #$case_id marked as completed");
            $success = 'Case has been marked as completed.';
            $case['status'] = 'completed';
            $case['lawyer_notes'] = $lawyer_notes;
        } else {
            $error = 'Error completing case.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Case - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="lawyer.php">Dashboard</a></li>
                <li><a href="../legal/resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Case</h1>
            <a href="lawyer.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <?php if ($just_assigned): ?>
            <div class="alert alert-success">
                <strong>Case Successfully Assigned!</strong> You have taken on this case. The client will be notified of your assignment.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Case Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Case Information</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <p><strong>Case Title:</strong> <?php echo htmlspecialchars($case['case_title']); ?></p>
                    <p><strong>Client:</strong> <?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($case['client_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($case['client_phone'] ?? 'Not provided'); ?></p>
                    <p><strong>Case Type:</strong> <?php echo ucfirst($case['case_type']); ?></p>
                </div>
                <div>
                    <p><strong>Prison:</strong> <?php echo htmlspecialchars($case['prison_name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($case['prison_location']); ?></p>
                    <p><strong>Urgency:</strong> <?php echo get_urgency_badge($case['urgency_level']); ?></p>
                    <p><strong>Status:</strong> <?php echo get_case_status_badge($case['status']); ?></p>
                    <p><strong>Assigned:</strong> <?php echo format_date($case['assigned_at']); ?></p>
                </div>
            </div>
            
            <div class="mt-3">
                <h4>Case Description:</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--secondary-color);">
                    <?php echo nl2br(htmlspecialchars($case['case_description'])); ?>
                </div>
            </div>
            
            <?php if ($case['warden_notes']): ?>
            <div class="mt-3">
                <h4>Warden Verification Notes:</h4>
                <div style="background: #e8f5e8; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--success-color);">
                    <?php echo nl2br(htmlspecialchars($case['warden_notes'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
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
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #e0e0e0;">
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

        <!-- Case Management -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Case Management</h3>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="lawyer_notes" class="form-label">Case Notes & Progress Updates</label>
                    <textarea id="lawyer_notes" name="lawyer_notes" class="form-control" rows="6" 
                              placeholder="Document your progress, communications with the client, next steps, and any important case developments..."><?php echo htmlspecialchars($case['lawyer_notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="alert alert-info">
                    <strong>Client Contact:</strong> You can reach the client at <?php echo htmlspecialchars($case['client_email']); ?><?php if ($case['client_phone']): ?> or <?php echo htmlspecialchars($case['client_phone']); ?><?php endif; ?>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 1rem;">
                    <?php if ($case['status'] === 'assigned'): ?>
                        <button type="submit" name="action" value="update_progress" class="btn btn-primary">
                            Start Working on Case
                        </button>
                    <?php elseif ($case['status'] === 'in_progress'): ?>
                        <button type="submit" name="action" value="update_progress" class="btn btn-primary">
                            Update Progress
                        </button>
                        <button type="submit" name="action" value="complete_case" class="btn btn-success" 
                                onclick="return confirm('Are you sure you want to mark this case as completed?')">
                            Mark as Completed
                        </button>
                    <?php elseif ($case['status'] === 'completed'): ?>
                        <div class="alert alert-info">This case has been completed.</div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

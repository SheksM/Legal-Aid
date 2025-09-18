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
           u.first_name as client_first_name, u.last_name as client_last_name, u.email as client_email
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    WHERE c.id = ? AND c.status = 'verified'
");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    header('Location: lawyer.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lawyer_notes = sanitize_input($_POST['lawyer_notes']);
    
    $stmt = $pdo->prepare("UPDATE cases SET status = 'assigned', lawyer_id = ?, lawyer_notes = ?, assigned_at = NOW() WHERE id = ? AND status = 'verified'");
    
    if ($stmt->execute([$_SESSION['user_id'], $lawyer_notes, $case_id])) {
        log_action($pdo, $_SESSION['user_id'], 'case_assigned', "Case #$case_id assigned to lawyer");
        header('Location: manage_case.php?id=' . $case_id . '&assigned=1');
        exit();
    } else {
        $error = 'Error assigning case. It may have been taken by another lawyer.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Case - Legal Aid Beyond Bars</title>
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
            <h1>Take Case</h1>
            <a href="lawyer.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

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
                    <p><strong>Verified:</strong> <?php echo format_date($case['verified_at']); ?></p>
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
                <h4>Warden Notes:</h4>
                <div style="background: #e8f5e8; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--success-color);">
                    <?php echo nl2br(htmlspecialchars($case['warden_notes'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Assignment Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Take This Case</h3>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="lawyer_notes" class="form-label">Initial Notes (Optional)</label>
                    <textarea id="lawyer_notes" name="lawyer_notes" class="form-control" rows="4" 
                              placeholder="Add any initial thoughts, questions, or next steps for this case..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <strong>Commitment:</strong> By taking this case, you are committing to provide pro bono legal assistance to this client. Please ensure you have the time and expertise to handle this type of case.
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="lawyer.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-success" 
                            onclick="return confirm('Are you sure you want to take this case? This action cannot be undone.')">
                        Take This Case
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

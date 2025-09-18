<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_multiple_roles(['client', 'warden', 'lawyer', 'admin']);

$case_id = (int)$_GET['id'];

// Get case details with all related information
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           client.first_name as client_first_name, client.last_name as client_last_name, client.email as client_email,
           warden.first_name as warden_first_name, warden.last_name as warden_last_name,
           lawyer.first_name as lawyer_first_name, lawyer.last_name as lawyer_last_name, lawyer.email as lawyer_email
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users client ON c.client_id = client.id
    LEFT JOIN users warden ON c.warden_id = warden.id
    LEFT JOIN users lawyer ON c.lawyer_id = lawyer.id
    WHERE c.id = ?
");
$stmt->execute([$case_id]);
$case = $stmt->fetch();

if (!$case) {
    header('Location: ' . $_SESSION['role'] . '.php');
    exit();
}

// Check permissions - clients can only view their own cases
if ($_SESSION['role'] === 'client' && $case['client_id'] != $_SESSION['user_id']) {
    header('Location: client.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="<?php echo $_SESSION['role']; ?>.php">Dashboard</a></li>
                <li><a href="../legal/resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Case Details</h1>
            <a href="<?php echo $_SESSION['role']; ?>.php" class="btn btn-outline">Back to Dashboard</a>
        </div>

        <!-- Case Overview -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><?php echo htmlspecialchars($case['case_title']); ?></h3>
                <div class="d-flex" style="gap: 1rem;">
                    <?php echo get_case_status_badge($case['status']); ?>
                    <?php echo get_urgency_badge($case['urgency_level']); ?>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h4>Client Information</h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($case['client_email']); ?></p>
                    <p><strong>Case Type:</strong> <?php echo ucfirst($case['case_type']); ?></p>
                    <p><strong>Submitted:</strong> <?php echo format_date($case['created_at']); ?></p>
                </div>
                <div>
                    <h4>Prison Information</h4>
                    <p><strong>Prison:</strong> <?php echo htmlspecialchars($case['prison_name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($case['prison_location']); ?></p>
                    <p><strong>Urgency Level:</strong> <?php echo ucfirst($case['urgency_level']); ?></p>
                    <?php if ($case['verified_at']): ?>
                        <p><strong>Verified:</strong> <?php echo format_date($case['verified_at']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Case Description -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Case Description</h3>
            </div>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; border-left: 4px solid var(--secondary-color);">
                <?php echo nl2br(htmlspecialchars($case['case_description'])); ?>
            </div>
            
            <?php
            // Get case documents - only show download links to wardens and lawyers
            if (in_array($_SESSION['role'], ['warden', 'lawyer', 'admin'])):
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
            <?php endif; ?>
        </div>

        <!-- Case Progress -->
        <div class="dashboard-grid">
            <!-- Warden Verification -->
            <?php if ($case['warden_notes'] || $case['status'] !== 'pending'): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Warden Verification</h3>
                </div>
                <?php if ($case['warden_first_name']): ?>
                    <p><strong>Verified by:</strong> <?php echo htmlspecialchars($case['warden_first_name'] . ' ' . $case['warden_last_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo format_date($case['verified_at']); ?></p>
                    <?php if ($case['warden_notes']): ?>
                        <div class="mt-2">
                            <strong>Notes:</strong>
                            <div style="background: #e8f5e8; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
                                <?php echo nl2br(htmlspecialchars($case['warden_notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Pending warden verification</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Lawyer Assignment -->
            <?php if ($case['lawyer_first_name'] || $case['status'] === 'verified'): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Legal Representation</h3>
                </div>
                <?php if ($case['lawyer_first_name']): ?>
                    <p><strong>Assigned Lawyer:</strong> <?php echo htmlspecialchars($case['lawyer_first_name'] . ' ' . $case['lawyer_last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($case['lawyer_email']); ?></p>
                    <p><strong>Assigned:</strong> <?php echo format_date($case['assigned_at']); ?></p>
                    <?php if ($case['lawyer_notes']): ?>
                        <div class="mt-2">
                            <strong>Lawyer Notes:</strong>
                            <div style="background: #e3f2fd; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
                                <?php echo nl2br(htmlspecialchars($case['lawyer_notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Available for lawyer assignment</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Case Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Case Timeline</h3>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <strong>Case Submitted</strong> - <?php echo format_date($case['created_at']); ?>
                    <br><small class="text-muted">Case submitted by <?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></small>
                </div>
                
                <?php if ($case['verified_at']): ?>
                <div class="timeline-item">
                    <strong>Case Verified</strong> - <?php echo format_date($case['verified_at']); ?>
                    <br><small class="text-muted">Verified by <?php echo htmlspecialchars($case['warden_first_name'] . ' ' . $case['warden_last_name']); ?></small>
                </div>
                <?php endif; ?>
                
                <?php if ($case['assigned_at']): ?>
                <div class="timeline-item">
                    <strong>Lawyer Assigned</strong> - <?php echo format_date($case['assigned_at']); ?>
                    <br><small class="text-muted">Assigned to <?php echo htmlspecialchars($case['lawyer_first_name'] . ' ' . $case['lawyer_last_name']); ?></small>
                </div>
                <?php endif; ?>
                
                <?php if ($case['status'] === 'completed'): ?>
                <div class="timeline-item">
                    <strong>Case Completed</strong> - <?php echo format_date($case['updated_at']); ?>
                    <br><small class="text-muted">Case marked as completed</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    .timeline-item {
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .timeline-item:last-child {
        border-bottom: none;
    }
    </style>
</body>
</html>

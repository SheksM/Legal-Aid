<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('warden');

$user = get_user_by_id($pdo, $_SESSION['user_id']);

// Get pending cases for verification - only from warden's assigned prison
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           u.first_name as client_first_name, u.last_name as client_last_name, u.email as client_email
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    JOIN users w ON w.id = ? AND w.prison_id = c.prison_id
    WHERE c.status = 'pending'
    ORDER BY c.urgency_level DESC, c.created_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
$pending_cases = $stmt->fetchAll();

// Get verified cases
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           u.first_name as client_first_name, u.last_name as client_last_name
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    WHERE c.warden_id = ? AND c.status != 'pending'
    ORDER BY c.verified_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$verified_cases = $stmt->fetchAll();

// Get statistics - only for warden's assigned prison
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_pending,
        SUM(CASE WHEN urgency_level = 'critical' THEN 1 ELSE 0 END) as critical_cases,
        SUM(CASE WHEN urgency_level = 'high' THEN 1 ELSE 0 END) as high_cases
    FROM cases c
    JOIN users w ON w.id = ? AND w.prison_id = c.prison_id
    WHERE c.status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as verified_by_me FROM cases WHERE warden_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$my_stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Dashboard - Legal Aid Beyond Bars</title>
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
        <h1>Warden Dashboard</h1>
        <p class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>. Review and verify submitted legal aid cases.</p>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_pending']; ?></div>
                <div class="stat-label">Pending Verification</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['critical_cases']; ?></div>
                <div class="stat-label">Critical Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['high_cases']; ?></div>
                <div class="stat-label">High Priority</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $my_stats['verified_by_me']; ?></div>
                <div class="stat-label">Verified by Me</div>
            </div>
        </div>

        <!-- Pending Cases for Verification -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Cases Pending Verification</h3>
            </div>
            
            <?php if (empty($pending_cases)): ?>
                <div class="text-center" style="padding: 2rem;">
                    <p>No cases pending verification at this time.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Case Title</th>
                                <th>Client</th>
                                <th>Prison</th>
                                <th>Type</th>
                                <th>Urgency</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_cases as $case): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                                <td><?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></td>
                                <td><?php echo htmlspecialchars($case['prison_name']); ?></td>
                                <td><?php echo ucfirst($case['case_type']); ?></td>
                                <td><?php echo get_urgency_badge($case['urgency_level']); ?></td>
                                <td><?php echo format_date($case['created_at']); ?></td>
                                <td>
                                    <a href="verify_case.php?id=<?php echo $case['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Review</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recently Verified Cases -->
        <?php if (!empty($verified_cases)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recently Verified Cases</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Case Title</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($verified_cases as $case): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                            <td><?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></td>
                            <td><?php echo get_case_status_badge($case['status']); ?></td>
                            <td><?php echo format_date($case['verified_at']); ?></td>
                            <td>
                                <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

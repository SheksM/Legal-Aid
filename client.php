<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('client');

$user = get_user_by_id($pdo, $_SESSION['user_id']);

// Get user's cases
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           u.first_name as lawyer_first_name, u.last_name as lawyer_last_name
    FROM cases c 
    LEFT JOIN prisons p ON c.prison_id = p.id
    LEFT JOIN users u ON c.lawyer_id = u.id
    WHERE c.client_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cases = $stmt->fetchAll();

// Get case statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_cases,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_cases,
        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_cases,
        SUM(CASE WHEN status = 'assigned' OR status = 'in_progress' THEN 1 ELSE 0 END) as active_cases,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_cases
    FROM cases WHERE client_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="client.php">Dashboard</a></li>
                <li><a href="submit_case.php">Submit Case</a></li>
                <li><a href="../legal/resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
        <p class="mb-4">Manage your legal aid requests and track their progress.</p>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_cases']; ?></div>
                <div class="stat-label">Total Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending_cases']; ?></div>
                <div class="stat-label">Pending Verification</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_cases']; ?></div>
                <div class="stat-label">Active Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_cases']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="d-flex" style="gap: 1rem;">
                <a href="submit_case.php" class="btn btn-primary">Submit New Case</a>
                <a href="../legal/resources.php" class="btn btn-outline">Legal Resources</a>
            </div>
        </div>

        <!-- Cases List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Your Cases</h3>
            </div>
            
            <?php if (empty($cases)): ?>
                <div class="text-center" style="padding: 2rem;">
                    <p>You haven't submitted any cases yet.</p>
                    <a href="submit_case.php" class="btn btn-primary">Submit Your First Case</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Case Title</th>
                                <th>Prison</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Urgency</th>
                                <th>Assigned Lawyer</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cases as $case): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                                <td><?php echo htmlspecialchars($case['prison_name']); ?></td>
                                <td><?php echo ucfirst($case['case_type']); ?></td>
                                <td><?php echo get_case_status_badge($case['status']); ?></td>
                                <td><?php echo get_urgency_badge($case['urgency_level']); ?></td>
                                <td>
                                    <?php if ($case['lawyer_first_name']): ?>
                                        <?php echo htmlspecialchars($case['lawyer_first_name'] . ' ' . $case['lawyer_last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($case['created_at']); ?></td>
                                <td>
                                    <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

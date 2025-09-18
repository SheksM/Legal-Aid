<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$user = get_user_by_id($pdo, $_SESSION['user_id']);

// Get system statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) as clients,
        SUM(CASE WHEN role = 'lawyer' THEN 1 ELSE 0 END) as lawyers,
        SUM(CASE WHEN role = 'warden' THEN 1 ELSE 0 END) as wardens,
        SUM(CASE WHEN is_approved = 0 AND role != 'admin' THEN 1 ELSE 0 END) as pending_approval
    FROM users
");
$stmt->execute();
$user_stats = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_cases,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_cases,
        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_cases,
        SUM(CASE WHEN status = 'assigned' OR status = 'in_progress' THEN 1 ELSE 0 END) as active_cases,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_cases
    FROM cases
");
$stmt->execute();
$case_stats = $stmt->fetch();

// Get pending user approvals
$stmt = $pdo->prepare("SELECT * FROM users WHERE is_approved = 0 AND role != 'admin' ORDER BY created_at DESC");
$stmt->execute();
$pending_users = $stmt->fetchAll();

// Get recent activity
$stmt = $pdo->prepare("
    SELECT sl.*, u.first_name, u.last_name, u.role 
    FROM system_logs sl 
    LEFT JOIN users u ON sl.user_id = u.id 
    ORDER BY sl.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_activity = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_wardens.php">Manage Wardens</a></li>
                <li><a href="manage_prisons.php">Manage Prisons</a></li>
                <li><a href="../legal/manage_resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>System Administration</h1>
        <p class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>. Monitor and manage the Legal Aid platform.</p>

        <!-- System Statistics -->
        <h2>System Overview</h2>
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['pending_approval']; ?></div>
                <div class="stat-label">Pending Approvals</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $case_stats['total_cases']; ?></div>
                <div class="stat-label">Total Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $case_stats['active_cases']; ?></div>
                <div class="stat-label">Active Cases</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- User Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Statistics</h3>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $user_stats['clients']; ?></div>
                        <div class="stat-label">Clients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $user_stats['lawyers']; ?></div>
                        <div class="stat-label">Lawyers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $user_stats['wardens']; ?></div>
                        <div class="stat-label">Wardens</div>
                    </div>
                </div>
            </div>

            <!-- Case Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Case Statistics</h3>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $case_stats['pending_cases']; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $case_stats['verified_cases']; ?></div>
                        <div class="stat-label">Verified</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $case_stats['completed_cases']; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending User Approvals -->
        <?php if (!empty($pending_users)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Pending User Approvals</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $pending_user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pending_user['first_name'] . ' ' . $pending_user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($pending_user['username']); ?></td>
                            <td><?php echo htmlspecialchars($pending_user['email']); ?></td>
                            <td><?php echo ucfirst($pending_user['role']); ?></td>
                            <td><?php echo format_date($pending_user['created_at']); ?></td>
                            <td>
                                <a href="approve_user.php?id=<?php echo $pending_user['id']; ?>&action=approve" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Approve</a>
                                <a href="approve_user.php?id=<?php echo $pending_user['id']; ?>&action=reject" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent System Activity</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $activity): ?>
                        <tr>
                            <td>
                                <?php if ($activity['first_name']): ?>
                                    <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                    <small class="text-muted">(<?php echo $activity['role']; ?>)</small>
                                <?php else: ?>
                                    <span class="text-muted">System</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                            <td><?php echo htmlspecialchars($activity['description'] ?? ''); ?></td>
                            <td><?php echo format_date($activity['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

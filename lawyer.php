<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('lawyer');

$user = get_user_by_id($pdo, $_SESSION['user_id']);

// Get available verified cases
$filter_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$filter_urgency = isset($_GET['urgency']) ? sanitize_input($_GET['urgency']) : '';
$filter_location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';

$where_conditions = ["c.status = 'verified'"];
$params = [];

if ($filter_type) {
    $where_conditions[] = "c.case_type = ?";
    $params[] = $filter_type;
}
if ($filter_urgency) {
    $where_conditions[] = "c.urgency_level = ?";
    $params[] = $filter_urgency;
}
if ($filter_location) {
    $where_conditions[] = "p.county = ?";
    $params[] = $filter_location;
}

$where_clause = implode(' AND ', $where_conditions);

$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location, p.county,
           u.first_name as client_first_name, u.last_name as client_last_name
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    WHERE $where_clause
    ORDER BY c.urgency_level DESC, c.verified_at ASC
");
$stmt->execute($params);
$available_cases = $stmt->fetchAll();

// Get my assigned cases
$stmt = $pdo->prepare("
    SELECT c.*, p.name as prison_name, p.location as prison_location,
           u.first_name as client_first_name, u.last_name as client_last_name, u.email as client_email
    FROM cases c 
    JOIN prisons p ON c.prison_id = p.id
    JOIN users u ON c.client_id = u.id
    WHERE c.lawyer_id = ? AND c.status IN ('assigned', 'in_progress')
    ORDER BY c.assigned_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$my_cases = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as available_cases,
        SUM(CASE WHEN urgency_level = 'critical' THEN 1 ELSE 0 END) as critical_available
    FROM cases WHERE status = 'verified'
");
$stmt->execute();
$stats = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as my_active_cases,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as my_completed_cases
    FROM cases WHERE lawyer_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$my_stats = $stmt->fetch();

// Get unique counties for filter
$stmt = $pdo->prepare("SELECT DISTINCT county FROM prisons ORDER BY county");
$stmt->execute();
$counties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lawyer Dashboard - Legal Aid Beyond Bars</title>
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
        <h1>Lawyer Dashboard</h1>
        <p class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>. Browse and take on verified legal aid cases.</p>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['available_cases']; ?></div>
                <div class="stat-label">Available Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['critical_available']; ?></div>
                <div class="stat-label">Critical Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $my_stats['my_active_cases']; ?></div>
                <div class="stat-label">My Active Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $my_stats['my_completed_cases']; ?></div>
                <div class="stat-label">Completed Cases</div>
            </div>
        </div>

        <!-- My Active Cases -->
        <?php if (!empty($my_cases)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">My Active Cases</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Case Title</th>
                            <th>Client</th>
                            <th>Prison</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_cases as $case): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                            <td><?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></td>
                            <td><?php echo htmlspecialchars($case['prison_name']); ?></td>
                            <td><?php echo ucfirst($case['case_type']); ?></td>
                            <td><?php echo get_case_status_badge($case['status']); ?></td>
                            <td><?php echo format_date($case['assigned_at']); ?></td>
                            <td>
                                <a href="manage_case.php?id=<?php echo $case['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Manage</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Case Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Filter Available Cases</h3>
            </div>
            
            <form method="GET" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="type" class="form-label">Case Type</label>
                        <select id="type" name="type" class="form-control form-select">
                            <option value="">All Types</option>
                            <option value="criminal" <?php echo $filter_type === 'criminal' ? 'selected' : ''; ?>>Criminal</option>
                            <option value="civil" <?php echo $filter_type === 'civil' ? 'selected' : ''; ?>>Civil</option>
                            <option value="family" <?php echo $filter_type === 'family' ? 'selected' : ''; ?>>Family</option>
                            <option value="other" <?php echo $filter_type === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency" class="form-label">Urgency</label>
                        <select id="urgency" name="urgency" class="form-control form-select">
                            <option value="">All Urgency Levels</option>
                            <option value="critical" <?php echo $filter_urgency === 'critical' ? 'selected' : ''; ?>>Critical</option>
                            <option value="high" <?php echo $filter_urgency === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="medium" <?php echo $filter_urgency === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo $filter_urgency === 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location" class="form-label">County</label>
                        <select id="location" name="location" class="form-control form-select">
                            <option value="">All Counties</option>
                            <?php foreach ($counties as $county): ?>
                                <option value="<?php echo $county['county']; ?>" <?php echo $filter_location === $county['county'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($county['county']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter Cases</button>
                        <a href="lawyer.php" class="btn btn-outline ml-2">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Available Cases -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Available Verified Cases</h3>
            </div>
            
            <?php if (empty($available_cases)): ?>
                <div class="text-center" style="padding: 2rem;">
                    <p>No cases match your current filters or no verified cases are available.</p>
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
                                <th>Verified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($available_cases as $case): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                                <td><?php echo htmlspecialchars($case['client_first_name'] . ' ' . $case['client_last_name']); ?></td>
                                <td><?php echo htmlspecialchars($case['prison_name'] . ', ' . $case['county']); ?></td>
                                <td><?php echo ucfirst($case['case_type']); ?></td>
                                <td><?php echo get_urgency_badge($case['urgency_level']); ?></td>
                                <td><?php echo format_date($case['verified_at']); ?></td>
                                <td>
                                    <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                    <a href="take_case.php?id=<?php echo $case['id']; ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Take Case</a>
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

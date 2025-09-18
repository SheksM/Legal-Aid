<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get all published legal resources
$stmt = $pdo->prepare("
    SELECT lr.*, u.first_name, u.last_name 
    FROM legal_resources lr 
    JOIN users u ON lr.created_by = u.id 
    WHERE lr.is_published = 1 
    ORDER BY lr.category, lr.title
");
$stmt->execute();
$resources = $stmt->fetchAll();

// Group resources by category
$grouped_resources = [];
foreach ($resources as $resource) {
    $grouped_resources[$resource['category']][] = $resource;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Resources - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="../dashboard/<?php echo $_SESSION['role']; ?>.php">Dashboard</a></li>
                <li><a href="resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <?php else: ?>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="../auth/login.php">Login</a></li>
                <li><a href="../auth/register.php">Register</a></li>
            </ul>
        </nav>
    </header>
    <?php endif; ?>

    <div class="container">
        <h1>Legal Resources</h1>
        <p class="mb-4">Access important legal information, understand your rights, and learn about legal procedures.</p>

        <!-- Legal Rights -->
        <?php if (isset($grouped_resources['rights'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Your Legal Rights</h3>
            </div>
            
            <?php foreach ($grouped_resources['rights'] as $resource): ?>
            <div class="resource-item">
                <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                <div class="resource-content">
                    <?php echo nl2br(htmlspecialchars($resource['content'])); ?>
                </div>
                <small class="text-muted">
                    Last updated: <?php echo format_date($resource['updated_at']); ?>
                </small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Legal Procedures -->
        <?php if (isset($grouped_resources['procedures'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Legal Procedures</h3>
            </div>
            
            <?php foreach ($grouped_resources['procedures'] as $resource): ?>
            <div class="resource-item">
                <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                <div class="resource-content">
                    <?php echo nl2br(htmlspecialchars($resource['content'])); ?>
                </div>
                <small class="text-muted">
                    Last updated: <?php echo format_date($resource['updated_at']); ?>
                </small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- FAQ -->
        <?php if (isset($grouped_resources['faq'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Frequently Asked Questions</h3>
            </div>
            
            <?php foreach ($grouped_resources['faq'] as $resource): ?>
            <div class="resource-item">
                <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                <div class="resource-content">
                    <?php echo nl2br(htmlspecialchars($resource['content'])); ?>
                </div>
                <small class="text-muted">
                    Last updated: <?php echo format_date($resource['updated_at']); ?>
                </small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Forms -->
        <?php if (isset($grouped_resources['forms'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Legal Forms & Documents</h3>
            </div>
            
            <?php foreach ($grouped_resources['forms'] as $resource): ?>
            <div class="resource-item">
                <h4><?php echo htmlspecialchars($resource['title']); ?></h4>
                <div class="resource-content">
                    <?php echo nl2br(htmlspecialchars($resource['content'])); ?>
                </div>
                <small class="text-muted">
                    Last updated: <?php echo format_date($resource['updated_at']); ?>
                </small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Need More Help?</h3>
            </div>
            
            <div class="alert alert-info">
                <strong>Legal Aid Beyond Bars Platform</strong><br>
                If you need personalized legal assistance, please register and submit your case through our platform. 
                Our verified pro bono lawyers will review your case and provide direct assistance.
            </div>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="text-center">
                <a href="../auth/register.php" class="btn btn-primary">Register for Legal Aid</a>
                <a href="../auth/login.php" class="btn btn-outline">Login</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .resource-item {
        padding: 1.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .resource-item:last-child {
        border-bottom: none;
    }
    .resource-content {
        margin: 1rem 0;
        line-height: 1.6;
    }
    </style>
</body>
</html>

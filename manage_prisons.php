<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$error = '';
$success = '';

// Handle prison management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_prison'])) {
        $name = sanitize_input($_POST['name']);
        $location = sanitize_input($_POST['location']);
        $county = sanitize_input($_POST['county']);
        $contact_phone = sanitize_input($_POST['contact_phone']);
        
        if (empty($name) || empty($location) || empty($county)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO prisons (name, location, county, contact_phone) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $location, $county, $contact_phone])) {
                log_action($pdo, $_SESSION['user_id'], 'prison_added', "Prison added: $name");
                $success = 'Prison added successfully.';
            } else {
                $error = 'Error adding prison.';
            }
        }
    } elseif (isset($_POST['delete_prison'])) {
        $prison_id = (int)$_POST['prison_id'];
        
        // Check if prison has cases
        $stmt = $pdo->prepare("SELECT COUNT(*) as case_count FROM cases WHERE prison_id = ?");
        $stmt->execute([$prison_id]);
        $result = $stmt->fetch();
        
        if ($result['case_count'] > 0) {
            $error = 'Cannot delete prison with existing cases.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM prisons WHERE id = ?");
            if ($stmt->execute([$prison_id])) {
                log_action($pdo, $_SESSION['user_id'], 'prison_deleted', "Prison ID $prison_id deleted");
                $success = 'Prison deleted successfully.';
            } else {
                $error = 'Error deleting prison.';
            }
        }
    }
}

// Get all prisons with case counts
$stmt = $pdo->prepare("
    SELECT p.*, COUNT(c.id) as case_count 
    FROM prisons p 
    LEFT JOIN cases c ON p.id = c.prison_id 
    GROUP BY p.id 
    ORDER BY p.name
");
$stmt->execute();
$prisons = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Prisons - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_prisons.php">Manage Prisons</a></li>
                <li><a href="../legal/manage_resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Prison Management</h1>
        <p class="mb-4">Manage prison facilities in the system.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add New Prison -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Add New Prison</h3>
            </div>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="name" class="form-label">Prison Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="county" class="form-label">County *</label>
                        <input type="text" id="county" name="county" class="form-control" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="location" class="form-label">Location *</label>
                        <input type="text" id="location" name="location" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_phone" class="form-label">Contact Phone</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" placeholder="+254...">
                    </div>
                </div>
                
                <button type="submit" name="add_prison" class="btn btn-primary">Add Prison</button>
            </form>
        </div>

        <!-- Existing Prisons -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Existing Prisons</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Prison Name</th>
                            <th>Location</th>
                            <th>County</th>
                            <th>Contact Phone</th>
                            <th>Cases</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prisons as $prison): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prison['name']); ?></td>
                            <td><?php echo htmlspecialchars($prison['location']); ?></td>
                            <td><?php echo htmlspecialchars($prison['county']); ?></td>
                            <td><?php echo htmlspecialchars($prison['contact_phone'] ?? 'N/A'); ?></td>
                            <td><?php echo $prison['case_count']; ?></td>
                            <td>
                                <?php if ($prison['case_count'] == 0): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="prison_id" value="<?php echo $prison['id']; ?>">
                                        <button type="submit" name="delete_prison" class="btn btn-danger" 
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                                onclick="return confirm('Are you sure you want to delete this prison?')">
                                            Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Has cases</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

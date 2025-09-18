<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

// Get all users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY role, created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Handle user status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action === 'toggle_approval') {
        $stmt = $pdo->prepare("UPDATE users SET is_approved = NOT is_approved WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$user_id])) {
            log_action($pdo, $_SESSION['user_id'], 'user_status_changed', "User ID $user_id approval status toggled");
            header('Location: manage_users.php');
            exit();
        }
    } elseif ($action === 'delete_user') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$user_id])) {
            log_action($pdo, $_SESSION['user_id'], 'user_deleted', "User ID $user_id deleted");
            header('Location: manage_users.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Legal Aid Beyond Bars</title>
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
        <h1>User Management</h1>
        <p class="mb-4">Manage all platform users, approve accounts, and monitor user activity.</p>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Users</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-dark">Admin</span>
                                <?php elseif ($user['is_approved']): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo format_date($user['created_at']); ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="toggle_approval" 
                                                class="btn <?php echo $user['is_approved'] ? 'btn-warning' : 'btn-success'; ?>" 
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                            <?php echo $user['is_approved'] ? 'Suspend' : 'Approve'; ?>
                                        </button>
                                        <button type="submit" name="action" value="delete_user" 
                                                class="btn btn-danger" 
                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                            Delete
                                        </button>
                                    </form>
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

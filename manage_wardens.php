<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$error = '';
$success = '';

// Handle warden registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_warden') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $prison_id = (int)$_POST['prison_id'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($prison_id)) {
        $error = 'Please fill in all required fields.';
    } elseif (strpos($first_name, '.') !== false || strpos($last_name, '.') !== false) {
        $error = 'Names cannot contain periods (.). Please enter a valid name.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Create warden account
            $password_hash = hash_password($password);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, first_name, last_name, phone, prison_id, is_approved) VALUES (?, ?, ?, 'warden', ?, ?, ?, ?, TRUE)");
            
            if ($stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $phone, $prison_id])) {
                $user_id = $pdo->lastInsertId();
                log_action($pdo, $_SESSION['user_id'], 'warden_created', "Created warden account for $first_name $last_name");
                $success = 'Warden account created successfully!';
                
                // Clear form data
                $username = $email = $password = $first_name = $last_name = $phone = $prison_id = '';
            } else {
                $error = 'Error creating warden account. Please try again.';
            }
        }
    }
}

// Handle warden deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_warden') {
    $warden_id = (int)$_POST['warden_id'];
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'warden'");
    if ($stmt->execute([$warden_id])) {
        log_action($pdo, $_SESSION['user_id'], 'warden_deleted', "Deleted warden account ID: $warden_id");
        $success = 'Warden account deleted successfully!';
    } else {
        $error = 'Error deleting warden account.';
    }
}

// Get all prisons
$stmt = $pdo->prepare("SELECT * FROM prisons ORDER BY name");
$stmt->execute();
$prisons = $stmt->fetchAll();

// Get all wardens with their prison information
$stmt = $pdo->prepare("
    SELECT u.*, p.name as prison_name, p.location as prison_location 
    FROM users u 
    LEFT JOIN prisons p ON u.prison_id = p.id 
    WHERE u.role = 'warden' 
    ORDER BY u.created_at DESC
");
$stmt->execute();
$wardens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Wardens - Legal Aid Beyond Bars</title>
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
        <h1>Manage Prison Wardens</h1>
        <p class="mb-4">Create and manage prison warden accounts. Each warden is assigned to a specific prison.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add New Warden Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Add New Prison Warden</h3>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_warden">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" 
                               value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="prison_id" class="form-label">Assigned Prison *</label>
                    <select id="prison_id" name="prison_id" class="form-control form-select" required>
                        <option value="">Select prison</option>
                        <?php foreach ($prisons as $prison): ?>
                            <option value="<?php echo $prison['id']; ?>" 
                                    <?php echo (isset($prison_id) && $prison_id == $prison['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prison['name'] . ' - ' . $prison['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="+254...">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Create Warden Account</button>
            </form>
        </div>

        <!-- Existing Wardens -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current Prison Wardens</h3>
            </div>
            
            <?php if (empty($wardens)): ?>
                <div class="text-center" style="padding: 2rem;">
                    <p>No prison wardens registered yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Assigned Prison</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wardens as $warden): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($warden['first_name'] . ' ' . $warden['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($warden['username']); ?></td>
                                <td><?php echo htmlspecialchars($warden['email']); ?></td>
                                <td><?php echo htmlspecialchars($warden['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($warden['prison_name']): ?>
                                        <?php echo htmlspecialchars($warden['prison_name']); ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($warden['prison_location']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">No prison assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($warden['created_at']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this warden account?');">
                                        <input type="hidden" name="action" value="delete_warden">
                                        <input type="hidden" name="warden_id" value="<?php echo $warden['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function validateName(input) {
        const value = input.value;
        if (value.includes('.')) {
            alert('Please enter a valid name without periods (.)');
            input.focus();
            return false;
        }
        return true;
    }

    // Add event listeners for name validation
    document.getElementById('first_name').addEventListener('blur', function() {
        validateName(this);
    });

    document.getElementById('last_name').addEventListener('blur', function() {
        validateName(this);
    });

    // Prevent form submission if names contain periods
    document.querySelector('form[action=""]').addEventListener('submit', function(e) {
        const firstName = document.getElementById('first_name');
        const lastName = document.getElementById('last_name');
        
        if (!validateName(firstName) || !validateName(lastName)) {
            e.preventDefault();
            return false;
        }
    });
    </script>
</body>
</html>

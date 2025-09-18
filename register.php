<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize_input($_POST['role']);
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($role) || empty($first_name) || empty($last_name)) {
        $error = 'Please fill in all required fields.';
    } elseif (strpos($first_name, '.') !== false || strpos($last_name, '.') !== false) {
        $error = 'Names cannot contain periods (.). Please enter a valid name.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($role, ['client', 'lawyer'])) {
        $error = 'Invalid role selected.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Create user account
            $password_hash = hash_password($password);
            $is_approved = ($role === 'client') ? true : false; // Auto-approve clients, others need admin approval
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, first_name, last_name, phone, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $password_hash, $role, $first_name, $last_name, $phone, $is_approved])) {
                $user_id = $pdo->lastInsertId();
                log_action($pdo, $user_id, 'register', "New $role account created");
                
                if ($is_approved) {
                    $success = 'Account created successfully! You can now log in.';
                } else {
                    $success = 'Account created successfully! Your account is pending approval by an administrator.';
                }
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card" style="max-width: 500px;">
            <div class="login-header">
                <h1 class="login-title">Create Account</h1>
                <p class="login-subtitle">Join our legal aid platform</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="role" class="form-label">I am a *</label>
                    <select id="role" name="role" class="form-control form-select" required>
                        <option value="">Select your role</option>
                        <option value="client">Imprisoned woman or representative</option>
                        <option value="lawyer">Pro bono lawyer</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="+254...">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <small>
                        <strong>Note:</strong> Warden and lawyer accounts require administrator approval before access is granted.
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
                
                <div class="text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
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
    document.querySelector('form').addEventListener('submit', function(e) {
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

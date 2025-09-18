<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && verify_password($password, $user['password_hash'])) {
            if (!$user['is_approved'] && $user['role'] !== 'admin') {
                $error = 'Your account is pending approval. Please contact an administrator.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                log_action($pdo, $user['id'], 'login', 'User logged in');
                
                header('Location: ../index.php');
                exit();
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Legal Aid Beyond Bars</h1>
                <p class="login-subtitle">Connecting imprisoned women with legal support</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                
                <div class="text-center">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
            
            <div class="mt-4">
                <h6>Demo Accounts:</h6>
                <small class="text-muted">
                    Admin: admin / password<br>
                    Test accounts will be created during setup
                </small>
            </div>
        </div>
    </div>
</body>
</html>

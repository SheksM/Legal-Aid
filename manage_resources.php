<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('admin');

$error = '';
$success = '';

// Handle resource management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_resource'])) {
        $title = sanitize_input($_POST['title']);
        $content = sanitize_input($_POST['content']);
        $category = sanitize_input($_POST['category']);
        
        if (empty($title) || empty($content) || empty($category)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO legal_resources (title, content, category, created_by) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$title, $content, $category, $_SESSION['user_id']])) {
                log_action($pdo, $_SESSION['user_id'], 'resource_added', "Legal resource added: $title");
                $success = 'Legal resource added successfully.';
            } else {
                $error = 'Error adding resource.';
            }
        }
    } elseif (isset($_POST['toggle_publish'])) {
        $resource_id = (int)$_POST['resource_id'];
        $stmt = $pdo->prepare("UPDATE legal_resources SET is_published = NOT is_published WHERE id = ?");
        if ($stmt->execute([$resource_id])) {
            log_action($pdo, $_SESSION['user_id'], 'resource_toggled', "Resource ID $resource_id publish status toggled");
            $success = 'Resource status updated.';
        }
    } elseif (isset($_POST['delete_resource'])) {
        $resource_id = (int)$_POST['resource_id'];
        $stmt = $pdo->prepare("DELETE FROM legal_resources WHERE id = ?");
        if ($stmt->execute([$resource_id])) {
            log_action($pdo, $_SESSION['user_id'], 'resource_deleted', "Resource ID $resource_id deleted");
            $success = 'Resource deleted successfully.';
        }
    }
}

// Get all legal resources
$stmt = $pdo->prepare("
    SELECT lr.*, u.first_name, u.last_name 
    FROM legal_resources lr 
    JOIN users u ON lr.created_by = u.id 
    ORDER BY lr.category, lr.title
");
$stmt->execute();
$resources = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Legal Resources - Legal Aid Beyond Bars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="../dashboard/admin.php">Dashboard</a></li>
                <li><a href="../dashboard/manage_users.php">Manage Users</a></li>
                <li><a href="../dashboard/manage_prisons.php">Manage Prisons</a></li>
                <li><a href="manage_resources.php">Legal Resources</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Manage Legal Resources</h1>
        <p class="mb-4">Add and manage legal information, FAQs, and educational content.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add New Resource -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Add New Legal Resource</h3>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="category" class="form-label">Category *</label>
                    <select id="category" name="category" class="form-control form-select" required>
                        <option value="">Select category</option>
                        <option value="rights">Legal Rights</option>
                        <option value="procedures">Legal Procedures</option>
                        <option value="faq">FAQ</option>
                        <option value="forms">Forms & Documents</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content" class="form-label">Content *</label>
                    <textarea id="content" name="content" class="form-control" rows="8" 
                              placeholder="Enter the legal resource content. Use clear, simple language that is easy to understand." required></textarea>
                </div>
                
                <button type="submit" name="add_resource" class="btn btn-primary">Add Resource</button>
            </form>
        </div>

        <!-- Existing Resources -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Existing Legal Resources</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resources as $resource): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resource['title']); ?></td>
                            <td><?php echo ucfirst($resource['category']); ?></td>
                            <td>
                                <?php if ($resource['is_published']): ?>
                                    <span class="badge badge-success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($resource['first_name'] . ' ' . $resource['last_name']); ?></td>
                            <td><?php echo format_date($resource['created_at']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                    <button type="submit" name="toggle_publish" 
                                            class="btn <?php echo $resource['is_published'] ? 'btn-warning' : 'btn-success'; ?>" 
                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        <?php echo $resource['is_published'] ? 'Unpublish' : 'Publish'; ?>
                                    </button>
                                    <button type="submit" name="delete_resource" 
                                            class="btn btn-danger" 
                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                            onclick="return confirm('Are you sure you want to delete this resource?')">
                                        Delete
                                    </button>
                                </form>
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

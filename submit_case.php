<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

check_role('client');

$error = '';
$success = '';

// Get all prisons for the dropdown
$stmt = $pdo->prepare("SELECT * FROM prisons ORDER BY name");
$stmt->execute();
$prisons = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_title = sanitize_input($_POST['case_title']);
    $case_description = sanitize_input($_POST['case_description']);
    $case_type = sanitize_input($_POST['case_type']);
    $urgency_level = sanitize_input($_POST['urgency_level']);
    $prison_id = (int)$_POST['prison_id'];
    
    // Validation
    if (empty($case_title) || empty($case_description) || empty($case_type) || empty($prison_id)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($case_type, ['criminal', 'civil', 'family', 'other'])) {
        $error = 'Invalid case type selected.';
    } elseif (!in_array($urgency_level, ['low', 'medium', 'high', 'critical'])) {
        $error = 'Invalid urgency level selected.';
    } else {
        // Handle file uploads
        $upload_errors = [];
        $uploaded_files = [];
        
        if (!empty($_FILES['case_documents']['name'][0])) {
            $upload_dir = '../uploads/case_documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
            $max_file_size = 5 * 1024 * 1024; // 5MB
            
            foreach ($_FILES['case_documents']['name'] as $key => $name) {
                if (!empty($name)) {
                    $tmp_name = $_FILES['case_documents']['tmp_name'][$key];
                    $size = $_FILES['case_documents']['size'][$key];
                    $error_code = $_FILES['case_documents']['error'][$key];
                    
                    if ($error_code !== UPLOAD_ERR_OK) {
                        $upload_errors[] = "Error uploading $name";
                        continue;
                    }
                    
                    if ($size > $max_file_size) {
                        $upload_errors[] = "$name is too large (max 5MB)";
                        continue;
                    }
                    
                    $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($file_ext, $allowed_types)) {
                        $upload_errors[] = "$name has invalid file type";
                        continue;
                    }
                    
                    $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
                    $unique_name = time() . '_' . $safe_name;
                    $file_path = $upload_dir . $unique_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $uploaded_files[] = [
                            'name' => $name,
                            'path' => $file_path
                        ];
                    } else {
                        $upload_errors[] = "Failed to upload $name";
                    }
                }
            }
        }
        
        if (!empty($upload_errors)) {
            $error = 'File upload errors: ' . implode(', ', $upload_errors);
        } else {
            // Insert the case
            $stmt = $pdo->prepare("INSERT INTO cases (client_id, prison_id, case_title, case_description, case_type, urgency_level) VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$_SESSION['user_id'], $prison_id, $case_title, $case_description, $case_type, $urgency_level])) {
                $case_id = $pdo->lastInsertId();
                
                // Insert uploaded documents
                if (!empty($uploaded_files)) {
                    $doc_stmt = $pdo->prepare("INSERT INTO case_documents (case_id, document_name, document_path, uploaded_by) VALUES (?, ?, ?, ?)");
                    foreach ($uploaded_files as $file) {
                        $doc_stmt->execute([$case_id, $file['name'], $file['path'], $_SESSION['user_id']]);
                    }
                }
                
                log_action($pdo, $_SESSION['user_id'], 'case_submitted', "Case #$case_id submitted: $case_title");
                $success = 'Your case has been submitted successfully! It will be reviewed by a prison warden for verification.';
                
                // Clear form data
                $case_title = $case_description = $case_type = $urgency_level = $prison_id = '';
            } else {
                $error = 'Error submitting case. Please try again.';
                // Clean up uploaded files if case insertion failed
                foreach ($uploaded_files as $file) {
                    if (file_exists($file['path'])) {
                        unlink($file['path']);
                    }
                }
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
    <title>Submit Case - Legal Aid Beyond Bars</title>
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
        <h1>Submit Legal Aid Request</h1>
        <p class="mb-4">Fill out this form to request legal assistance. Your case will be reviewed by a prison warden and then made available to pro bono lawyers.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Case Information</h3>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="case_title" class="form-label">Case Title *</label>
                    <input type="text" id="case_title" name="case_title" class="form-control" 
                           placeholder="Brief description of your legal issue" 
                           value="<?php echo htmlspecialchars($case_title ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prison_id" class="form-label">Prison Location *</label>
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
                        <label for="case_type" class="form-label">Case Type *</label>
                        <select id="case_type" name="case_type" class="form-control form-select" required>
                            <option value="">Select case type</option>
                            <option value="criminal" <?php echo (isset($case_type) && $case_type === 'criminal') ? 'selected' : ''; ?>>Criminal</option>
                            <option value="civil" <?php echo (isset($case_type) && $case_type === 'civil') ? 'selected' : ''; ?>>Civil</option>
                            <option value="family" <?php echo (isset($case_type) && $case_type === 'family') ? 'selected' : ''; ?>>Family</option>
                            <option value="other" <?php echo (isset($case_type) && $case_type === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency_level" class="form-label">Urgency Level *</label>
                        <select id="urgency_level" name="urgency_level" class="form-control form-select" required>
                            <option value="">Select urgency</option>
                            <option value="low" <?php echo (isset($urgency_level) && $urgency_level === 'low') ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo (isset($urgency_level) && $urgency_level === 'medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo (isset($urgency_level) && $urgency_level === 'high') ? 'selected' : ''; ?>>High</option>
                            <option value="critical" <?php echo (isset($urgency_level) && $urgency_level === 'critical') ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="case_description" class="form-label">Detailed Description *</label>
                    <textarea id="case_description" name="case_description" class="form-control" rows="6" 
                              placeholder="Please provide a detailed description of your legal issue, including relevant dates, circumstances, and any previous legal actions taken..."
                              required><?php echo htmlspecialchars($case_description ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="case_documents" class="form-label">Supporting Documents (Optional)</label>
                    <input type="file" id="case_documents" name="case_documents[]" class="form-control" 
                           multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt">
                    <small class="form-text text-muted">
                        Upload relevant documents (PDF, DOC, DOCX, JPG, PNG, TXT). Maximum 5MB per file.
                    </small>
                </div>
                
                <div class="alert alert-info">
                    <strong>Important:</strong> Please provide as much detail as possible. This information will help wardens verify your case and lawyers understand how they can assist you.
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="client.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Case</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

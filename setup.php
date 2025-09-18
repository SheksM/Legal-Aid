<?php
// Setup script for Legal Aid Beyond Bars platform
// Run this file once to initialize the database

require_once 'config/database.php';

echo "<h1>Legal Aid Beyond Bars - Database Setup</h1>";

try {
    // Read and execute schema
    $schema = file_get_contents('database/schema.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p style='color: green;'>✓ Database schema created successfully</p>";
    
    // Read and execute sample data
    if (file_exists('database/sample_data.sql')) {
        $sample_data = file_get_contents('database/sample_data.sql');
        $statements = explode(';', $sample_data);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<p style='color: green;'>✓ Sample data inserted successfully</p>";
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>Your Legal Aid Beyond Bars platform is ready to use.</p>";
    echo "<h3>Default Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: admin, password: password</li>";
    echo "<li><strong>Test Users:</strong> password for all test users: password</li>";
    echo "</ul>";
    echo "<p><a href='auth/login.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

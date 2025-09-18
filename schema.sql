-- Legal Aid Beyond Bars Database Schema
CREATE DATABASE IF NOT EXISTS legal_aid_db;
USE legal_aid_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'warden', 'lawyer', 'admin') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    prison_id INT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prison_id) REFERENCES prisons(id) ON DELETE SET NULL
);

-- Prisons table
CREATE TABLE prisons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    county VARCHAR(50) NOT NULL,
    contact_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cases table
CREATE TABLE cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    prison_id INT NOT NULL,
    case_title VARCHAR(200) NOT NULL,
    case_description TEXT NOT NULL,
    case_type ENUM('criminal', 'civil', 'family', 'other') NOT NULL,
    urgency_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'verified', 'assigned', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
    warden_id INT NULL,
    lawyer_id INT NULL,
    warden_notes TEXT NULL,
    lawyer_notes TEXT NULL,
    verified_at TIMESTAMP NULL,
    assigned_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prison_id) REFERENCES prisons(id),
    FOREIGN KEY (warden_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (lawyer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Case documents table
CREATE TABLE case_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(500) NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Legal resources table
CREATE TABLE legal_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('rights', 'procedures', 'faq', 'forms') NOT NULL,
    is_published BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- System logs table
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample prisons
INSERT INTO prisons (name, location, county, contact_phone) VALUES
('Langata Women\'s Prison', 'Langata, Nairobi', 'Nairobi', '+254-20-891234'),
('Thika Women\'s Prison', 'Thika', 'Kiambu', '+254-67-123456'),
('Eldoret Women\'s Prison', 'Eldoret', 'Uasin Gishu', '+254-53-789012'),
('Mombasa Women\'s Prison', 'Mombasa', 'Mombasa', '+254-41-345678'),
('Nyeri Women\'s Prison', 'Nyeri', 'Nyeri', '+254-61-567890');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role, first_name, last_name, is_approved) VALUES
('admin', 'admin@legalaid.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', TRUE);

-- Insert sample legal resources
INSERT INTO legal_resources (title, content, category, created_by) VALUES
('Your Basic Legal Rights', 'Every person has the right to legal representation, the right to remain silent, and the right to be treated with dignity...', 'rights', 1),
('How to File an Appeal', 'To file an appeal, you must submit your application within 14 days of the judgment...', 'procedures', 1),
('Frequently Asked Questions', 'Q: Can I get free legal help? A: Yes, through this platform you can connect with pro bono lawyers...', 'faq', 1);

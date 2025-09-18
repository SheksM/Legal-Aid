-- Sample test data for Legal Aid Beyond Bars platform
-- Run this after the main schema.sql to populate with test data

USE legal_aid_db;

-- Insert sample users (password for all: password)
INSERT INTO users (username, email, password_hash, role, first_name, last_name, phone, is_approved) VALUES
-- Sample clients
('mary_client', 'mary@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Mary', 'Wanjiku', '+254-700-123456', TRUE),
('grace_rep', 'grace@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Grace', 'Muthoni', '+254-700-234567', TRUE),
('jane_client', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Jane', 'Akinyi', '+254-700-345678', TRUE),

-- Sample wardens
('warden_nairobi', 'warden.nairobi@prisons.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warden', 'John', 'Kamau', '+254-20-891234', TRUE),
('warden_mombasa', 'warden.mombasa@prisons.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warden', 'Sarah', 'Ochieng', '+254-41-345678', TRUE),

-- Sample lawyers
('lawyer_criminal', 'lawyer1@lawfirm.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lawyer', 'David', 'Kipchoge', '+254-722-111222', TRUE),
('lawyer_family', 'lawyer2@advocates.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lawyer', 'Susan', 'Wambui', '+254-733-333444', TRUE),
('lawyer_civil', 'lawyer3@legal.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lawyer', 'Michael', 'Otieno', '+254-744-555666', TRUE);

-- Insert sample cases
INSERT INTO cases (client_id, prison_id, case_title, case_description, case_type, urgency_level, status, warden_id, lawyer_id, warden_notes, lawyer_notes, verified_at, assigned_at) VALUES
-- Completed case
(2, 1, 'Appeal for Wrongful Conviction', 'I was wrongfully convicted of theft in 2022. I have new evidence that proves my innocence including witness statements and CCTV footage that was not presented during my trial. I need help filing an appeal.', 'criminal', 'high', 'completed', 4, 6, 'Case verified. Client has compelling evidence for appeal.', 'Successfully filed appeal. Case is now under review by Court of Appeal.', '2024-01-15 10:30:00', '2024-01-16 14:20:00'),

-- Active case
(3, 2, 'Child Custody Dispute', 'My ex-husband is trying to gain full custody of our children while I am incarcerated. I need legal representation to ensure I maintain my parental rights and that the children are placed with my mother as agreed.', 'family', 'high', 'in_progress', 4, 7, 'Verified. Urgent family matter requiring immediate attention.', 'Currently preparing custody documents and coordinating with family court.', '2024-01-20 09:15:00', '2024-01-21 11:45:00'),

-- Verified case awaiting lawyer
(1, 3, 'Property Rights Dispute', 'My family is trying to sell our ancestral land while I am imprisoned. I need help to stop the sale as I have ownership rights. They are taking advantage of my situation.', 'civil', 'medium', 'verified', 5, NULL, 'Case verified. Client has valid concerns about property rights.', NULL, '2024-01-25 16:20:00', NULL),

-- Pending verification
(2, 4, 'Domestic Violence Case Review', 'I was convicted of assault but it was self-defense against my abusive husband. I have medical records and police reports that were not properly considered. I need help reviewing my case.', 'criminal', 'critical', 'pending', NULL, NULL, NULL, NULL, NULL, NULL),

-- Another pending case
(1, 1, 'Employment Discrimination', 'Before my incarceration, I was unfairly dismissed from my job due to pregnancy. I want to file a case against my former employer for discrimination and seek compensation.', 'civil', 'low', 'pending', NULL, NULL, NULL, NULL, NULL, NULL);

-- Insert additional legal resources
INSERT INTO legal_resources (title, content, category, created_by) VALUES
('Understanding Your Right to Legal Representation', 'Every person charged with a criminal offense has the right to legal representation. If you cannot afford a lawyer, the state must provide one for you. This right extends to all stages of criminal proceedings including:\n\n1. Police interrogation\n2. Court appearances\n3. Sentencing hearings\n4. Appeals process\n\nYou have the right to:\n- Consult with your lawyer in private\n- Have your lawyer present during questioning\n- Request a different lawyer if you are not satisfied\n- Receive competent legal representation', 'rights', 1),

('How to Prepare for Your Appeal', 'Filing an appeal requires careful preparation and adherence to strict deadlines. Here are the key steps:\n\n1. File Notice of Appeal within 14 days of judgment\n2. Obtain complete trial records\n3. Identify legal errors in your trial\n4. Prepare written arguments (appellant brief)\n5. Attend oral arguments if scheduled\n\nCommon grounds for appeal include:\n- Procedural errors during trial\n- Improper jury instructions\n- Insufficient evidence\n- Ineffective legal representation\n- Prosecutorial misconduct', 'procedures', 1),

('Parental Rights While Incarcerated', 'Being in prison does not automatically terminate your parental rights. However, you may face challenges in maintaining custody. Important points:\n\n- You can designate a temporary guardian\n- Regular contact with children should be maintained\n- Participate in available parenting programs\n- Work with social services to create a reunification plan\n- Legal representation is crucial in custody proceedings\n\nThe court will consider the best interests of the child, your rehabilitation efforts, and your release plans.', 'rights', 1),

('Common Legal Questions', 'Q: Can I represent myself in court?\nA: Yes, you have the right to self-representation, but it is strongly recommended to have legal counsel, especially for serious charges.\n\nQ: How long do I have to file an appeal?\nA: Generally 14 days from the date of judgment, but this can vary by case type.\n\nQ: Can my family visit me while my case is pending?\nA: Yes, unless specifically restricted by the court or prison administration.\n\nQ: What if I cannot afford court fees?\nA: You may apply for fee waivers based on financial hardship.\n\nQ: Can I change lawyers if I am not satisfied?\nA: Yes, but the court must approve the change and it may delay proceedings.', 'faq', 1);

-- Insert system logs for sample activity
INSERT INTO system_logs (user_id, action, description, ip_address) VALUES
(1, 'login', 'Admin user logged in', '127.0.0.1'),
(2, 'case_submitted', 'Case #4 submitted: Domestic Violence Case Review', '192.168.1.100'),
(4, 'case_verified', 'Case #1 verified', '192.168.1.101'),
(6, 'case_assigned', 'Case #1 assigned to lawyer', '192.168.1.102'),
(7, 'case_assigned', 'Case #2 assigned to lawyer', '192.168.1.103'),
(6, 'case_completed', 'Case #1 marked as completed', '192.168.1.102');

-- Update existing database to add prison_id to users table
-- Run this script to update an existing database

ALTER TABLE users ADD COLUMN prison_id INT NULL AFTER phone;
ALTER TABLE users ADD FOREIGN KEY (prison_id) REFERENCES prisons(id) ON DELETE SET NULL;

-- Update any existing wardens to have proper prison assignments
-- This is a placeholder - you would need to manually assign wardens to prisons
-- UPDATE users SET prison_id = 1 WHERE role = 'warden' AND username = 'specific_warden_username';

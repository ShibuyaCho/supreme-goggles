-- Cannabis POS Database Initialization for Render
-- This script sets up the basic database structure

-- Create the main database if it doesn't exist
CREATE DATABASE IF NOT EXISTS cannabis_pos_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create the application user
CREATE USER IF NOT EXISTS 'cannabis_user'@'%' IDENTIFIED BY 'secure_password_to_be_changed';

-- Grant privileges
GRANT ALL PRIVILEGES ON cannabis_pos_production.* TO 'cannabis_user'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON cannabis_pos_production.* TO 'cannabis_user'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the cannabis database
USE cannabis_pos_production;

-- Set optimal MySQL settings for the application
SET GLOBAL innodb_buffer_pool_size = 134217728; -- 128MB
SET GLOBAL max_connections = 100;
SET GLOBAL innodb_log_file_size = 67108864; -- 64MB

-- Create basic health check table
CREATE TABLE IF NOT EXISTS health_check (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(20) DEFAULT 'healthy',
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert initial health check record
INSERT INTO health_check (status) VALUES ('healthy') ON DUPLICATE KEY UPDATE checked_at = CURRENT_TIMESTAMP;

-- Log the initialization
INSERT INTO health_check (status) VALUES ('initialized');

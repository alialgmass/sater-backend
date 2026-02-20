-- Create tenant database template for stancl/tenancy
-- Run this script in MySQL before running migrations

CREATE DATABASE IF NOT EXISTS mysql_template CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Verify creation
SHOW DATABASES LIKE 'mysql_template';

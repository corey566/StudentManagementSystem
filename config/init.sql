-- Create database
CREATE DATABASE IF NOT EXISTS education_portal;
USE education_portal;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(20) UNIQUE,
    university VARCHAR(100),
    department ENUM('IT', 'Business Management', 'Biomedical') NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL
);

-- Modules table
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    module_type VARCHAR(50), -- 'Web Application', 'Mobile Application', etc.
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Uploads table
CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    department ENUM('IT', 'Business Management', 'Biomedical') NOT NULL,
    module_type VARCHAR(50), -- Web/Mobile for IT
    module_name VARCHAR(100), -- Specific module name
    category VARCHAR(50) NOT NULL, -- backend, frontend, lecture notes, etc.
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Comments table for moderation
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Downloads tracking
CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    user_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default departments
INSERT INTO departments (name, code) VALUES 
('IT', 'IT'),
('Business Management', 'BM'),
('Biomedical', 'BIO');

-- Insert default modules
INSERT INTO modules (name, department_id, module_type) VALUES
-- IT modules
('Web Development', 1, 'Web Application'),
('Mobile Development', 1, 'Mobile Application'),
-- Business modules
('Marketing Management', 2, NULL),
('Business Law', 2, NULL),
('Financial Accounting', 2, NULL),
-- Biomedical modules
('Human Anatomy', 3, NULL),
('Biomedical Instrumentation', 3, NULL),
('Pharmacology', 3, NULL);

-- Insert default categories
INSERT INTO categories (name, department_id) VALUES
-- IT categories
('Backend', 1),
('Frontend', 1),
('Full Stack', 1),
('Styling (CSS)', 1),
('Native', 1),
('Cross Platform', 1),
-- Business and Biomedical categories
('Lecture Notes', 2),
('Assignment', 2),
('Research Paper', 2),
('Past Paper', 2),
('Lecture Notes', 3),
('Assignment', 3),
('Research Paper', 3),
('Past Paper', 3),
('Lab Report', 3);

-- Create default admin user (password: admin123)
INSERT INTO users (username, password, email, full_name, department, is_admin) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@university.edu', 'System Administrator', 'IT', TRUE);

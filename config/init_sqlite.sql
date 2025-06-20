-- SQLite Database Schema for Education Portal
-- Create tables and insert initial data

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(20) UNIQUE,
    university VARCHAR(100),
    department VARCHAR(50) NOT NULL CHECK (department IN ('IT', 'Business Management', 'Biomedical')),
    is_admin BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL
);

-- Modules table
CREATE TABLE IF NOT EXISTS modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    department_id INTEGER,
    module_type VARCHAR(50),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    department_id INTEGER,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Uploads table
CREATE TABLE IF NOT EXISTS uploads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    department VARCHAR(50) NOT NULL CHECK (department IN ('IT', 'Business Management', 'Biomedical')),
    module_type VARCHAR(50),
    module_name VARCHAR(100),
    category VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER,
    approval_status VARCHAR(20) DEFAULT 'pending' CHECK (approval_status IN ('pending', 'approved', 'rejected')),
    approved_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Comments table for moderation
CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    upload_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    comment TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Downloads tracking
CREATE TABLE IF NOT EXISTS downloads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    upload_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default departments
INSERT OR IGNORE INTO departments (name, code) VALUES 
('IT', 'IT'),
('Business Management', 'BM'),
('Biomedical', 'BIO');

-- Insert default modules
INSERT OR IGNORE INTO modules (name, department_id, module_type) VALUES
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
INSERT OR IGNORE INTO categories (name, department_id) VALUES
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
INSERT OR IGNORE INTO users (username, password, email, full_name, department, is_admin) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@university.edu', 'System Administrator', 'IT', 1);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_uploads_user_id ON uploads(user_id);
CREATE INDEX IF NOT EXISTS idx_uploads_department ON uploads(department);
CREATE INDEX IF NOT EXISTS idx_uploads_approval_status ON uploads(approval_status);
CREATE INDEX IF NOT EXISTS idx_uploads_created_at ON uploads(created_at);
CREATE INDEX IF NOT EXISTS idx_downloads_upload_id ON downloads(upload_id);
CREATE INDEX IF NOT EXISTS idx_downloads_user_id ON downloads(user_id);
CREATE INDEX IF NOT EXISTS idx_comments_upload_id ON comments(upload_id);
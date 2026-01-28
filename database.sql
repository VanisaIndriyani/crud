-- Create Database
CREATE DATABASE IF NOT EXISTS validation_db;
USE validation_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Will store hashed password
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    software VARCHAR(100) NOT NULL,
    version VARCHAR(50),
    description TEXT,
    status ENUM('Draft', 'In Progress', 'Completed') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Project Stages Table
CREATE TABLE IF NOT EXISTS project_stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    status ENUM('Not Started', 'In Progress', 'Completed') DEFAULT 'Not Started',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Insert Default User (admin / password123)
-- Password hash for 'password123' is generated via password_hash()
INSERT INTO users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert Dummy Projects
INSERT INTO projects (id, name, software, version, description, status, created_at) VALUES
(1, 'contoh', 'sap v1', '1.0', 'Example project', 'Draft', '2026-01-28 10:00:00'),
(2, 'AUTOPACK SFSV', 'VEAsoft v1.0', '1.0', 'Integrated application for machine', 'In Progress', '2026-01-22 14:30:00');

-- Insert Stages for Project 1
INSERT INTO project_stages (project_id, name, status) VALUES
(1, 'User Request Specification', 'Not Started'),
(1, 'IQ - Installation Qualification', 'Not Started'),
(1, 'OQ - Operational Qualification', 'Not Started'),
(1, 'PQ - Performance Qualification', 'Not Started'),
(1, 'Laporan Validasi', 'Not Started');

-- Insert Stages for Project 2
INSERT INTO project_stages (project_id, name, status) VALUES
(2, 'User Request Specification', 'Completed'),
(2, 'IQ - Installation Qualification', 'Completed'),
(2, 'OQ - Operational Qualification', 'In Progress'),
(2, 'PQ - Performance Qualification', 'Not Started'),
(2, 'Laporan Validasi', 'Not Started');



-- Database: cms_project (Thinkflow)
CREATE DATABASE IF NOT EXISTS cms_project;
USE cms_project;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    profile_photo VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    github VARCHAR(255) DEFAULT NULL,
    twitter VARCHAR(255) DEFAULT NULL,
    linkedin VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts Table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Likes Table
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@cms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- =============================================
-- Migration: Run these if the database already exists
-- (Safe to run multiple times)
-- =============================================
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT NULL AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL AFTER profile_photo;
ALTER TABLE users ADD COLUMN IF NOT EXISTS website VARCHAR(255) DEFAULT NULL AFTER bio;
ALTER TABLE users ADD COLUMN IF NOT EXISTS github VARCHAR(255) DEFAULT NULL AFTER website;
ALTER TABLE users ADD COLUMN IF NOT EXISTS twitter VARCHAR(255) DEFAULT NULL AFTER github;
ALTER TABLE users ADD COLUMN IF NOT EXISTS linkedin VARCHAR(255) DEFAULT NULL AFTER twitter;

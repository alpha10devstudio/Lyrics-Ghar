-- Lyrics Ghar - Complete Database Schema
-- Created for Islamic Lyrics Platform

CREATE DATABASE IF NOT EXISTS lyricsghar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lyricsghar;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    meta_title VARCHAR(200),
    meta_description VARCHAR(500),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Languages Table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    code VARCHAR(10),
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Artists Table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    biography TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lyrics Table
CREATE TABLE IF NOT EXISTS lyrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    artist_id INT,
    category_id INT,
    language_id INT,
    lyrics TEXT NOT NULL,
    youtube_id VARCHAR(20),
    thumbnail VARCHAR(255),
    description TEXT,
    theme VARCHAR(255),
    writer VARCHAR(150),
    composer VARCHAR(150),
    vocalist VARCHAR(150),
    production VARCHAR(150),
    publisher VARCHAR(150),
    credits TEXT,
    views INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'draft',
    meta_title VARCHAR(200),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_artist (artist_id),
    INDEX idx_category (category_id),
    INDEX idx_language (language_id),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_views (views),
    INDEX idx_created (created_at),
    FULLTEXT INDEX idx_search (title, lyrics, description),
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags Table
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lyrics Tags Junction Table
CREATE TABLE IF NOT EXISTS lyrics_tags (
    lyric_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (lyric_id, tag_id),
    FOREIGN KEY (lyric_id) REFERENCES lyrics(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Views Log Table (for abuse prevention)
CREATE TABLE IF NOT EXISTS views_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lyric_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lyric_ip (lyric_id, ip_address),
    INDEX idx_viewed_at (viewed_at),
    FOREIGN KEY (lyric_id) REFERENCES lyrics(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Categories
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Hamd', 'hamd', 'Praise and glorification of Allah', 1),
('Naat', 'naat', 'Poetry in praise of Prophet Muhammad (PBUH)', 2),
('Ghazal', 'ghazal', 'Islamic ghazals and spiritual poetry', 3),
('Nasheed', 'nasheed', 'Islamic vocal music without instruments', 4),
('Islamic Song', 'islamic-song', 'General Islamic songs and vocals', 5),
('Ramadan', 'ramadan', 'Special Ramadan and Eid songs', 6),
('Children Islamic Song', 'children-islamic-song', 'Islamic songs for children', 7),
('Other Islamic Content', 'other-islamic-content', 'Other permissible Islamic content', 8);

-- Insert Default Languages
INSERT INTO languages (name, slug, code, direction) VALUES
('Bengali', 'bengali', 'bn', 'ltr'),
('Arabic', 'arabic', 'ar', 'rtl'),
('English', 'english', 'en', 'ltr'),
('Urdu', 'urdu', 'ur', 'rtl'),
('Hindi', 'hindi', 'hi', 'ltr'),
('Other', 'other', 'ot', 'ltr');

-- Insert Default Admin (username: admin, password: admin123 - CHANGE IMMEDIATELY)
-- Password hash for 'admin123' using password_hash()
INSERT INTO admin_users (username, email, password_hash, name, role) VALUES
('admin', 'admin@lyricsghar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin');

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'Lyrics Ghar'),
('site_tagline', 'Islamic Lyrics, All in One Place'),
('site_description', 'Discover and read lyrics of Islamic songs, Ghazals, Nasheeds, Hamd, Naat and more.'),
('site_email', 'contact@lyricsghar.great-site.net'),
('posts_per_page', '12'),
('enable_registration', '0');

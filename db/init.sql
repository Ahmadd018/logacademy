CREATE DATABASE IF NOT EXISTS logacademy;
USE logacademy;

-- Normal users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(200) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin table with random suffix (Chain 1 - must find via information_schema)
CREATE TABLE admin_x7k9mq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(200)
);

-- Messages table (Chain 2 - XSS)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    body TEXT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OTP table (Chain 6)
CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    otp VARCHAR(4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -- intentionally no expiry enforcement
);

-- Documents table (Chain 4)
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255),
    original_name VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Search index table (Chain 1 - SQLi target)
CREATE TABLE search_index (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT,
    category VARCHAR(100)
);

-- Seed users
INSERT INTO users (username, email, password, full_name, phone) VALUES
('ahmad.aghamaliyev', 'ahmad@logacademy.local', MD5('password123'), 'Elvin Məmmədov', '+994501234567'),
('aytac.huseynova', 'aytac@logacademy.local', MD5('mypassword'), 'Aytac Hüseynova', '+994552345678'),
('nicat.aliyev', 'nicat@logacademy.local', MD5('nicat2024'), 'Nicat Əliyev', '+994703456789'),
('leyla.rzayeva', 'leyla@logacademy.local', MD5('leyla1234'), 'Leyla Rzayeva', '+994774567890'),
('tural.hasanov', 'tural@logacademy.local', MD5('tural999'), 'Tural Həsənov', '+994605678901');

-- Seed admin (Chain 1 target)
INSERT INTO admin_x7k9mq (username, password, email) VALUES
('superadmin', MD5('Admin@2024!'), 'admin@logacademy.local');

-- Seed search content
INSERT INTO search_index (title, content, category) VALUES
('Tələbə qeydiyyatı haqqında', 'Tələbə qeydiyyatı üçün tələb olunan sənədlər...', 'elanlar'),
('İmtahan cədvəli 2024', 'Yaz semestri imtahan cədvəli aşağıda verilmişdir...', 'imtahan'),
('Təqaüd proqramı', 'Dövlət təqaüdü almaq üçün müraciət edin...', 'maliyyə'),
('Kitabxana saatları', 'Kitabxana həftə içi 09:00-18:00 arası açıqdır...', 'xidmətlər'),
('Yataqxana qaydaları', 'Yataqxanada yaşayan tələbələr üçün qaydalar...', 'yataqxana');

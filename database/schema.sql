CREATE DATABASE IF NOT EXISTS payproof_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE payproof_db;

-- USERS (admin/bursary)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','bursary') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- STUDENTS
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  matric_no VARCHAR(30) NOT NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  department VARCHAR(120) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PAYMENTS
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  reference VARCHAR(80) NOT NULL UNIQUE,
  payment_date DATE NOT NULL,
  channel VARCHAR(50) DEFAULT 'portal',
  status ENUM('paid','pending') NOT NULL DEFAULT 'paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- RECEIPTS
CREATE TABLE IF NOT EXISTS receipts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payment_id INT NOT NULL UNIQUE,
  token VARCHAR(80) NOT NULL UNIQUE,
  receipt_hash CHAR(64) NOT NULL,
  status ENUM('unverified','verified','rejected','reused') NOT NULL DEFAULT 'unverified',
  verified_by INT DEFAULT NULL,
  verified_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- VERIFICATION LOGS (audit)
CREATE TABLE IF NOT EXISTS verification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_id INT DEFAULT NULL,
  actor_id INT DEFAULT NULL,
  action VARCHAR(40) NOT NULL,
  details VARCHAR(255) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE SET NULL,
  FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- No default users are inserted. Run `php database/seed.php` to create demo
-- accounts with random passwords.

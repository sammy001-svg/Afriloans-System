-- Loan Management System Database Schema

-- CREATE DATABASE IF NOT EXISTS loan_system;
-- USE loan_system;

-- Admins / Staff Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'staff') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Loan Products Table
CREATE TABLE IF NOT EXISTS loan_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    interest_rate DECIMAL(5, 2) NOT NULL, -- Annual or monthly rate
    min_amount DECIMAL(15, 2) NOT NULL,
    max_amount DECIMAL(15, 2) NOT NULL,
    min_duration_months INT DEFAULT 1,
    max_duration_months INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clients Table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    address TEXT,
    status ENUM('active', 'blacklisted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Loans Table
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    product_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    interest_amount DECIMAL(15, 2) NOT NULL,
    total_payable DECIMAL(15, 2) NOT NULL,
    duration_months INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'disbursed', 'paid', 'defaulted') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    disbursed_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES loan_products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_ref VARCHAR(100) UNIQUE, -- e.g., M-Pesa Receipt Number
    payment_method ENUM('mpesa', 'bank', 'cash') DEFAULT 'mpesa',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user (password is 'admin123')
INSERT INTO users (username, password, full_name, role) 
VALUES ('admin', '$2y$10$f9O2P9M.r1U1K5xVz4G7O.R6vB5hQx9V0w7B8Z8m1L8yC6R3A2m8G', 'System Administrator', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert some sample loan products
INSERT INTO loan_products (name, description, interest_rate, min_amount, max_amount, max_duration_months) 
VALUES 
('Instant Personal Loan', 'Small loans for urgent needs.', 10.00, 1000.00, 50000.00, 3),
('Business Growth Loan', 'Capital for small and medium businesses.', 5.00, 50000.00, 500000.00, 12),
('Education Loan', 'Loans for school fees and educational expenses.', 3.00, 5000.00, 100000.00, 6)
ON DUPLICATE KEY UPDATE name=name;

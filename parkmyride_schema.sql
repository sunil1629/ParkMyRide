DROP DATABASE IF EXISTS parkmyride;

CREATE DATABASE parkmyride CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE parkmyride;

CREATE TABLE slot_types (
    type VARCHAR(50) PRIMARY KEY
);

INSERT INTO slot_types (type) VALUES
('two-wheeler'),
('compact'),
('suv');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    number_plate VARCHAR(50) NOT NULL,
    type ENUM('bike','compact','suv') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE parking_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_number VARCHAR(50) NOT NULL UNIQUE,
    slot_type VARCHAR(50) NOT NULL,
    status ENUM('available','occupied') DEFAULT 'available',
    FOREIGN KEY (slot_type) REFERENCES slot_types(type)
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    slot_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    status ENUM('active','completed','cancelled') DEFAULT 'active',
    otp_hash VARCHAR(255) NULL,
    otp_expires_at DATETIME NULL,
    otp_attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id)
);

CREATE TABLE parking_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    checkin_time DATETIME,
    checkout_time DATETIME,
    total_hours DECIMAL(6,2),
    amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50),
    status ENUM('pending','paid','failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES parking_sessions(id) ON DELETE CASCADE
);

INSERT INTO parking_slots (slot_number,slot_type,status) VALUES
('A1','compact','available'),
('A2','compact','available'),
('B1','suv','available'),
('B2','suv','available'),
('C1','two-wheeler','available'),
('C2','two-wheeler','available');
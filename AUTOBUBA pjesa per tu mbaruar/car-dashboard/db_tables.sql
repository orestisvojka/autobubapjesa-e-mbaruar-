-- Database: car_dealership
CREATE DATABASE car_dealership;
USE car_dealership;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'sales', 'manager') DEFAULT 'sales',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    mileage INT,
    color VARCHAR(30),
    status ENUM('In Stock', 'Sold', 'Reserved', 'Rented') DEFAULT 'In Stock',
    vin VARCHAR(17) UNIQUE,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_path VARCHAR(255)
);

-- Customers table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sales transactions
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    customer_id INT NOT NULL,
    user_id INT NOT NULL,
    sale_price DECIMAL(10, 2) NOT NULL,
    sale_date DATE NOT NULL,
    payment_method ENUM('Cash', 'Finance', 'Lease'),
    status ENUM('Completed', 'Pending', 'Canceled') DEFAULT 'Completed',
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Rental transactions
CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    customer_id INT NOT NULL,
    user_id INT NOT NULL,
    daily_rate DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    actual_return_date DATE,
    status ENUM('Active', 'Completed', 'Overdue') DEFAULT 'Active',
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample admin user
INSERT INTO users (username, password, full_name, role) 
VALUES ('admin', '$2y$10$yourhashedpasswordhere', 'Admin User', 'admin');
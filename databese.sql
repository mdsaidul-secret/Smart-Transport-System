CREATE DATABASE IF NOT EXISTS transport_system;
USE transport_system;


CREATE TABLE vehicles (
    vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(30) NOT NULL UNIQUE,
    owner_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    vehicle_type VARCHAR(20) NOT NULL,
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    is_blocked INT DEFAULT 0,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE toll_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(30) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(20) UNIQUE,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_number) REFERENCES vehicles(vehicle_number) ON DELETE CASCADE
);

CREATE TABLE fuel_purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(30) NOT NULL,
    fuel_type VARCHAR(20) NOT NULL,
    liters DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_number) REFERENCES vehicles(vehicle_number) ON DELETE CASCADE
);

CREATE TABLE parking_reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(30) NOT NULL,
    hours INT NOT NULL,
    slot_number VARCHAR(10) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_number) REFERENCES vehicles(vehicle_number) ON DELETE CASCADE
);

CREATE TABLE recharges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(30) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    recharge_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_number) REFERENCES vehicles(vehicle_number) ON DELETE CASCADE
);


INSERT INTO vehicles (vehicle_number, owner_name, phone, vehicle_type, wallet_balance) VALUES
('DHAKA-1234', 'Rahman Khan', '01711111111', 'Car', 500.00),
('CHITTAGONG-5678', 'Sultana Begum', '01822222222', 'Bus', 1000.00),
('SYLHET-9012', 'Karim Ali', '01933333333', 'Truck', 200.00),
('RAJSHAHI-4567', 'Fatema Akter', '01644444444', 'Car', 750.00),
('KHULNA-8901', 'Hasan Miah', '01555555555', 'Bike', 100.00);

INSERT INTO toll_payments (vehicle_number, amount, transaction_id) VALUES
('DHAKA-1234', 80, 'TXN1001'),
('CHITTAGONG-5678', 150, 'TXN1002'),
('DHAKA-1234', 80, 'TXN1003'),
('RAJSHAHI-4567', 80, 'TXN1004');

INSERT INTO fuel_purchases (vehicle_number, fuel_type, liters, total_cost) VALUES
('DHAKA-1234', 'Petrol', 10, 14.00),
('SYLHET-9012', 'Diesel', 20, 22.00),
('DHAKA-1234', 'Petrol', 15, 21.00),
('CHITTAGONG-5678', 'Diesel', 30, 33.00),
('KHULNA-8901', 'Petrol', 5, 7.00);

INSERT INTO parking_reservations (vehicle_number, hours, slot_number, total_amount) VALUES
('DHAKA-1234', 2, 'P-101', 50.00),
('CHITTAGONG-5678', 3, 'P-102', 75.00),
('SYLHET-9012', 1, 'P-103', 25.00),
('RAJSHAHI-4567', 2, 'P-104', 50.00);

INSERT INTO recharges (vehicle_number, amount) VALUES
('DHAKA-1234', 100.00),
('CHITTAGONG-5678', 200.00),
('KHULNA-8901', 50.00),
('DHAKA-1234', 150.00)

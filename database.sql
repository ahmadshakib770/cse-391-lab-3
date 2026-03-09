DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS mechanics;

CREATE TABLE mechanics (
    mechanic_id INT AUTO_INCREMENT PRIMARY KEY,
    mechanic_name VARCHAR(100) NOT NULL,
    max_clients INT DEFAULT 4,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_address TEXT NOT NULL,
    client_phone VARCHAR(20) NOT NULL,
    car_license VARCHAR(50) NOT NULL,
    car_engine VARCHAR(50) NOT NULL,
    appointment_date DATE NOT NULL,
    mechanic_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (mechanic_id) REFERENCES mechanics(mechanic_id),
    
    UNIQUE KEY unique_car_per_day (car_license, appointment_date),
    
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_mechanic_id (mechanic_id),
    INDEX idx_client_phone (client_phone),
    INDEX idx_car_license (car_license)
);

INSERT INTO mechanics (mechanic_name, max_clients, status) VALUES
('Ahmed Rahman', 4, 'active'),
('Mohammad Ali', 4, 'active'),
('Karim Hassan', 4, 'active'),
('Rakib Islam', 4, 'active'),
('Fahim Ahmed', 4, 'active');

INSERT INTO appointments (client_name, client_address, client_phone, car_license, car_engine, appointment_date, mechanic_id) VALUES
('Shakib Khan', '12 Gulshan Avenue, Dhaka', '01712345678', 'DHA-GA-1234', 'ENG123456', '2026-03-05', 1),
('Tamim Iqbal', '45 Banani Road, Dhaka', '01823456789', 'DHA-BA-5678', 'ENG789012', '2026-03-05', 1),
('Mushfiqur Rahim', '78 Dhanmondi, Dhaka', '01934567890', 'DHA-DH-9012', 'ENG345678', '2026-03-06', 2);

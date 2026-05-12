-- 1. Create and Select the database
CREATE DATABASE IF NOT EXISTS blood_bank_db;
USE blood_bank_db;

-- 2. Create the appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Used for 'Booking #' and deletion
    event_id INT NOT NULL,                      -- Connects to the event the donor chose
    donor_name VARCHAR(100) NOT NULL,           -- Stores 'Donor Name'
    blood_type VARCHAR(10) NOT NULL,            -- Stores 'Blood Type'
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Used for 'Registration Date'
);

CREATE DATABASE blood_donation_system;

USE blood_donation_system;
-- 1 User Table
CREATE TABLE User (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL,
    status VARCHAR(15) DEFAULT 'Active'
) AUTO_INCREMENT = 1;

-- 2 Donor Table
CREATE TABLE Donor (
    donor_id INT UNSIGNED PRIMARY KEY,
    blood_type VARCHAR(5) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    FOREIGN KEY (donor_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- 3 Event Organizer Table
CREATE TABLE EventOrganizer (
    organizer_id INT UNSIGNED PRIMARY KEY,
    organization_name VARCHAR(50) NOT NULL,
    verification_status VARCHAR(15) NOT NULL,
    FOREIGN KEY (organizer_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- 4 Hospital Table
CREATE TABLE Hospital (
    hospital_id INT UNSIGNED PRIMARY KEY,
    hospital_name VARCHAR(50) NOT NULL,
    address VARCHAR(100) NOT NULL,
    verification_status VARCHAR(15) NOT NULL,
    FOREIGN KEY (hospital_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- 5 Blood Donation Event Table
CREATE TABLE Event (
    event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    event_time VARCHAR(10) NOT NULL,
    venue VARCHAR(100) NOT NULL,
    capacity INT UNSIGNED NOT NULL,
    status VARCHAR(15) NOT NULL,
    organizer_id INT UNSIGNED NOT NULL,
    `desc` TEXT,
    FOREIGN KEY (organizer_id) REFERENCES EventOrganizer(organizer_id)
) AUTO_INCREMENT = 1;

-- 6 Appointment Table
CREATE TABLE Appointment (
    appointment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donor_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(10) NOT NULL,
    status VARCHAR(15) NOT NULL,
    FOREIGN KEY (donor_id) REFERENCES Donor(donor_id),
    FOREIGN KEY (event_id) REFERENCES Event(event_id)
);

-- 7 Blood Inventory Table
CREATE TABLE BloodInventory (
    inventory_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT UNSIGNED NOT NULL,
    blood_type VARCHAR(5) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('Normal','Low','Critical') NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
);

-- 8 Registration Request Table
CREATE TABLE RegistrationRequest (
    request_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT UNSIGNED NOT NULL,
    documents VARCHAR(100) NOT NULL,
    status VARCHAR(15) NOT NULL,
    submit_date DATE NOT NULL,
    rejection_reason VARCHAR(100),
    FOREIGN KEY (hospital_id) REFERENCES Hospital(hospital_id)
);

-- 9 Notification Table
CREATE TABLE Notification (
    notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(150) NOT NULL,
    channel VARCHAR(15) NOT NULL,
    status VARCHAR(15) NOT NULL,
    sent_date DATE NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

-- 10 Report Table
CREATE TABLE Report (
    report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(20) NOT NULL,
    generated_by INT UNSIGNED NOT NULL,
    generated_date DATE NOT NULL,
    export_format VARCHAR(10) NOT NULL,
    FOREIGN KEY (generated_by) REFERENCES User(user_id)
);

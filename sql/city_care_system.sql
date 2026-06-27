CREATE DATABASE IF NOT EXISTS city_care_system;
USE city_care_system;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS complaint_status;
DROP TABLE IF EXISTS complaint_assignment;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'citizen'
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(150) NOT NULL,
    officer_name VARCHAR(150) NOT NULL
);

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    complaint_code VARCHAR(50) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL,
    priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT '',
    status ENUM('Pending','Under Investigation','Resolved','Rejected') NOT NULL DEFAULT 'Pending',
    submit_date DATETIME NOT NULL,
    CONSTRAINT fk_complaint_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE complaint_assignment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL UNIQUE,
    department_id INT NOT NULL,
    assigned_date DATETIME NOT NULL,
    CONSTRAINT fk_assignment_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    CONSTRAINT fk_assignment_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE complaint_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    status VARCHAR(100) NOT NULL,
    notes TEXT NOT NULL,
    update_time DATETIME NOT NULL,
    CONSTRAINT fk_status_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NULL,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO admins (name,email,password) VALUES
('Administrator','admin@example.com','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9');

INSERT INTO users (name,email,phone,password,role) VALUES
('Demo Citizen','citizen@example.com','01700000000','4b4b4c19fdc4b422ca5a52085c3ba8fd2087c62afb06dae791f8fb9c51c56b4b','citizen');

INSERT INTO departments (department_name, officer_name) VALUES
('Road & Infrastructure','Ahsan Rahman'),
('Drainage & Sanitation','Nusrat Islam'),
('Street Lights','Farhan Ahmed'),
('Waste Management','Tanvir Hasan');

INSERT INTO complaints (user_id, complaint_code, category, priority, description, location, image, status, submit_date) VALUES
(1,'CC-20260408-1001','Road & Infrastructure','High','Large pothole causing traffic risk.','Mirpur Road, Dhaka','', 'Pending', NOW()),
(1,'CC-20260408-1002','Street Lights','Medium','Three street lights are not working at night.','Uttara Sector 7','', 'Under Investigation', NOW()),
(1,'CC-20260408-1003','Waste Management','Low','Garbage has not been collected for several days.','Dhanmondi 27','', 'Resolved', NOW());

INSERT INTO complaint_assignment (complaint_id, department_id, assigned_date) VALUES
(2,3,NOW()),
(3,4,NOW());

INSERT INTO complaint_status (complaint_id, status, notes, update_time) VALUES
(1,'Pending','Complaint submitted successfully',NOW()),
(2,'Pending','Complaint submitted successfully',NOW()),
(2,'Under Investigation','Field team assigned for inspection',NOW()),
(3,'Pending','Complaint submitted successfully',NOW()),
(3,'Resolved','Issue resolved and area cleaned',NOW());

INSERT INTO notifications (complaint_id,user_id,title,message,created_at) VALUES
(1,1,'Complaint Submitted','Your complaint CC-20260408-1001 has been submitted successfully.',NOW()),
(2,1,'Complaint Update','Your complaint CC-20260408-1002 is now under investigation.',NOW()),
(3,1,'Complaint Resolved','Your complaint CC-20260408-1003 has been resolved.',NOW());

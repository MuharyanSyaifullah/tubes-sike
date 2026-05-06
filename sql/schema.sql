CREATE DATABASE IF NOT EXISTS sik_rehabilitasi;
USE sik_rehabilitasi;

CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('L','P') NOT NULL,
    address VARCHAR(150),
    diagnosis VARCHAR(100) NOT NULL,
    disability_type VARCHAR(50) NOT NULL,
    status ENUM('stable','monitoring','high-risk','urgent') NOT NULL DEFAULT 'monitoring',
    room VARCHAR(50),
    blood_type VARCHAR(5),
    admission_date DATE,
    clinician VARCHAR(100),
    progress INT DEFAULT 0,
    phase VARCHAR(30),
    latest_assessment DATETIME,
    heart_rate INT,
    spo2 INT,
    blood_pressure VARCHAR(15)
);

CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    report_date DATE NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    narrative TEXT,
    mobility TINYINT,
    communication TINYINT,
    social_skills TINYINT,
    CONSTRAINT fk_reports_patient FOREIGN KEY (patient_id) REFERENCES patients(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE sensor_readings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    session_date DATE NOT NULL,
    angle DECIMAL(5,2),
    gyro_x DECIMAL(8,2),
    gyro_y DECIMAL(8,2),
    gyro_z DECIMAL(8,2),
    CONSTRAINT fk_sensor_patient FOREIGN KEY (patient_id) REFERENCES patients(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

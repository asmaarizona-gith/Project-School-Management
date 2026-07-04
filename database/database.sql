-- ============================================================
-- School Management System - Database Schema
-- Database: school_management
-- ============================================================

CREATE DATABASE IF NOT EXISTS school_management DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE school_management;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USERS TABLE (Admin Login)
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CLASSES TABLE
-- ============================================================
DROP TABLE IF EXISTS classes;
CREATE TABLE classes (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    section VARCHAR(50) DEFAULT NULL,
    room VARCHAR(50) DEFAULT NULL,
    capacity INT(11) DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SUBJECTS TABLE
-- ============================================================
DROP TABLE IF EXISTS subjects;
CREATE TABLE subjects (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TEACHERS TABLE
-- ============================================================
DROP TABLE IF EXISTS teachers;
CREATE TABLE teachers (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    gender ENUM('Male','Female','Other') DEFAULT 'Male',
    qualification VARCHAR(100) DEFAULT NULL,
    subject_id INT(11) UNSIGNED DEFAULT NULL,
    class_id INT(11) UNSIGNED DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_class (class_id),
    INDEX idx_subject (subject_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENTS TABLE
-- ============================================================
DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    gender ENUM('Male','Female','Other') DEFAULT 'Male',
    date_of_birth DATE DEFAULT NULL,
    admission_no VARCHAR(20) NOT NULL UNIQUE,
    class_id INT(11) UNSIGNED DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_admission (admission_no),
    INDEX idx_class (class_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ATTENDANCE TABLE
-- ============================================================
DROP TABLE IF EXISTS attendance;
CREATE TABLE attendance (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) UNSIGNED NOT NULL,
    class_id INT(11) UNSIGNED DEFAULT NULL,
    date DATE NOT NULL,
    status ENUM('Present','Absent','Late','Half Day') NOT NULL DEFAULT 'Present',
    remark VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student (student_id),
    INDEX idx_class (class_id),
    INDEX idx_date (date),
    UNIQUE KEY uk_attendance (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- RESULTS TABLE
-- ============================================================
DROP TABLE IF EXISTS results;
CREATE TABLE results (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) UNSIGNED NOT NULL,
    class_id INT(11) UNSIGNED DEFAULT NULL,
    subject_id INT(11) UNSIGNED NOT NULL,
    marks DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    total_marks DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    grade VARCHAR(2) DEFAULT NULL,
    exam_term ENUM('Term 1','Term 2','Term 3','Final') NOT NULL DEFAULT 'Term 1',
    exam_year YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student (student_id),
    INDEX idx_class (class_id),
    INDEX idx_subject (subject_id),
    INDEX idx_term (exam_term),
    INDEX idx_year (exam_year),
    UNIQUE KEY uk_result (student_id, subject_id, exam_term, exam_year),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- INSERT DEMO DATA
-- ============================================================

-- Admin Account (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@school.com', '$2y$10$0bZO7olgJbNGh/xOWKeujOV20BsMMCgFYiPfIsjc.qODFvX2ZOY.i', 'admin');

-- Classes
INSERT INTO classes (name, section, room, capacity) VALUES
('Grade 1', 'A', 'Room 101', 30),
('Grade 1', 'B', 'Room 102', 30),
('Grade 2', 'A', 'Room 103', 30),
('Grade 2', 'B', 'Room 104', 30),
('Grade 3', 'A', 'Room 201', 30),
('Grade 3', 'B', 'Room 202', 30),
('Grade 4', 'A', 'Room 203', 30),
('Grade 4', 'B', 'Room 204', 30),
('Grade 5', 'A', 'Room 301', 30),
('Grade 5', 'B', 'Room 302', 30);

-- Subjects
INSERT INTO subjects (name, code, description) VALUES
('Mathematics', 'MATH01', 'Fundamental Mathematics'),
('English Language', 'ENG01', 'English Language and Literature'),
('Science', 'SCI01', 'General Science'),
('Social Studies', 'SST01', 'Social Studies and History'),
('Kiswahili', 'KIS01', 'Kiswahili Language'),
('ICT', 'ICT01', 'Information Communication Technology'),
('Physical Education', 'PE01', 'Physical Education and Sports'),
('Art & Craft', 'ART01', 'Creative Arts and Crafts');

-- Teachers
INSERT INTO teachers (firstname, lastname, email, phone, address, gender, qualification, subject_id, class_id) VALUES
('John', 'Mwangi', 'john.mwangi@school.com', '0712345678', '123 Main Street, Nairobi', 'Male', 'B.Ed Mathematics', 1, 1),
('Jane', 'Wanjiku', 'jane.wanjiku@school.com', '0723456789', '456 Park Road, Nairobi', 'Female', 'M.A English', 2, 2),
('Peter', 'Kamau', 'peter.kamau@school.com', '0734567890', '789 Hill Avenue, Nairobi', 'Male', 'B.Sc Science', 3, 3),
('Mary', 'Nyambura', 'mary.nyambura@school.com', '0745678901', '321 Valley Drive, Nairobi', 'Female', 'B.Ed Social Studies', 4, 4),
('David', 'Ochieng', 'david.ochieng@school.com', '0756789012', '654 Lake View, Kisumu', 'Male', 'B.Ed Kiswahili', 5, 5),
('Sarah', 'Akinyi', 'sarah.akinyi@school.com', '0767890123', '987 River Road, Kisumu', 'Female', 'B.Sc ICT', 6, 6),
('James', 'Kiprop', 'james.kiprop@school.com', '0778901234', '147 Eldoret Road, Eldoret', 'Male', 'B.Ed Physical Education', 7, 7),
('Grace', 'Chebet', 'grace.chebet@school.com', '0789012345', '258 Forest Lane, Eldoret', 'Female', 'B.A Fine Arts', 8, 8);

-- Students
INSERT INTO students (firstname, lastname, email, phone, address, gender, date_of_birth, admission_no, class_id) VALUES
('Alice', 'Wanjiku', 'alice.wanjiku@school.com', '0711111111', '100 Sunshine Estate, Nairobi', 'Female', '2016-03-15', 'ADM001', 1),
('Brian', 'Kiprop', 'brian.kiprop@school.com', '0722222222', '200 Green Valley, Nairobi', 'Male', '2016-07-22', 'ADM002', 1),
('Catherine', 'Nyambura', 'catherine.nyambura@school.com', '0733333333', '300 Blue Heights, Nairobi', 'Female', '2016-01-10', 'ADM003', 2),
('Daniel', 'Mwangi', 'daniel.mwangi@school.com', '0744444444', '400 Red House, Nairobi', 'Male', '2015-11-05', 'ADM004', 2),
('Emily', 'Akinyi', 'emily.akinyi@school.com', '0755555555', '500 White Villa, Kisumu', 'Female', '2015-05-30', 'ADM005', 3),
('Francis', 'Ochieng', 'francis.ochieng@school.com', '0766666666', '600 Silver Star, Kisumu', 'Male', '2015-09-18', 'ADM006', 3),
('George', 'Kamau', 'george.kamau@school.com', '0777777777', '700 Golden Gate, Nairobi', 'Male', '2014-02-14', 'ADM007', 4),
('Hannah', 'Chebet', 'hannah.chebet@school.com', '0788888888', '800 Diamond Park, Eldoret', 'Female', '2014-08-25', 'ADM008', 4),
('Ian', 'Maina', 'ian.maina@school.com', '0799999999', '900 Pearl Court, Nairobi', 'Male', '2013-04-20', 'ADM009', 5),
('Janet', 'Wambui', 'janet.wambui@school.com', '0701010101', '101 Ruby Lane, Nairobi', 'Female', '2013-12-01', 'ADM010', 5),
('Kevin', 'Odhiambo', 'kevin.odhiambo@school.com', '0702020202', '202 Emerald Road, Kisumu', 'Male', '2013-06-10', 'ADM011', 6),
('Linda', 'Atieno', 'linda.atieno@school.com', '0703030303', '303 Sapphire Street, Kisumu', 'Female', '2012-10-15', 'ADM012', 6),
('Michael', 'Njoroge', 'michael.njoroge@school.com', '0704040404', '404 Topaz Avenue, Nairobi', 'Male', '2012-03-08', 'ADM013', 7),
('Nancy', 'Wairimu', 'nancy.wairimu@school.com', '0705050505', '505 Amber Drive, Nairobi', 'Female', '2012-07-19', 'ADM014', 7),
('Oscar', 'Kipngeno', 'oscar.kipngeno@school.com', '0706060606', '606 Coral Way, Eldoret', 'Male', '2011-01-25', 'ADM015', 8),
('Patricia', 'Jelagat', 'patricia.jelagat@school.com', '0707070707', '707 Jade Path, Eldoret', 'Female', '2011-09-12', 'ADM016', 8),
('Quinton', 'Mutua', 'quinton.mutua@school.com', '0708080808', '808 Opal Court, Nairobi', 'Male', '2010-05-04', 'ADM017', 9),
('Rose', 'Mbula', 'rose.mbula@school.com', '0709090909', '909 Pearl Street, Nairobi', 'Female', '2010-11-20', 'ADM018', 9),
('Samuel', 'Kipkoech', 'samuel.kipkoech@school.com', '0710101010', '1010 Quartz Lane, Eldoret', 'Male', '2009-08-16', 'ADM019', 10),
('Tina', 'Chepkoech', 'tina.chepkoech@school.com', '0711112222', '1111 Granite Road, Eldoret', 'Female', '2009-02-28', 'ADM020', 10);

-- Attendance (sample)
INSERT INTO attendance (student_id, class_id, date, status) VALUES
(1, 1, '2026-01-10', 'Present'),
(2, 1, '2026-01-10', 'Present'),
(3, 2, '2026-01-10', 'Present'),
(4, 2, '2026-01-10', 'Absent'),
(5, 3, '2026-01-10', 'Present'),
(6, 3, '2026-01-10', 'Late'),
(7, 4, '2026-01-10', 'Present'),
(8, 4, '2026-01-10', 'Present'),
(9, 5, '2026-01-10', 'Present'),
(10, 5, '2026-01-10', 'Absent');

-- Results (sample)
INSERT INTO results (student_id, class_id, subject_id, marks, total_marks, grade, exam_term, exam_year) VALUES
(1, 1, 1, 85.00, 100.00, 'A', 'Term 1', 2026),
(1, 1, 2, 78.00, 100.00, 'B+', 'Term 1', 2026),
(1, 1, 3, 92.00, 100.00, 'A', 'Term 1', 2026),
(2, 1, 1, 72.00, 100.00, 'B', 'Term 1', 2026),
(2, 1, 2, 65.00, 100.00, 'B-', 'Term 1', 2026),
(2, 1, 3, 88.00, 100.00, 'A', 'Term 1', 2026),
(3, 2, 1, 90.00, 100.00, 'A', 'Term 1', 2026),
(3, 2, 4, 82.00, 100.00, 'A-', 'Term 1', 2026),
(4, 2, 1, 55.00, 100.00, 'C', 'Term 1', 2026),
(4, 2, 4, 60.00, 100.00, 'B-', 'Term 1', 2026);

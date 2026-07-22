-- =====================================================================
-- Student Attendance System - Database Schema (3NF)
-- =====================================================================
CREATE DATABASE IF NOT EXISTS student_attendance_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_attendance_system;

-- ---------------------------------------------------------------------
-- Table: users  (admins + teachers login here)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(120) NOT NULL,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','teacher') NOT NULL DEFAULT 'teacher',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: students
-- ---------------------------------------------------------------------
CREATE TABLE students (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    student_number   VARCHAR(30)  NOT NULL UNIQUE,
    full_name        VARCHAR(120) NOT NULL,
    email            VARCHAR(150) NOT NULL UNIQUE,
    phone_encrypted  VARCHAR(255) DEFAULT NULL, -- AES encrypted at application layer
    course_name      VARCHAR(120) NOT NULL,
    year_level       VARCHAR(20)  DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: classes (a subject/section taught by ONE teacher)
-- ---------------------------------------------------------------------
CREATE TABLE classes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_name  VARCHAR(120) NOT NULL,
    class_code  VARCHAR(30)  NOT NULL UNIQUE,
    teacher_id  INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_classes_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: enrollments (many-to-many: students <-> classes)
-- ---------------------------------------------------------------------
CREATE TABLE enrollments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    class_id    INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_class (student_id, class_id),
    CONSTRAINT fk_enroll_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_enroll_class   FOREIGN KEY (class_id)   REFERENCES classes(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: attendance (one row = one student, one class, one date)
-- ---------------------------------------------------------------------
CREATE TABLE attendance (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    student_id       INT NOT NULL,
    class_id         INT NOT NULL,
    attendance_date  DATE NOT NULL,
    status           ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
    recorded_by      INT NOT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_class_date (student_id, class_id, attendance_date),
    CONSTRAINT fk_att_student FOREIGN KEY (student_id)  REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_class   FOREIGN KEY (class_id)    REFERENCES classes(id)  ON DELETE CASCADE,
    CONSTRAINT fk_att_user    FOREIGN KEY (recorded_by) REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- SAMPLE DATA
-- =====================================================================

-- Demo login accounts.
-- Password for BOTH accounts below is:  Password123
-- (hash generated with PHP password_hash() using PASSWORD_DEFAULT/bcrypt)
INSERT INTO users (full_name, username, email, password_hash, role) VALUES
('System Administrator', 'admin', 'admin@school.edu', '$2b$12$WYmm8L4cSxvqj0Neq2fgSe0Bh57.BjSqlgykeyS4KLCt0NF/TXYou', 'admin'),
('Mary Nakato', 'mnakato', 'mary.teacher@school.edu', '$2b$12$WYmm8L4cSxvqj0Neq2fgSe0Bh57.BjSqlgykeyS4KLCt0NF/TXYou', 'teacher'),
('John Okello', 'jokello', 'john.teacher@school.edu', '$2b$12$WYmm8L4cSxvqj0Neq2fgSe0Bh57.BjSqlgykeyS4KLCt0NF/TXYou', 'teacher');

-- Sample classes
INSERT INTO classes (class_name, class_code, teacher_id) VALUES
('Introduction to Programming', 'CS101', 2),
('Database Systems', 'CS201', 2),
('Business Statistics', 'BS150', 3);

-- Sample students (phone_encrypted left NULL - fill in via the app so it gets encrypted properly)
INSERT INTO students (student_number, full_name, email, phone_encrypted, course_name, year_level) VALUES
('STD-0001', 'Alice Namuli', 'alice.namuli@student.edu', NULL, 'BSc. Information Technology', 'Year 2'),
('STD-0002', 'Brian Ssemwogerere', 'brian.s@student.edu', NULL, 'BSc. Information Technology', 'Year 2'),
('STD-0003', 'Grace Achieng', 'grace.a@student.edu', NULL, 'Bachelor of Business Administration', 'Year 1'),
('STD-0004', 'David Mugisha', 'david.m@student.edu', NULL, 'BSc. Information Technology', 'Year 3'),
('STD-0005', 'Fiona Namutebi', 'fiona.n@student.edu', NULL, 'Bachelor of Business Administration', 'Year 1');

-- Sample enrollments
INSERT INTO enrollments (student_id, class_id) VALUES
(1,1),(2,1),(4,1),
(1,2),(4,2),
(3,3),(5,3);

-- Sample attendance for today
INSERT INTO attendance (student_id, class_id, attendance_date, status, recorded_by) VALUES
(1, 1, CURDATE(), 'present', 2),
(2, 1, CURDATE(), 'absent', 2),
(4, 1, CURDATE(), 'late', 2),
(3, 3, CURDATE(), 'present', 3),
(5, 3, CURDATE(), 'excused', 3);

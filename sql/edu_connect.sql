-- Active: 1726913926958@@127.0.0.1@3306@edu_connect_hub
CREATE DATABASE edu_connect_hub;
USE edu_connect_hub;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('student', 'teacher') NOT NULL,
    class ENUM('6', '7', '8', '9', '10') NOT NULL,
    subject ENUM('Bangla', 'English', 'Math', 'Physics', 'Chemistry', 'Biology') NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class ENUM('6', '7', '8', '9', '10') NOT NULL,
    subject VARCHAR(50) NOT NULL,
    chapter VARCHAR(100),
    topic VARCHAR(100),
    questions TEXT NOT NULL,  -- JSON Encoded
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE student_quiz_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_email VARCHAR(100),
    quiz_id INT,
    question_no INT,
    selected_option CHAR(1),
    is_correct TINYINT(1),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);


CREATE TABLE quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    attempted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_email VARCHAR(100) NOT NULL,
    teacher_email VARCHAR(100) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    status ENUM('Present', 'Absent') NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (student_email) REFERENCES users(email),
    FOREIGN KEY (teacher_email) REFERENCES users(email),
    UNIQUE(student_email, teacher_email, subject, date)
);


CREATE TABLE teacher_ppt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE student_quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    class ENUM('6', '7', '8', '9', '10') NOT NULL,
    subject VARCHAR(50) NOT NULL,
    topic VARCHAR(100) NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    selected_answers TEXT NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_name, quiz_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);
CREATE TABLE enrollments (
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    teacher_email VARCHAR(100) NOT NULL,
    UNIQUE (student_email, subject, teacher_email)
);
CREATE TABLE teacher_quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_email VARCHAR(100),
    class ENUM('6', '7', '8', '9', '10') NOT NULL,
    subject VARCHAR(50) NOT NULL,
    quiz_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);

CREATE TABLE student_quiz_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_email VARCHAR(100),
    quiz_id INT,
    attempted TINYINT(1) DEFAULT 0,
    score INT DEFAULT NULL,
    submitted_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE(student_email, quiz_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);















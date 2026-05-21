-- ============================================================
-- Library Management System - Database Setup Script
-- Run this in your MySQL client (phpMyAdmin, MySQL Workbench, etc.)
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- ============================================================
-- TABLE: users
-- Stores login credentials and roles for all system users
-- Roles: 'admin', 'librarian', 'student'
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- Store hashed passwords (PHP password_hash)
    role     ENUM('admin','librarian','student') NOT NULL DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: books
-- Stores the library's book inventory
-- ============================================================
CREATE TABLE IF NOT EXISTS books (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NOT NULL,
    author        VARCHAR(150) NOT NULL,
    isbn          VARCHAR(20)  UNIQUE,
    genre         VARCHAR(80),
    year_published YEAR,
    copies_total  INT NOT NULL DEFAULT 1,   -- total copies owned
    copies_available INT NOT NULL DEFAULT 1, -- copies currently on shelf
    added_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- ============================================================
-- TABLE: Transaction
-- Stores the transaction between librarian and student's
-- ============================================================
CREATE TABLE IF NOT EXISTS booksTransaction (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    userID   INT NOT NULL,
    bookID   INT NOT NULL,
    quantity INT NOT NULL,
    createdBy VARCHAR(80),
    transactionStatus   INT NOT NULL,
    createdDate DATETIME NOT NULL,
    FOREIGN KEY (userID) REFERENCES users(id)
    FOREIGN KEY (bookID) REFERENCES books(id)
)

-- ============================================================
-- SAMPLE DATA
-- Passwords below are bcrypt hashes of the shown plain text.
-- admin123  -> hash for admin & librarian accounts
-- student123 -> hash for student account
-- Generate fresh hashes with: password_hash('yourpassword', PASSWORD_BCRYPT)
-- ============================================================

INSERT INTO users (username, password, role) VALUES
('admin',     '$2y$12$XuCxKbnYHaOJUFVGcFr.IuKp8Oz95Bp5o6uqbPm/V.I1.5PmFdxjy', 'admin'),
('librarian', '$2y$12$XuCxKbnYHaOJUFVGcFr.IuKp8Oz95Bp5o6uqbPm/V.I1.5PmFdxjy', 'librarian'),
('student',   '$2y$12$TH37DkxTSX6tgHMN3sE9E.e0B/a8TkLdkHp.s4yN2rLVUqOUHbFJi', 'student');
-- Plain-text passwords: admin → admin123 | librarian → admin123 | student → student123

INSERT INTO books (title, author, isbn, genre, year_published, copies_total, copies_available) VALUES
('To Kill a Mockingbird',    'Harper Lee',          '978-0-06-112008-4', 'Fiction',       1960, 3, 3),
('1984',                     'George Orwell',       '978-0-45-228285-3', 'Dystopian',     1949, 2, 2),
('The Great Gatsby',         'F. Scott Fitzgerald', '978-0-74-326009-8', 'Classic',       1925, 2, 1),
('A Brief History of Time',  'Stephen Hawking',     '978-0-55-305340-1', 'Science',       1988, 1, 1),
('Sapiens',                  'Yuval Noah Harari',   '978-0-06-231609-7', 'Non-Fiction',   2011, 2, 2),
('The Alchemist',            'Paulo Coelho',        '978-0-06-231500-7', 'Fiction',       1988, 3, 3),
('Introduction to Algorithms','Thomas H. Cormen',   '978-0-26-204630-5', 'Computer Sci.', 2009, 1, 1),
('Pride and Prejudice',      'Jane Austen',         '978-0-14-143951-8', 'Classic',       1813, 2, 2);

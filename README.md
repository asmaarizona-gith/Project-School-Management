# School Management System

A complete, production-ready School Management System built with PHP 8, MySQL, Bootstrap 5, and JavaScript.

## Features

- Professional Admin Dashboard with Statistics
- Student Management (CRUD) with Search & Pagination
- Teacher Management (CRUD) with Search & Pagination
- Class Management (CRUD) with Search & Pagination
- Subject Management (CRUD) with Search & Pagination
- Attendance Tracking with Date & Class Filtering
- Results Management with Grade Calculation
- Secure Authentication System
- Responsive Design (Mobile & Desktop)
- Flash Messages & Form Validation
- PDO Prepared Statements for Security
- XSS Protection via htmlspecialchars

## Installation

1. Copy the project folder to `C:\xampp\htdocs\Project School Management`

2. Start Apache and MySQL in XAMPP Control Panel

3. Open phpMyAdmin and import the database file:
   - `database/database.sql`

4. Open in browser:
   ```
   http://localhost/Project%20School%20Management/
   ```

## Default Admin Login

- **Email:** admin@school.com
- **Password:** admin123

## Requirements

- XAMPP (Apache + MySQL + PHP 8)
- Web browser (Chrome, Firefox, Edge)

## Technology Stack

- **Backend:** PHP 8, PDO, MySQL
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript, Font Awesome
- **Security:** Prepared Statements, Password Hashing, Session Management

## Project Structure

```
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── auth/
├── config/
├── dashboard/
├── database/
├── includes/
├── students/
├── teachers/
├── classes/
├── subjects/
├── attendance/
├── results/
├── uploads/
└── index.php
```

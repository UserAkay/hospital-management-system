Hospital Management System

A role-based Hospital Management System developed as an MCA academic project to manage hospital operations such as patients, doctors, appointments, schedules, medical records, billing, and patient verification.

Overview

The Hospital Management System is a web-based application designed to provide separate functionality for different hospital users. It uses role-based authentication and access control to ensure that administrators, doctors, receptionists, and patients can access the features relevant to their roles.

The application was developed using PHP and MySQL/MariaDB and runs locally using the XAMPP development environment.

Features

Authentication & Authorization

- User login and logout
- Role-based access control
- Separate dashboards for different user roles
- Patient self-registration
- Patient verification workflow
- Forgot password and password reset functionality
- Session-based authentication

Admin Module

- Admin dashboard
- Doctor management
- Patient management
- User account creation
- Doctor activation and deactivation
- Patient restoration
- Doctor schedule management
- Appointment management
- Billing management
- Audit log monitoring
- Patient data export

Doctor Module

- Doctor dashboard
- View assigned appointments
- View patients
- Manage patient visits
- Create medical records
- Manage doctor availability and schedules
- View relevant patient information

Receptionist Module

- Receptionist dashboard
- Manage appointment requests
- Approve or reject patient appointment requests
- Manage appointments
- Assist with patient verification
- Manage billing-related operations

Patient Module

- Patient registration
- Patient verification
- Patient dashboard
- Request appointments
- View appointments
- Cancel appointments
- View visit information
- View medical records
- View bills

Appointment Management

- Appointment request workflow
- Appointment approval and rejection
- Appointment status management
- Appointment cancellation
- Doctor availability and schedule management
- Available time-slot checking

Billing

- Create bills
- View bills
- Track payment status
- Mark bills as paid

Data Export

Patient information can be exported using:

- CSV
- Excel
- PDF

PDF generation is implemented using TCPDF.

Audit Logging

The system includes audit logging to record important application activities for administrative monitoring.

User Roles

Role| Main Responsibilities
Admin| Manage doctors, patients, users, schedules, appointments, billing, and audit logs
Doctor| Manage visits, medical records, appointments, and schedules
Receptionist| Manage appointment requests, patient verification, and reception-related operations
Patient| Register, request appointments, and view appointments, records, and bills

Appointment Workflow

Patient
   |
   v
Appointment Request
   |
   v
Receptionist
   |
   +---- Reject ----> Request Rejected
   |
   +---- Approve ---> Appointment Created
                         |
                         v
                    Appointment

Patient Verification Workflow

Patient verification follows a controlled approval workflow:

Patient Registration
        |
        v
     Pending
        |
   +----+----+
   |         |
Approve     Reject
   |         |
   v         v
Approved   Rejected

Technology Stack

Frontend

- HTML5
- CSS3
- JavaScript
- Tailwind CSS
- Chart.js

Backend

- PHP 8.2

Database

- MySQL / MariaDB
- PDO
- phpMyAdmin

Development Environment

- XAMPP
- Apache
- Visual Studio Code

Additional Library

- TCPDF for PDF generation

Screenshots

Login

"Login Page" (screenshots/01-login.png)

Admin Dashboard

"Admin Dashboard" (screenshots/02-admin-dashboard.png)

Doctor Management

"Manage Doctors" (screenshots/03-manage-doctors.png)

Patient Management

"Manage Patients" (screenshots/04-manage-patients.png)

Appointment Management

"Appointments" (screenshots/05-appointments.png)

Billing

"Billing" (screenshots/06-billing.png)

Database

The project includes the database SQL file:

hospital_management.sql

This SQL file can be imported into MySQL/MariaDB through phpMyAdmin to create the project database and required tables.

Project Structure

hospital-management/
│
├── assets/
│
├── config/
│   ├── database.php
│   └── session_check.php
│
├── controllers/
│   ├── AddDoctorController.php
│   ├── AddPatientController.php
│   ├── CreateAppointmentController.php
│   ├── CreateMedicalRecordController.php
│   ├── UpdateDoctorController.php
│   ├── UpdatePatientController.php
│   ├── UpdateAppointmentStatus.php
│   ├── SetScheduleController.php
│   └── ...
│
├── exports/
│   ├── export_patients_csv.php
│   ├── export_patients_excel.php
│   └── export_patients_pdf.php
│
├── models/
│
├── screenshots/
│   ├── 01-login.png
│   ├── 02-admin-dashboard.png
│   ├── 03-manage-doctors.png
│   ├── 04-manage-patients.png
│   ├── 05-appointments.png
│   └── 06-billing.png
│
├── tcpdf/
│   └── TCPDF library files
│
├── utils/
│   └── audit_logger.php
│
├── views/
│   ├── login.php
│   ├── admin_dashboard.php
│   ├── doctor_dashboard.php
│   ├── reception_dashboard.php
│   ├── patient_dashboard.php
│   └── ...
│
├── .gitignore
├── hospital_management.sql
├── index.php
└── README.md

Installation & Setup

1. Install XAMPP

Install XAMPP with Apache and MySQL/MariaDB.

2. Clone the Repository

Clone the repository and place the project inside the XAMPP "htdocs" directory:

xampp/htdocs/hospital-management

3. Start XAMPP

Start the following services from the XAMPP Control Panel:

Apache
MySQL

4. Create the Database

Open phpMyAdmin and create a database named:

hospital_management

5. Import the SQL File

Import the following file into the "hospital_management" database:

hospital_management.sql

6. Configure Database Connection

The default local XAMPP configuration uses:

Host: localhost
Database: hospital_management
Username: root
Password: empty

The database configuration is located at:

config/database.php

Update the credentials if your local MySQL/MariaDB configuration is different.

7. Run the Application

Open the following URL in your browser:

http://localhost/hospital-management/

Security Considerations

- Passwords are handled using PHP password hashing mechanisms.
- Database operations use PDO prepared statements.
- Session-based authentication is implemented.
- Role-based authorization restricts access to protected modules.
- Sensitive configuration files and environment files are excluded through ".gitignore".

Project Purpose

This project was developed as part of the MCA academic curriculum and demonstrates practical implementation of:

- Web application development
- PHP backend development
- Relational database management
- CRUD operations
- Authentication and authorization
- Role-based access control
- Session management
- Appointment workflows
- Billing management
- Medical record management
- Data export
- Audit logging
- Dashboard development

Future Improvements

Potential future enhancements include:

- Email/SMS appointment notifications
- Online payment integration
- Pharmacy management
- Bed and ward management
- Advanced reporting and analytics
- REST API integration
- Cloud deployment
- Automated testing
- Containerized deployment using Docker

Author

Akash Kumar

MCA Graduate | Software Development & IT

This project is part of my software development portfolio and demonstrates practical experience with PHP, MySQL/MariaDB, JavaScript, and web application development.
# Enrollment Requirement Validator

**Individual Final Project – PFEBSIT 002 (Weeks 13–18)**

A Flutter Web application integrated with a Laravel 12 backend API that helps students and enrollment staff organize, submit, manage, and verify enrollment requirements.

## Project Information

- **Student:** Sam Russell M. So
- **Program/Section:** BSIT – 3A
- **School:** University of La Salette
- **Selected Project:** Project 23 – Enrollment Requirement Validator
- **Frontend:** Flutter Web
- **Backend:** Laravel 12
- **Database:** SQLite
- **API Testing:** Postman
- **Development Environment:** Visual Studio Code
- **Version Control:** Git and GitHub

## Project Overview

The Enrollment Requirement Validator is designed to make the enrollment requirement submission and validation process more organized and manageable.

Students can enter their information, view their required documents, mark requirements as submitted, and monitor their completion progress through a checklist and progress indicator.

The Laravel backend provides API endpoints for managing student records and enrollment requirements. It allows the frontend and backend to work as separate applications while communicating through API requests.

The project also aims to support automated missing-requirement detection, secure API integration, and more reliable validation processes as development continues.

## Current Features and Development Progress

| Feature | Status |
|---|---|
| Flutter Web project setup | Completed |
| Student ID and name fields | Completed |
| Requirements checklist | Completed |
| Missing requirements detection | Completed |
| Completion progress indicator | Completed |
| Basic input validation | Completed |
| Laravel 12 backend setup | Completed |
| Database migrations | Completed |
| Student API implementation | Implemented |
| Requirements API implementation | Implemented |
| Postman API testing | Tested |
| GitHub repository setup | Completed |
| Flutter and Laravel project upload | Completed |
| User authentication | Pending |
| Role-based authorization | Pending |
| Automated reminders and retries | Pending |
| Secure API integration and configuration | Pending |
| Security testing and hardening | Pending |

## Technologies Used

- **Flutter and Dart** – Frontend development and user interface.
- **Laravel 12 and PHP** – Backend development and REST API.
- **SQLite** – Current database configuration.
- **Postman** – API endpoint testing.
- **Visual Studio Code** – Code editing and development.
- **Git and GitHub** – Source code management and project collaboration.

## API Endpoints

The Laravel backend provides endpoints for student and enrollment requirement management.

### Student API

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/students` | Retrieve all students |
| POST | `/api/students` | Create a student record |
| GET | `/api/students/{id}` | Retrieve a specific student |
| PUT/PATCH | `/api/students/{id}` | Update student information |
| DELETE | `/api/students/{id}` | Delete a student record |

### Requirements API

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/requirements` | Retrieve all requirements |
| POST | `/api/requirements` | Create a requirement |
| GET | `/api/requirements/{id}` | Retrieve a specific requirement |
| PUT/PATCH | `/api/requirements/{id}` | Update a requirement |
| DELETE | `/api/requirements/{id}` | Delete a requirement |

*Note: These are the documented API routes. Refer to the Laravel `routes/api.php` file for the actual registered endpoints.*

## Repository Structure

The GitHub repository contains the Flutter frontend, Laravel backend, and project evidence.

```text
enrollment-requirement-validator-final/
│
├── android/
├── enrollment_validator_api/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── artisan
│   ├── composer.json
│   └── README.md
│
├── evidence/
├── ios/
├── lib/
│   └── main.dart
├── linux/
├── macos/
├── test/
├── web/
├── windows/
├── .gitignore
├── analysis_options.yaml
├── pubspec.yaml
├── pubspec.lock
└── README.md
```

## How to Run the Project

The frontend and backend are separate applications. Both must be running when testing their integration.

### 1. Frontend – Flutter Web

1. Open the project root folder in Visual Studio Code.
2. Open the integrated terminal.
3. Install the required Flutter packages:

```bash
flutter pub get
```

4. Run the Flutter Web application:

```bash
flutter run -d chrome
```

### 2. Backend – Laravel API

1. Open the `enrollment_validator_api` folder in Visual Studio Code.
2. Open a terminal inside that folder.
3. Install the Laravel dependencies:

```bash
composer install
```

4. Configure the `.env` file and database connection.
5. Run the database migrations:

```bash
php artisan migrate
```

6. Start the Laravel development server:

```bash
php artisan serve
```

7. The local API server will be available at:

```text
http://127.0.0.1:8000
```

## API Testing Using Postman

Postman is used to test and verify the Laravel backend API endpoints.

The documented testing activities include:

- Creating student records.
- Retrieving student records.
- Creating enrollment requirements.
- Retrieving all requirements.
- Retrieving a specific requirement.
- Updating requirement information or status.
- Deleting requirement records.

Successful API responses include HTTP status codes such as `200 OK` and `201 Created`, depending on the operation.

## Project Development Plan

| Week | Development Focus | Status |
|---|---|---|
| Week 13 | Automation Foundation | In Progress |
| Week 14 | Secure Integration | Pending |
| Week 15 | Testing and Hardening | Pending |
| Weeks 16–18 | Integration, evaluation, documentation, and project defense | Pending |

### Planned Improvements

The remaining development work focuses on the following:

- Automatic detection of missing enrollment requirements.
- Reminder scheduling and deadline notifications.
- Retry and recovery handling for temporary API failures.
- User authentication and role-based authorization.
- Secure API communication and protected configuration.
- Input validation and safe error handling.
- Functional, integration, reliability, and security testing.
- Final system evaluation and user documentation.

## User Manual and Documentation

A separate User Manual will document how to operate the system.

It will include instructions and screenshots covering:

1. Opening the application.
2. Entering student information.
3. Viewing the enrollment requirements checklist.
4. Marking requirements as submitted.
5. Identifying missing requirements.
6. Monitoring completion progress.
7. Managing student and requirement records through the backend API.

Project evidence and development screenshots are maintained in the `evidence/` directory.

## Project Notes

- The system uses fictional student information for development and testing.
- The Flutter frontend and Laravel backend are maintained as separate applications within the same GitHub repository.
- The current database configuration uses SQLite.
- Authentication, authorization, automation, and additional security features remain part of the development plan.
- No production student records or credentials should be committed to the public repository.
- The `.env` file containing local configuration and secrets should remain excluded from GitHub.

## Author

**Sam Russell M. So**  
BSIT – 3A  
University of La Salette

**Repository:** https://github.com/Samatrix35/enrollment-requirement-validator-final

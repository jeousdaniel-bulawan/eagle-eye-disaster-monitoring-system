# Kalinga Management System

A PHP-based records and management system developed to centralize and organize operational information for Bahay Kalinga.

## Overview

Kalinga is a web-based management application for maintaining centralized records and supporting day-to-day administrative operations.

The system includes separate modules for client/resident records, inventory, medical records, employee records, and administrative dashboard functions.

## Features

- Administrative dashboard
- Client/resident record management
- Inventory management
- Medical record management
- Employee record management
- Create, edit, and delete workflows
- Live search functionality
- Database-backed record storage
- User authentication and password management
- Administrative navigation and reporting views

## Technology Stack

| Category | Technologies |
|---|---|
| Backend | PHP |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript |
| UI Framework | Bootstrap |
| Icons | Bootstrap Icons |
| Database Access | PHP database connection / CRUD operations |

## Project Structure

The repository contains the application's PHP pages, supporting assets, and database-related code.

### Main modules

- `dashboard.php` — dashboard and recent records
- `inventory.php` — inventory management
- `medical.php` — medical records
- `employees.php` — employee records
- `admin.php` — administrative interface
- `index.php` — application entry point

### Supporting operations

The project also includes dedicated create, edit, delete, and live-search PHP files for the application's record modules.

## Security

This repository is a portfolio version of the project.

**Do not commit:**
- Database passwords
- Production database credentials
- `.env` files containing secrets
- API keys
- Real resident/client records
- Real medical records
- Real employee personal information
- Database dumps containing private information

Database credentials in the public version should be supplied through environment variables or a local configuration file that is excluded from Git.

## Local Setup

### Requirements

- PHP
- MySQL or MariaDB
- Apache or another PHP-compatible web server

### Setup

1. Clone the repository.
2. Place the project in your local PHP web-server directory.
3. Create a local MySQL/MariaDB database.
4. Configure the database connection using your local credentials.
5. Import a **sanitized** database schema/sample dataset if available.
6. Open the application through your local web server.

Do not use real organizational or resident data in a public development environment.

## Project Context

**Academic / Technical Project**

Developed as a centralized information-management solution for Bahay Kalinga, with a focus on organizing operational records and improving access to information.

## Author

**Jeous Daniel Bulawan**

Bachelor of Science in Computer Engineering  
Polytechnic University of the Philippines – Parañaque City Campus

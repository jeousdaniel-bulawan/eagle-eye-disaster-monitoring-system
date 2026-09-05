# Eagle Eye Disaster Monitoring System

A full-stack disaster response monitoring system designed to support
disaster monitoring and rescue coordination through centralized mission
management, operational dashboards, role-based access control, and
automated incident ingestion.

## Overview

Eagle Eye is a web-based disaster monitoring platform developed to
centralize incident information and support disaster response
operations.

The system provides different interfaces and access levels for users,
operators, and administrators while maintaining controlled access to
operational data and system functions.

## Features

### Authentication & Account Management
- User registration and authentication
- User profile management
- Password reset request and approval workflow
- Role-based access control
- Protected routes

### Mission Management
- Mission creation and management
- Mission monitoring
- Operational dashboards
- User/operator interfaces

### Incident Monitoring
- Automated incident ingestion
- Incident filtering by location and incident type
- Storage of relevant incidents in the system database

### Reports & Administration
- Report management
- Administrative dashboard
- User management
- Audit logging
- Administrative controls

## Automated Incident Ingestion

Eagle Eye includes an automated incident-ingestion service built with
Playwright, Crawlee, and Node.js.

The service monitors community posts for disaster-related incidents,
filters relevant information based on location and incident type, and
stores qualifying incidents in the system database.

The automation is scheduled using `node-cron`.

## Technology Stack

| Category | Technologies |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | Node.js |
| Database | Supabase, PostgreSQL |
| Web Automation | Playwright, Crawlee |
| Scheduling | node-cron |
| Authentication | Supabase Authentication |

## System Architecture

```text
                    ┌─────────────────────┐
                    │      Users          │
                    │  Operators / Admins │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │   Eagle Eye Web App │
                    │                     │
                    │ Authentication      │
                    │ Dashboards          │
                    │ Missions             │
                    │ Reports              │
                    │ Administration       │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │      Supabase       │
                    │    PostgreSQL       │
                    └──────────▲──────────┘
                               │
                               │
                    ┌──────────┴──────────┐
                    │ Incident Ingestion  │
                    │                     │
                    │ Playwright          │
                    │ Crawlee             │
                    │ Node.js             │
                    │ node-cron            │
                    └──────────┬──────────┘
                               │
                               ▼
                    Community Incident Data

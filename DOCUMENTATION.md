# FairFare System - Complete Project Documentation

**Version:** 1.0.0  
**Last Updated:** March 19, 2026  
**Author:** Development Team

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Installation Guide](#installation-guide)
5. [User Manual](#user-manual)
6. [Admin Manual](#admin-manual)
7. [Security Features](#security-features)
8. [Troubleshooting](#troubleshooting)
9. [API Reference](#api-reference)

---

## Project Overview

### What is FairFare System?

FairFare is a comprehensive web-based platform designed for the Ongata Rongai transport sector. It provides:

- **Fare Transparency:** Real-time access to current fare information for all transport routes
- **Incident Reporting:** Easy-to-use reporting system for transport-related incidents
- **Accountability Tracking:** Centralized system for monitoring and resolving issues
- **Admin Management:** Comprehensive tools for administrators to manage fares and incidents

### Key Features

1. **User Authentication & Authorization**
   - Secure login and registration
   - Role-based access control (User/Admin)
   - Session management

2. **Fare Management**
   - View current fares for all routes
   - Add and update fare information
   - Track fare history and changes
   - Predict fare trends

3. **Incident Management**
   - Report incidents (overcharging, misconduct, etc.)
   - Track incident status
   - Admin resolution workflow
   - Export incident data

4. **Administrative Dashboard**
   - System overview with key statistics
   - Quick action buttons
   - Recent incidents monitoring
   - User activity logs

### Technologies Used

- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, Bootstrap 5.3.2
- **Additional Libraries:** 
  - Bootstrap Icons
  - JavaScript (ES6+)

---

## System Architecture

### Application Structure

```
FairFareSystem/
├── index.php                 # Landing page
├── login.php                 # User authentication
├── register.php              # New user registration  
├── logout.php                # Session termination
├── config.php                # Database & system configuration
├── .htaccess                 # URL rewriting rules
│
├── includes/
│   ├── header.php            # Navigation & layout template
│   ├── auth.php              # Authentication functions
│   └── config.php            # System configuration
│
├── Incident Management
│   ├── report_incident.php   # Report new incident
│   ├── view_incidents.php    # View all incidents (admin)
│   ├── resolve_incident.php  # Mark incident as resolved
│   ├── delete_incident.php   # Delete incident record
│   └── export_incidents.php  # Export incidents to CSV
│
├── Fare Management
│   ├── view_fares.php        # Display all fares
│   ├── update_fares.php      # Add/update fare information
│   ├── fare_history.php      # View fare changes history
│   ├── fare_prediction.php   # Fare trend analysis
│   └── route_map.php         # Route mapping with Google Maps
│
├── Admin Functions
│   ├── admin_dashboard.php   # Admin main dashboard
│   ├── admin_logs.php        # Activity logs
│   ├── incident_heatmap.php  # Visual incident distribution
│   └── send_sms.php          # SMS notifications
│
└── assets/
    └── images/
        └── matatu-bg.jpg     # Background image

```

### Data Flow

```
User Request
    ↓
Header.php (included)
    ↓
Config.php (DB connection)
    ↓
Auth.php (Check permissions)
    ↓
Process Request (CRUD operations)
    ↓
Generate Response (HTML output)
    ↓
Footer Section
    ↓
Browser Output
```

---

## Database Schema

### Tables Overview

#### 1. **users** Table
Stores user account information

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT 1,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. **fares** Table
Stores fare information for routes

```sql
CREATE TABLE fares (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route VARCHAR(100) NOT NULL,
    fare DECIMAL(10, 2) NOT NULL,
    effective_date DATE NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 3. **incidents** Table
Stores reported incidents

```sql
CREATE TABLE incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    route VARCHAR(100) NOT NULL,
    incident_type VARCHAR(50),
    description LONGTEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id)
);
```

#### 4. **fare_history** Table
Tracks fare changes over time

```sql
CREATE TABLE fare_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route VARCHAR(100) NOT NULL,
    old_fare DECIMAL(10, 2),
    new_fare DECIMAL(10, 2) NOT NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (changed_by) REFERENCES users(id)
);
```

#### 5. **activity_logs** Table
Logs all user activities

```sql
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (user_id, created_at)
);
```

#### 6. **admin_logs** Table
Logs admin-specific actions

```sql
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    admin_name VARCHAR(100),
    action VARCHAR(200) NOT NULL,
    details TEXT,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);
```

---

## Installation Guide

### System Requirements

- **Server:** Apache 2.4+
- **PHP:** 7.4 or higher
- **Database:** MySQL 5.7+ or MariaDB 10.2+
- **Web Server:** Apache with mod_rewrite enabled

### Step-by-Step Installation

#### 1. **Create Database**

```sql
CREATE DATABASE fairfare_system_db;
USE fairfare_system_db;

-- Execute all table creation scripts from Database Schema section above
```

#### 2. **Upload Files**

Place all files in your web root (typically `htdocs` for XAMPP):

```
C:\xampp\htdocs\FairFareSystem\
```

#### 3. **Configure Database Connection**

Edit `config.php`:

```php
$db_config = [
    'host' => 'localhost',
    'dbname' => 'fairfare_system_db',
    'username' => 'root',
    'password' => ''
];
```

#### 4. **Create Logs Directory**

```bash
mkdir logs
chmod 755 logs
```

#### 5. **Set File Permissions**

```bash
chmod 755 /path/to/FairFareSystem
chmod 644 /path/to/FairFareSystem/*.php
chmod 755 /path/to/FairFareSystem/includes
```

#### 6. **Verify Installation**

- Navigate to `http://localhost/FairFareSystem/`
- Try to register a new account
- Login and verify functionality

### First Time Setup

1. Create admin account via registration
2. Log in to admin panel
3. Add initial fare information
4. Start managing system

---

## User Manual

### For Regular Users

#### 1. **Registration**

1. Click "Register" link on navigation bar
2. Fill in the following details:
   - **Username:** 3-50 characters
   - **Email:** Valid email address
   - **Password:** Minimum 8 characters (uppercase, lowercase, numbers)
   - **Confirm Password:** Must match password

3. Click "Create Account"
4. Login with your credentials

#### 2. **View Fares**

1. Click "View Fares" in navigation
2. Browse list of all routes and current fares
3. Use search to find specific routes
4. View fare effective date and last update

#### 3. **Report an Incident**

1. Click "Report Incident" in navigation (requires login)
2. Fill in the form:
   - **Name:** Auto-filled with your username
   - **Email:** Auto-filled with your email
   - **Phone:** Optional contact number
   - **Route:** Name of the route where incident occurred
   - **Incident Type:** Select from: Overcharging, Misconduct, Unsafe Condition, Poor Service, Other
   - **Description:** Detailed description of incident (minimum 10 characters)

3. Click "Submit Incident Report"
4. Receive confirmation message
5. Admin team will review and respond

### Account Management

- **Change Password:** Click profile → Change Password
- **View Profile:** Click username dropdown
- **Logout:** Click "Logout" in dropdown menu
- **Delete Account:** Contact admin

---

## Admin Manual

### Access Requirements

- Must have admin role in database
- Login to access admin features

### Dashboard Features

#### 1. **Admin Dashboard**

Located at: `admin_dashboard.php`

Displays:
- Total incidents count
- Open incidents count
- Total fare entries
- Active users count
- Recent incidents (last 5)
- Quick action buttons

#### 2. **Manage Incidents**

**View Incidents:**
- Click "View Incidents" or navigate to `view_incidents.php`
- View all reported incidents in table format
- Filter by status (All, Open, In Progress, Resolved)
- Search by various fields

**Status Types:**
- **Open:** Newly reported, not yet reviewed
- **In Progress:** Being investigated/handled
- **Resolved:** Issue addressed and resolved
- **Closed:** Final status

**Actions:**
- **Resolve:** Mark incident as resolved
- **Delete:** Remove incident from system
- **Export:** Download incidents as CSV

#### 3. **Manage Fares**

**Add/Update Fares:**
- Go to `update_fares.php`
- Enter:
  - **Route Name:** Name of transport route
  - **Fare Amount:** In KES (Kenyan Shillings)
  - **Effective Date:** When fare becomes active

- Submit to add new fare entry

**View Fares:**
- Navigate to `view_fares.php`
- View all fares in table format
- Search by route name
- See effective dates and last updates

**Fare History:**
- Click "Fare History" link
- View all historical fare changes
- Track who made changes and when
- Identify fare trends

#### 4. **Activity Logs**

**Admin Logs:**
- Go to `admin_logs.php`
- View all admin actions
- Timestamps and details recorded
- Track system modifications

**Activity Logs:**
- Automatic user activity logging
- Login/logout records
- Report submissions
- Admin actions

#### 5. **Data Export**

**Export Incidents:**
- Click "Export Incidents" button
- Downloads incidents as CSV file
- Can import into Excel/Google Sheets
- Useful for external analysis

### Security Management

- **Monitor Logs:** Check `admin_logs.php` regularly
- **Review Incidents:** Address open incidents promptly
- **Manage Users:** Add/remove users as needed
- **Session Management:** Sessions expire after 30 minutes of inactivity

---

## Security Features

### Authentication Security

1. **Password Security**
   - Bcrypt hashing (PASSWORD_BCRYPT)
   - Minimum 8 characters required
   - Salt automatically generated
   - Invalid login attempts logged

2. **Session Security**
   - Secure session cookies
   - HTTPOnly flag enabled
   - SameSite policy: Strict
   - Auto-expiration after 30 minutes

3. **CSRF Protection**
   - Tokens generated for all forms
   - Tokens verified before processing
   - Unique per session

### Input Validation

1. **Sanitization**
   - HTML special characters escaped
   - Prepared statements for DB queries
   - No SQL injection possible

2. **Validation Rules**
   - Email format validation
   - Date format validation
   - Numeric type checking
   - String length limits

### SQL Injection Prevention

- All queries use prepared statements
- Parameter binding for all user inputs
- PDO with emulated prepares disabled

### XSS Protection

- Output escaping with `htmlspecialchars()`
- HTTP headers set for XSS prevention
- Safe HTML rendering

### Security Headers

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

### Data Protection

- HTTPS recommended for production
- Sensitive data not logged
- Database backups recommended
- User data stored securely

---

## Troubleshooting

### Common Issues

#### 1. **Database Connection Failed**

**Problem:** Error message about database connection

**Solutions:**
- Verify MySQL service is running
- Check database name in `config.php`
- Verify username and password
- Ensure database is created
- Check for typos in connection details

#### 2. **Login Not Working**

**Problem:** Cannot login despite correct credentials

**Solutions:**
- Clear browser cache and cookies
- Verify user account exists in database
- Check username/email is correct
- Ensure account is not disabled
- Check PHP session handling

#### 3. **Permission Denied**

**Problem:** 403 Forbidden or permission errors

**Solutions:**
- Set correct file permissions (644 for files, 755 for directories)
- Verify mod_rewrite is enabled
- Check .htaccess file exists
- Review .htaccess for errors

#### 4. **Forms Not Submitting**

**Problem:** Form submits but no action occurs

**Solutions:**
- Check CSRF token is valid
- Verify form method is POST/GET
- Check form field names match code
- Look for JavaScript errors in console
- Check server error logs

#### 5. **Emails Not Sending**

**Problem:** SMS/Email notifications not received

**Solutions:**
- Verify Africa's Talking API credentials
- Check API key in `send_sms.php`
- Verify phone numbers are correct
- Check logs for error messages
- Test API connection separately

### Error Logs

**Location:** `logs/error.log`

**Viewing Errors:**
```bash
tail -f logs/error.log
```

**Common Error Messages:**
- Database Connection Failed: Check config.php
- Permission Denied: Check file permissions
- Invalid Session: Clear cookies, login again
- CSRF Token Mismatch: Try form again

### Performance Optimization

1. **Database Optimization**
   - Add indexes to frequently searched columns
   - Regular backup and cleanup
   - Monitor query performance

2. **Caching**
   - Consider implementing page caching
   - Use browser caching headers
   - Cache static assets

3. **Monitoring**
   - Monitor server resources
   - Check PHP error logs
   - Monitor database performance

---

## API Reference

### Authentication Endpoints

#### User Registration
```
POST /register.php
Parameters:
  - username: string (3-50 chars)
  - email: string (valid email)
  - password: string (min 8 chars)
  - password_confirm: string (matches password)
  - csrf_token: string
```

#### User Login
```
POST /login.php
Parameters:
  - email: string
  - password: string
  - csrf_token: string
```

#### Logout
```
GET /logout.php
```

### Incident Management Endpoints

#### Report Incident
```
POST /report_incident.php
Parameters:
  - name: string
  - email: string
  - phone: string (optional)
  - route: string
  - incident_type: string
  - description: string
  - csrf_token: string
```

#### View Incidents
```
GET /view_incidents.php (Admin only)
Parameters:
  - status: string (all, open, in_progress, resolved)
  - sort: string (created_at, id, route, status)
  - order: string (ASC, DESC)
```

#### Resolve Incident
```
GET /resolve_incident.php?id=INTEGER (Admin only)
```

#### Delete Incident
```
GET /delete_incident.php?id=INTEGER (Admin only)
```

#### Export Incidents
```
GET /export_incidents.php (Admin only)
```

### Fare Management Endpoints

#### View Fares
```
GET /view_fares.php
Parameters:
  - search: string (route name search)
```

#### Update Fare
```
POST /update_fares.php (Admin only)
Parameters:
  - route: string
  - fare: float
  - effective_date: date
  - csrf_token: string
```

#### Fare History
```
GET /fare_history.php (Admin only)
```

---

## Support & Maintenance

### Regular Maintenance Tasks

1. **Daily**
   - Monitor system logs
   - Check for error messages
   - Verify system uptime

2. **Weekly**
   - Review new incidents
   - Check database size
   - Review user activities

3. **Monthly**
   - Database backup
   - Performance review
   - Security audit

4. **Quarterly**
   - System updates
   - Security patches
   - Feature enhancement planning

### Contact & Support

For technical support:
- Check documentation first
- Review error logs
- Contact system administrator
- Provide error details and reproduction steps

---

## Version History

### Version 1.0.0 (March 2026)
- Initial release
- Core features implemented
- Full documentation provided

---

## License & Usage

FairFare System is developed for the Ongata Rongai transport sector.
All rights reserved.

---

**Document End**

*This documentation is current as of March 19, 2026*
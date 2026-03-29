# FairFare System - Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Step 1: Initialize Database

1. **Open your browser** and go to:
   ```
   http://localhost/FairFareSystem/setup.php
   ```

2. **Click "Create Database Tables"** - This will create all necessary tables in `fairfare_system_db`

3. **Click "Create Admin User"** - This creates the admin account with these credentials:
   - **Email:** `admin@fairfare.local`
   - **Password:** `Admin@123`

   ⚠️ **Change this password after first login!**

---

### Step 2: Test the System

#### A. User Registration
1. Go to: `http://localhost/FairFareSystem/index.php`
2. Click **"Register"** link in navigation bar
3. Fill in the form:
   - Username: `testuser`
   - Email: `testuser@example.com`
   - Password: `TestPass123`
   - Confirm: `TestPass123`
4. Click **"Create Account"**
5. You should see success message

#### B. User Login
1. Click **"Login"** in navigation bar
2. Enter credentials:
   - Email: `testuser@example.com`
   - Password: `TestPass123`
3. Click **"Login"**
4. You should see Dashboard with username in dropdown

#### C. Report Incident
1. After logging in, click **"Report Incident"** in navbar
2. Fill in the form:
   - Name: Your Name
   - Email: Your Email
   - Phone: 0712345678
   - Route: Downtown-Orongai
   - Type: Overcharging
   - Description: Driver overcharged passengers
3. Click **"Report Incident"**
4. Should see success confirmation

#### D. View Fares
1. Click **"View Fares"** in navbar
2. See current fare list (will be empty until admin adds fares)

#### E. Admin Login (Optional)
1. Click **"Logout"** if logged in as regular user
2. Click **"Login"**
3. Enter:
   - Email: `admin@fairfare.local`
   - Password: `Admin@123`
4. After login, you'll see **"Admin"** menu in navbar
5. Click Admin dropdown to access:
   - Dashboard
   - View Incidents
   - Update Fares
   - Activity Logs

---

## 🔧 Testing Checklist

- [ ] Setup page loads without errors
- [ ] Database tables created successfully
- [ ] Admin user created successfully
- [ ] Can register new user account
- [ ] Can login with new user
- [ ] Can report incidents
- [ ] Can view empty fares list
- [ ] Can login with admin account
- [ ] Can see admin menu
- [ ] Navigation links all work
- [ ] Logout works properly

---

## 📝 Default Test Credentials

### Admin Account
```
Email: admin@fairfare.local
Password: Admin@123
```

### Test User (Create via Registration)
```
Username: testuser
Email: testuser@example.com
Password: TestPass123
```

### Test Routes Available
- Downtown-Orongai
- Orongai-Karen
- Orongai-Kikambala
- Orongai-Vet

---

## 🐛 Troubleshooting

### Problem: "Database Connection Failed" Error
**Solution:**
1. Check if `fairfare_system_db` database exists in MySQL
2. Verify MySQL is running (XAMPP Control Panel)
3. Default connection: `localhost` / user: `root` / password: empty

### Problem: Navigation Links Not Working
**Solution:**
1. Ensure Apache mod_rewrite is enabled
2. Check that `.htaccess` file exists in FairFareSystem folder
3. Try accessing direct URLs like:
   - `http://localhost/FairFareSystem/login.php`
   - `http://localhost/FairFareSystem/register.php`

### Problem: "CSRF Token Invalid" Error
**Solution:**
1. Clear browser cookies
2. Refresh the page
3. Try again with fresh CSRF token

### Problem: Can't Login After Registration
**Solution:**
1. Check if you used correct email/password
2. Verify database connection is working
3. Check `logs/error.log` for database errors

---

## 🔒 Security Notes

1. **Delete setup.php** after initial setup for security
2. **Change admin password** immediately after first login
3. **Enable HTTPS** in production environment
4. **Set strong passwords** (8+ characters, mixed case, numbers)
5. **Regularly backup** the database

---

## 📧 Support

For issues or questions, check:
- DOCUMENTATION.md - Full system documentation
- logs/error.log - Application error logs
- Apache error log - Server issues

---

**Last Updated:** March 19, 2026  
**FairFare System v1.0.0**

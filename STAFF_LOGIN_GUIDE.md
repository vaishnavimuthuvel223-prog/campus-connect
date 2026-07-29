# Staff Login System - Configuration Guide

## System Overview

The staff login system has been redesigned to use **Staff ID + Password** authentication without email requirements.

### Login Requirements
- **Username Field**: Staff ID (numeric identifier)
- **Password Field**: Assigned password (can be changed after login)
- **Email**: NOT required for login (optional in database)
- **Department**: Stored in database but not required for login

---

## Current Staff Credentials

| Staff ID | Name | Department | Email | Status |
|----------|------|------------|-------|--------|
| 19 | CSE Coordinator | CSE | cse.coordinator@college.edu | Active |
| 20 | ECE Coordinator | ECE | ece.coordinator@college.edu | Active |
| 21 | ME Coordinator | ME | me.coordinator@college.edu | Active |
| 22 | CE Coordinator | CE | ce.coordinator@college.edu | Active |
| 23 | IT Coordinator | IT | it.coordinator@college.edu | Active |
| 24 | VLSI Coordinator | VLSI | vlsi.coordinator@college.edu | Active |
| 25 | EEE Coordinator | EEE | eee.coordinator@college.edu | Active |
| 26 | AIML Coordinator | AIML | aiml.coordinator@college.edu | Active |
| 27 | AIDS Coordinator | AIDS | aids.coordinator@college.edu | Active |

**Initial passwords are set in the database. Staff must change their password on first login.**

---

## Login Flow

### Step 1: Access Login Page
- URL: `/staff/login.php`
- Page shows Staff Portal login form

### Step 2: Enter Credentials
```
Staff ID: [numeric ID only]
Password: [current password]
```

### Step 3: Authentication
- System validates Staff ID exists in database
- System verifies password hash
- Rate limiting: Max 5 attempts per 15 minutes
- Session prevention: Only one active session per staff member

### Step 4: Dashboard Access
- Staff redirected to `/staff/dashboard.php`
- Session stores: user_id, name, dept_id, staff_code

---

## Profile Management

### What Staff CAN Do:
✅ Change password
✅ Upload/update profile picture

### What Staff CANNOT Do:
❌ Change name (fixed by admin)
❌ Update email (fixed by admin)
❌ Change department (fixed by admin)
❌ Modify staff ID (immutable)

### Profile Access
- URL: `/staff/profile.php`
- Requires active staff session
- Shows read-only staff information
- Password change form available
- Profile picture upload available

---

## Database Schema

### department_staff Table
```sql
CREATE TABLE department_staff (
  staff_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  dept_id INT NOT NULL,
  email VARCHAR(100) NULL,  -- Optional, not used for login
  password VARCHAR(255) NOT NULL,
  profile_pic VARCHAR(255) DEFAULT NULL,
  staff_code VARCHAR(50) UNIQUE DEFAULT NULL,
  last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### Key Points:
- `staff_id`: Primary identifier (numeric, auto-increment)
- `email`: Optional (can be NULL)
- `staff_code`: Unique identifier (nullable, for future use)
- `password`: Bcrypt hashed password
- `last_password_change`: Tracks when password was last updated

---

## Security Features

### Authentication
- ✓ Bcrypt password hashing (PASSWORD_BCRYPT)
- ✓ CSRF token validation on login form
- ✓ Rate limiting (5 attempts / 15 minutes)
- ✓ Concurrent session prevention
- ✓ Failed login logging

### Session Management
- ✓ Secure session initialization
- ✓ Role-based access control (staff role)
- ✓ Automatic logout on inactivity
- ✓ Session data validation on each request

### Isolation
- ✓ Staff can only access their own profile
- ✓ Password changes affect only their account
- ✓ Profile picture updates are user-specific
- ✓ No cross-staff data access possible

---

## Admin Management

### To Reset a Staff Password (Admin):
```sql
UPDATE department_staff 
SET password = PASSWORD_HASH_HERE 
WHERE staff_id = STAFF_ID_NUMBER;
```

### To Create New Staff:
```sql
INSERT INTO department_staff (name, dept_id, email, password)
VALUES ('Staff Name', DEPT_ID, 'email@college.edu', PASSWORD_HASH);
-- MySQL will auto-assign staff_id
```

### To Update Staff Info (Admin Only):
```sql
UPDATE department_staff 
SET name = 'New Name', email = 'newemail@college.edu'
WHERE staff_id = STAFF_ID_NUMBER;
```

---

## Troubleshooting

### "Invalid Staff ID or password"
- Verify Staff ID is numeric (not staff code)
- Confirm password is correct
- Check account hasn't been locked due to rate limiting

### Session Issues
- Clear browser cookies
- Wait 15 minutes if rate limited
- Try different browser if persistent

### Password Change Fails
- Current password must be correct
- New password must be at least 6 characters
- Confirm password must match exactly

---

## Data Isolation Verification

✓ Each staff member can only modify their own `staff_id`
✓ Password updates use `WHERE staff_id = $_SESSION['user_id']`
✓ Profile pictures are stored with staff_id in filename
✓ No bulk operations possible on other staff accounts

---

## Files Modified

- `/staff/login.php` - Login form with Staff ID + Password
- `/staff/profile.php` - Profile page with password change only
- `/config/db.php` - Database connection and initialization
- Database schema - Email made optional

---

## Testing Checklist

- [ ] Login with Staff ID (19-27) and password works
- [ ] Invalid Staff ID shows error
- [ ] Invalid password shows error  
- [ ] Rate limiting blocks after 5 attempts
- [ ] Profile shows staff info (read-only)
- [ ] Password change updates database
- [ ] New password works on next login
- [ ] Profile picture uploads correctly
- [ ] Logging out clears session
- [ ] No cross-staff data access possible

---

## Notes

- This system uses numeric Staff ID as the primary identifier
- Email is optional and not used for authentication
- Staff cannot modify their name or department
- Only password and profile picture are user-editable
- Admin can reset passwords or update staff information
- Data isolation is maintained at database query level

---

**Last Updated**: March 31, 2026
**System Status**: ✓ Fully Configured and Tested

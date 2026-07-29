-- ===== STAFF LOGIN FIX =====
-- Ensure staff_id is the primary identifier, remove email dependencies
-- Allow only password changes after login

-- 1. Ensure staff_id column is unique and not nullable
ALTER TABLE department_staff MODIFY COLUMN staff_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- 2. Make email optional (staff don't need emails assigned)
ALTER TABLE department_staff MODIFY COLUMN email VARCHAR(100) NULL;

-- 3. Ensure all staff have proper staff_ids (already auto-increment from step 1)

-- 4. Verify password hashes are correct for all staff
-- Staff will be able to change password after login

-- 5. Remove any profile_completed column if it exists and is causing issues
ALTER TABLE department_staff DROP COLUMN IF EXISTS profile_completed;

-- 6. Add a last_password_change column for security tracking
ALTER TABLE department_staff ADD COLUMN IF NOT EXISTS last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 7. Add a locked_until column for brute force protection
ALTER TABLE department_staff ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL DEFAULT NULL;

-- Sample staff data (using auto-increment staff_id)
-- Staff will login with: staff_id (numeric ID), password
-- Email is NOT used for login

-- Current staff data remains intact

COMMIT;

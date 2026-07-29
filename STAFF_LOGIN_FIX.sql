-- ============================================
-- STAFF LOGIN SECURITY & DATABASE FIXES
-- ============================================
-- This script fixes staff login issues and data isolation problems

-- 1. ADD MISSING COLUMNS TO department_staff
ALTER TABLE `department_staff` 
ADD COLUMN `profile_completed` TINYINT(1) DEFAULT 0 AFTER `staff_code`,
ADD UNIQUE INDEX `idx_email` (`email`),
ADD UNIQUE INDEX `idx_staff_code` (`staff_code`);

-- 2. ENSURE staff_id IS AUTO_INCREMENT (if not already)
-- First, check if AUTO_INCREMENT exists; if not, alter it
ALTER TABLE `department_staff` 
MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique staff identifier';

-- 3. GENERATE UNIQUE STAFF CODES (if NULL)
UPDATE `department_staff` SET `staff_code` = CONCAT('STF', LPAD(`staff_id`, 4, '0')) WHERE `staff_code` IS NULL;

-- 4. ENSURE FOREIGN KEY RELATIONSHIPS
-- Verify no orphaned staff records (optional - for data integrity)
-- Add constraints if tables reference department_staff

-- ============================================
-- VERIFICATION QUERIES
-- ============================================
-- Run these to verify the fixes:

-- Check department_staff structure:
DESCRIBE `department_staff`;

-- Check staff records with their codes:
SELECT `staff_id`, `name`, `email`, `staff_code`, `dept_id`, `profile_completed`, `created_at` 
FROM `department_staff` 
ORDER BY `staff_id`;

-- Check for any NULL staff_codes (should be empty):
SELECT * FROM `department_staff` WHERE `staff_code` IS NULL;

-- Check for duplicate staff_codes (should be empty):
SELECT `staff_code`, COUNT(*) as count 
FROM `department_staff` 
WHERE `staff_code` IS NOT NULL 
GROUP BY `staff_code` 
HAVING count > 1;

-- ============================================
-- NEW STAFF CREDENTIALS FOR LOGIN
-- ============================================
-- Staff can now login with their:
-- - Staff Code (e.g., STF0006, STF0007, STF0008)
-- - Password (as set in database)
-- - Department ID (stored in session)

-- Current Staff Codes:
-- Staff ID 6: STF0006 (Thilagavathi C - Department 5)
-- Staff ID 7: STF0007 (selvi - Department 1)
-- Staff ID 8: STF0008 (sivanandham - Department 2)

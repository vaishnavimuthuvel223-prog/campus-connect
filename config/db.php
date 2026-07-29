<?php
// ===== SECURITY INITIALIZATION =====
// Include security configuration and helper class
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/SecurityHelper.php';

// Initialize security for all pages
$security = null;

// Database configuration
$host = 'localhost';
$dbname = 'campuss';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize security helper
    $security = new SecurityHelper($conn);
    $security->initializeSession();

    // Ensure `skill_category` exists for required tables (safe auto-migration)
    try {
        $conn->query("SELECT skill_category FROM companies LIMIT 1");
    } catch (PDOException $e) {
        try {
            $conn->exec("ALTER TABLE companies ADD COLUMN skill_category VARCHAR(32) DEFAULT NULL AFTER description");
        } catch (PDOException $e2) {
            // ignore if fails (table missing/non-applicable)
        }
    }

    try {
        $conn->query("SELECT skill_category FROM students LIMIT 1");
    } catch (PDOException $e) {
        try {
            $conn->exec("ALTER TABLE students ADD COLUMN skill_category VARCHAR(32) DEFAULT NULL AFTER current_semester");
        } catch (PDOException $e2) {
            // ignore if fails (table missing/non-applicable)
        }
    }

    // Ensure drives has role column (for placement role info)
    try {
        $conn->query("SELECT role FROM drives LIMIT 1");
    } catch (PDOException $e) {
        try {
            $conn->exec("ALTER TABLE drives ADD COLUMN role VARCHAR(255) DEFAULT NULL AFTER min_cgpa");
        } catch (PDOException $e2) {
            // ignore if fails
        }
    }

    // ===== PROJECT-WIDE AUTO_INCREMENT SELF-HEAL =====
    // Root cause of the "Duplicate entry '0' for key PRIMARY" errors seen on
    // Companies, Drives, etc: several tables' primary keys lost their
    // AUTO_INCREMENT property. Earlier fixes patched this one table at a time
    // (and always too late in the page, after the INSERT already ran), so it
    // kept resurfacing on whichever table was touched next. This runs once per
    // request, BEFORE any page logic, and repairs every table in one place.
    ensureAutoIncrementHealth($conn);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Repairs AUTO_INCREMENT on every known primary key in the schema.
 * - No-ops instantly (one cheap SHOW COLUMNS) once a table is already healthy.
 * - If a stray row is already sitting at id = 0 (the symptom of the missing
 *   AUTO_INCREMENT), it is safely renumbered to a free id instead of deleted,
 *   and any child tables that reference it are updated to match — no data lost.
 */
function ensureAutoIncrementHealth(PDO $conn): void {
    // table => [primary key column, [[child table, child fk column], ...]]
    $tables = [
        'admin_login_logs'    => ['log_id', []],
        'announcements'       => ['announcement_id', []],
        'applications'        => ['application_id', []],
        'area_of_interest'    => ['interest_id', []],
        'companies'           => ['company_id', [['drives', 'company_id'], ['company_reviews', 'company_id']]],
        'company_reviews'     => ['review_id', []],
        'departments'         => ['dept_id', [['students', 'dept_id'], ['department_staff', 'dept_id'], ['drive_departments', 'dept_id']]],
        'department_staff'    => ['staff_id', [['staff_notifications', 'staff_id']]],
        'staff_notifications' => ['notification_id', []],
        'drives'              => ['drive_id', [['drive_departments', 'drive_id'], ['applications', 'drive_id']]],
        'drive_departments'   => ['id', []],
        'internships'         => ['internship_id', []],
        'login_attempts'      => ['attempt_id', []],
        'notifications'       => ['notification_id', []],
        'placement_admin'     => ['admin_id', []],
        'projects'            => ['project_id', []],
        'skills'              => ['skill_id', []],
        'staff_login_logs'    => ['log_id', []],
        'students'            => ['student_id', [['applications', 'student_id'], ['area_of_interest', 'student_id'], ['projects', 'student_id'], ['skills', 'student_id'], ['internships', 'student_id']]],
        'student_login_logs'  => ['log_id', []],
    ];

    foreach ($tables as $table => [$pk, $children]) {
        try {
            // Skip tables that don't exist in this database at all.
            $exists = $conn->query("SHOW TABLES LIKE " . $conn->quote($table))->fetchColumn();
            if (!$exists) continue;

            // Cheap check: already healthy? Skip immediately.
            $col = $conn->query("SHOW COLUMNS FROM `$table` WHERE Field = " . $conn->quote($pk))->fetch();
            if (!$col || stripos($col['Extra'], 'auto_increment') !== false) continue;

            // A row stuck at id = 0 is what causes the very next insert to
            // collide. Move it to a fresh id first (never delete real data).
            $zeroExists = (int)$conn->query("SELECT COUNT(*) FROM `$table` WHERE `$pk` = 0")->fetchColumn();
            if ($zeroExists > 0) {
                $maxId = (int)$conn->query("SELECT COALESCE(MAX(`$pk`), 0) FROM `$table`")->fetchColumn();
                $freeId = max($maxId, 0) + 1;
                $conn->exec("UPDATE `$table` SET `$pk` = $freeId WHERE `$pk` = 0 LIMIT 1");
                foreach ($children as [$childTable, $childCol]) {
                    try {
                        $conn->exec("UPDATE `$childTable` SET `$childCol` = $freeId WHERE `$childCol` = 0");
                    } catch (Exception $e) {
                        // Child table/column may not exist in this schema — safe to skip.
                    }
                }
            }

            $maxId = (int)$conn->query("SELECT COALESCE(MAX(`$pk`), 0) FROM `$table`")->fetchColumn();
            $conn->exec("ALTER TABLE `$table` MODIFY COLUMN `$pk` INT(11) NOT NULL AUTO_INCREMENT");
            $conn->exec("ALTER TABLE `$table` AUTO_INCREMENT = " . ($maxId + 1));
        } catch (Exception $e) {
            // Best-effort repair; never break page load over one stubborn table.
        }
    }
}

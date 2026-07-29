<?php
/**
 * STAFF DATABASE SETUP - COMPREHENSIVE FIX
 * Ensures database is ready for staff login with provided credentials
 */

require_once 'config/db.php';

$report = [];
$report[] = "🔧 STAFF DATABASE COMPREHENSIVE FIX";
$report[] = "=====================================\n";

try {
    // ===== STEP 1: Check current structure =====
    $report[] = "STEP 1: Checking current database structure...";
    
    $columns = $conn->query("DESCRIBE department_staff")->fetchAll();
    $columnNames = array_column($columns, 'Field');
    $report[] = "Table columns: " . implode(", ", $columnNames);
    
    $currentCount = $conn->query("SELECT COUNT(*) FROM department_staff")->fetchColumn();
    $report[] = "Current records: $currentCount\n";
    
    // ===== STEP 2: Verify staff_id column type =====
    $report[] = "STEP 2: Checking staff_id column type...";
    $staffIdColumn = array_filter($columns, function($col) { return $col['Field'] === 'staff_id'; });
    $staffIdColumn = array_values($staffIdColumn)[0] ?? null;
    
    if ($staffIdColumn) {
        $report[] = "staff_id type: " . $staffIdColumn['Type'];
        
        // If it's INT, we need to change it to VARCHAR
        if (strpos($staffIdColumn['Type'], 'int') !== false) {
            $report[] = "⚠️ staff_id is INT, converting to VARCHAR...";
            
            try {
                // Drop primary key
                $conn->exec("ALTER TABLE department_staff DROP PRIMARY KEY");
                $report[] = "  ✅ Dropped INT primary key";
                
                // Change type
                $conn->exec("ALTER TABLE department_staff MODIFY COLUMN staff_id VARCHAR(50)");
                $report[] = "  ✅ Changed staff_id to VARCHAR(50)";
                
                // Add back unique constraint
                $conn->exec("ALTER TABLE department_staff ADD UNIQUE KEY unique_staff_id (staff_id)");
                $report[] = "  ✅ Added UNIQUE constraint";
            } catch (Exception $e) {
                $report[] = "  ⚠️ Could not alter: " . $e->getMessage();
            }
        }
    }
    
    $report[] = "";
    
    // ===== STEP 3: Prepare staff data =====
    $report[] = "STEP 3: Preparing staff data...";
    
    $staffData = [
        ['staff_id' => 'CSE-001', 'dept_id' => 1, 'name' => 'CSE Coordinator', 'email' => 'cse.coordinator@college.edu', 'password' => 'CSE@2024Sec'],
        ['staff_id' => 'ECE-002', 'dept_id' => 2, 'name' => 'ECE Coordinator', 'email' => 'ece.coordinator@college.edu', 'password' => 'ECE@2024Sec'],
        ['staff_id' => 'ME-003', 'dept_id' => 3, 'name' => 'ME Coordinator', 'email' => 'me.coordinator@college.edu', 'password' => 'ME@2024Sec'],
        ['staff_id' => 'CE-004', 'dept_id' => 4, 'name' => 'CE Coordinator', 'email' => 'ce.coordinator@college.edu', 'password' => 'CE@2024Sec'],
        ['staff_id' => 'IT-005', 'dept_id' => 5, 'name' => 'IT Coordinator', 'email' => 'it.coordinator@college.edu', 'password' => 'IT@2024Sec'],
        ['staff_id' => 'VLSI-006', 'dept_id' => 6, 'name' => 'VLSI Coordinator', 'email' => 'vlsi.coordinator@college.edu', 'password' => 'VLSI@2024Sec'],
        ['staff_id' => 'EEE-007', 'dept_id' => 7, 'name' => 'EEE Coordinator', 'email' => 'eee.coordinator@college.edu', 'password' => 'EEE@2024Sec'],
        ['staff_id' => 'AIML-008', 'dept_id' => 8, 'name' => 'AIML Coordinator', 'email' => 'aiml.coordinator@college.edu', 'password' => 'AIML@2024Sec'],
        ['staff_id' => 'AIDS-009', 'dept_id' => 9, 'name' => 'AIDS Coordinator', 'email' => 'aids.coordinator@college.edu', 'password' => 'AIDS@2024Sec']
    ];
    
    $report[] = "Loaded 9 staff records\n";
    
    // ===== STEP 4: Clear old records =====
    $report[] = "STEP 4: Clearing old/invalid records...";
    $conn->exec("DELETE FROM department_staff");
    $report[] = "✅ Old records cleared\n";
    
    // ===== STEP 5: Insert new staff =====
    $report[] = "STEP 5: Inserting new staff records...";
    $report[] = "────────────────────────────────────\n";
    
    $successCount = 0;
    $credentials = [];
    
    $stmt = $conn->prepare("
        INSERT INTO department_staff (staff_id, dept_id, name, email, password, profile_completed, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");
    
    foreach ($staffData as $staff) {
        try {
            $hashedPassword = password_hash($staff['password'], PASSWORD_BCRYPT, ['cost' => 10]);
            
            $stmt->execute([
                $staff['staff_id'],
                $staff['dept_id'],
                $staff['name'],
                $staff['email'],
                $hashedPassword
            ]);
            
            $report[] = "✅ {$staff['staff_id']} - {$staff['name']}";
            $report[] = "    Password: {$staff['password']}";
            
            $credentials[] = [
                'staff_id' => $staff['staff_id'],
                'password' => $staff['password']
            ];
            
            $successCount++;
        } catch (Exception $e) {
            $report[] = "❌ {$staff['staff_id']} - ERROR: " . $e->getMessage();
        }
    }
    
    $report[] = "\n────────────────────────────────────";
    $report[] = "✅ {$successCount}/9 staff inserted successfully\n";
    
    // ===== STEP 6: Verification =====
    $report[] = "STEP 6: Verification...";
    $report[] = "────────────────────────────────────\n";
    
    $allStaff = $conn->query("
        SELECT s.staff_id, s.name, d.dept_name, s.email, s.profile_completed
        FROM department_staff s
        LEFT JOIN departments d ON s.dept_id = d.dept_id
        ORDER BY s.staff_id
    ")->fetchAll();
    
    foreach ($allStaff as $staff) {
        $profile_status = $staff['profile_completed'] ? '✅ Complete' : '⏳ Pending';
        $report[] = $staff['staff_id'] . " | " . str_pad($staff['name'], 20) . " | " . $staff['dept_name'] . " | " . $profile_status;
    }
    
    $totalCount = count($allStaff);
    $report[] = "\n────────────────────────────────────";
    $report[] = "Total Records: $totalCount/9\n";
    
    // ===== STEP 7: Test password verification =====
    $report[] = "STEP 7: Testing password verification...";
    $report[] = "────────────────────────────────────\n";
    
    $testStaff = $conn->query("SELECT * FROM department_staff WHERE staff_id = 'CSE-001'")->fetch();
    if ($testStaff) {
        $testPassword = 'CSE@2024Sec';
        $isValid = password_verify($testPassword, $testStaff['password']);
        $testResult = $isValid ? '✅ VALID' : '❌ INVALID';
        $report[] = "Test: CSE-001 login with 'CSE@2024Sec' - $testResult";
    }
    
    $report[] = "\n────────────────────────────────────";
    
    // ===== FINAL SUMMARY =====
    if ($totalCount === 9 && $successCount === 9) {
        $report[] = "\n🎉 DATABASE SETUP COMPLETE!\n";
        $report[] = "📋 STAFF LOGIN CREDENTIALS";
        $report[] = "═════════════════════════════════════\n";
        $report[] = "Login URL: http://localhost/campuss/staff/login.php\n";
        
        foreach ($credentials as $cred) {
            $report[] = "Staff ID: {$cred['staff_id']}";
            $report[] = "Password: {$cred['password']}";
            $report[] = "";
        }
        $report[] = "✅ All staff can now login with their Staff ID + Password\n";
        $report[] = "Next Step: Staff login → Complete Profile → Access Dashboard";
    } else {
        $report[] = "\n⚠️ SETUP INCOMPLETE - {$totalCount}/9 records in database";
    }

} catch (Exception $e) {
    $report[] = "\n❌ CRITICAL ERROR: " . $e->getMessage();
    $report[] = $e->getTraceAsString();
}

// Output report
echo implode("\n", $report);

// Also save to log file for reference
$logFile = __DIR__ . '/staff_setup_log.txt';
file_put_contents($logFile, implode("\n", $report), FILE_APPEND);

?>

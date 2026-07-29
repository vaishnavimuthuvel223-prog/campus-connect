<?php
/**
 * FIX STAFF DATABASE - Convert INT staff_id to VARCHAR for department-based IDs
 */

require_once 'config/db.php';

echo "🔧 FIXING STAFF DATABASE STRUCTURE\n";
echo "====================================\n\n";

try {
    echo "Step 1: Backing up current staff data...\n";
    $currentStaff = $conn->query("SELECT * FROM department_staff")->fetchAll();
    echo "✅ Backup created (" . count($currentStaff) . " records)\n\n";

    echo "Step 2: Modifying staff_id column from INT to VARCHAR...\n";
    
    // First, drop the primary key constraint
    try {
        $conn->exec("ALTER TABLE department_staff DROP PRIMARY KEY");
        echo "✅ Removed INT primary key\n";
    } catch (Exception $e) {
        echo "   (Primary key already removed)\n";
    }

    // Change column type
    try {
        $conn->exec("ALTER TABLE department_staff MODIFY COLUMN staff_id VARCHAR(50)");
        echo "✅ Changed staff_id to VARCHAR(50)\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        throw $e;
    }

    // Add unique constraint
    try {
        $conn->exec("ALTER TABLE department_staff ADD UNIQUE KEY unique_staff_id (staff_id)");
        echo "✅ Added UNIQUE constraint\n";
    } catch (Exception $e) {
        echo "   (UNIQUE constraint may already exist)\n";
    }

    echo "\nStep 3: Clearing old staff records...\n";
    $conn->exec("DELETE FROM department_staff");
    echo "✅ Old records cleared\n\n";

    echo "Step 4: Inserting new staff with department-based IDs...\n";
    echo "──────────────────────────────────────────────────────\n\n";

    // New staff data
    $staffData = [
        ['staff_id' => 'CSE-001', 'dept_id' => 1, 'name' => 'CSE Coordinator', 'email' => 'cse@college.edu', 'password' => 'CSE@2024Sec'],
        ['staff_id' => 'ECE-002', 'dept_id' => 2, 'name' => 'ECE Coordinator', 'email' => 'ece@college.edu', 'password' => 'ECE@2024Sec'],
        ['staff_id' => 'ME-003', 'dept_id' => 3, 'name' => 'ME Coordinator', 'email' => 'me@college.edu', 'password' => 'ME@2024Sec'],
        ['staff_id' => 'CE-004', 'dept_id' => 4, 'name' => 'CE Coordinator', 'email' => 'ce@college.edu', 'password' => 'CE@2024Sec'],
        ['staff_id' => 'IT-005', 'dept_id' => 5, 'name' => 'IT Coordinator', 'email' => 'it@college.edu', 'password' => 'IT@2024Sec'],
        ['staff_id' => 'VLSI-006', 'dept_id' => 6, 'name' => 'VLSI Coordinator', 'email' => 'vlsi@college.edu', 'password' => 'VLSI@2024Sec'],
        ['staff_id' => 'EEE-007', 'dept_id' => 7, 'name' => 'EEE Coordinator', 'email' => 'eee@college.edu', 'password' => 'EEE@2024Sec'],
        ['staff_id' => 'AIML-008', 'dept_id' => 8, 'name' => 'AIML Coordinator', 'email' => 'aiml@college.edu', 'password' => 'AIML@2024Sec'],
        ['staff_id' => 'AIDS-009', 'dept_id' => 9, 'name' => 'AIDS Coordinator', 'email' => 'aids@college.edu', 'password' => 'AIDS@2024Sec']
    ];

    $stmt = $conn->prepare("
        INSERT INTO department_staff (staff_id, dept_id, name, email, password, profile_completed, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");

    $successCount = 0;
    $loginCredentials = [];

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

            echo "✅ {$staff['staff_id']} - {$staff['name']}\n";
            $loginCredentials[] = [
                'staff_id' => $staff['staff_id'],
                'password' => $staff['password'],
                'email' => $staff['email']
            ];
            $successCount++;
        } catch (Exception $e) {
            echo "❌ {$staff['staff_id']} - " . $e->getMessage() . "\n";
        }
    }

    echo "\n──────────────────────────────────────────────────────\n\n";
    echo "✅ {$successCount}/9 staff records created successfully!\n\n";

    // Verify
    echo "Step 5: Verification\n";
    echo "──────────────────────────────────────────────────────\n\n";

    $records = $conn->query("
        SELECT s.staff_id, s.name, d.dept_name, s.email 
        FROM department_staff s
        LEFT JOIN departments d ON s.dept_id = d.dept_id
        ORDER BY s.staff_id
    ")->fetchAll();

    echo "📊 Staff Database Contents:\n\n";
    foreach ($records as $rec) {
        echo "  Staff ID: " . $rec['staff_id'] . "\n";
        echo "  Name: " . $rec['name'] . "\n";
        echo "  Department: " . $rec['dept_name'] . "\n";
        echo "  Email: " . $rec['email'] . "\n";
        echo "  ───────────────────────────────\n";
    }

    $total = $conn->query("SELECT COUNT(*) FROM department_staff")->fetchColumn();
    echo "\nTotal Records: $total/9\n\n";

    if ($total == 9) {
        echo "🎉 DATABASE SETUP COMPLETE!\n\n";
        echo "📋 LOGIN CREDENTIALS:\n";
        echo "────────────────────────\n\n";
        echo "URL: http://localhost/campuss/staff/login.php\n\n";
        foreach ($loginCredentials as $cred) {
            echo "Staff ID:  " . $cred['staff_id'] . "\n";
            echo "Password:  " . $cred['password'] . "\n";
            echo "Email:     " . $cred['email'] . "\n";
            echo "\n";
        }
        echo "✅ All staff can now login with their Staff ID and password!\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

?>

<?php
/**
 * STAFF SETUP - Update with Department-Based Staff IDs
 * Replaces old setup with the new staff credentials
 */

require_once 'config/db.php';

// New staff data with department-based IDs
$staffData = [
    [
        'staff_id' => 'CSE-001',
        'dept_id' => 1,
        'dept_name' => 'Computer Science Engineering',
        'name' => 'CSE Coordinator',
        'email' => 'cse.coordinator@college.edu',
        'temp_password' => 'CSE@2024Sec'
    ],
    [
        'staff_id' => 'ECE-002',
        'dept_id' => 2,
        'dept_name' => 'Electronics and Communication Engineering',
        'name' => 'ECE Coordinator',
        'email' => 'ece.coordinator@college.edu',
        'temp_password' => 'ECE@2024Sec'
    ],
    [
        'staff_id' => 'ME-003',
        'dept_id' => 3,
        'dept_name' => 'Mechanical Engineering',
        'name' => 'ME Coordinator',
        'email' => 'me.coordinator@college.edu',
        'temp_password' => 'ME@2024Sec'
    ],
    [
        'staff_id' => 'CE-004',
        'dept_id' => 4,
        'dept_name' => 'Civil Engineering',
        'name' => 'CE Coordinator',
        'email' => 'ce.coordinator@college.edu',
        'temp_password' => 'CE@2024Sec'
    ],
    [
        'staff_id' => 'IT-005',
        'dept_id' => 5,
        'dept_name' => 'Information Technology',
        'name' => 'IT Coordinator',
        'email' => 'it.coordinator@college.edu',
        'temp_password' => 'IT@2024Sec'
    ],
    [
        'staff_id' => 'VLSI-006',
        'dept_id' => 6,
        'dept_name' => 'VLSI',
        'name' => 'VLSI Coordinator',
        'email' => 'vlsi.coordinator@college.edu',
        'temp_password' => 'VLSI@2024Sec'
    ],
    [
        'staff_id' => 'EEE-007',
        'dept_id' => 7,
        'dept_name' => 'Electrical and Electronics Engineering',
        'name' => 'EEE Coordinator',
        'email' => 'eee.coordinator@college.edu',
        'temp_password' => 'EEE@2024Sec'
    ],
    [
        'staff_id' => 'AIML-008',
        'dept_id' => 8,
        'dept_name' => 'Artificial Intelligence and Machine Learning',
        'name' => 'AIML Coordinator',
        'email' => 'aiml.coordinator@college.edu',
        'temp_password' => 'AIML@2024Sec'
    ],
    [
        'staff_id' => 'AIDS-009',
        'dept_id' => 9,
        'dept_name' => 'Artificial Intelligence and Data Science',
        'name' => 'AIDS Coordinator',
        'email' => 'aids.coordinator@college.edu',
        'temp_password' => 'AIDS@2024Sec'
    ]
];

echo "🔄 STAFF DATABASE UPDATE\n";
echo "========================================\n\n";

try {
    // Step 1: Clear old staff records
    echo "Step 1: Clearing old staff records...\n";
    $conn->exec("DELETE FROM department_staff");
    echo "✅ Old records removed\n\n";

    // Step 2: Prepare insert statement with staff_id column
    echo "Step 2: Inserting new staff with updated IDs...\n";
    echo "────────────────────────────────────────────\n\n";

    // First check table structure
    $columns = $conn->query("DESCRIBE department_staff")->fetchAll();
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('staff_id', $columnNames)) {
        // Need to add staff_id column if it doesn't exist
        echo "⚠️  Adding staff_id column to table...\n";
        try {
            $conn->exec("ALTER TABLE department_staff ADD COLUMN staff_id VARCHAR(50) UNIQUE AFTER dept_id");
        } catch (Exception $e) {
            echo "Note: staff_id column may already exist\n";
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO department_staff (dept_id, staff_id, name, email, password, profile_completed, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");

    $successCount = 0;
    foreach ($staffData as $staff) {
        try {
            $hashedPassword = password_hash($staff['temp_password'], PASSWORD_BCRYPT, ['cost' => 10]);
            
            $stmt->execute([
                $staff['dept_id'],
                $staff['staff_id'],
                $staff['name'],
                $staff['email'],
                $hashedPassword
            ]);

            echo "✅ {$staff['staff_id']} - {$staff['dept_name']}\n";
            echo "   └─ Password: {$staff['temp_password']}\n";
            $successCount++;
        } catch (Exception $e) {
            echo "❌ {$staff['staff_id']} - Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n────────────────────────────────────────────\n\n";
    echo "✅ {$successCount}/9 STAFF RECORDS CREATED\n\n";

    // Step 3: Verify the setup
    echo "📊 VERIFICATION\n";
    echo "────────────────────────────────────────────\n\n";
    
    $staffList = $conn->query("SELECT staff_id, name, dept_id FROM department_staff ORDER BY staff_id")->fetchAll();
    foreach ($staffList as $staff) {
        echo "  • {$staff['staff_id']} - {$staff['name']}\n";
    }

    $count = $conn->query("SELECT COUNT(*) FROM department_staff")->fetchColumn();
    echo "\n✅ Total staff in database: {$count}/9\n\n";

    if ($count === 9) {
        echo "🎉 DATABASE READY FOR STAFF LOGIN!\n";
        echo "─────────────────────────────────\n";
        echo "Login URL: http://localhost/campuss/staff/login.php\n";
        echo "Use Staff ID + Password from above.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

?>

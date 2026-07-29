<?php
// Complete Staff Setup - Fresh Start
// Clears old data and creates 9 staff with fixed credentials

$pdo = new PDO('mysql:host=localhost;dbname=campuss', 'root', '');

echo "🔐 COMPLETE STAFF SETUP - FRESH START\n";
echo str_repeat("=", 70) . "\n\n";

// Step 1: Delete all existing staff
try {
    $pdo->exec("DELETE FROM department_staff");
    echo "✅ Cleared old staff data\n\n";
} catch (Exception $e) {
    echo "❌ Error clearing: " . $e->getMessage() . "\n\n";
}

// Step 2: Create 9 staff with FIXED credentials - ONE per department
$staffData = [
    [
        'dept_id' => 1,
        'dept_name' => 'Computer Science Engineering',
        'staff_code' => 'CSE-001',
        'password' => 'CSE@2024Sec',
        'name' => 'CSE Coordinator',
        'email' => 'cse.coordinator@college.edu'
    ],
    [
        'dept_id' => 2,
        'dept_name' => 'Electronics and Communication Engineering',
        'staff_code' => 'ECE-002',
        'password' => 'ECE@2024Sec',
        'name' => 'ECE Coordinator',
        'email' => 'ece.coordinator@college.edu'
    ],
    [
        'dept_id' => 3,
        'dept_name' => 'Mechanical Engineering',
        'staff_code' => 'ME-003',
        'password' => 'ME@2024Sec',
        'name' => 'ME Coordinator',
        'email' => 'me.coordinator@college.edu'
    ],
    [
        'dept_id' => 4,
        'dept_name' => 'Civil Engineering',
        'staff_code' => 'CE-004',
        'password' => 'CE@2024Sec',
        'name' => 'CE Coordinator',
        'email' => 'ce.coordinator@college.edu'
    ],
    [
        'dept_id' => 5,
        'dept_name' => 'Information Technology',
        'staff_code' => 'IT-005',
        'password' => 'IT@2024Sec',
        'name' => 'IT Coordinator',
        'email' => 'it.coordinator@college.edu'
    ],
    [
        'dept_id' => 6,
        'dept_name' => 'VLSI',
        'staff_code' => 'VLSI-006',
        'password' => 'VLSI@2024Sec',
        'name' => 'VLSI Coordinator',
        'email' => 'vlsi.coordinator@college.edu'
    ],
    [
        'dept_id' => 7,
        'dept_name' => 'Electrical and Electronics Engineering',
        'staff_code' => 'EEE-007',
        'password' => 'EEE@2024Sec',
        'name' => 'EEE Coordinator',
        'email' => 'eee.coordinator@college.edu'
    ],
    [
        'dept_id' => 8,
        'dept_name' => 'Artificial Intelligence and Machine Learning',
        'staff_code' => 'AIML-008',
        'password' => 'AIML@2024Sec',
        'name' => 'AIML Coordinator',
        'email' => 'aiml.coordinator@college.edu'
    ],
    [
        'dept_id' => 9,
        'dept_name' => 'Artificial Intelligence and Data Science',
        'staff_code' => 'AIDS-009',
        'password' => 'AIDS@2024Sec',
        'name' => 'AIDS Coordinator',
        'email' => 'aids.coordinator@college.edu'
    ]
];

echo "📝 CREATING 9 STAFF:\n\n";

$count = 0;
foreach ($staffData as $staff) {
    try {
        // Hash password with bcrypt cost=12
        $hashedPassword = password_hash($staff['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Insert staff
        $stmt = $pdo->prepare("
            INSERT INTO department_staff 
            (dept_id, staff_code, password, name, email, profile_completed, created_at) 
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        
        $stmt->execute([
            $staff['dept_id'],
            $staff['staff_code'],
            $hashedPassword,
            $staff['name'],
            $staff['email']
        ]);
        
        echo "✅ " . $staff['staff_code'] . " - " . $staff['dept_name'] . "\n";
        echo "   Password: " . $staff['password'] . " (hashed)\n";
        echo "   Email: " . $staff['email'] . "\n\n";
        
        $count++;
    } catch (Exception $e) {
        echo "❌ " . $staff['staff_code'] . " - Error: " . $e->getMessage() . "\n\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "✅ COMPLETED: $count/9 staff created\n\n";

// Verify
$result = $pdo->query("SELECT COUNT(*) as total FROM department_staff")->fetch();
echo "📊 Database verification: " . $result['total'] . " staff members in database\n";

$staff = $pdo->query("SELECT staff_id, dept_id, staff_code FROM department_staff ORDER BY dept_id")->fetchAll();
echo "\n📋 STAFF LIST:\n";
foreach ($staff as $s) {
    echo "   ID: {$s['staff_id']} | Dept: {$s['dept_id']} | Code: {$s['staff_code']}\n";
}

echo "\n✅ ALL 9 STAFF READY TO LOGIN!\n";
echo "🔗 Go to: http://localhost/campuss/staff/login.php\n";
?>

<?php
/**
 * STAFF SETUP SCRIPT - Generate Secure Staff IDs
 * Run this to setup 9 staff coordinators with secure credentials
 */

require_once 'config/db.php';

// Define the 9 departments with secure staff data
$staffData = [
    [
        'dept_id' => 1,
        'dept_name' => 'Computer Science Engineering',
        'name' => 'CSE Coordinator',
        'email' => 'cse.coordinator@college.edu',
        'staff_code' => 'STAFF-CSE-SEC-2024-001',
        'temp_password' => 'CSE@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 2,
        'dept_name' => 'Electronics and Communication Engineering',
        'name' => 'ECE Coordinator',
        'email' => 'ece.coordinator@college.edu',
        'staff_code' => 'STAFF-ECE-SEC-2024-002',
        'temp_password' => 'ECE@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 3,
        'dept_name' => 'Mechanical Engineering',
        'name' => 'ME Coordinator',
        'email' => 'me.coordinator@college.edu',
        'staff_code' => 'STAFF-ME-SEC-2024-003',
        'temp_password' => 'ME@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 4,
        'dept_name' => 'Civil Engineering',
        'name' => 'CE Coordinator',
        'email' => 'ce.coordinator@college.edu',
        'staff_code' => 'STAFF-CE-SEC-2024-004',
        'temp_password' => 'CE@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 5,
        'dept_name' => 'Information Technology',
        'name' => 'IT Coordinator',
        'email' => 'it.coordinator@college.edu',
        'staff_code' => 'STAFF-IT-SEC-2024-005',
        'temp_password' => 'IT@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 6,
        'dept_name' => 'VLSI',
        'name' => 'VLSI Coordinator',
        'email' => 'vlsi.coordinator@college.edu',
        'staff_code' => 'STAFF-VLSI-SEC-2024-006',
        'temp_password' => 'VLSI@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 7,
        'dept_name' => 'Electrical and Electronics Engineering',
        'name' => 'EEE Coordinator',
        'email' => 'eee.coordinator@college.edu',
        'staff_code' => 'STAFF-EEE-SEC-2024-007',
        'temp_password' => 'EEE@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 8,
        'dept_name' => 'Artificial Intelligence and Machine Learning',
        'name' => 'AIML Coordinator',
        'email' => 'aiml.coordinator@college.edu',
        'staff_code' => 'STAFF-AIML-SEC-2024-008',
        'temp_password' => 'AIML@Coord2024Sec!Secure'
    ],
    [
        'dept_id' => 9,
        'dept_name' => 'Artificial Intelligence and Data Science',
        'name' => 'AIDS Coordinator',
        'email' => 'aids.coordinator@college.edu',
        'staff_code' => 'STAFF-AIDS-SEC-2024-009',
        'temp_password' => 'AIDS@Coord2024Sec!Secure'
    ]
];

echo "🔒 SECURE STAFF SETUP\n";
echo "====================\n\n";

try {
    // Step 1: Delete all existing staff
    echo "Step 1: Removing old staff members...\n";
    $conn->exec("DELETE FROM department_staff");
    echo "✅ Old staff removed\n\n";

    // Step 2: Insert new staff with secure IDs
    echo "Step 2: Creating new staff coordinators...\n";
    echo "─────────────────────────────────────────\n\n";

    $stmt = $conn->prepare("
        INSERT INTO department_staff (dept_id, name, email, password, staff_code, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $credentials = [];

    foreach ($staffData as $staff) {
        // Hash password using bcrypt
        $hashedPassword = password_hash($staff['temp_password'], PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Insert staff
        $stmt->execute([
            $staff['dept_id'],
            $staff['name'],
            $staff['email'],
            $hashedPassword,
            $staff['staff_code']
        ]);

        // Store credentials for display
        $credentials[] = [
            'dept_name' => $staff['dept_name'],
            'name' => $staff['name'],
            'email' => $staff['email'],
            'staff_code' => $staff['staff_code'],
            'temp_password' => $staff['temp_password']
        ];

        echo "✅ {$staff['dept_name']}\n";
        echo "   └─ Email: {$staff['email']}\n";
        echo "   └─ Staff ID: {$staff['staff_code']}\n\n";
    }

    echo "───────────────────────────────────────────\n\n";
    echo "✅ ALL 9 STAFF COORDINATORS CREATED SUCCESSFULLY!\n\n";

    // Step 3: Display credentials summary
    echo "🔐 LOGIN CREDENTIALS SUMMARY\n";
    echo "=============================\n\n";
    
    echo "URL: http://localhost/campuss/staff/login.php\n\n";

    foreach ($credentials as $cred) {
        echo "Department: {$cred['dept_name']}\n";
        echo "├─ Email: {$cred['email']}\n";
        echo "├─ Staff ID: {$cred['staff_code']}\n";
        echo "├─ Temporary Password: {$cred['temp_password']}\n";
        echo "└─ Status: Ready to login\n\n";
    }

    // Step 4: Verify the setup
    echo "───────────────────────────────────────────\n\n";
    echo "✅ VERIFICATION\n";
    
    $count = $conn->query("SELECT COUNT(*) FROM department_staff")->fetchColumn();
    echo "Total staff created: {$count}/9 ✅\n\n";

    // Step 5: Show departments without staff (if any)
    $unassigned = $conn->query("
        SELECT d.dept_id, d.dept_name 
        FROM departments d 
        LEFT JOIN department_staff ds ON d.dept_id = ds.dept_id 
        WHERE ds.dept_id IS NULL
    ")->fetchAll();

    if (count($unassigned) > 0) {
        echo "⚠️ Departments without staff:\n";
        foreach ($unassigned as $dept) {
            echo "   └─ {$dept['dept_name']} (ID: {$dept['dept_id']})\n";
        }
    } else {
        echo "✅ All 9 departments have a staff coordinator\n";
    }

    echo "\n✅ SETUP COMPLETE!\n";
    echo "🔒 YOUR SYSTEM IS NOW SECURED WITH DEPARTMENT-SPECIFIC STAFF!\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>

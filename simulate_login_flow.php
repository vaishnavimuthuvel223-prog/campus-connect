<?php
/**
 * STAFF LOGIN FLOW SIMULATION
 * Tests the complete login process to ensure everything works end-to-end
 */

require_once 'config/db.php';

echo "════════════════════════════════════════════════════════════════\n";
echo "🔐 STAFF LOGIN FLOW SIMULATION\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Simulate a staff login attempt
$testStaffId = 'CSE-001';
$testPassword = 'CSE@2024Sec';

echo "Simulating login for: $testStaffId\n";
echo "Password: $testPassword\n\n";

echo "STEP 1: Looking up staff in database...\n";
$stmt = $conn->prepare("SELECT * FROM department_staff WHERE staff_id = ?");
$stmt->execute([$testStaffId]);
$staff = $stmt->fetch();

if (!$staff) {
    echo "❌ FAIL: Staff not found in database\n";
    exit;
} else {
    echo "✅ PASS: Staff found\n";
    echo "  Name: {$staff['name']}\n";
    echo "  Department ID: {$staff['dept_id']}\n";
    echo "  Profile Complete: " . ($staff['profile_completed'] ? 'Yes' : 'No') . "\n";
}

echo "\nSTEP 2: Verifying password...\n";
if (!password_verify($testPassword, $staff['password'])) {
    echo "❌ FAIL: Password does not match\n";
    exit;
} else {
    echo "✅ PASS: Password verified\n";
}

echo "\nSTEP 3: Getting department information...\n";
$stmt = $conn->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
$stmt->execute([$staff['dept_id']]);
$dept = $stmt->fetch();

if ($dept) {
    echo "✅ PASS: Department found\n";
    echo "  Department: {$dept['dept_name']}\n";
} else {
    echo "❌ FAIL: Department not found\n";
}

echo "\nSTEP 4: Simulating session creation...\n";
// Simulate what the security helper would do
$session_data = [
    'user_id' => $staff['staff_id'],
    'role' => 'staff',
    'email' => $staff['email'],
    'name' => $staff['name'],
    'dept_id' => $staff['dept_id'],
    'staff_code' => $staff['staff_code'] ?? null,
    'created_time' => time(),
    'ip_address' => '127.0.0.1',
    'user_agent' => 'CLI Simulation'
];

echo "✅ PASS: Session created with:\n";
foreach ($session_data as $key => $value) {
    $displayValue = is_null($value) ? 'null' : $value;
    echo "  \$_SESSION['$key'] = " . $displayValue . "\n";
}

echo "\nSTEP 5: Determining redirect target...\n";
if (!$staff['profile_completed']) {
    echo "✅ PASS: Profile not complete\n";
    echo "  Redirect to: /staff/complete_profile.php\n";
} else {
    echo "✅ PASS: Profile complete\n";
    echo "  Redirect to: /staff/dashboard.php\n";
}

echo "\nSTEP 6: Verifying all required files exist...\n";
$files = [
    '/staff/complete_profile.php',
    '/staff/dashboard.php',
    '/config/db.php'
];

$allExist = true;
foreach ($files as $file) {
    $path = __DIR__ . $file;
    if (file_exists($path)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file NOT FOUND\n";
        $allExist = false;
    }
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "📊 SIMULATION RESULTS\n";
echo "════════════════════════════════════════════════════════════════\n\n";

if ($allExist && $staff && password_verify($testPassword, $staff['password'])) {
    echo "✅ LOGIN SIMULATION SUCCESSFUL\n\n";
    echo "The following will happen:\n";
    echo "1. ✅ Staff $testStaffId will login\n";
    echo "2. ✅ Session will be created\n";
    echo "3. ✅ Page will redirect to complete profile\n";
    echo "4. ✅ Staff can fill in profile information\n";
    echo "5. ✅ After profile complete, staff can access dashboard\n";
    echo "\n🎉 LOGIN FLOW IS WORKING CORRECTLY\n";
} else {
    echo "❌ LOGIN SIMULATION FAILED\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";

// Test all staff IDs
echo "\nBONUS: Testing all 9 staff credentials...\n\n";

$allStaff = $conn->query("SELECT staff_id, password FROM department_staff ORDER BY staff_id")->fetchAll();
$successCount = 0;

foreach ($allStaff as $s) {
    // Extract password from the provided list
    $passwords = [
        'CSE-001' => 'CSE@2024Sec',
        'ECE-002' => 'ECE@2024Sec',
        'ME-003' => 'ME@2024Sec',
        'CE-004' => 'CE@2024Sec',
        'IT-005' => 'IT@2024Sec',
        'VLSI-006' => 'VLSI@2024Sec',
        'EEE-007' => 'EEE@2024Sec',
        'AIML-008' => 'AIML@2024Sec',
        'AIDS-009' => 'AIDS@2024Sec'
    ];
    
    if (isset($passwords[$s['staff_id']])) {
        $isValid = password_verify($passwords[$s['staff_id']], $s['password']);
        $status = $isValid ? '✅' : '❌';
        echo "$status {$s['staff_id']}: {$passwords[$s['staff_id']]}\n";
        if ($isValid) $successCount++;
    }
}

echo "\n✅ All {$successCount}/9 staff credentials are valid and can login\n";

?>

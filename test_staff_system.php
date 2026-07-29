<?php
/**
 * STAFF SYSTEM - COMPREHENSIVE TEST
 * Verifies all components are ready for staff login and profile completion
 */

require_once 'config/db.php';

$tests = [];

echo "════════════════════════════════════════════════════════════════\n";
echo "🧪 STAFF SYSTEM - COMPREHENSIVE TEST SUITE\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// TEST 1: Database Connection
echo "TEST 1: Database Connection\n";
echo "──────────────────────────────────────────────────────────────\n";
try {
    $test = $conn->query("SELECT 1")->fetch();
    echo "✅ Database connection: PASS\n";
    $tests['db_connection'] = true;
} catch (Exception $e) {
    echo "❌ Database connection: FAIL - " . $e->getMessage() . "\n";
    $tests['db_connection'] = false;
}
echo "\n";

// TEST 2: Table Structure
echo "TEST 2: Table Structure\n";
echo "──────────────────────────────────────────────────────────────\n";
try {
    $columns = $conn->query("DESCRIBE department_staff")->fetchAll();
    $columnNames = array_column($columns, 'Field');
    $required = ['staff_id', 'dept_id', 'name', 'email', 'password', 'profile_completed'];
    $missing = array_diff($required, $columnNames);
    
    if (empty($missing)) {
        echo "✅ All required columns present\n";
        $tests['table_structure'] = true;
    } else {
        echo "❌ Missing columns: " . implode(", ", $missing) . "\n";
        $tests['table_structure'] = false;
    }
    
    // Check staff_id type
    $staffIdType = array_column(array_filter($columns, fn($c) => $c['Field'] === 'staff_id'), 'Type');
    echo "   staff_id type: " . implode(", ", $staffIdType) . "\n";
} catch (Exception $e) {
    echo "❌ Table structure check: FAIL - " . $e->getMessage() . "\n";
    $tests['table_structure'] = false;
}
echo "\n";

// TEST 3: Staff Records
echo "TEST 3: Staff Records in Database\n";
echo "──────────────────────────────────────────────────────────────\n";
try {
    $count = $conn->query("SELECT COUNT(*) FROM department_staff")->fetchColumn();
    echo "Total staff records: $count\n";
    
    if ($count === 9) {
        echo "✅ All 9 records present\n";
        $tests['staff_records_count'] = true;
    } else {
        echo "❌ Expected 9 records, found $count\n";
        $tests['staff_records_count'] = false;
    }
    
    $staffList = $conn->query("SELECT staff_id, name, dept_id FROM department_staff ORDER BY staff_id")->fetchAll();
    foreach ($staffList as $staff) {
        echo "   • {$staff['staff_id']} - {$staff['name']}\n";
    }
} catch (Exception $e) {
    echo "❌ Staff records check: FAIL - " . $e->getMessage() . "\n";
    $tests['staff_records_count'] = false;
}
echo "\n";

// TEST 4: Password Hashing
echo "TEST 4: Password Hashing\n";
echo "──────────────────────────────────────────────────────────────\n";
try {
    $staff = $conn->query("SELECT * FROM department_staff WHERE staff_id = 'CSE-001'")->fetch();
    if ($staff && strpos($staff['password'], '$2y$') === 0) {
        echo "✅ Password is bcrypt hashed\n";
        $tests['password_hashing'] = true;
    } else {
        echo "❌ Password is not properly hashed\n";
        $tests['password_hashing'] = false;
    }
} catch (Exception $e) {
    echo "❌ Password hashing check: FAIL - " . $e->getMessage() . "\n";
    $tests['password_hashing'] = false;
}
echo "\n";

// TEST 5: Password Verification
echo "TEST 5: Password Verification\n";
echo "──────────────────────────────────────────────────────────────\n";
try {
    $testPasswords = [
        ['id' => 'CSE-001', 'pwd' => 'CSE@2024Sec'],
        ['id' => 'ECE-002', 'pwd' => 'ECE@2024Sec'],
        ['id' => 'AIDS-009', 'pwd' => 'AIDS@2024Sec']
    ];
    
    $allValid = true;
    foreach ($testPasswords as $test) {
        $stmt = $conn->prepare("SELECT password FROM department_staff WHERE staff_id = ?");
        $stmt->execute([$test['id']]);
        $staff = $stmt->fetch();
        if ($staff) {
            $isValid = password_verify($test['pwd'], $staff['password']);
            $status = $isValid ? '✅' : '❌';
            echo "{$status} {$test['id']}: {$test['pwd']} - " . ($isValid ? 'VALID' : 'INVALID') . "\n";
            $allValid = $allValid && $isValid;
        }
    }
    $tests['password_verification'] = $allValid;
} catch (Exception $e) {
    echo "❌ Password verification: FAIL - " . $e->getMessage() . "\n";
    $tests['password_verification'] = false;
}
echo "\n";

// TEST 6: Login System Files
echo "TEST 6: Login System Files\n";
echo "──────────────────────────────────────────────────────────────\n";
$requiredFiles = [
    '/staff/login.php' => 'Staff login page',
    '/staff/complete_profile.php' => 'Profile completion page',
    '/staff/dashboard.php' => 'Staff dashboard',
    '/config/db.php' => 'Database configuration',
    '/config/SecurityHelper.php' => 'Security helper'
];

$filesExist = true;
foreach ($requiredFiles as $file => $desc) {
    $path = __DIR__ . $file;
    $exists = file_exists($path);
    $status = $exists ? '✅' : '❌';
    echo "{$status} {$desc}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    $filesExist = $filesExist && $exists;
}
$tests['required_files'] = $filesExist;
echo "\n";

// TEST 7: Departments Configuration
echo "TEST 7: Departments Configuration\n";
echo "──────────────────────────────────────────────────────────────\n";
try {
    $deptCount = $conn->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    if ($deptCount >= 9) {
        echo "✅ All 9 departments configured\n";
        $tests['departments'] = true;
    } else {
        echo "⚠️  Only $deptCount departments found (expected 9)\n";
        $tests['departments'] = false;
    }
} catch (Exception $e) {
    echo "❌ Department check: FAIL - " . $e->getMessage() . "\n";
    $tests['departments'] = false;
}
echo "\n";

// TEST 8: Session & Security Setup
echo "TEST 8: Session & Security Setup\n";
echo "──────────────────────────────────────────────────────────────\n";
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
echo "✅ Session support: Available\n";
if (function_exists('password_verify')) {
    echo "✅ Password verification: Available\n";
    $tests['security_setup'] = true;
} else {
    echo "❌ Password verification: NOT available\n";
    $tests['security_setup'] = false;
}
echo "\n";

// SUMMARY
echo "════════════════════════════════════════════════════════════════\n";
echo "📊 TEST SUMMARY\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$passed = array_sum($tests);
$total = count($tests);

foreach ($tests as $name => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "$status: " . ucwords(str_replace('_', ' ', $name)) . "\n";
}

echo "\n────────────────────────────────────────────────────────────────\n";
echo "Result: $passed/$total tests passed\n";

if ($passed === $total) {
    echo "\n🎉 ALL TESTS PASSED - SYSTEM READY!\n";
    echo "\n📋 NEXT STEPS FOR STAFF:\n";
    echo "1. Go to: http://localhost/campuss/staff/login.php\n";
    echo "2. Enter Staff ID (e.g., CSE-001)\n";
    echo "3. Enter Password (e.g., CSE@2024Sec)\n";
    echo "4. Complete your profile information\n";
    echo "5. Access staff dashboard\n";
} else {
    echo "\n⚠️ SOME TESTS FAILED - Please check configuration\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";

?>

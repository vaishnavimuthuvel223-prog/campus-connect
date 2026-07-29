<?php
// Diagnostic script to check staff database
require_once 'config/db.php';

echo "=== STAFF DATABASE DIAGNOSTIC ===\n\n";

try {
    // Check table structure
    echo "1. Checking department_staff table structure:\n";
    $columns = $conn->query("DESCRIBE department_staff")->fetchAll();
    foreach ($columns as $col) {
        echo "   - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n2. Current staff records in database:\n";
    $result = $conn->query("SELECT COUNT(*) as count FROM department_staff")->fetch();
    echo "   Total records: " . $result['count'] . "\n\n";
    
    if ($result['count'] > 0) {
        $staffList = $conn->query("SELECT staff_id, name, dept_id, password, profile_completed FROM department_staff ORDER BY staff_id")->fetchAll();
        foreach ($staffList as $staff) {
            echo "   Staff ID: " . $staff['staff_id'] . "\n";
            echo "   Name: " . $staff['name'] . "\n";
            echo "   Dept ID: " . $staff['dept_id'] . "\n";
            echo "   Password Hash: " . substr($staff['password'], 0, 20) . "...\n";
            echo "   Profile Complete: " . ($staff['profile_completed'] ? 'Yes' : 'No') . "\n";
            echo "   ---\n";
        }
    }
    
    echo "\n3. Testing password verification:\n";
    // Test if CSE-001 password works
    $testStaff = $conn->query("SELECT * FROM department_staff WHERE staff_id = 'CSE-001'")->fetch();
    if ($testStaff) {
        $testPassword = "CSE@2024Sec";
        $isValid = password_verify($testPassword, $testStaff['password']);
        echo "   CSE-001 password test: " . ($isValid ? 'VALID ✓' : 'INVALID ✗') . "\n";
    } else {
        echo "   CSE-001 not found in database!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>

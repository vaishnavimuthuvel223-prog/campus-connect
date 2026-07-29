<?php
$conn = new PDO('mysql:host=localhost;dbname=campuss;charset=utf8mb4', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Make email optional
    $conn->exec('ALTER TABLE department_staff MODIFY COLUMN email VARCHAR(100) NULL');
    echo "✓ Email made optional\n";
} catch (Exception $e) {
    echo "Info: " . $e->getMessage() . "\n";
}

try {
    // Add security columns
    $conn->exec('ALTER TABLE department_staff ADD COLUMN IF NOT EXISTS last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    echo "✓ Added last_password_change column\n";
} catch (Exception $e) {
    echo "Info: " . $e->getMessage() . "\n";
}

try {
    // Check current staff records
    $result = $conn->query('SELECT staff_id, name, email, password FROM department_staff ORDER BY staff_id')->fetchAll(PDO::FETCH_ASSOC);
    echo "\n✓ Current Staff Records:\n";
    echo str_repeat('=', 80) . "\n";
    printf("%-10s %-30s %-30s %-15s\n", 'Staff ID', 'Name', 'Email', 'Password Hash');
    echo str_repeat('-', 80) . "\n";
    foreach ($result as $row) {
        printf("%-10s %-30s %-30s %-15s\n", 
            $row['staff_id'], 
            substr($row['name'], 0, 28), 
            substr($row['email'] ?? 'N/A', 0, 28),
            substr($row['password'], -10) . '...'
        );
    }
    echo str_repeat('=', 80) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "✓ Database schema updated successfully!\n";
echo "\nStaff Login Configuration:\n";
echo "- Login with: Staff ID + Password\n";
echo "- Email is NOT used for login\n";
echo "- Password can be changed in profile after login\n";
echo "- Name/Department info cannot be changed by staff\n";
?>

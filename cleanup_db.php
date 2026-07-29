<?php
require 'config/db.php';

try {
    // Delete in correct order to respect foreign keys
    
    // 1. Delete applications (references drive_id and student_id)
    $conn->exec("DELETE FROM applications");
    echo "✅ Deleted all applications<br>";
    
    // 2. Delete drive_departments (references drive_id and dept_id)
    $conn->exec("DELETE FROM drive_departments");
    echo "✅ Deleted all drive_departments<br>";
    
    // 3. Delete drives (references company_id)
    $conn->exec("DELETE FROM drives");
    echo "✅ Deleted all drives<br>";
    
    // 4. Delete companies
    $conn->exec("DELETE FROM companies");
    echo "✅ Deleted all companies<br>";
    
    // Reset auto-increment counters
    $conn->exec("ALTER TABLE companies AUTO_INCREMENT = 1");
    echo "✅ Reset companies auto_increment<br>";
    
    $conn->exec("ALTER TABLE drives AUTO_INCREMENT = 1");
    echo "✅ Reset drives auto_increment<br>";
    
    echo "<br><strong style='color:green;'>Database cleanup complete! All companies and drives have been deleted.</strong><br>";
    echo "You can now start creating companies and drives fresh.<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

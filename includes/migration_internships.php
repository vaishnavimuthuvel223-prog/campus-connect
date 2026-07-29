<?php
// Auto-migration for internships table
// Include this at the top of any file that needs to query the internships table

if (isset($conn)) {
    try {
        $conn->query("SELECT internship_id FROM internships LIMIT 1");
    } catch (Exception $e) {
        $conn->exec("CREATE TABLE internships (
            internship_id INT PRIMARY KEY AUTO_INCREMENT,
            student_id INT NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            role VARCHAR(255) NOT NULL,
            duration VARCHAR(100) NOT NULL,
            stipend DECIMAL(10,2) DEFAULT NULL,
            project_title VARCHAR(255) DEFAULT NULL,
            project_description TEXT DEFAULT NULL,
            technologies_used TEXT DEFAULT NULL,
            start_date DATE DEFAULT NULL,
            end_date DATE DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
        )");
    }
}
?>

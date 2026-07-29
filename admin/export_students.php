<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

// Auto-create internships table if missing
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

$export_type = $_POST['export_type'] ?? 'all';
$dept_filter = $_POST['dept'] ?? 'all';
$status_filter = $_POST['status'] ?? 'all';
$company_id = $_POST['company'] ?? null;
$fields = $_POST['fields'] ?? ['name', 'reg_no', 'dept_name', 'cgpa', 'email', 'verification_status', 'total_applications', 'selected_count', 'created_at', 'tenth_percentage', 'twelfth_percentage', 'school_name', 'father_occupation', 'mother_occupation', 'parents_phone', 'github_link', 'linkedin_link', 'hackerrank_score', 'cgpa_sem1', 'cgpa_sem2', 'cgpa_sem3', 'cgpa_sem4', 'cgpa_sem5', 'cgpa_sem6', 'cgpa_sem7', 'cgpa_sem8'];

$students = [];
$filename = '';

if ($export_type === 'company' && $company_id && $company_id !== 'all') {
    // Export students by specific company
    $stmt = $conn->prepare("SELECT company_name FROM companies WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $companyData = $stmt->fetch();
    $company_name = $companyData ? $companyData['company_name'] : 'Unknown';

    $stmt = $conn->prepare("
        SELECT DISTINCT s.name, s.reg_no, d.dept_name, s.cgpa, s.email, s.phone, s.dob, s.address, 
                s.father_name, s.father_phone, s.mother_name, s.mother_phone, s.batch_year, s.backlogs, 
                s.cleared_backlogs, s.verification_status, s.created_at, c.company_name,
                s.tenth_percentage, s.twelfth_percentage, s.school_name, s.father_occupation, s.mother_occupation, s.parents_phone,
                s.github_link, s.linkedin_link, s.hackerrank_score, s.cgpa_sem1, s.cgpa_sem2, s.cgpa_sem3, s.cgpa_sem4, s.cgpa_sem5, s.cgpa_sem6, s.cgpa_sem7, s.cgpa_sem8,
                (CASE WHEN a.final_status = 'selected' THEN 'Selected' WHEN a.final_status = 'rejected' THEN 'Rejected' ELSE 'Applied' END) AS final_status,
                (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id) AS total_applications,
                (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id AND final_status = 'selected') AS selected_count
        FROM students s
        JOIN departments d ON s.dept_id = d.dept_id
        JOIN applications a ON s.student_id = a.student_id
        JOIN drives dr ON a.drive_id = dr.drive_id
        JOIN companies c ON dr.company_id = c.company_id
        WHERE c.company_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$company_id]);
    $students = $stmt->fetchAll();
    $filename = 'students_' . preg_replace('/[^a-zA-Z0-9]/', '_', $company_name) . '_' . date('Y-m-d') . '.csv';

} else {
    // Export all students with filters
    $where = "WHERE 1=1";
    $params = [];

    if ($dept_filter !== 'all') {
        $where .= " AND s.dept_id = ?";
        $params[] = (int)$dept_filter;
    }
    if ($status_filter !== 'all') {
        $where .= " AND s.verification_status = ?";
        $params[] = $status_filter;
    }

    $stmt = $conn->prepare("
        SELECT s.name, s.reg_no, d.dept_name, s.cgpa, s.email, s.phone, s.dob, s.address, s.father_name, s.father_phone, s.mother_name, s.mother_phone, s.batch_year, s.backlogs, s.cleared_backlogs,
               s.verification_status, s.created_at,
               s.tenth_percentage, s.twelfth_percentage, s.school_name, s.father_occupation, s.mother_occupation, s.parents_phone,
               s.github_link, s.linkedin_link, s.hackerrank_score, s.cgpa_sem1, s.cgpa_sem2, s.cgpa_sem3, s.cgpa_sem4, s.cgpa_sem5, s.cgpa_sem6, s.cgpa_sem7, s.cgpa_sem8,
               (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id) AS total_applications,
               (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id AND final_status = 'selected') AS selected_count,
               NULL AS company_name,
               NULL AS final_status
        FROM students s
        JOIN departments d ON s.dept_id = d.dept_id
        $where
        ORDER BY d.dept_name, s.name
    ");
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    $filename = 'all_students_' . date('Y-m-d') . '.csv';
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header row
$header = [];
if (in_array('name', $fields)) $header[] = 'Name';
if (in_array('reg_no', $fields)) $header[] = 'Reg No';
if (in_array('dept_name', $fields)) $header[] = 'Department';
if (in_array('cgpa', $fields)) $header[] = 'CGPA';
if (in_array('email', $fields)) $header[] = 'Email';
if (in_array('phone', $fields)) $header[] = 'Phone';
if (in_array('dob', $fields)) $header[] = 'DOB';
if (in_array('address', $fields)) $header[] = 'Address';
if (in_array('father_name', $fields)) $header[] = 'Father Name';
if (in_array('father_phone', $fields)) $header[] = 'Father Phone';
if (in_array('mother_name', $fields)) $header[] = 'Mother Name';
if (in_array('mother_phone', $fields)) $header[] = 'Mother Phone';
if (in_array('batch_year', $fields)) $header[] = 'Batch Year';
if (in_array('backlogs', $fields)) $header[] = 'Backlogs';
if (in_array('cleared_backlogs', $fields)) $header[] = 'Cleared Backlogs';
if (in_array('verification_status', $fields)) $header[] = 'Verification Status';
if (in_array('total_applications', $fields)) $header[] = 'Applications';
if (in_array('selected_count', $fields)) $header[] = 'Selected';
if (in_array('created_at', $fields)) $header[] = 'Registered On';
if (in_array('company_name', $fields)) $header[] = 'Company Name';
if (in_array('final_status', $fields)) $header[] = 'Application Status';
if (in_array('tenth_percentage', $fields)) $header[] = '10th Percentage';
if (in_array('twelfth_percentage', $fields)) $header[] = '12th Percentage';
if (in_array('school_name', $fields)) $header[] = 'School Name';
if (in_array('father_occupation', $fields)) $header[] = 'Father Occupation';
if (in_array('mother_occupation', $fields)) $header[] = 'Mother Occupation';
if (in_array('parents_phone', $fields)) $header[] = 'Parents Phone';
if (in_array('github_link', $fields)) $header[] = 'GitHub Link';
if (in_array('linkedin_link', $fields)) $header[] = 'LinkedIn Link';
if (in_array('hackerrank_score', $fields)) $header[] = 'HackerRank Score';
if (in_array('cgpa_sem1', $fields)) $header[] = 'CGPA Sem 1';
if (in_array('cgpa_sem2', $fields)) $header[] = 'CGPA Sem 2';
if (in_array('cgpa_sem3', $fields)) $header[] = 'CGPA Sem 3';
if (in_array('cgpa_sem4', $fields)) $header[] = 'CGPA Sem 4';
if (in_array('cgpa_sem5', $fields)) $header[] = 'CGPA Sem 5';
if (in_array('cgpa_sem6', $fields)) $header[] = 'CGPA Sem 6';
if (in_array('cgpa_sem7', $fields)) $header[] = 'CGPA Sem 7';
if (in_array('cgpa_sem8', $fields)) $header[] = 'CGPA Sem 8';

fputcsv($output, $header);

foreach ($students as $s) {
    $row = [];
    if (in_array('name', $fields)) $row[] = $s['name'];
    if (in_array('reg_no', $fields)) $row[] = $s['reg_no'];
    if (in_array('dept_name', $fields)) $row[] = $s['dept_name'];
    if (in_array('cgpa', $fields)) $row[] = number_format($s['cgpa'], 2);
    if (in_array('email', $fields)) $row[] = $s['email'];
    if (in_array('phone', $fields)) $row[] = $s['phone'];
    if (in_array('dob', $fields)) $row[] = $s['dob'];
    if (in_array('address', $fields)) $row[] = $s['address'];
    if (in_array('father_name', $fields)) $row[] = $s['father_name'];
    if (in_array('father_phone', $fields)) $row[] = $s['father_phone'];
    if (in_array('mother_name', $fields)) $row[] = $s['mother_name'];
    if (in_array('mother_phone', $fields)) $row[] = $s['mother_phone'];
    if (in_array('batch_year', $fields)) $row[] = $s['batch_year'] ? $s['batch_year'] . '-' . ($s['batch_year'] + 4) : '';
    if (in_array('backlogs', $fields)) $row[] = $s['backlogs'];
    if (in_array('cleared_backlogs', $fields)) $row[] = $s['cleared_backlogs'];
    if (in_array('verification_status', $fields)) $row[] = ucfirst($s['verification_status']);
    if (in_array('total_applications', $fields)) $row[] = $s['total_applications'];
    if (in_array('selected_count', $fields)) $row[] = $s['selected_count'];
    if (in_array('created_at', $fields)) $row[] = date('d M Y h:i A', strtotime($s['created_at']));
    if (in_array('company_name', $fields)) $row[] = $s['company_name'] ?? '';
    if (in_array('final_status', $fields)) $row[] = $s['final_status'] ?? '';
    if (in_array('tenth_percentage', $fields)) $row[] = $s['tenth_percentage'] ? number_format($s['tenth_percentage'], 2) : '';
    if (in_array('twelfth_percentage', $fields)) $row[] = $s['twelfth_percentage'] ? number_format($s['twelfth_percentage'], 2) : '';
    if (in_array('school_name', $fields)) $row[] = $s['school_name'] ?? '';
    if (in_array('father_occupation', $fields)) $row[] = $s['father_occupation'] ?? '';
    if (in_array('mother_occupation', $fields)) $row[] = $s['mother_occupation'] ?? '';
    if (in_array('parents_phone', $fields)) $row[] = $s['parents_phone'] ?? '';
    if (in_array('github_link', $fields)) $row[] = $s['github_link'] ?? '';
    if (in_array('linkedin_link', $fields)) $row[] = $s['linkedin_link'] ?? '';
    if (in_array('hackerrank_score', $fields)) $row[] = $s['hackerrank_score'] ? number_format($s['hackerrank_score']) : '';
    if (in_array('cgpa_sem1', $fields)) $row[] = $s['cgpa_sem1'] ? number_format($s['cgpa_sem1'], 2) : '';
    if (in_array('cgpa_sem2', $fields)) $row[] = $s['cgpa_sem2'] ? number_format($s['cgpa_sem2'], 2) : '';
    if (in_array('cgpa_sem3', $fields)) $row[] = $s['cgpa_sem3'] ? number_format($s['cgpa_sem3'], 2) : '';
    if (in_array('cgpa_sem4', $fields)) $row[] = $s['cgpa_sem4'] ? number_format($s['cgpa_sem4'], 2) : '';
    if (in_array('cgpa_sem5', $fields)) $row[] = $s['cgpa_sem5'] ? number_format($s['cgpa_sem5'], 2) : '';
    if (in_array('cgpa_sem6', $fields)) $row[] = $s['cgpa_sem6'] ? number_format($s['cgpa_sem6'], 2) : '';
    if (in_array('cgpa_sem7', $fields)) $row[] = $s['cgpa_sem7'] ? number_format($s['cgpa_sem7'], 2) : '';
    if (in_array('cgpa_sem8', $fields)) $row[] = $s['cgpa_sem8'] ? number_format($s['cgpa_sem8'], 2) : '';

    fputcsv($output, $row);
}

fclose($output);
exit;

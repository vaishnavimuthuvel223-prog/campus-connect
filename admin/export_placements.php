<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$report_type = $_POST['report_type'] ?? 'selected_students';
$fields = $_POST['fields'] ?? ['name', 'reg_no', 'dept_name', 'cgpa', 'company_name', 'package'];

if (empty($fields)) {
    $fields = ['name', 'reg_no', 'dept_name', 'cgpa', 'company_name', 'package'];
}

$filename = 'placement_report_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Selected Students Report
if ($report_type === 'selected_students') {
    $stmt = $conn->query("
        SELECT s.student_id, s.name, s.reg_no, d.dept_name, s.cgpa, s.email, s.phone, s.batch_year,
               c.company_name, c.package, dr.role, dr.drive_date, a.created_at as application_date
        FROM applications a
        JOIN students s ON a.student_id = s.student_id
        JOIN departments d ON s.dept_id = d.dept_id
        JOIN drives dr ON a.drive_id = dr.drive_id
        JOIN companies c ON dr.company_id = c.company_id
        WHERE a.final_status = 'selected'
        ORDER BY d.dept_name, s.name
    ");
    $data = $stmt->fetchAll();

    // Header
    $header = [];
    if (in_array('name', $fields)) $header[] = 'Student Name';
    if (in_array('reg_no', $fields)) $header[] = 'Registration No';
    if (in_array('dept_name', $fields)) $header[] = 'Department';
    if (in_array('cgpa', $fields)) $header[] = 'CGPA';
    if (in_array('batch_year', $fields)) $header[] = 'Batch Year';
    if (in_array('email', $fields)) $header[] = 'Email';
    if (in_array('phone', $fields)) $header[] = 'Phone';
    if (in_array('company_name', $fields)) $header[] = 'Company';
    if (in_array('package', $fields)) $header[] = 'Package (LPA)';
    if (in_array('role', $fields)) $header[] = 'Role';
    if (in_array('drive_date', $fields)) $header[] = 'Drive Date';
    if (in_array('application_date', $fields)) $header[] = 'Application Date';

    fputcsv($output, $header);

    // Data rows
    foreach ($data as $row) {
        $csvRow = [];
        if (in_array('name', $fields)) $csvRow[] = $row['name'];
        if (in_array('reg_no', $fields)) $csvRow[] = $row['reg_no'];
        if (in_array('dept_name', $fields)) $csvRow[] = $row['dept_name'];
        if (in_array('cgpa', $fields)) $csvRow[] = number_format($row['cgpa'], 2);
        if (in_array('batch_year', $fields)) $csvRow[] = $row['batch_year'] ? $row['batch_year'] . '-' . ($row['batch_year'] + 4) : '';
        if (in_array('email', $fields)) $csvRow[] = $row['email'];
        if (in_array('phone', $fields)) $csvRow[] = $row['phone'];
        if (in_array('company_name', $fields)) $csvRow[] = $row['company_name'];
        if (in_array('package', $fields)) $csvRow[] = $row['package'];
        if (in_array('role', $fields)) $csvRow[] = $row['role'];
        if (in_array('drive_date', $fields)) $csvRow[] = date('d-M-Y', strtotime($row['drive_date']));
        if (in_array('application_date', $fields)) $csvRow[] = date('d-M-Y H:i', strtotime($row['application_date']));
        
        fputcsv($output, $csvRow);
    }
}

// Company Summary Report
elseif ($report_type === 'company_summary') {
    $stmt = $conn->query("
        SELECT c.company_id, c.company_name, c.package,
               COUNT(a.application_id) AS total_applications,
               SUM(CASE WHEN a.final_status = 'selected' THEN 1 ELSE 0 END) AS selected_count
        FROM companies c
        LEFT JOIN drives dr ON c.company_id = dr.company_id
        LEFT JOIN applications a ON dr.drive_id = a.drive_id
        GROUP BY c.company_id
        ORDER BY selected_count DESC
    ");
    $data = $stmt->fetchAll();

    // Header
    $header = ['Company Name', 'Package (LPA)', 'Total Applications', 'Selected Students'];
    fputcsv($output, $header);

    // Data rows
    foreach ($data as $row) {
        fputcsv($output, [
            $row['company_name'],
            $row['package'],
            $row['total_applications'] ?? 0,
            $row['selected_count'] ?? 0
        ]);
    }
}

// Department Summary Report
elseif ($report_type === 'department_summary') {
    $stmt = $conn->query("
        SELECT d.dept_name,
            (SELECT COUNT(*) FROM students WHERE dept_id = d.dept_id) AS total_students,
            (SELECT COUNT(*) FROM students WHERE dept_id = d.dept_id AND verification_status = 'approved') AS approved_students,
            (SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = d.dept_id) AS applied_students,
            (SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = d.dept_id AND a.final_status = 'selected') AS placed_students
        FROM departments d
        ORDER BY d.dept_name
    ");
    $data = $stmt->fetchAll();

    // Header
    $header = ['Department', 'Total Students', 'Verified', 'Applied', 'Placed'];
    fputcsv($output, $header);

    // Data rows
    foreach ($data as $row) {
        $pct = $row['approved_students'] > 0 ? round(($row['placed_students'] / $row['approved_students']) * 100, 1) : 0;
        fputcsv($output, [
            $row['dept_name'],
            $row['total_students'],
            $row['approved_students'],
            $row['applied_students'],
            $row['placed_students'] . ' (' . $pct . '%)'
        ]);
    }
}

// Complete Placement Report (All data)
else {
    $stmt = $conn->query("
        SELECT s.student_id, s.name, s.reg_no, d.dept_name, s.cgpa, s.email, s.phone, s.batch_year,
               c.company_name, c.package, dr.role, a.final_status,
               (SELECT COUNT(*) FROM applications WHERE student_id = s.student_id) AS total_applications
        FROM students s
        JOIN departments d ON s.dept_id = d.dept_id
        LEFT JOIN applications a ON s.student_id = a.student_id
        LEFT JOIN drives dr ON a.drive_id = dr.drive_id
        LEFT JOIN companies c ON dr.company_id = c.company_id
        ORDER BY d.dept_name, s.name
    ");
    $data = $stmt->fetchAll();

    // Header
    $header = [];
    if (in_array('name', $fields)) $header[] = 'Student Name';
    if (in_array('reg_no', $fields)) $header[] = 'Registration No';
    if (in_array('dept_name', $fields)) $header[] = 'Department';
    if (in_array('cgpa', $fields)) $header[] = 'CGPA';
    if (in_array('batch_year', $fields)) $header[] = 'Batch Year';
    if (in_array('email', $fields)) $header[] = 'Email';
    if (in_array('phone', $fields)) $header[] = 'Phone';
    if (in_array('company_name', $fields)) $header[] = 'Company';
    if (in_array('package', $fields)) $header[] = 'Package';
    if (in_array('role', $fields)) $header[] = 'Role';
    $header[] = 'Applications'; // Always include
    $header[] = 'Status'; // Always include

    fputcsv($output, $header);

    // Data rows
    foreach ($data as $row) {
        $csvRow = [];
        if (in_array('name', $fields)) $csvRow[] = $row['name'];
        if (in_array('reg_no', $fields)) $csvRow[] = $row['reg_no'];
        if (in_array('dept_name', $fields)) $csvRow[] = $row['dept_name'];
        if (in_array('cgpa', $fields)) $csvRow[] = number_format($row['cgpa'], 2);
        if (in_array('batch_year', $fields)) $csvRow[] = $row['batch_year'] ? $row['batch_year'] . '-' . ($row['batch_year'] + 4) : '';
        if (in_array('email', $fields)) $csvRow[] = $row['email'];
        if (in_array('phone', $fields)) $csvRow[] = $row['phone'];
        if (in_array('company_name', $fields)) $csvRow[] = $row['company_name'] ?? 'N/A';
        if (in_array('package', $fields)) $csvRow[] = $row['package'] ?? 'N/A';
        if (in_array('role', $fields)) $csvRow[] = $row['role'] ?? 'N/A';
        $csvRow[] = $row['total_applications'];
        $csvRow[] = $row['final_status'] ? ucfirst($row['final_status']) : 'Not Applied';
        
        fputcsv($output, $csvRow);
    }
}

fclose($output);
exit;

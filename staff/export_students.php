<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$dept_id = $_SESSION['dept_id'];

// Get department name
$stmt = $conn->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
$stmt->execute([$dept_id]);
$dept_name = $stmt->fetchColumn();

// Filter
$filter = $_POST['filter'] ?? 'all';
$fields = $_POST['fields'] ?? ['name', 'reg_no', 'dept_name', 'cgpa', 'email', 'verification_status', 'created_at'];

$where = "WHERE s.dept_id = ?";
$params = [$dept_id];
if ($filter === 'pending') { $where .= " AND s.verification_status = 'pending'"; }
elseif ($filter === 'approved') { $where .= " AND s.verification_status = 'approved'"; }
elseif ($filter === 'rejected') { $where .= " AND s.verification_status = 'rejected'"; }

$stmt = $conn->prepare("
    SELECT s.name, s.reg_no, d.dept_name, s.cgpa, s.email, s.phone, s.dob, s.address, s.father_name, s.father_phone, s.mother_name, s.mother_phone, s.batch_year, s.backlogs, s.cleared_backlogs, s.skill_category, s.verification_status, s.created_at,
           COUNT(CASE WHEN a.final_status = 'selected' THEN 1 END) AS selections
    FROM students s
    JOIN departments d ON s.dept_id = d.dept_id
    LEFT JOIN applications a ON s.student_id = a.student_id
    $where
    GROUP BY s.student_id
    ORDER BY s.created_at DESC
");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Generate CSV
$filename = 'students_' . preg_replace('/[^a-zA-Z0-9]/', '_', $dept_name) . '_' . date('Y-m-d') . '.csv';

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
if (in_array('skill_category', $fields)) $header[] = 'Skill Category';
if (in_array('selections', $fields)) $header[] = 'Placements';
if (in_array('verification_status', $fields)) $header[] = 'Status';
if (in_array('created_at', $fields)) $header[] = 'Registered On';

fputcsv($output, $header);

// Data rows
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
    if (in_array('skill_category', $fields)) $row[] = $s['skill_category'];
    if (in_array('selections', $fields)) $row[] = $s['selections'];
    if (in_array('verification_status', $fields)) $row[] = ucfirst($s['verification_status']);
    if (in_array('created_at', $fields)) $row[] = date('d M Y h:i A', strtotime($s['created_at']));

    fputcsv($output, $row);
}

fclose($output);
exit;

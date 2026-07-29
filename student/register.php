<?php
session_start();
require_once '../config/db.php';

$error = '';
$success = '';

$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $reg_no = trim($_POST['reg_no']);
    $dept_id = (int)$_POST['dept_id'];
    $cgpa = (float)$_POST['cgpa'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $batch_year = (int)$_POST['batch_year'];
    $backlogs = (int)$_POST['backlogs'];
    $current_semester = (int)$_POST['current_semester'];
    $skill_category = $_POST['skill_category'] ?? '';
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    $allowed_skills = ['elite category','general','service now','salesforce','SAP','Cybersecurity'];
    if (!in_array($skill_category, $allowed_skills)) {
        $error = 'Invalid skill category.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($cgpa < 0 || $cgpa > 10) {
        $error = 'CGPA must be between 0 and 10.';
    } elseif ($batch_year < 2000 || $batch_year > 2030) {
        $error = 'Invalid batch year.';
    } elseif ($backlogs < 0) {
        $error = 'Backlogs cannot be negative.';
    } elseif ($current_semester < 1 || $current_semester > 8) {
        $error = 'Invalid semester.';
    } else {
        // Check duplicate
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ? OR reg_no = ?");
        $stmt->execute([$email, $reg_no]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email or Registration Number already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO students (name, reg_no, dept_id, cgpa, email, phone, batch_year, backlogs, current_semester, skill_category, password) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $reg_no, $dept_id, $cgpa, $email, $phone ?: null, $batch_year ?: null, $backlogs, $current_semester, $skill_category, $hash]);
            $success = 'Registration successful! Please wait for department staff verification before logging in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Campus Recruitment</title>
    <link rel="stylesheet" href="/campuss/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" style="max-width:500px;">
        <h2>📝 Student Registration</h2>
        <p class="subtitle">Create your placement account</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Registration Number</label>
                <input type="text" name="reg_no" class="form-control" required placeholder="e.g. CSE2021001" value="<?= htmlspecialchars($_POST['reg_no'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="dept_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['dept_id'] ?>" <?= (isset($_POST['dept_id']) && $_POST['dept_id'] == $d['dept_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['dept_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Skill Category</label>
                <select name="skill_category" class="form-control" required>
                    <option value="">-- Select Skill Category --</option>
                    <option value="elite category" <?= (isset($_POST['skill_category']) && $_POST['skill_category'] == 'elite category') ? 'selected' : '' ?>>Elite Category</option>
                    <option value="general" <?= (isset($_POST['skill_category']) && $_POST['skill_category'] == 'general') ? 'selected' : '' ?>>General</option>
                    <option value="service now" <?= (isset($_POST['skill_category']) && $_POST['skill_category'] == 'service now') ? 'selected' : '' ?>>Service Now</option>
                    <option value="salesforce" <?= (isset($_POST['skill_category']) && $_POST['skill_category'] == 'salesforce') ? 'selected' : '' ?>>Salesforce</option>
                    <option value="SAP" <?= (isset($_POST['skill_category']) && $_POST['skill_category'] == 'SAP') ? 'selected' : '' ?>>SAP</option>
                    <option value="Cybersecurity" <?= (isset($_POST['skill_category']) && $_POST['skill_category'] == 'Cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
                </select>
            </div>
            <div class="form-group">
                <label>CGPA (out of 10)</label>
                <input type="number" step="0.01" min="0" max="10" name="cgpa" class="form-control" required value="<?= htmlspecialchars($_POST['cgpa'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="e.g. 9876543210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Batch Year</label>
                <input type="number" name="batch_year" class="form-control" placeholder="e.g. 2021" min="2000" max="2030" value="<?= htmlspecialchars($_POST['batch_year'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Number of Backlogs</label>
                <input type="number" name="backlogs" class="form-control" min="0" value="<?= htmlspecialchars($_POST['backlogs'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label>Current Semester</label>
                <input type="number" name="current_semester" class="form-control" min="1" max="8" value="<?= htmlspecialchars($_POST['current_semester'] ?? '1') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Register</button>
        </form>
        <div class="form-footer">
            Already have an account? <a href="login.php">Login here</a><br>
            <a href="/campuss/">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Auto-add phone column if missing
try {
    $conn->query("SELECT phone FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER email");
}

// Auto-add new columns if missing
try {
    $conn->query("SELECT profile_pic FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL AFTER resume");
}
try {
    $conn->query("SELECT batch_year FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN batch_year INT DEFAULT NULL AFTER phone");
}
try {
    $conn->query("SELECT backlogs FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN backlogs INT DEFAULT 0 AFTER batch_year");
}
try {
    $conn->query("SELECT cleared_backlogs FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN cleared_backlogs INT DEFAULT 0 AFTER backlogs");
}
try {
    $conn->query("SELECT dob FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN dob DATE DEFAULT NULL AFTER cleared_backlogs");
}
try {
    $conn->query("SELECT address FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN address TEXT DEFAULT NULL AFTER dob");
}
try {
    $conn->query("SELECT father_name FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN father_name VARCHAR(255) DEFAULT NULL AFTER address");
}
try {
    $conn->query("SELECT mother_name FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN mother_name VARCHAR(255) DEFAULT NULL AFTER father_name");
}
try {
    $conn->query("SELECT mentor_name FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN mentor_name VARCHAR(255) DEFAULT NULL AFTER mother_name");
}
try {
    $conn->query("SELECT class_advisor_name FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN class_advisor_name VARCHAR(255) DEFAULT NULL AFTER mentor_name");
}
try {
    $conn->query("SELECT father_phone FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN father_phone VARCHAR(20) DEFAULT NULL AFTER class_advisor_name");
}
try {
    $conn->query("SELECT mother_phone FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN mother_phone VARCHAR(20) DEFAULT NULL AFTER father_phone");
}
try {
    $conn->query("SELECT current_semester FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN current_semester INT DEFAULT 1 AFTER mother_phone");
}
try {
    $conn->query("SELECT tenth_percentage FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN tenth_percentage DECIMAL(5,2) DEFAULT NULL AFTER current_semester");
}
try {
    $conn->query("SELECT twelfth_percentage FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN twelfth_percentage DECIMAL(5,2) DEFAULT NULL AFTER tenth_percentage");
}
try {
    $conn->query("SELECT school_name FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN school_name VARCHAR(255) DEFAULT NULL AFTER twelfth_percentage");
}
try {
    $conn->query("SELECT mother_occupation FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN mother_occupation VARCHAR(255) DEFAULT NULL AFTER school_name");
}
try {
    $conn->query("SELECT father_occupation FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN father_occupation VARCHAR(255) DEFAULT NULL AFTER mother_occupation");
}
try {
    $conn->query("SELECT parents_phone FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN parents_phone VARCHAR(20) DEFAULT NULL AFTER father_occupation");
}
try {
    $conn->query("SELECT github_link FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN github_link VARCHAR(255) DEFAULT NULL AFTER parents_phone");
}
try {
    $conn->query("SELECT linkedin_link FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN linkedin_link VARCHAR(255) DEFAULT NULL AFTER github_link");
}
try {
    $conn->query("SELECT hackerrank_score FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN hackerrank_score INT DEFAULT NULL AFTER linkedin_link");
}
// Add skill_category column if missing
try {
    $conn->query("SELECT skill_category FROM students LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE students ADD COLUMN skill_category VARCHAR(32) DEFAULT NULL AFTER linkedin_link");
}

for ($i = 1; $i <= 8; $i++) {
    try {
        $conn->query("SELECT cgpa_sem{$i} FROM students LIMIT 1");
    } catch (Exception $e) {
        $conn->exec("ALTER TABLE students ADD COLUMN cgpa_sem{$i} DECIMAL(4,2) DEFAULT NULL AFTER hackerrank_score");
    }
}

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

// Auto-create projects table if missing
try {
    $conn->query("SELECT project_id FROM projects LIMIT 1");
} catch (Exception $e) {
    $conn->exec("CREATE TABLE projects (
        project_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        project_title VARCHAR(255) NOT NULL,
        project_description TEXT DEFAULT NULL,
        technologies_used TEXT DEFAULT NULL,
        project_link VARCHAR(255) DEFAULT NULL,
        start_date DATE DEFAULT NULL,
        end_date DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
    )");
}

// Auto-create skills table if missing
try {
    $conn->query("SELECT skill_id FROM skills LIMIT 1");
} catch (Exception $e) {
    $conn->exec("CREATE TABLE skills (
        skill_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        skill_name VARCHAR(255) NOT NULL,
        skill_type ENUM('technical','soft') NOT NULL DEFAULT 'technical',
        proficiency_level ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
    )");
}

// Auto-create area_of_interest table if missing
try {
    $conn->query("SELECT interest_id FROM area_of_interest LIMIT 1");
} catch (Exception $e) {
    $conn->exec("CREATE TABLE area_of_interest (
        interest_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        interest_name VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
    )");
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $batch_year = (int)$_POST['batch_year'];
    $backlogs = (int)$_POST['backlogs'];
    $cleared_backlogs = (int)$_POST['cleared_backlogs'];
    $dob = trim($_POST['dob']);
    $address = trim($_POST['address']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
    $mentor_name = trim($_POST['mentor_name']);
    $class_advisor_name = trim($_POST['class_advisor_name']);
    $father_phone = trim($_POST['father_phone']);
    $mother_phone = trim($_POST['mother_phone']);
    $current_semester = (int)$_POST['current_semester'];
    $tenth_percentage = trim($_POST['tenth_percentage']);
    $twelfth_percentage = trim($_POST['twelfth_percentage']);
    $school_name = trim($_POST['school_name']);
    $mother_occupation = trim($_POST['mother_occupation']);
    $father_occupation = trim($_POST['father_occupation']);
    $github_link = trim($_POST['github_link']);
    $linkedin_link = trim($_POST['linkedin_link']);
    $hackerrank_score = trim($_POST['hackerrank_score']);
    $cgpa_sem1 = trim($_POST['cgpa_sem1']);
    $cgpa_sem2 = trim($_POST['cgpa_sem2']);
    $cgpa_sem3 = trim($_POST['cgpa_sem3']);
    $cgpa_sem4 = trim($_POST['cgpa_sem4']);
    $cgpa_sem5 = trim($_POST['cgpa_sem5']);
    $cgpa_sem6 = trim($_POST['cgpa_sem6']);
    $cgpa_sem7 = trim($_POST['cgpa_sem7']);
    $cgpa_sem8 = trim($_POST['cgpa_sem8']);
    $skill_category = $_POST['skill_category'] ?? '';

    $allowed_skills = ['elite category','general','service now','salesforce','SAP','Cybersecurity'];
    if (!in_array($skill_category, $allowed_skills)) {
        $error = 'Invalid skill category.';
    } elseif (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($batch_year < 2000 || $batch_year > 2030) {
        $error = 'Invalid batch year.';
    } elseif ($backlogs < 0) {
        $error = 'Backlogs cannot be negative.';
    } elseif ($current_semester < 1 || $current_semester > 8) {
        $error = 'Invalid semester.';
    } else {
        // Check email uniqueness (excluding self)
        $chk = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND student_id != ?");
        $chk->execute([$email, $student_id]);
        if ($chk->fetchColumn() > 0) {
            $error = 'Email already used by another student.';
        } else {
            $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ?, batch_year = ?, backlogs = ?, cleared_backlogs = ?, dob = ?, address = ?, father_name = ?, mother_name = ?, mentor_name = ?, class_advisor_name = ?, father_phone = ?, mother_phone = ?, current_semester = ?, tenth_percentage = ?, twelfth_percentage = ?, school_name = ?, mother_occupation = ?, father_occupation = ?, github_link = ?, linkedin_link = ?, hackerrank_score = ?, cgpa_sem1 = ?, cgpa_sem2 = ?, cgpa_sem3 = ?, cgpa_sem4 = ?, cgpa_sem5 = ?, cgpa_sem6 = ?, cgpa_sem7 = ?, cgpa_sem8 = ?, skill_category = ? WHERE student_id = ?");
            $stmt->execute([$name, $email, $phone ?: null, $batch_year ?: null, $backlogs, $cleared_backlogs, $dob ?: null, $address ?: null, $father_name ?: null, $mother_name ?: null, $mentor_name ?: null, $class_advisor_name ?: null, $father_phone ?: null, $mother_phone ?: null, $current_semester, $tenth_percentage ?: null, $twelfth_percentage ?: null, $school_name ?: null, $mother_occupation ?: null, $father_occupation ?: null, $github_link ?: null, $linkedin_link ?: null, $hackerrank_score ?: null, $cgpa_sem1 ?: null, $cgpa_sem2 ?: null, $cgpa_sem3 ?: null, $cgpa_sem4 ?: null, $cgpa_sem5 ?: null, $cgpa_sem6 ?: null, $cgpa_sem7 ?: null, $cgpa_sem8 ?: null, $skill_category, $student_id]);
            $_SESSION['name'] = $name;
            $success = 'Profile updated successfully!';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current hash
    $stmt = $conn->prepare("SELECT password FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?")->execute([$newHash, $student_id]);
        $success = 'Password changed successfully!';
    }
}

// Handle internship addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_internship'])) {
    $company_name = trim($_POST['company_name']);
    $role = trim($_POST['role']);
    $duration = trim($_POST['duration']);
    $stipend = trim($_POST['stipend']);
    $project_title = trim($_POST['project_title']);
    $project_description = trim($_POST['project_description']);
    $technologies_used = trim($_POST['technologies_used']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    if (empty($company_name) || empty($role) || empty($duration)) {
        $error = 'Company name, role, and duration are required for internship.';
    } else {
        $stmt = $conn->prepare("INSERT INTO internships (student_id, company_name, role, duration, stipend, project_title, project_description, technologies_used, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $company_name, $role, $duration, $stipend ?: null, $project_title ?: null, $project_description ?: null, $technologies_used ?: null, $start_date ?: null, $end_date ?: null]);
        $success = 'Internship added successfully!';
    }
}

// Handle internship deletion
if (isset($_GET['delete_internship'])) {
    $internship_id = (int)$_GET['delete_internship'];
    $stmt = $conn->prepare("DELETE FROM internships WHERE internship_id = ? AND student_id = ?");
    $stmt->execute([$internship_id, $student_id]);
    $success = 'Internship removed successfully!';
    header('Location: profile.php');
    exit;
}

// Handle project addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $project_title = trim($_POST['project_title']);
    $project_description = trim($_POST['project_description']);
    $technologies_used = trim($_POST['technologies_used']);
    $project_link = trim($_POST['project_link']);
    $start_date = trim($_POST['project_start_date']);
    $end_date = trim($_POST['project_end_date']);

    if (empty($project_title)) {
        $error = 'Project title is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO projects (student_id, project_title, project_description, technologies_used, project_link, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $project_title, $project_description ?: null, $technologies_used ?: null, $project_link ?: null, $start_date ?: null, $end_date ?: null]);
        $success = 'Project added successfully!';
    }
}

// Handle project deletion
if (isset($_GET['delete_project'])) {
    $project_id = (int)$_GET['delete_project'];
    $stmt = $conn->prepare("DELETE FROM projects WHERE project_id = ? AND student_id = ?");
    $stmt->execute([$project_id, $student_id]);
    $success = 'Project removed successfully!';
    header('Location: profile.php');
    exit;
}

// Handle skill addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    $skill_name = trim($_POST['skill_name']);
    $skill_type = trim($_POST['skill_type']);
    $proficiency_level = trim($_POST['proficiency_level']);

    if (empty($skill_name)) {
        $error = 'Skill name is required.';
    } elseif (!in_array($skill_type, ['technical', 'soft'])) {
        $error = 'Invalid skill type.';
    } elseif (!in_array($proficiency_level, ['beginner', 'intermediate', 'advanced', 'expert'])) {
        $error = 'Invalid proficiency level.';
    } else {
        $stmt = $conn->prepare("INSERT INTO skills (student_id, skill_name, skill_type, proficiency_level) VALUES (?, ?, ?, ?)");
        $stmt->execute([$student_id, $skill_name, $skill_type, $proficiency_level]);
        $success = 'Skill added successfully!';
    }
}

// Handle skill deletion
if (isset($_GET['delete_skill'])) {
    $skill_id = (int)$_GET['delete_skill'];
    $stmt = $conn->prepare("DELETE FROM skills WHERE skill_id = ? AND student_id = ?");
    $stmt->execute([$skill_id, $student_id]);
    $success = 'Skill removed successfully!';
    header('Location: profile.php');
    exit;
}

// Handle interest addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_interest'])) {
    $interest_name = trim($_POST['interest_name']);
    $description = trim($_POST['interest_description']);

    if (empty($interest_name)) {
        $error = 'Area of interest name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO area_of_interest (student_id, interest_name, description) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $interest_name, $description ?: null]);
        $success = 'Area of interest added successfully!';
    }
}

// Handle interest deletion
if (isset($_GET['delete_interest'])) {
    $interest_id = (int)$_GET['delete_interest'];
    $stmt = $conn->prepare("DELETE FROM area_of_interest WHERE interest_id = ? AND student_id = ?");
    $stmt->execute([$interest_id, $student_id]);
    $success = 'Area of interest removed successfully!';
    header('Location: profile.php');
    exit;
}

// Handle resume upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['resume'];
    $allowed = ['pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error. Please try again.';
    } elseif (!in_array($ext, $allowed)) {
        $error = 'Only PDF files are allowed.';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = 'File size must be under 2MB.';
    } else {
        $uploadDir = '../uploads/resumes/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = 'resume_' . $student_id . '_' . time() . '.' . $ext;
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $stmt = $conn->prepare("UPDATE students SET resume = ? WHERE student_id = ?");
            $stmt->execute([$filename, $student_id]);
            $success = 'Resume uploaded successfully!';
        } else {
            $error = 'Failed to save file.';
        }
    }
}

// Handle resume delete
if (isset($_GET['delete_resume'])) {
    $stmt = $conn->prepare("SELECT resume FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $oldResume = $stmt->fetchColumn();
    if ($oldResume) {
        $path = '../uploads/resumes/' . $oldResume;
        if (file_exists($path)) unlink($path);
        $conn->prepare("UPDATE students SET resume = NULL WHERE student_id = ?")->execute([$student_id]);
        $success = 'Resume removed.';
    }
    header('Location: profile.php');
    exit;
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['profile_pic'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error. Please try again.';
    } elseif (!in_array($ext, $allowed)) {
        $error = 'Only JPG, PNG, GIF files are allowed.';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = 'File size must be under 2MB.';
    } else {
        $uploadDir = '../uploads/profiles/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = 'profile_' . $student_id . '_' . time() . '.' . $ext;
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Delete old profile pic
            $stmt = $conn->prepare("SELECT profile_pic FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $oldPic = $stmt->fetchColumn();
            if ($oldPic) {
                $oldPath = '../uploads/profiles/' . $oldPic;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $stmt = $conn->prepare("UPDATE students SET profile_pic = ? WHERE student_id = ?");
            $stmt->execute([$filename, $student_id]);
            $success = 'Profile picture uploaded successfully!';
        } else {
            $error = 'Failed to save file.';
        }
    }
}

// Handle profile picture delete
if (isset($_GET['delete_profile_pic'])) {
    $stmt = $conn->prepare("SELECT profile_pic FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $oldPic = $stmt->fetchColumn();
    if ($oldPic) {
        $path = '../uploads/profiles/' . $oldPic;
        if (file_exists($path)) unlink($path);
        $conn->prepare("UPDATE students SET profile_pic = NULL WHERE student_id = ?")->execute([$student_id]);
        $success = 'Profile picture removed.';
    }
    header('Location: profile.php');
    exit;
}

// Fetch student
$stmt = $conn->prepare("SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.dept_id = d.dept_id WHERE s.student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Fetch internships
$internshipStmt = $conn->prepare("SELECT * FROM internships WHERE student_id = ? ORDER BY start_date DESC");
$internshipStmt->execute([$student_id]);
$internships = $internshipStmt->fetchAll();

// Fetch projects
$projectStmt = $conn->prepare("SELECT * FROM projects WHERE student_id = ? ORDER BY start_date DESC");
$projectStmt->execute([$student_id]);
$projects = $projectStmt->fetchAll();

// Fetch skills
$skillStmt = $conn->prepare("SELECT * FROM skills WHERE student_id = ? ORDER BY created_at DESC");
$skillStmt->execute([$student_id]);
$skills = $skillStmt->fetchAll();

// Fetch interests
$interestStmt = $conn->prepare("SELECT * FROM area_of_interest WHERE student_id = ? ORDER BY created_at DESC");
$interestStmt->execute([$student_id]);
$interests = $interestStmt->fetchAll();

// Calculate average CGPA
$cgpaSum = 0;
$cgpaCount = 0;
for ($i = 1; $i <= 8; $i++) {
    if (!empty($student['cgpa_sem' . $i])) {
        $cgpaSum += $student['cgpa_sem' . $i];
        $cgpaCount++;
    }
}
$averageCgpa = $cgpaCount > 0 ? round($cgpaSum / $cgpaCount, 2) : null;

// Count stats
$appStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$appStmt->execute([$student_id]);
$totalApps = $appStmt->fetchColumn();

$selStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND final_status = 'selected'");
$selStmt->execute([$student_id]);
$selected = $selStmt->fetchColumn();

$pageTitle = 'My Profile';
$showNav = true;
$currentPage = 'profile';
include '../includes/header.php';
?>

<div class="main-content-wrapper">
<div class="container">
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="page-header"><h2>👤 My Profile</h2></div>

    <!-- Profile Card -->
    <div class="card profile-hero">
        <div class="profile-top">
            <div class="profile-avatar">
                <?php if ($student['profile_pic']): ?>
                    <img src="/campuss/uploads/profiles/<?= htmlspecialchars($student['profile_pic']) ?>" alt="Profile Picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= strtoupper(substr($student['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($student['name']) ?></h3>
                <p style="color:var(--gray);margin:0.25rem 0;"><?= htmlspecialchars($student['reg_no']) ?> &bull; <?= htmlspecialchars($student['dept_name']) ?></p>
                <?php if (!empty($student['skill_category'])): ?>
                    <span class="badge badge-warning">Skill Category: <?= htmlspecialchars(ucwords($student['skill_category'])) ?></span>
                <?php endif; ?>
                <div class="d-flex gap-1 flex-wrap" style="margin-top:0.5rem;">
                    <span class="badge badge-<?= $student['verification_status'] === 'approved' ? 'success' : ($student['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                        <?= ucfirst($student['verification_status']) ?>
                    </span>
                    <span class="badge badge-info">Current CGPA: <?= $averageCgpa ? number_format($averageCgpa, 2) : number_format($student['cgpa'], 2) ?></span>
                    <?php if ($averageCgpa): ?>
                        <span class="badge badge-success">Overall CGPA: <?= number_format($averageCgpa, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($student['batch_year']): ?>
                        <span class="badge badge-secondary">Batch: <?= $student['batch_year'] ?></span>
                    <?php endif; ?>
                    <?php if ($student['backlogs'] > 0): ?>
                        <span class="badge badge-danger">Backlogs: <?= $student['backlogs'] ?></span>
                    <?php endif; ?>
                    <span class="badge badge-secondary">Semester: <?= $student['current_semester'] ?? 1 ?></span>
                    <span class="badge badge-secondary"><?= $totalApps ?> Application<?= $totalApps !== 1 ? 's' : '' ?></span>
                    <?php if ($selected > 0): ?>
                        <span class="badge badge-success">🎉 <?= $selected ?> Selected</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Completion Bar -->
    <?php
    $completion_fields = [
        'name' => $student['name'],
        'email' => $student['email'],
        'phone' => $student['phone'] ?? '',
        'dob' => $student['dob'] ?? '',
        'address' => $student['address'] ?? '',
        'father_name' => $student['father_name'] ?? '',
        'mother_name' => $student['mother_name'] ?? '',
        'father_phone' => $student['father_phone'] ?? '',
        'mother_phone' => $student['mother_phone'] ?? '',
        'batch_year' => $student['batch_year'] ?? '',
        'tenth_percentage' => $student['tenth_percentage'] ?? '',
        'twelfth_percentage' => $student['twelfth_percentage'] ?? '',
        'school_name' => $student['school_name'] ?? '',
        'github_link' => $student['github_link'] ?? '',
        'linkedin_link' => $student['linkedin_link'] ?? '',
        'skill_category' => $student['skill_category'] ?? '',
        'resume' => $student['resume'] ?? '',
        'profile_pic' => $student['profile_pic'] ?? '',
        'cgpa_sem1' => $student['cgpa_sem1'] ?? '',
        'hackerrank_score' => $student['hackerrank_score'] ?? '',
    ];
    $filled = count(array_filter($completion_fields, fn($v) => $v !== '' && $v !== null));
    $total = count($completion_fields);
    $pct = round(($filled / $total) * 100);
    $skills_done = count($skills) > 0;
    $internships_done = count($internships) > 0;
    if ($skills_done) { $pct = min(100, $pct + 5); }
    if ($internships_done) { $pct = min(100, $pct + 5); }
    $bar_color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
    ?>
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">📋 Profile Completion</div>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="background:#e5e7eb;border-radius:20px;height:18px;overflow:hidden;">
                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $bar_color ?>;border-radius:20px;transition:width 0.5s ease;"></div>
                </div>
                <p style="margin-top:0.4rem;font-size:0.85rem;color:var(--gray);">
                    <?= $filled ?>/<?= $total ?> fields filled
                    <?php if ($skills_done): ?> &bull; ✅ Skills added<?php endif; ?>
                    <?php if ($internships_done): ?> &bull; ✅ Internship added<?php endif; ?>
                </p>
            </div>
            <div style="font-size:2rem;font-weight:900;color:<?= $bar_color ?>;"><?= $pct ?>%</div>
        </div>
        <?php if ($pct < 100): ?>
        <div style="margin-top:0.75rem;font-size:0.85rem;color:#6b7280;">
            💡 <strong>To improve:</strong>
            <?php
            $missing = [];
            if (empty($student['phone'])) $missing[] = 'Phone';
            if (empty($student['profile_pic'])) $missing[] = 'Profile Photo';
            if (empty($student['resume'])) $missing[] = 'Resume';
            if (empty($student['github_link'])) $missing[] = 'GitHub';
            if (empty($student['linkedin_link'])) $missing[] = 'LinkedIn';
            if (empty($student['skill_category'])) $missing[] = 'Skill Category';
            if (!$skills_done) $missing[] = 'Add a Skill';
            if (!$internships_done) $missing[] = 'Add an Internship';
            echo count($missing) > 0 ? implode(', ', array_slice($missing, 0, 4)) . (count($missing) > 4 ? '...' : '') : 'Almost done!';
            ?>
        </div>
        <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- Edit Personal Info -->
        <div class="card">
            <div class="card-header">✏️ Edit Information</div>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Skill Category</label>
                    <select name="skill_category" class="form-control" required>
                        <option value="">-- Select Skill Category --</option>
                        <option value="elite category" <?= ($student['skill_category'] == 'elite category') ? 'selected' : '' ?>>Elite Category</option>
                        <option value="general" <?= ($student['skill_category'] == 'general') ? 'selected' : '' ?>>General</option>
                        <option value="service now" <?= ($student['skill_category'] == 'service now') ? 'selected' : '' ?>>Service Now</option>
                        <option value="salesforce" <?= ($student['skill_category'] == 'salesforce') ? 'selected' : '' ?>>Salesforce</option>
                        <option value="SAP" <?= ($student['skill_category'] == 'SAP') ? 'selected' : '' ?>>SAP</option>
                        <option value="Cybersecurity" <?= ($student['skill_category'] == 'Cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" placeholder="e.g. 9876543210">
                </div>
                <div class="form-group">
                    <label>Batch Year</label>
                    <input type="number" name="batch_year" class="form-control" value="<?= htmlspecialchars($student['batch_year'] ?? '') ?>" placeholder="e.g. 2021" min="2000" max="2030">
                </div>
                <div class="form-group">
                    <label>Number of Backlogs</label>
                    <input type="number" name="backlogs" class="form-control" value="<?= htmlspecialchars($student['backlogs'] ?? 0) ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Cleared Backlogs</label>
                    <input type="number" name="cleared_backlogs" class="form-control" value="<?= htmlspecialchars($student['cleared_backlogs'] ?? 0) ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($student['dob'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Father's Name</label>
                    <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control" value="<?= htmlspecialchars($student['mother_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Mentor Name</label>
                    <input type="text" name="mentor_name" class="form-control" value="<?= htmlspecialchars($student['mentor_name'] ?? '') ?>" placeholder="Enter your mentor's name">
                </div>
                <div class="form-group">
                    <label>Class Advisor Name</label>
                    <input type="text" name="class_advisor_name" class="form-control" value="<?= htmlspecialchars($student['class_advisor_name'] ?? '') ?>" placeholder="Enter your class advisor's name">
                </div>
                <div class="form-group">
                    <label>Father's Phone</label>
                    <input type="tel" name="father_phone" class="form-control" value="<?= htmlspecialchars($student['father_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Mother's Phone</label>
                    <input type="tel" name="mother_phone" class="form-control" value="<?= htmlspecialchars($student['mother_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Current Semester</label>
                    <select name="current_semester" class="form-control" required>
                        <option value="">-- Select Semester --</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= ($student['current_semester'] ?? 1) == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>10th Percentage</label>
                    <input type="number" step="0.01" name="tenth_percentage" class="form-control" value="<?= htmlspecialchars($student['tenth_percentage'] ?? '') ?>" placeholder="e.g. 95.50">
                </div>
                <div class="form-group">
                    <label>12th Percentage</label>
                    <input type="number" step="0.01" name="twelfth_percentage" class="form-control" value="<?= htmlspecialchars($student['twelfth_percentage'] ?? '') ?>" placeholder="e.g. 92.30">
                </div>
                <div class="form-group">
                    <label>School Name</label>
                    <input type="text" name="school_name" class="form-control" value="<?= htmlspecialchars($student['school_name'] ?? '') ?>" placeholder="e.g. ABC High School">
                </div>
                <div class="form-group">
                    <label>Mother's Occupation</label>
                    <input type="text" name="mother_occupation" class="form-control" value="<?= htmlspecialchars($student['mother_occupation'] ?? '') ?>" placeholder="e.g. Teacher">
                </div>
                <div class="form-group">
                    <label>Father's Occupation</label>
                    <input type="text" name="father_occupation" class="form-control" value="<?= htmlspecialchars($student['father_occupation'] ?? '') ?>" placeholder="e.g. Engineer">
                </div>
                <div class="form-group">
                    <label>GitHub Link</label>
                    <input type="url" name="github_link" class="form-control" value="<?= htmlspecialchars($student['github_link'] ?? '') ?>" placeholder="https://github.com/username">
                </div>
                <div class="form-group">
                    <label>LinkedIn Link</label>
                    <input type="url" name="linkedin_link" class="form-control" value="<?= htmlspecialchars($student['linkedin_link'] ?? '') ?>" placeholder="https://linkedin.com/in/username">
                </div>
                <div class="form-group">
                    <label>HackerRank Score</label>
                    <input type="number" name="hackerrank_score" class="form-control" value="<?= htmlspecialchars($student['hackerrank_score'] ?? '') ?>" placeholder="e.g. 1500">
                </div>
                <div class="form-group">
                    <label>Semester CGPAs</label>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.5rem;">
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <input type="number" step="0.01" name="cgpa_sem<?= $i ?>" class="form-control" value="<?= htmlspecialchars($student['cgpa_sem' . $i] ?? '') ?>" placeholder="Sem <?= $i ?> CGPA">
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($student['dept_name']) ?>" disabled>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Right column -->
        <div>
            <!-- Change Password -->
            <div class="card">
                <div class="card-header">🔒 Change Password</div>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                </form>
            </div>

            <!-- Resume -->
            <div class="card">
                <div class="card-header">📄 Resume</div>
                <?php if ($student['resume']): ?>
                    <div class="d-flex gap-1 align-center flex-wrap" style="margin-bottom:1rem;">
                        <a href="/campuss/uploads/resumes/<?= htmlspecialchars($student['resume']) ?>" target="_blank" class="btn btn-info btn-sm">📥 View Current Resume</a>
                        <a href="?delete_resume=1" class="btn btn-danger btn-sm" onclick="return confirm('Remove your resume?')">🗑 Remove</a>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No resume uploaded yet.</p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label><?= $student['resume'] ? 'Upload New Resume' : 'Upload Resume' ?> (PDF, max 2MB)</label>
                        <input type="file" name="resume" class="form-control" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                </form>
            </div>

            <!-- Profile Picture -->
            <div class="card">
                <div class="card-header">📸 Profile Picture</div>
                <?php if ($student['profile_pic']): ?>
                    <div class="d-flex gap-1 align-center flex-wrap" style="margin-bottom:1rem;">
                        <img src="/campuss/uploads/profiles/<?= htmlspecialchars($student['profile_pic']) ?>" alt="Profile Picture" style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:2px solid var(--primary);">
                        <a href="?delete_profile_pic=1" class="btn btn-danger btn-sm" onclick="return confirm('Remove your profile picture?')">🗑 Remove</a>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No profile picture uploaded yet.</p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label><?= $student['profile_pic'] ? 'Upload New Picture' : 'Upload Profile Picture' ?> (JPG, PNG, GIF, max 2MB)</label>
                        <input type="file" name="profile_pic" class="form-control" accept=".jpg,.jpeg,.png,.gif" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                </form>
            </div>

            <!-- Internships -->
            <div class="card">
                <div class="card-header">💼 Internships & Projects</div>
                <?php if (count($internships) > 0): ?>
                    <div style="margin-bottom:1rem;">
                        <?php foreach ($internships as $internship): ?>
                            <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.5rem;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem;">
                                    <div>
                                        <strong><?= htmlspecialchars($internship['company_name']) ?></strong> - <?= htmlspecialchars($internship['role']) ?>
                                        <br><small style="color:var(--gray);"><?= htmlspecialchars($internship['duration']) ?><?php if ($internship['stipend']): ?> | ₹<?= number_format($internship['stipend']) ?>/month<?php endif; ?></small>
                                    </div>
                                    <a href="?delete_internship=<?= $internship['internship_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this internship?')">🗑</a>
                                </div>
                                <?php if ($internship['project_title']): ?>
                                    <div style="margin-top:0.5rem;">
                                        <strong>Project:</strong> <?= htmlspecialchars($internship['project_title']) ?>
                                        <?php if ($internship['project_description']): ?><br><small><?= htmlspecialchars($internship['project_description']) ?></small><?php endif; ?>
                                        <?php if ($internship['technologies_used']): ?><br><small><strong>Technologies:</strong> <?= htmlspecialchars($internship['technologies_used']) ?></small><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($internship['start_date'] && $internship['end_date']): ?>
                                    <div style="margin-top:0.5rem;">
                                        <small style="color:var(--gray);"><?= date('M Y', strtotime($internship['start_date'])) ?> - <?= date('M Y', strtotime($internship['end_date'])) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No internships added yet.</p>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Company Name *</label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role/Position *</label>
                        <input type="text" name="role" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Duration *</label>
                        <input type="text" name="duration" class="form-control" placeholder="e.g. 3 months, Summer 2023" required>
                    </div>
                    <div class="form-group">
                        <label>Stipend (₹/month)</label>
                        <input type="number" name="stipend" class="form-control" placeholder="e.g. 15000">
                    </div>
                    <div class="form-group">
                        <label>Project Title</label>
                        <input type="text" name="project_title" class="form-control" placeholder="e.g. E-commerce Website">
                    </div>
                    <div class="form-group">
                        <label>Project Description</label>
                        <textarea name="project_description" class="form-control" rows="2" placeholder="Brief description of the project"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Technologies Used</label>
                        <input type="text" name="technologies_used" class="form-control" placeholder="e.g. React, Node.js, MongoDB">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <button type="submit" name="add_internship" class="btn btn-primary btn-sm">Add Internship</button>
                </form>
            </div>

            <!-- Projects -->
            <div class="card">
                <div class="card-header">🚀 Personal Projects</div>
                <?php if (count($projects) > 0): ?>
                    <div style="margin-bottom:1rem;">
                        <?php foreach ($projects as $project): ?>
                            <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.5rem;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:0.5rem;">
                                    <div>
                                        <strong><?= htmlspecialchars($project['project_title']) ?></strong>
                                        <?php if ($project['project_link']): ?><br><small><a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank">🔗 View Project</a></small><?php endif; ?>
                                    </div>
                                    <a href="?delete_project=<?= $project['project_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this project?')">🗑</a>
                                </div>
                                <?php if ($project['project_description']): ?>
                                    <div style="margin-top:0.5rem;">
                                        <small><?= htmlspecialchars($project['project_description']) ?></small>
                                    </div>
                                <?php endif; ?>
                                <?php if ($project['technologies_used']): ?>
                                    <div style="margin-top:0.5rem;">
                                        <small><strong>Technologies:</strong> <?= htmlspecialchars($project['technologies_used']) ?></small>
                                    </div>
                                <?php endif; ?>
                                <?php if ($project['start_date'] && $project['end_date']): ?>
                                    <div style="margin-top:0.5rem;">
                                        <small style="color:var(--gray);"><?= date('M Y', strtotime($project['start_date'])) ?> - <?= date('M Y', strtotime($project['end_date'])) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No projects added yet.</p>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Project Title *</label>
                        <input type="text" name="project_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Project Description</label>
                        <textarea name="project_description" class="form-control" rows="2" placeholder="Brief description of the project"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Technologies Used</label>
                        <input type="text" name="technologies_used" class="form-control" placeholder="e.g. Python, Django, PostgreSQL">
                    </div>
                    <div class="form-group">
                        <label>Project Link</label>
                        <input type="url" name="project_link" class="form-control" placeholder="https://github.com/username/project">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="project_start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="project_end_date" class="form-control">
                        </div>
                    </div>
                    <button type="submit" name="add_project" class="btn btn-primary btn-sm">Add Project</button>
                </form>
            </div>

            <!-- Technical Skills -->
            <div class="card">
                <div class="card-header">🛠️ Technical Skills</div>
                <?php
                $technicalSkills = array_filter($skills, function($skill) {
                    return $skill['skill_type'] === 'technical';
                });
                ?>
                <?php if (count($technicalSkills) > 0): ?>
                    <div style="margin-bottom:1rem;">
                        <?php foreach ($technicalSkills as $skill): ?>
                            <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <strong><?= htmlspecialchars($skill['skill_name']) ?></strong>
                                    <br><small style="color:var(--gray);"><?= ucfirst($skill['proficiency_level']) ?></small>
                                </div>
                                <a href="?delete_skill=<?= $skill['skill_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this skill?')">🗑</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No technical skills added yet.</p>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="skill_type" value="technical">
                    <div class="form-group">
                        <label>Technical Skill Name *</label>
                        <input type="text" name="skill_name" class="form-control" placeholder="e.g. JavaScript, Python, React" required>
                    </div>
                    <div class="form-group">
                        <label>Proficiency Level *</label>
                        <select name="proficiency_level" class="form-control" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <button type="submit" name="add_skill" class="btn btn-primary btn-sm">Add Technical Skill</button>
                </form>
            </div>

            <!-- Soft Skills -->
            <div class="card">
                <div class="card-header">🤝 Soft Skills</div>
                <?php
                $softSkills = array_filter($skills, function($skill) {
                    return $skill['skill_type'] === 'soft';
                });
                ?>
                <?php if (count($softSkills) > 0): ?>
                    <div style="margin-bottom:1rem;">
                        <?php foreach ($softSkills as $skill): ?>
                            <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <strong><?= htmlspecialchars($skill['skill_name']) ?></strong>
                                    <br><small style="color:var(--gray);"><?= ucfirst($skill['proficiency_level']) ?></small>
                                </div>
                                <a href="?delete_skill=<?= $skill['skill_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this skill?')">🗑</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No soft skills added yet.</p>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="skill_type" value="soft">
                    <div class="form-group">
                        <label>Soft Skill Name *</label>
                        <input type="text" name="skill_name" class="form-control" placeholder="e.g. Communication, Leadership, Teamwork" required>
                    </div>
                    <div class="form-group">
                        <label>Proficiency Level *</label>
                        <select name="proficiency_level" class="form-control" required>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <button type="submit" name="add_skill" class="btn btn-primary btn-sm">Add Soft Skill</button>
                </form>
            </div>

            <!-- Area of Interest -->
            <div class="card">
                <div class="card-header">🎯 Area of Interest</div>
                <?php if (count($interests) > 0): ?>
                    <div style="margin-bottom:1rem;">
                        <?php foreach ($interests as $interest): ?>
                            <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <strong><?= htmlspecialchars($interest['interest_name']) ?></strong>
                                    <?php if ($interest['description']): ?><br><small style="color:var(--gray);"><?= htmlspecialchars($interest['description']) ?></small><?php endif; ?>
                                </div>
                                <a href="?delete_interest=<?= $interest['interest_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this interest?')">🗑</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:1rem;">No areas of interest added yet.</p>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Area of Interest *</label>
                        <input type="text" name="interest_name" class="form-control" placeholder="e.g. Machine Learning, Web Development" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="interest_description" class="form-control" rows="2" placeholder="Brief description of your interest in this area"></textarea>
                    </div>
                    <button type="submit" name="add_interest" class="btn btn-primary btn-sm">Add Interest</button>
                </form>
            </div>

<?php include '../includes/footer.php'; ?>

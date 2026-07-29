<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: ../login.php'); exit; }
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
        project_link VARCHAR(500) DEFAULT NULL,
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
        skill_type ENUM('technical', 'soft') NOT NULL,
        proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
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

$dept_id = $_SESSION['dept_id'];
$student_id = (int)($_GET['id'] ?? 0);

// Fetch student - verify belongs to staff's department
$stmt = $conn->prepare("SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.dept_id = d.dept_id WHERE s.student_id = ? AND s.dept_id = ?");
$stmt->execute([$student_id, $dept_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: students.php');
    exit;
}

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

// Fetch applications
$stmt = $conn->prepare("
    SELECT a.*, c.company_name, d.drive_date
    FROM applications a
    JOIN drives d ON a.drive_id = d.drive_id
    JOIN companies c ON d.company_id = c.company_id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$student_id]);
$applications = $stmt->fetchAll();

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

// Count stats
$totalApps = count($applications);
$selected = 0;
foreach ($applications as $a) {
    if ($a['final_status'] === 'selected') {
        $selected++;
    }
}

// Calculate profile completion percentage
$completionFields = [
    'name' => !empty($student['name']),
    'email' => !empty($student['email']),
    'phone' => !empty($student['phone']),
    'batch_year' => !empty($student['batch_year']),
    'current_semester' => !empty($student['current_semester']),
    'dob' => !empty($student['dob']),
    'address' => !empty($student['address']),
    'father_name' => !empty($student['father_name']),
    'mother_name' => !empty($student['mother_name']),
    'father_phone' => !empty($student['father_phone']),
    'mother_phone' => !empty($student['mother_phone']),
    'tenth_percentage' => !empty($student['tenth_percentage']),
    'twelfth_percentage' => !empty($student['twelfth_percentage']),
    'school_name' => !empty($student['school_name']),
    'mother_occupation' => !empty($student['mother_occupation']),
    'father_occupation' => !empty($student['father_occupation']),
    'github_link' => !empty($student['github_link']),
    'linkedin_link' => !empty($student['linkedin_link']),
    'hackerrank_score' => !empty($student['hackerrank_score']),
    'skill_category' => !empty($student['skill_category']),
    'resume' => !empty($student['resume']),
    'profile_pic' => !empty($student['profile_pic']),
];

$cgpaCompleted = 0;
for ($i = 1; $i <= 8; $i++) {
    if (!empty($student['cgpa_sem' . $i])) {
        $cgpaCompleted++;
    }
}
$completionFields['cgpa_semesters'] = $cgpaCompleted > 0; // At least one CGPA filled

$completionFields['internships'] = count($internships) > 0;
$completionFields['projects'] = count($projects) > 0;
$completionFields['skills'] = count($skills) > 0;
$completionFields['interests'] = count($interests) > 0;

$completedCount = 0;
foreach ($completionFields as $field => $completed) {
    if ($completed) $completedCount++;
}

$profileCompletion = round(($completedCount / count($completionFields)) * 100);

$pageTitle = 'Student Profile';
$showNav = true;
$currentPage = 'students';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <a href="students.php" class="btn btn-secondary" style="margin-bottom:1rem;">← Back to Students</a>
        <div style="display:flex;gap:1rem;align-items:center;margin-bottom:1rem;">
            <h2>👤 <?= htmlspecialchars($student['name']) ?> - Full Profile</h2>
            <a href="download_student_pdf.php?id=<?= $student_id ?>" class="btn btn-primary" target="_blank">📄 Download Profile (PDF)</a>
        </div>
    </div>

    <!-- Student Header -->
    <div class="card profile-hero">
        <div class="profile-top">
            <div class="profile-avatar" style="width:120px;height:120px;">
                <?php if ($student['profile_pic']): ?>
                    <img src="/campuss/uploads/profiles/<?= htmlspecialchars($student['profile_pic']) ?>" alt="Profile Picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= strtoupper(substr($student['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($student['name']) ?></h3>
                <p style="color:var(--gray);margin:0.25rem 0;"><?= htmlspecialchars($student['reg_no']) ?> &bull; <?= htmlspecialchars($student['dept_name']) ?></p>
                <p style="color:#475569;margin:0.25rem 0;font-size:.95rem;line-height:1.4;">
                    Mentor: <strong><?= htmlspecialchars($student['mentor_name'] ?? $student['mentor'] ?? 'N/A') ?></strong>
                    &nbsp;|&nbsp;
                    Class Advisor: <strong><?= htmlspecialchars($student['class_advisor_name'] ?? $student['class_advisor'] ?? 'N/A') ?></strong>
                </p>
                <?php if (!empty($student['skill_category'])): ?>
                    <span class="badge badge-warning">Skill Category: <?= htmlspecialchars(ucwords($student['skill_category'])) ?></span>
                <?php endif; ?>
                <div class="d-flex gap-1 flex-wrap" style="margin-top:0.5rem;">
                    <span class="badge badge-<?= $profileCompletion >= 80 ? 'success' : ($profileCompletion >= 50 ? 'warning' : 'danger') ?>">
                        Profile: <?= $profileCompletion ?>% Complete
                    </span>
                    <span class="badge badge-<?= $student['verification_status'] === 'approved' ? 'success' : ($student['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                        <?= ucfirst($student['verification_status']) ?>
                    </span>
                    <span class="badge badge-info">Current CGPA: <?= $averageCgpa ? number_format($averageCgpa, 2) : number_format($student['cgpa'], 2) ?></span>
                    <?php if ($student['current_semester'] && !empty($student['cgpa_sem' . $student['current_semester']])): ?>
                        <span class="badge badge-primary">Sem <?= $student['current_semester'] ?> CGPA: <?= number_format($student['cgpa_sem' . $student['current_semester']], 2) ?></span>
                    <?php endif; ?>
                    <?php if ($averageCgpa): ?>
                        <span class="badge badge-success">Overall CGPA: <?= number_format($averageCgpa, 2) ?></span>
                    <?php endif; ?>
                    <?php if ($student['batch_year']): ?>
                        <span class="badge badge-secondary">Batch: <?= $student['batch_year'] . '-' . ($student['batch_year'] + 4) ?></span>
                    <?php endif; ?>
                    <?php if ($student['backlogs'] > 0): ?>
                        <span class="badge badge-danger">Backlogs: <?= $student['backlogs'] ?></span>
                    <?php else: ?>
                        <span class="badge badge-success">No Backlogs</span>
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

    <!-- Academic Information -->
    <div class="card">
        <div class="card-header">📚 Academic Information</div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;">
            <div>
                <strong>Registration Number:</strong><br>
                <?= htmlspecialchars($student['reg_no']) ?>
            </div>
            <div>
                <strong>Department:</strong><br>
                <?= htmlspecialchars($student['dept_name']) ?>
            </div>
            <div>
                <strong>CGPA:</strong><br>
                <?= number_format($student['cgpa'], 2) ?>
                <?php if ($averageCgpa): ?><br><small>Average: <?= number_format($averageCgpa, 2) ?></small><?php endif; ?>
            </div>
            <div>
                <strong>10th Percentage:</strong><br>
                <?= $student['tenth_percentage'] ? number_format($student['tenth_percentage'], 2) . '%' : 'N/A' ?>
            </div>
            <div>
                <strong>12th Percentage:</strong><br>
                <?= $student['twelfth_percentage'] ? number_format($student['twelfth_percentage'], 2) . '%' : 'N/A' ?>
            </div>
            <div>
                <strong>School Name:</strong><br>
                <?= htmlspecialchars($student['school_name'] ?? 'N/A') ?>
            </div>
            <div>
                <strong>Batch Year:</strong><br>
                <?= $student['batch_year'] ? $student['batch_year'] . ' - ' . ($student['batch_year'] + 4) : 'N/A' ?>
            </div>
            <div>
                <strong>Current Semester:</strong><br>
                Semester <?= $student['current_semester'] ?? 1 ?>
            </div>
            <div>
                <strong>Backlogs:</strong><br>
                Current: <?= $student['backlogs'] ?>, Cleared: <?= $student['cleared_backlogs'] ?>
            </div>
            <div>
                <strong>Verification Status:</strong><br>
                <span class="badge badge-<?= $student['verification_status'] === 'approved' ? 'success' : ($student['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                    <?= ucfirst($student['verification_status']) ?>
                </span>
            </div>
            <div>
                <strong>Registered On:</strong><br>
                <?= date('d M Y h:i A', strtotime($student['created_at'])) ?>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="card">
        <div class="card-header">📞 Contact Information</div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;">
            <div>
                <strong>Email:</strong><br>
                <?= htmlspecialchars($student['email']) ?>
            </div>
            <div>
                <strong>Phone:</strong><br>
                <?= htmlspecialchars($student['phone'] ?? 'N/A') ?>
            </div>
            <div>
                <strong>Date of Birth:</strong><br>
                <?= $student['dob'] ? date('d M Y', strtotime($student['dob'])) : 'N/A' ?>
            </div>
            <div>
                <strong>Address:</strong><br>
                <?= htmlspecialchars($student['address'] ?? 'N/A') ?>
            </div>
        </div>
    </div>

    <!-- Parent Information -->
    <div class="card">
        <div class="card-header">👨‍👩‍👧 Parent Information</div>
        <div style="padding:1.5rem;">
            <!-- Father's Information -->
            <div style="margin-bottom:1.5rem;padding:1rem;border:1px solid var(--border);border-radius:8px;background:#f8f9fa;">
                <h4 style="margin:0 0 1rem 0;color:#333;">👨 Father's Details</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                    <div><strong>Name:</strong><br><?= htmlspecialchars($student['father_name'] ?? 'N/A') ?></div>
                    <div><strong>Occupation:</strong><br><?= htmlspecialchars($student['father_occupation'] ?? 'N/A') ?></div>
                    <div><strong>Phone:</strong><br><?= htmlspecialchars($student['father_phone'] ?? 'N/A') ?></div>
                </div>
            </div>
            <!-- Mother's Information -->
            <div style="padding:1rem;border:1px solid var(--border);border-radius:8px;background:#f8f9fa;">
                <h4 style="margin:0 0 1rem 0;color:#333;">👩 Mother's Details</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                    <div><strong>Name:</strong><br><?= htmlspecialchars($student['mother_name'] ?? 'N/A') ?></div>
                    <div><strong>Occupation:</strong><br><?= htmlspecialchars($student['mother_occupation'] ?? 'N/A') ?></div>
                    <div><strong>Phone:</strong><br><?= htmlspecialchars($student['mother_phone'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Details -->
    <div class="card">
        <div class="card-header">🔗 Additional Information</div>
        <div style="padding:1.5rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;">
            <div>
                <strong>GitHub Profile:</strong><br>
                <?php if ($student['github_link']): ?>
                    <a href="<?= htmlspecialchars($student['github_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View GitHub</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
            <div>
                <strong>LinkedIn Profile:</strong><br>
                <?php if ($student['linkedin_link']): ?>
                    <a href="<?= htmlspecialchars($student['linkedin_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View LinkedIn</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
            <div>
                <strong>HackerRank Score:</strong><br>
                <?= $student['hackerrank_score'] ? number_format($student['hackerrank_score']) : 'N/A' ?>
            </div>
        </div>

        <!-- Semester CGPA Table -->
        <div style="margin-top:1.5rem;">
            <h4 style="margin-bottom:1rem;color:#333;">📊 Semester-wise CGPA</h4>
            <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th style="border:1px solid var(--border);padding:0.5rem;text-align:center;">Semester</th>
                            <th style="border:1px solid var(--border);padding:0.5rem;text-align:center;">CGPA</th>
                            <th style="border:1px solid var(--border);padding:0.5rem;text-align:center;">Semester</th>
                            <th style="border:1px solid var(--border);padding:0.5rem;text-align:center;">CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i += 2): ?>
                        <tr>
                            <td style="border:1px solid var(--border);padding:0.5rem;text-align:center;font-weight:bold;">Sem <?= $i ?></td>
                            <td style="border:1px solid var(--border);padding:0.5rem;text-align:center;">
                                <?= !empty($student['cgpa_sem' . $i]) ? number_format($student['cgpa_sem' . $i], 2) : '-' ?>
                            </td>
                            <td style="border:1px solid var(--border);padding:0.5rem;text-align:center;font-weight:bold;">Sem <?= $i + 1 ?></td>
                            <td style="border:1px solid var(--border);padding:0.5rem;text-align:center;">
                                <?= !empty($student['cgpa_sem' . ($i + 1)]) ? number_format($student['cgpa_sem' . ($i + 1)], 2) : '-' ?>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($averageCgpa): ?>
                <div style="margin-top:1rem;text-align:center;">
                    <strong>Overall Average CGPA: <span style="color:#007bff;font-size:1.1em;"><?= number_format($averageCgpa, 2) ?></span></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resume -->
    <?php if ($student['resume']): ?>
    <div class="card">
        <div class="card-header">📄 Resume</div>
        <div style="padding:1.5rem;">
            <a href="/campuss/uploads/resumes/<?= htmlspecialchars($student['resume']) ?>" target="_blank" class="btn btn-info">📥 Download Resume</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Internships & Projects -->
    <?php if (count($internships) > 0): ?>
    <div class="card">
        <div class="card-header">💼 Internships & Projects (<?= count($internships) ?>)</div>
        <div style="padding:1.5rem;">
            <?php foreach ($internships as $internship): ?>
                <div style="border:1px solid var(--border);border-radius:8px;padding:1.5rem;margin-bottom:1rem;background:#f8f9fa;">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:1rem;">
                        <div>
                            <h4 style="margin:0 0 0.5rem 0;color:#333;"><?= htmlspecialchars($internship['company_name']) ?></h4>
                            <p style="margin:0;color:#666;"><strong>Role:</strong> <?= htmlspecialchars($internship['role']) ?> | <strong>Duration:</strong> <?= htmlspecialchars($internship['duration']) ?><?php if ($internship['stipend']): ?> | <strong>Stipend:</strong> ₹<?= number_format($internship['stipend']) ?>/month<?php endif; ?></p>
                            <?php if ($internship['start_date'] && $internship['end_date']): ?>
                                <p style="margin:0.5rem 0 0 0;color:#666;"><strong>Period:</strong> <?= date('M Y', strtotime($internship['start_date'])) ?> - <?= date('M Y', strtotime($internship['end_date'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($internship['project_title']): ?>
                        <div style="margin-top:1rem;">
                            <h5 style="margin:0 0 0.5rem 0;color:#333;">Project Details</h5>
                            <p style="margin:0 0 0.5rem 0;"><strong>Title:</strong> <?= htmlspecialchars($internship['project_title']) ?></p>
                            <?php if ($internship['project_description']): ?>
                                <p style="margin:0 0 0.5rem 0;"><strong>Description:</strong> <?= htmlspecialchars($internship['project_description']) ?></p>
                            <?php endif; ?>
                            <?php if ($internship['technologies_used']): ?>
                                <p style="margin:0 0 0.5rem 0;"><strong>Technologies:</strong> <?= htmlspecialchars($internship['technologies_used']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Personal Projects -->
    <?php if (count($projects) > 0): ?>
    <div class="card">
        <div class="card-header">🚀 Personal Projects (<?= count($projects) ?>)</div>
        <div style="padding:1.5rem;">
            <?php foreach ($projects as $project): ?>
                <div style="border:1px solid var(--border);border-radius:8px;padding:1.5rem;margin-bottom:1rem;background:#f8f9fa;">
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:1rem;">
                        <div>
                            <h4 style="margin:0 0 0.5rem 0;color:#333;"><?= htmlspecialchars($project['project_title']) ?></h4>
                            <?php if ($project['start_date'] && $project['end_date']): ?>
                                <p style="margin:0;color:#666;"><strong>Period:</strong> <?= date('M Y', strtotime($project['start_date'])) ?> - <?= date('M Y', strtotime($project['end_date'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($project['project_description']): ?>
                        <div style="margin-bottom:1rem;">
                            <strong>Description:</strong><br>
                            <p style="margin:0.5rem 0;color:#555;"><?= htmlspecialchars($project['project_description']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($project['technologies_used']): ?>
                        <div style="margin-bottom:1rem;">
                            <strong>Technologies Used:</strong><br>
                            <p style="margin:0.5rem 0;color:#555;"><?= htmlspecialchars($project['technologies_used']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($project['project_link']): ?>
                        <div>
                            <strong>Project Link:</strong><br>
                            <a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="margin-top:0.5rem;">View Project</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Technical Skills -->
    <?php
    $technicalSkills = array_filter($skills, function($skill) {
        return $skill['skill_type'] === 'technical';
    });
    ?>
    <?php if (count($technicalSkills) > 0): ?>
    <div class="card">
        <div class="card-header">🛠️ Technical Skills (<?= count($technicalSkills) ?>)</div>
        <div style="padding:1.5rem;">
            <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                <?php foreach ($technicalSkills as $skill): ?>
                    <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;background:#f8f9fa;min-width:200px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong style="color:#333;"><?= htmlspecialchars($skill['skill_name']) ?></strong>
                                <br><small style="color:#666;"><?= ucfirst($skill['proficiency_level']) ?> Level</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Soft Skills -->
    <?php
    $softSkills = array_filter($skills, function($skill) {
        return $skill['skill_type'] === 'soft';
    });
    ?>
    <?php if (count($softSkills) > 0): ?>
    <div class="card">
        <div class="card-header">🤝 Soft Skills (<?= count($softSkills) ?>)</div>
        <div style="padding:1.5rem;">
            <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                <?php foreach ($softSkills as $skill): ?>
                    <div style="border:1px solid var(--border);border-radius:8px;padding:1rem;background:#f8f9fa;min-width:200px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong style="color:#333;"><?= htmlspecialchars($skill['skill_name']) ?></strong>
                                <br><small style="color:#666;"><?= ucfirst($skill['proficiency_level']) ?> Level</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Areas of Interest -->
    <?php if (count($interests) > 0): ?>
    <div class="card">
        <div class="card-header">🎯 Areas of Interest (<?= count($interests) ?>)</div>
        <div style="padding:1.5rem;">
            <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                <?php foreach ($interests as $interest): ?>
                    <div style="border:1px solid var(--border);border-radius:8px;padding:1.5rem;background:#f8f9fa;min-width:250px;">
                        <h4 style="margin:0 0 0.5rem 0;color:#333;"><?= htmlspecialchars($interest['interest_name']) ?></h4>
                        <?php if ($interest['description']): ?>
                            <p style="margin:0;color:#555;"><?= htmlspecialchars($interest['description']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Applications -->
    <div class="card">
        <div class="card-header">💼 Applications (<?= $totalApps ?>)</div>
        <?php if ($totalApps > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Drive Date</th>
                        <th>Applied On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['company_name']) ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($app['drive_date']))) ?></td>
                        <td><?= date('d M Y', strtotime($app['applied_at'])) ?></td>
                        <td>
                            <span class="badge badge-<?= $app['final_status'] === 'selected' ? 'success' : ($app['final_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($app['final_status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="padding:1.5rem;text-align:center;color:var(--gray);">
            No applications yet.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

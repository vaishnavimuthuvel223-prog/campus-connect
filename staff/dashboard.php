
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$dept_id = $_SESSION['dept_id'];

$stmt = $conn->prepare("SELECT dept_name FROM departments WHERE dept_id = ?");
$stmt->execute([$dept_id]);
$dept_name = $stmt->fetchColumn();

$totalStudents = $conn->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ?");
$totalStudents->execute([$dept_id]);
$totalStudents = $totalStudents->fetchColumn();

$pending = $conn->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ? AND verification_status = 'pending'");
$pending->execute([$dept_id]);
$pending = $pending->fetchColumn();

$approved = $conn->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ? AND verification_status = 'approved'");
$approved->execute([$dept_id]);
$approved = $approved->fetchColumn();

$placed = $conn->prepare("SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN students s ON a.student_id = s.student_id WHERE s.dept_id = ? AND a.final_status = 'selected'");
$placed->execute([$dept_id]);
$placed = $placed->fetchColumn();

$stmt = $conn->prepare("SELECT * FROM students WHERE dept_id = ? AND verification_status = 'pending' ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$dept_id]);
$pendingStudents = $stmt->fetchAll();

try {
    $annStmt = $conn->query("SELECT * FROM announcements WHERE is_active = 1 AND target IN ('all','staff') ORDER BY FIELD(priority,'urgent','important','normal'), created_at DESC LIMIT 5");
    $staffAnnouncements = $annStmt->fetchAll();
} catch (Exception $e) { $staffAnnouncements = []; }

$pageTitle = 'Staff Dashboard';
$showNav = true;
$currentPage = 'dashboard';
include '../includes/header.php';
?>
<style>
body.dark-mode .staff-page { background: var(--dark-bg-secondary, #1e293b); color: var(--dark-text-primary, #e2e8f0); }
body.dark-mode .staff-page .staff-header h1,
body.dark-mode .staff-page .staff-header p,
body.dark-mode .staff-page .staff-stat-label { color: var(--dark-text-secondary, #cbd5e1); }
body.dark-mode .staff-page .staff-card { background: var(--dark-card-bg, #1f2937); border-color: var(--dark-border-color, #334155); }
body.dark-mode .staff-page .staff-stat { background: rgba(15, 23, 42, 0.85); border-color: rgba(100, 116, 139, 0.4); }
</style>

<div class="staff-page">
<div class="staff-container">


<!-- Header -->
<div class="staff-header welcome-drop">
  <h1>Department Dashboard</h1>
  <p><?= htmlspecialchars($dept_name) ?> | Staff Management Console</p>
</div>

<!-- Statistics -->
<div class="staff-stats">
  <div class="staff-stat">
    <div class="staff-stat-num"><?= $totalStudents ?></div>
    <div class="staff-stat-label">Total Students</div>
  </div>
  <div class="staff-stat">
    <div class="staff-stat-num"><?= $pending ?></div>
    <div class="staff-stat-label">Pending Verification</div>
  </div>
  <div class="staff-stat">
    <div class="staff-stat-num"><?= $approved ?></div>
    <div class="staff-stat-label">Approved Students</div>
  </div>
  <div class="staff-stat">
    <div class="staff-stat-num"><?= $placed ?></div>
    <div class="staff-stat-label">Students Placed</div>
  </div>
</div>

<!-- Announcements -->
<?php if (count($staffAnnouncements) > 0): ?>
<div class="staff-card">
  <div class="staff-card-title">Announcements</div>
  <?php foreach ($staffAnnouncements as $i => $ann): ?>
    <?php if ($i < 3): ?>
    <div class="staff-ann-item">
      <?php if ($ann['priority'] === 'urgent'): ?>
        <div class="staff-ann-priority staff-ann-urgent">URGENT</div>
      <?php elseif ($ann['priority'] === 'important'): ?>
        <div class="staff-ann-priority staff-ann-important">IMPORTANT</div>
      <?php endif; ?>
      <div class="staff-ann-title"><?= htmlspecialchars($ann['title']) ?></div>
      <div class="staff-ann-message"><?= nl2br(htmlspecialchars($ann['message'])) ?></div>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Pending Verifications -->
<?php if (count($pendingStudents) > 0): ?>
<div class="staff-card">
  <div class="staff-card-title">Pending Student Verifications</div>
  <div style="overflow-x: auto;">
    <table class="staff-table">
      <thead>
        <tr>
          <th>Reg No</th>
          <th>Student Name</th>
          <th>CGPA</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingStudents as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['reg_no']) ?></strong></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= number_format($s['cgpa'], 2) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td>
            <a href="students.php?action=approve&id=<?= $s['student_id'] ?>" class="staff-btn staff-btn-sm" onclick="return confirm('Approve this student?')">Approve</a>
            <a href="students.php?action=reject&id=<?= $s['student_id'] ?>" class="staff-btn staff-btn-sm" onclick="return confirm('Reject this student?')" style="background: linear-gradient(135deg, #ef4444, #dc2626);">Reject</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <a href="students.php?filter=pending" class="staff-btn">View All Pending</a>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="staff-card">
  <div class="staff-card-title">Quick Actions</div>
  <a href="students.php" class="staff-btn">Manage Students</a>
  <a href="stats.php" class="staff-btn">View Statistics</a>
  <a href="profile.php" class="staff-btn">My Profile</a>
</div>

</div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>

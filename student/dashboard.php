<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.dept_id = d.dept_id WHERE s.student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
$_SESSION['verification_status'] = $student['verification_status'];

$stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$stmt->execute([$student_id]);
$totalApps = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND final_status = 'selected'");
$stmt->execute([$student_id]);
$selected = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM drives dr JOIN drive_departments dd ON dr.drive_id = dd.drive_id WHERE dd.dept_id = ? AND dr.min_cgpa <= ? AND dr.status IN ('upcoming','ongoing') AND dr.drive_id NOT IN (SELECT drive_id FROM applications WHERE student_id = ?)");
$stmt->execute([$student['dept_id'], $student['cgpa'], $student_id]);
$eligibleDrives = $stmt->fetchColumn();

try {
    $annStmt = $conn->query("SELECT * FROM announcements WHERE is_active = 1 AND target IN ('all','students') ORDER BY FIELD(priority,'urgent','important','normal'), created_at DESC LIMIT 5");
    $announcements = $annStmt->fetchAll();
} catch (Exception $e) { $announcements = []; }

$stmt = $conn->prepare("SELECT a.*, c.company_name, c.package, dr.drive_date FROM applications a JOIN drives dr ON a.drive_id = dr.drive_id JOIN companies c ON dr.company_id = c.company_id WHERE a.student_id = ? ORDER BY a.applied_at DESC LIMIT 5");
$stmt->execute([$student_id]);
$recentApps = $stmt->fetchAll();

$pageTitle = 'Student Dashboard';
$showNav = true;
$currentPage = 'dashboard';
include '../includes/header.php';
?>

<div class="stud-page">
  <div class="stud-container">

<!-- Header -->
<div class="stud-header">
  <div class="stud-welcome welcome-drop">
    <h1>Welcome, <?= htmlspecialchars($student['name']) ?></h1>
    <p><?= htmlspecialchars($student['dept_name']) ?> | Reg: <?= htmlspecialchars($student['reg_no']) ?></p>
  </div>
  <div class="stud-status">
    <div class="stud-status-label">Account Status</div>
    <div class="stud-status-badge"><?= ucfirst($student['verification_status']) ?></div>
  </div>
</div>

<!-- Alerts -->
<?php if ($student['verification_status'] === 'pending'): ?>
<div class="stud-alert">
  Account under verification by department staff. You cannot apply for drives yet.
</div>
<?php elseif ($student['verification_status'] === 'rejected'): ?>
<div class="stud-alert">
  Your account has been rejected. Please contact your department staff for more information.
</div>
<?php endif; ?>

<!-- Statistics -->
<div class="stud-stats">
  <div class="stud-stat">
    <div class="stud-stat-num"><?= $totalApps ?></div>
    <div class="stud-stat-label">Total Applications</div>
  </div>
  <div class="stud-stat">
    <div class="stud-stat-num"><?= $selected ?></div>
    <div class="stud-stat-label">Selections</div>
  </div>
  <div class="stud-stat">
    <div class="stud-stat-num"><?= $eligibleDrives ?></div>
    <div class="stud-stat-label">Eligible Drives</div>
  </div>
  <div class="stud-stat">
    <div class="stud-stat-num"><?= number_format($student['cgpa'], 2) ?></div>
    <div class="stud-stat-label">CGPA Score</div>
  </div>
</div>

<!-- Student Details -->
<div class="stud-card">
  <div class="stud-card-title">Your Profile</div>
  <div class="stud-details">
    <div class="stud-detail-item">
      <div class="stud-detail-label">Full Name</div>
      <div class="stud-detail-value"><?= htmlspecialchars($student['name']) ?></div>
    </div>
    <div class="stud-detail-item">
      <div class="stud-detail-label">Registration Number</div>
      <div class="stud-detail-value"><?= htmlspecialchars($student['reg_no']) ?></div>
    </div>
    <div class="stud-detail-item">
      <div class="stud-detail-label">Department</div>
      <div class="stud-detail-value"><?= htmlspecialchars($student['dept_name']) ?></div>
    </div>
    <div class="stud-detail-item">
      <div class="stud-detail-label">CGPA</div>
      <div class="stud-detail-value"><?= number_format($student['cgpa'], 2) ?></div>
    </div>
    <div class="stud-detail-item">
      <div class="stud-detail-label">Email</div>
      <div class="stud-detail-value" style="font-size: 0.95rem; word-break: break-all;"><?= htmlspecialchars($student['email']) ?></div>
    </div>
    <div class="stud-detail-item">
      <div class="stud-detail-label">Resume</div>
      <div class="stud-detail-value">
        <?php if ($student['resume']): ?>
          <a href="/campuss/uploads/resumes/<?= htmlspecialchars($student['resume']) ?>" target="_blank" class="stud-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem; margin: 0;">View Resume</a>
        <?php else: ?>
          <span class="stud-badge stud-badge-warning">Not Uploaded</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <a href="profile.php" class="stud-btn">Edit Profile</a>
</div>

<!-- Announcements -->
<?php if (count($announcements) > 0): ?>
<div class="stud-card">
  <div class="stud-card-title">Announcements</div>
  <?php foreach ($announcements as $i => $ann): ?>
    <?php if ($i < 3): ?>
    <div class="stud-ann-item">
      <?php if ($ann['priority'] === 'urgent'): ?>
        <div class="stud-ann-priority stud-ann-urgent">URGENT</div>
      <?php elseif ($ann['priority'] === 'important'): ?>
        <div class="stud-ann-priority stud-ann-important">IMPORTANT</div>
      <?php endif; ?>
      <div class="stud-ann-title"><?= htmlspecialchars($ann['title']) ?></div>
      <div class="stud-ann-message"><?= nl2br(htmlspecialchars($ann['message'])) ?></div>
      <div class="stud-ann-date"><?= date('d M Y, H:i', strtotime($ann['created_at'])) ?></div>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
  <a href="notifications.php" class="stud-btn">View All</a>
</div>
<?php endif; ?>

<!-- Recent Applications -->
<?php if (count($recentApps) > 0): ?>
<div class="stud-card">
  <div class="stud-card-title">Recent Applications</div>
  <div style="overflow-x: auto;">
    <table class="stud-table">
      <thead>
        <tr>
          <th>Company</th>
          <th>Package</th>
          <th>Drive Date</th>
          <th>Round 1</th>
          <th>Round 2</th>
          <th>Final</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentApps as $app): ?>
        <tr>
          <td><strong><?= htmlspecialchars($app['company_name']) ?></strong></td>
          <td><?= htmlspecialchars($app['package']) ?></td>
          <td><?= date('d M Y', strtotime($app['drive_date'])) ?></td>
          <td><span class="stud-badge stud-badge-<?= $app['round1_status'] === 'pass' ? 'success' : ($app['round1_status'] === 'fail' ? 'danger' : 'secondary') ?>"><?= ucfirst($app['round1_status']) ?></span></td>
          <td><span class="stud-badge stud-badge-<?= $app['round2_status'] === 'pass' ? 'success' : ($app['round2_status'] === 'fail' ? 'danger' : 'secondary') ?>"><?= ucfirst($app['round2_status']) ?></span></td>
          <td><span class="stud-badge stud-badge-<?= $app['final_status'] === 'selected' ? 'success' : ($app['final_status'] === 'rejected' ? 'danger' : 'secondary') ?>"><?= ucfirst($app['final_status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <a href="results.php" class="stud-btn">View All Results</a>
</div>
<?php endif; ?>

<!-- Action Buttons -->
<div class="stud-card">
  <div class="stud-card-title">Quick Actions</div>
  <a href="drives.php" class="stud-btn">Browse Drives</a>
  <a href="results.php" class="stud-btn">View Results</a>
  <a href="calendar.php" class="stud-btn">Schedule</a>
</div>

</div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>

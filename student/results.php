<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// Handle withdraw application
if (isset($_GET['withdraw'])) {
    $app_id = (int)$_GET['withdraw'];
    $chk = $conn->prepare("
        SELECT a.*, dr.status AS drive_status
        FROM applications a
        JOIN drives dr ON a.drive_id = dr.drive_id
        WHERE a.application_id = ? AND a.student_id = ?
    ");
    $chk->execute([$app_id, $student_id]);
    $app = $chk->fetch();

    if (!$app) {
        $error = 'Application not found.';
    } elseif ($app['staff_approval'] === 'approved' && ($app['round1_status'] !== 'pending' || $app['final_status'] !== 'pending')) {
        $error = 'Cannot withdraw — your application is already being processed.';
    } elseif ($app['drive_status'] === 'completed') {
        $error = 'Cannot withdraw — this drive is already completed.';
    } else {
        $conn->prepare("DELETE FROM applications WHERE application_id = ? AND student_id = ?")->execute([$app_id, $student_id]);
        $success = 'Application withdrawn successfully.';
    }
}

$stmt = $conn->prepare("
    SELECT a.application_id, a.applied_at, a.staff_approval,
           a.round1_status, a.round2_status, a.final_status,
           c.company_name, c.package,
           dr.drive_date, dr.role, dr.status AS drive_status
    FROM applications a
    JOIN drives dr     ON a.drive_id    = dr.drive_id
    JOIN companies c   ON dr.company_id = c.company_id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$student_id]);
$applications = $stmt->fetchAll();

$pageTitle   = 'My Results';
$showNav     = true;
$currentPage = 'results';
include '../includes/header.php';
?>

<style>
.results-page { background: #f8fafc; min-height: 100vh; padding-bottom: 40px; }
.results-container { max-width: 1180px; margin: 0 auto; padding: 0 1.5rem; box-sizing: border-box; }
.results-header { margin-bottom: 2rem; }
.results-header h1 { font-size: 2.2rem; color: #1e293b; font-weight: 900; margin-bottom: 0.5rem; }
.results-header p { color: #64748b; font-size: 0.95rem; }

.results-table-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow-x: auto; }
.results-table-card table { width: 100%; min-width: 900px; border-collapse: collapse; }
.results-table-card th { padding: 1rem; text-align: left; background: #f1f5f9; color: #1e293b; font-weight: 700; border: 1px solid #e2e8f0; font-size: 0.83rem; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; }
.results-table-card td { padding: 0.95rem 1rem; color: #475569; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
.results-table-card tr:hover td { background: #f8fafc; }

/* Staff approval status badge */
.approval-pending  { display:inline-block; padding:0.35rem 0.85rem; border-radius:8px; font-size:0.78rem; font-weight:700; background:#fef3c7; color:#92400e; border:1.5px solid #fcd34d; white-space:nowrap; }
.approval-approved { display:inline-block; padding:0.35rem 0.85rem; border-radius:8px; font-size:0.78rem; font-weight:700; background:#dcfce7; color:#15803d; border:1.5px solid #86efac; white-space:nowrap; }
.approval-rejected { display:inline-block; padding:0.35rem 0.85rem; border-radius:8px; font-size:0.78rem; font-weight:700; background:#fee2e2; color:#b91c1c; border:1.5px solid #fca5a5; white-space:nowrap; }

.badge-pill { display:inline-block; padding:0.35rem 0.85rem; border-radius:8px; font-size:0.78rem; font-weight:700; }
.badge-pending  { background:rgba(217,119,6,0.15);  color:#92400e; }
.badge-pass     { background:rgba(34,197,94,0.15);   color:#15803d; }
.badge-fail     { background:rgba(239,68,68,0.15);   color:#b91c1c; }
.badge-selected { background:rgba(34,197,94,0.15);   color:#15803d; }
.badge-rejected { background:rgba(107,114,128,0.15); color:#475569; }

.empty-state { text-align:center; padding:3rem 2rem; background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:18px; }
.empty-state p { color:#64748b; font-size:1.1rem; }

.alert-box { padding:1.2rem; border-radius:14px; margin-bottom:2rem; border-left:4px solid; }
.alert-danger  { background:rgba(239,68,68,0.08);  border-left-color:#ef4444; color:#7f1d1d; }
.alert-success { background:rgba(34,197,94,0.08);  border-left-color:#22c55e; color:#14532d; }

.withdraw-btn { padding:0.45rem 1rem; background:#ef4444; color:#fff; border:none; border-radius:8px; font-weight:600; font-size:0.82rem; cursor:pointer; transition:all 0.2s; }
.withdraw-btn:hover { background:#dc2626; }

/* info box explaining flow */
.flow-info { background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.75rem; font-size:0.9rem; color:#1e40af; line-height:1.7; }
.flow-info strong { color:#1e3a8a; }
</style>

<div class="main-content-wrapper">
<div class="results-page">
<div class="results-container">

<div class="results-header">
  <h1>My Applications &amp; Results</h1>
  <p>Track the status of every drive you have applied to</p>
</div>

<?php if ($success): ?>
  <div class="alert-box alert-success">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert-box alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Workflow explanation -->
<div class="flow-info">
  <strong>How it works:</strong>
  After you apply, your department staff reviews and approves/rejects your application.
  Once approved by staff, the admin manages round results and final placement.
  You will receive an in-app notification and email at each stage.
</div>

<?php if (count($applications) === 0): ?>
  <div class="empty-state">
    <p style="font-size:2rem;margin-bottom:0.5rem;">📋</p>
    <p>You haven't applied to any drives yet.</p>
    <p><a href="drives.php" style="color:#2563eb;font-weight:700;">Browse eligible drives →</a></p>
  </div>
<?php else: ?>

<div class="results-table-card">
  <table>
    <thead>
      <tr>
        <th>Company</th>
        <th>Role</th>
        <th>Package</th>
        <th>Drive Date</th>
        <th>Staff Approval</th>
        <th>Round 1</th>
        <th>Round 2</th>
        <th>Final Result</th>
        <th>Applied On</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($applications as $app):
        $staffApproval = $app['staff_approval'];
        $approved      = ($staffApproval === 'approved');
        $staffRejected = ($staffApproval === 'rejected');

        // Can only withdraw if staff hasn't decided yet AND nothing processed
        $canWithdraw = (
            $staffApproval === 'pending' &&
            $app['drive_status'] !== 'completed'
        );
      ?>
      <tr>
        <td><strong style="color:#0f172a;"><?= htmlspecialchars($app['company_name']) ?></strong></td>
        <td><?= htmlspecialchars($app['role'] ?? '—') ?></td>
        <td><?= htmlspecialchars($app['package']) ?></td>
        <td><?= date('d M Y', strtotime($app['drive_date'])) ?></td>

        <!-- Staff approval -->
        <td>
          <?php if ($staffApproval === 'pending'): ?>
            <span class="approval-pending">⏳ Awaiting Staff</span>
          <?php elseif ($staffApproval === 'approved'): ?>
            <span class="approval-approved">✅ Staff Approved</span>
          <?php else: ?>
            <span class="approval-rejected">❌ Staff Rejected</span>
          <?php endif; ?>
        </td>

        <!-- Round 1 — only meaningful after staff approved -->
        <td>
          <?php if (!$approved && !$staffRejected): ?>
            <span class="badge-pill badge-pending" style="opacity:0.45;">—</span>
          <?php else: ?>
            <span class="badge-pill badge-<?= $app['round1_status'] === 'pass' ? 'pass' : ($app['round1_status'] === 'fail' ? 'fail' : 'pending') ?>">
              <?= strtoupper($app['round1_status']) ?>
            </span>
          <?php endif; ?>
        </td>

        <!-- Round 2 -->
        <td>
          <?php if (!$approved && !$staffRejected): ?>
            <span class="badge-pill badge-pending" style="opacity:0.45;">—</span>
          <?php else: ?>
            <span class="badge-pill badge-<?= $app['round2_status'] === 'pass' ? 'pass' : ($app['round2_status'] === 'fail' ? 'fail' : 'pending') ?>">
              <?= strtoupper($app['round2_status']) ?>
            </span>
          <?php endif; ?>
        </td>

        <!-- Final -->
        <td>
          <?php if ($staffRejected): ?>
            <span class="badge-pill badge-rejected">❌ Not Forwarded</span>
          <?php elseif (!$approved): ?>
            <span class="badge-pill badge-pending" style="opacity:0.45;">Pending</span>
          <?php else: ?>
            <?php
              $fs  = $app['final_status'];
              $cls = $fs === 'selected' ? 'selected' : ($fs === 'rejected' ? 'rejected' : 'pending');
              $lbl = $fs === 'selected' ? '🎉 Selected' : ($fs === 'rejected' ? '❌ Rejected' : '⏳ Pending');
            ?>
            <span class="badge-pill badge-<?= $cls ?>"><?= $lbl ?></span>
          <?php endif; ?>
        </td>

        <td style="white-space:nowrap;font-size:0.85rem;"><?= date('d M Y', strtotime($app['applied_at'])) ?></td>

        <td>
          <?php if ($canWithdraw): ?>
            <a href="?withdraw=<?= $app['application_id'] ?>"
               class="withdraw-btn"
               onclick="return confirm('Withdraw this application? This cannot be undone.')">Withdraw</a>
          <?php elseif ($app['final_status'] === 'selected'): ?>
            <span style="color:#15803d;font-weight:700;">🎉 Placed</span>
          <?php elseif ($staffRejected): ?>
            <span style="color:#94a3b8;font-size:0.82rem;">Contact staff</span>
          <?php else: ?>
            <span style="color:#94a3b8;font-size:0.82rem;">In progress</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php endif; ?>

</div><!-- /container -->
</div><!-- /page -->
</div><!-- /main-content-wrapper -->

<?php include '../includes/footer.php'; ?>

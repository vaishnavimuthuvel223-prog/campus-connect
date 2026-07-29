<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') { header('Location: login.php'); exit; }
require_once '../config/db.php';
require_once '../config/notification_helper.php';

// Ensure min_tenth and min_twelfth columns exist in drives table
try { $conn->query("SELECT min_tenth FROM drives LIMIT 1"); } catch (PDOException $e) {
    $conn->exec("ALTER TABLE drives ADD COLUMN min_tenth DECIMAL(5,2) DEFAULT NULL");
}
try { $conn->query("SELECT min_twelfth FROM drives LIMIT 1"); } catch (PDOException $e) {
    $conn->exec("ALTER TABLE drives ADD COLUMN min_twelfth DECIMAL(5,2) DEFAULT NULL");
}

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch student
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$student_skill_category = $student['skill_category'] ?? '';

// Handle apply POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_drive'])) {
    $drive_id = (int)$_POST['drive_id'];

    if ($student['verification_status'] !== 'approved') {
        $error = 'Your account is not yet approved by the admin. Please wait for approval.';
    } else {
        // Check deadline
        $chk = $conn->prepare("SELECT last_date FROM drives WHERE drive_id = ?");
        $chk->execute([$drive_id]);
        $driveInfo = $chk->fetch();
        if ($driveInfo && $driveInfo['last_date'] && strtotime($driveInfo['last_date']) < strtotime('today')) {
            $error = 'The application deadline for this drive has passed.';
        } else {
            // Verify eligibility
            $eligStmt = $conn->prepare("
                SELECT dr.*, dd.dept_id, c.skill_category AS company_skill, c.company_name,
                       dr.min_tenth, dr.min_twelfth
                FROM drives dr
                JOIN drive_departments dd ON dr.drive_id = dd.drive_id
                JOIN companies c ON dr.company_id = c.company_id
                WHERE dr.drive_id = ? AND dd.dept_id = ? AND dr.min_cgpa <= ?
            ");
            $eligStmt->execute([$drive_id, $student['dept_id'], $student['cgpa']]);
            $driveEligibility = $eligStmt->fetch();

            if (!$driveEligibility) {
                $error = 'You are not eligible for this drive (department or CGPA mismatch).';
            } elseif (
                !empty($driveEligibility['company_skill']) &&
                strtolower($driveEligibility['company_skill']) !== 'all' &&
                !empty($student_skill_category) &&
                strcasecmp($driveEligibility['company_skill'], $student_skill_category) !== 0
            ) {
                $error = 'This drive is restricted to students in the <strong>' . htmlspecialchars($driveEligibility['company_skill']) . '</strong> skill category. Your category is <strong>' . htmlspecialchars($student_skill_category) . '</strong>.';
            } elseif (!empty($driveEligibility['min_tenth']) && (float)($student['tenth_percentage'] ?? 0) < (float)$driveEligibility['min_tenth']) {
                $error = 'Your 10th percentage (' . number_format($student['tenth_percentage'] ?? 0, 1) . '%) is below the required minimum of ' . number_format($driveEligibility['min_tenth'], 1) . '%.';
            } elseif (!empty($driveEligibility['min_twelfth']) && (float)($student['twelfth_percentage'] ?? 0) < (float)$driveEligibility['min_twelfth']) {
                $error = 'Your 12th percentage (' . number_format($student['twelfth_percentage'] ?? 0, 1) . '%) is below the required minimum of ' . number_format($driveEligibility['min_twelfth'], 1) . '%.';
            } else {
                // Check already applied — exclude staff-rejected so student can re-apply if staff rejected
                $dupStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND drive_id = ? AND staff_approval != 'rejected'");
                $dupStmt->execute([$student_id, $drive_id]);
                if ($dupStmt->fetchColumn() > 0) {
                    $error = 'You have already applied for this drive.';
                } else {
                    $insStmt = $conn->prepare("INSERT INTO applications (student_id, drive_id, staff_approval) VALUES (?, ?, 'pending')");
                    $insStmt->execute([$student_id, $drive_id]);
                    $success = 'Application submitted successfully for <strong>' . htmlspecialchars($driveEligibility['company_name']) . '</strong>! Awaiting staff approval.';

                    // Notify the student themselves (in-app confirmation)
                    notifyInApp_Applied($conn, $student_id, $student['name'], $driveEligibility['company_name'], date('d M Y', strtotime($driveEligibility['drive_date'])));

                    // Notify staff in the department
                    notifyInApp_StaffNewApplication($conn, $student['dept_id'], $student['name'], $driveEligibility['company_name']);
                }
            }
        }
    }
}

// Check if skill_category column exists in companies
$company_skill_column_exists = true;
try {
    $conn->query("SELECT skill_category FROM companies LIMIT 1");
} catch (PDOException $e) {
    $company_skill_column_exists = false;
}

$select_fields = "dr.drive_id, c.company_name, c.package, c.description, dr.min_cgpa, dr.min_tenth, dr.min_twelfth, dr.role, dr.drive_date, dr.last_date, dr.status";
$where_skill = "";
// Two student_id params needed: one for already_applied subquery, one for my_approval_status subquery
$params = [$student_id, $student_id, $student['dept_id'], $student['cgpa']];

if ($company_skill_column_exists) {
    $select_fields .= ", c.skill_category";
    // Always include drives open to 'all' categories
    // If student has a skill category, also include drives matching that category
    if (!empty($student_skill_category)) {
        $where_skill = " AND (c.skill_category = ? OR c.skill_category = 'all' OR c.skill_category IS NULL OR c.skill_category = '')";
        $params[] = $student_skill_category;
    } else {
        // Student has no skill category - show all drives open to 'all' or with no category specified
        $where_skill = " AND (c.skill_category = 'all' OR c.skill_category IS NULL OR c.skill_category = '')";
    }
}

$sql = "
  SELECT $select_fields,
       (SELECT COUNT(*) FROM applications WHERE student_id = ? AND drive_id = dr.drive_id AND staff_approval != 'rejected') AS already_applied,
       (SELECT staff_approval FROM applications WHERE student_id = ? AND drive_id = dr.drive_id ORDER BY applied_at DESC LIMIT 1) AS my_approval_status
  FROM drives dr
  JOIN companies c ON dr.company_id = c.company_id
  JOIN drive_departments dd ON dr.drive_id = dd.drive_id
  WHERE dd.dept_id = ? AND dr.min_cgpa <= ? AND dr.status IN ('upcoming', 'ongoing') $where_skill
  GROUP BY dr.drive_id
  ORDER BY dr.drive_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$drives = $stmt->fetchAll();




$allDrivesStmt = $conn->prepare("
    SELECT dr.drive_id, c.company_name, c.package, dr.role, dr.min_cgpa, dr.drive_date, dr.last_date, dr.status
    FROM drives dr
    JOIN companies c ON dr.company_id = c.company_id
    ORDER BY dr.drive_date ASC
");
$allDrivesStmt->execute();
$allDrives = $allDrivesStmt->fetchAll();

$pageTitle = 'Recruitment Drives';
$showNav = true;
$currentPage = 'drives';
include '../includes/header.php';
?>
<style>
.drives-page { background: var(--bg-secondary, #f8fafc); color: var(--text-primary, #1e293b); min-height: 100vh; padding-top: 0; padding-bottom: 0; }
body.dark-mode .drives-page { background: #1e293b; color: #f1f5f9; }

.drives-container { max-width: 1200px; margin: 0 auto; padding: 0 2%; }
.drives-header { margin-bottom: 2rem; }
.drives-header h1 { font-size: 2.2rem; color: #1e293b; font-weight: 900; margin-bottom: 0.5rem; }
.drives-header p { color: #64748b; font-size: 0.95rem; }

.drive-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 2rem; margin-bottom: 1.5rem; transition: all 0.3s ease; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.drive-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

.drive-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem; }
.drive-header h3 { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0 0 0.3rem 0; }
.drive-status { display: inline-block; padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; }
.status-upcoming { background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }
.status-ongoing { background: rgba(34,197,94,0.15); color: #16a34a; border: 1px solid rgba(34,197,94,0.3); }
.status-completed { background: rgba(107,114,128,0.15); color: #6b7280; border: 1px solid rgba(107,114,128,0.3); }

.drive-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.detail-item { background: #f1f5f9; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; }
.detail-label { font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; }
.detail-value { font-size: 1rem; font-weight: 700; color: #0f172a; }

.badge-skill { display: inline-block; padding: 0.25rem 0.75rem; background: rgba(234,179,8,0.15); color: #a16207; border: 1px solid rgba(234,179,8,0.3); border-radius: 20px; font-size: 0.8rem; font-weight: 600; }

.drive-actions { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-top: 0.5rem; }
.apply-btn { padding: 0.8rem 2rem; background: linear-gradient(135deg, #3b82f6, #06b6d4); color: #fff; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; font-size: 1rem; box-shadow: 0 4px 15px rgba(59,130,246,0.3); }
.apply-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59,130,246,0.4); }
.applied-badge { padding: 0.6rem 1.2rem; background: rgba(34,197,94,0.12); color: #16a34a; border: 1.5px solid rgba(34,197,94,0.3); border-radius: 8px; font-weight: 700; }
.blocked-msg { padding: 0.6rem 1.2rem; background: rgba(239,68,68,0.08); color: #dc2626; border: 1.5px solid rgba(239,68,68,0.2); border-radius: 8px; font-weight: 600; font-size: 0.9rem; }

.alert-box { padding: 1.2rem 1.5rem; border-radius: 14px; margin-bottom: 2rem; border-left: 4px solid; }
.alert-danger { background: rgba(239,68,68,0.08); border-left-color: #ef4444; color: #dc2626; }
.alert-success { background: rgba(34,197,94,0.08); border-left-color: #22c55e; color: #16a34a; }

.empty-state { text-align: center; padding: 3rem 2rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 18px; }
.empty-state p { color: #64748b; font-size: 1.1rem; }

.ref-table { width: 100%; border-collapse: collapse; }
.ref-table th { padding: 1rem; text-align: left; background: #f1f5f9; color: #1e293b; font-weight: 700; font-size: 0.85rem; border-bottom: 2px solid #e2e8f0; }
.ref-table td { padding: 0.9rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
.ref-table tr:hover td { background: #f8fafc; }

/* ---- MODAL STYLES ---- */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-box { background: #fff; border-radius: 20px; padding: 2rem; max-width: 520px; width: 95%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: popIn 0.3s ease; }
@keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal-title { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0; }
.check-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 0.75rem; font-weight: 600; font-size: 0.95rem; }
.check-pass { background: rgba(34,197,94,0.1); color: #16a34a; border: 1px solid rgba(34,197,94,0.25); }
.check-fail { background: rgba(239,68,68,0.1); color: #dc2626; border: 1px solid rgba(239,68,68,0.25); }
.check-icon { font-size: 1.2rem; }
.modal-company-name { font-size: 1.1rem; font-weight: 700; color: #3b82f6; margin-bottom: 1rem; }
.modal-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.modal-confirm-btn { flex: 1; padding: 0.9rem; background: linear-gradient(135deg, #3b82f6, #06b6d4); color: #fff; border: none; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.3s; }
.modal-confirm-btn:hover { box-shadow: 0 6px 20px rgba(59,130,246,0.4); }
.modal-cancel-btn { flex: 1; padding: 0.9rem; background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; }
.modal-cancel-btn:hover { background: #e2e8f0; }

@media (max-width: 1024px) {
  .drives-container { padding: 0 1rem; }
  .drive-details { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .drive-header { flex-direction: column; }
  .drives-header h1 { font-size: 1.6rem; }
  .drive-details { grid-template-columns: 1fr; }
}
</style>

<!-- ELIGIBILITY VERIFICATION MODAL -->
<div class="modal-overlay" id="applyModal">
  <div class="modal-box">
    <div class="modal-title">🔍 Eligibility Verification</div>
    <div class="modal-company-name" id="modalCompanyName"></div>
    <div id="modalChecks"></div>
    <div class="modal-actions">
      <button class="modal-cancel-btn" onclick="closeModal()">Cancel</button>
      <button class="modal-confirm-btn" id="modalProceedBtn" onclick="submitApply()">✅ Confirm & Apply</button>
    </div>
  </div>
</div>

<!-- Hidden form submitted by modal -->
<form method="POST" action="drives.php" id="hiddenApplyForm" style="display:none;">
  <input type="hidden" name="drive_id" id="hiddenDriveId">
  <input type="hidden" name="apply_drive" value="1">
</form>

<div class="main-content-wrapper">
<div class="drives-page">
<div class="drives-container">

<div class="drives-header">
  <h1>Recruitment Drives</h1>
  <p>Browse and apply to placement drives you are eligible for</p>
</div>

<?php if ($success): ?>
  <div class="alert-box alert-success">✅ <?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert-box alert-danger">⚠️ <?= $error ?></div>
<?php endif; ?>

<!-- Student info for JS checks -->
<script>
const studentData = {
  verification_status: "<?= addslashes($student['verification_status'] ?? '') ?>",
  skill_category: "<?= addslashes($student_skill_category) ?>",
  cgpa: <?= (float)($student['cgpa'] ?? 0) ?>,
  tenth: <?= (float)($student['tenth_percentage'] ?? 0) ?>,
  twelfth: <?= (float)($student['twelfth_percentage'] ?? 0) ?>,
  dept_id: <?= (int)($student['dept_id'] ?? 0) ?>
};
</script>

<!-- Eligible Drives -->
<?php if (count($drives) > 0): ?>
<div style="margin-bottom: 2rem;">
  <h2 style="font-size:1.8rem;font-weight:900;color:#1e293b;margin-bottom:1.5rem;">Eligible Drives</h2>

  <?php foreach ($drives as $drive): ?>
  <?php
    $deadlinePassed = !empty($drive['last_date']) && strtotime($drive['last_date']) < strtotime('today');
    $skillCategory  = $drive['skill_category'] ?? '';
    $isCompleted    = ($drive['status'] === 'completed');
  ?>
  <div class="drive-card">
    <div class="drive-header">
      <div>
        <h3><?= htmlspecialchars($drive['company_name']) ?></h3>
        <?php if (!empty($skillCategory)): ?>
          <span class="badge-skill">🎯 Skill Category: <?= htmlspecialchars(ucwords($skillCategory)) ?></span>
        <?php endif; ?>
      </div>
      <span class="drive-status status-<?= $drive['status'] ?>">
        <?= strtoupper($drive['status']) ?>
      </span>
    </div>

    <div class="drive-details">
      <div class="detail-item">
        <div class="detail-label">Package</div>
        <div class="detail-value">₹<?= htmlspecialchars($drive['package']) ?> LPA</div>
      </div>
      <div class="detail-item">
        <div class="detail-label">Role</div>
        <div class="detail-value"><?= htmlspecialchars($drive['role'] ?? 'N/A') ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">Min CGPA</div>
        <div class="detail-value"><?= number_format($drive['min_cgpa'], 2) ?></div>
      </div>
      <?php if (!empty($drive['min_tenth'])): ?>
      <div class="detail-item">
        <div class="detail-label">Min 10th %</div>
        <div class="detail-value"><?= number_format($drive['min_tenth'], 1) ?>%</div>
      </div>
      <?php endif; ?>
      <?php if (!empty($drive['min_twelfth'])): ?>
      <div class="detail-item">
        <div class="detail-label">Min 12th %</div>
        <div class="detail-value"><?= number_format($drive['min_twelfth'], 1) ?>%</div>
      </div>
      <?php endif; ?>
      <div class="detail-item">
        <div class="detail-label">Skill Category</div>
        <div class="detail-value"><?= htmlspecialchars(ucwords($skillCategory ?: 'Open to All')) ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">Drive Date</div>
        <div class="detail-value"><?= date('d M Y', strtotime($drive['drive_date'])) ?></div>
      </div>
      <div class="detail-item">
        <div class="detail-label">Application Deadline</div>
        <div class="detail-value">
          <?php if ($drive['last_date']): ?>
            <?= date('d M Y', strtotime($drive['last_date'])) ?>
            <?php if ($deadlinePassed): ?>
              <span style="color:#dc2626;font-size:0.75rem;font-weight:700;"> ● CLOSED</span>
            <?php endif; ?>
          <?php else: ?>
            <span style="color:#64748b;">—</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($drive['description']): ?>
    <p style="color:#475569;margin:0 0 1.5rem;line-height:1.6;padding:1rem;background:#f1f5f9;border-radius:10px;border-left:3px solid #3b82f6;">
      <?= htmlspecialchars($drive['description']) ?>
    </p>
    <?php endif; ?>

    <div class="drive-actions">
      <?php if ($drive['already_applied']): ?>
        <?php
          $myStatus = $drive['my_approval_status'] ?? 'pending';
        ?>
        <?php if ($myStatus === 'pending'): ?>
          <span class="applied-badge" style="background:rgba(245,158,11,0.12);color:#92400e;border:1.5px solid #fcd34d;">⏳ Applied — Awaiting Staff Approval</span>
        <?php elseif ($myStatus === 'approved'): ?>
          <span class="applied-badge">✅ Applied — Staff Approved · Check Results</span>
        <?php else: ?>
          <span class="applied-badge" style="background:rgba(239,68,68,0.1);color:#b91c1c;border:1.5px solid #fca5a5;">❌ Staff Rejected — Contact your department</span>
        <?php endif; ?>

      <?php elseif ($student['verification_status'] !== 'approved'): ?>
        <span class="blocked-msg">⏳ Account pending admin approval — cannot apply yet</span>

      <?php elseif ($deadlinePassed): ?>
        <span class="blocked-msg">🔒 Application deadline has passed</span>

      <?php elseif ($isCompleted): ?>
        <span class="blocked-msg">🏁 Drive completed</span>

      <?php else: ?>
        <!-- Apply button that opens verification modal -->
        <button
          class="apply-btn"
          data-drive-id="<?= htmlspecialchars($drive['drive_id']) ?>"
          data-company-name="<?= htmlspecialchars($drive['company_name']) ?>"
          data-skill-category="<?= htmlspecialchars(ucwords($skillCategory)) ?>"
          data-min-cgpa="<?= htmlspecialchars(number_format($drive['min_cgpa'], 2)) ?>"
          data-min-tenth="<?= htmlspecialchars(!empty($drive['min_tenth']) ? number_format($drive['min_tenth'], 2) : '') ?>"
          data-min-twelfth="<?= htmlspecialchars(!empty($drive['min_twelfth']) ? number_format($drive['min_twelfth'], 2) : '') ?>"
          onclick="openApplyModalFromButton(this)"
        >Apply Now</button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
  <p style="font-size:2rem;margin-bottom:0.5rem;">📋</p>
  <p>No eligible drives available for your profile at the moment.</p>
  <p style="font-size:0.9rem;color:#94a3b8;">Check back later or contact the placement cell.</p>
</div>
<?php endif; ?>

<!-- All Drives Reference Table -->
<div style="margin-top: 3rem;">
  <h2 style="font-size:1.8rem;font-weight:900;color:#1e293b;margin-bottom:1.5rem;">All Available Drives</h2>
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:18px;padding:1.5rem;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
    <table class="ref-table">
      <thead>
        <tr>
          <th>Company</th>
          <th>Package</th>
          <th>Role</th>
          <th>Min CGPA</th>
          <th>Drive Date</th>
          <th>Deadline</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allDrives as $d): ?>
        <tr>
          <td style="font-weight:700;color:#0f172a;"><?= htmlspecialchars($d['company_name']) ?></td>
          <td><?= htmlspecialchars($d['package']) ?> LPA</td>
          <td><?= htmlspecialchars($d['role'] ?? '—') ?></td>
          <td><?= number_format($d['min_cgpa'], 2) ?></td>
          <td><?= date('d M Y', strtotime($d['drive_date'])) ?></td>
          <td>
            <?php if ($d['last_date']): ?>
              <?= date('d M Y', strtotime($d['last_date'])) ?>
              <?php if (strtotime($d['last_date']) < strtotime('today')): ?>
                <span style="color:#dc2626;font-size:0.75rem;font-weight:700;"> CLOSED</span>
              <?php endif; ?>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td>
            <span style="font-weight:700;color:<?= $d['status']==='upcoming'?'#3b82f6':($d['status']==='ongoing'?'#16a34a':'#6b7280') ?>;">
              <?= strtoupper($d['status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div><!-- /container -->
</div><!-- /page -->

<script>
let pendingDriveId = null;

// New wrapper function to read data from button attributes
function openApplyModalFromButton(btn) {
  const driveId = btn.dataset.driveId;
  const companyName = btn.dataset.companyName;
  const skillCategory = btn.dataset.skillCategory;
  const minCgpa = parseFloat(btn.dataset.minCgpa);
  const minTenth = btn.dataset.minTenth ? parseFloat(btn.dataset.minTenth) : null;
  const minTwelfth = btn.dataset.minTwelfth ? parseFloat(btn.dataset.minTwelfth) : null;
  
  openApplyModal(driveId, companyName, skillCategory, minCgpa, minTenth, minTwelfth);
}

function openApplyModal(driveId, companyName, skillCategory, minCgpa, minTenth, minTwelfth) {
  pendingDriveId = driveId;

  document.getElementById('modalCompanyName').textContent = '🏢 ' + companyName;

  // Run eligibility checks
  let checks = [];
  let allPass = true;

  // 1. Account approval
  if (studentData.verification_status === 'approved') {
    checks.push({ pass: true, label: 'Account verified & approved' });
  } else {
    checks.push({ pass: false, label: 'Account not yet approved by admin' });
    allPass = false;
  }

  // 2. CGPA check
  if (studentData.cgpa >= minCgpa) {
    checks.push({ pass: true, label: 'CGPA ' + studentData.cgpa.toFixed(2) + ' meets minimum ' + parseFloat(minCgpa).toFixed(2) });
  } else {
    checks.push({ pass: false, label: 'CGPA ' + studentData.cgpa.toFixed(2) + ' is below required ' + parseFloat(minCgpa).toFixed(2) });
    allPass = false;
  }

  // 3. 10th percentage check
  if (minTenth !== null) {
    if (studentData.tenth >= minTenth) {
      checks.push({ pass: true, label: '10th: ' + studentData.tenth.toFixed(1) + '% meets minimum ' + parseFloat(minTenth).toFixed(1) + '%' });
    } else {
      checks.push({ pass: false, label: '10th: ' + studentData.tenth.toFixed(1) + '% is below required ' + parseFloat(minTenth).toFixed(1) + '%' });
      allPass = false;
    }
  }

  // 4. 12th percentage check
  if (minTwelfth !== null) {
    if (studentData.twelfth >= minTwelfth) {
      checks.push({ pass: true, label: '12th: ' + studentData.twelfth.toFixed(1) + '% meets minimum ' + parseFloat(minTwelfth).toFixed(1) + '%' });
    } else {
      checks.push({ pass: false, label: '12th: ' + studentData.twelfth.toFixed(1) + '% is below required ' + parseFloat(minTwelfth).toFixed(1) + '%' });
      allPass = false;
    }
  }

  // 5. Skill category check
  if (!skillCategory || skillCategory === '' || skillCategory.toLowerCase() === 'open to all' || skillCategory.toLowerCase() === 'all') {
    checks.push({ pass: true, label: 'Open to all skill categories' });
  } else if (studentData.skill_category.toLowerCase() === skillCategory.toLowerCase()) {
    checks.push({ pass: true, label: 'Skill category matches: ' + skillCategory });
  } else {
    checks.push({ pass: false, label: 'Skill category mismatch — Drive: ' + skillCategory + ', Yours: ' + (studentData.skill_category || 'Not set') });
    allPass = false;
  }

  // Render checks
  const checksDiv = document.getElementById('modalChecks');
  checksDiv.innerHTML = checks.map(c =>
    '<div class="check-row ' + (c.pass ? 'check-pass' : 'check-fail') + '">' +
    '<span class="check-icon">' + (c.pass ? '✅' : '❌') + '</span>' +
    '<span>' + c.label + '</span></div>'
  ).join('');

  // Show/hide proceed button
  const proceedBtn = document.getElementById('modalProceedBtn');
  if (allPass) {
    proceedBtn.style.display = 'block';
    proceedBtn.textContent = '✅ Confirm & Apply';
  } else {
    proceedBtn.style.display = 'none';
    checksDiv.innerHTML += '<div style="margin-top:1rem;padding:0.75rem 1rem;background:rgba(239,68,68,0.08);border-radius:10px;color:#dc2626;font-weight:600;font-size:0.9rem;">⚠️ You do not meet all eligibility criteria for this drive.</div>';
  }

  document.getElementById('applyModal').classList.add('active');
}

function closeModal() {
  document.getElementById('applyModal').classList.remove('active');
  pendingDriveId = null;
}

function submitApply() {
  if (!pendingDriveId) return;
  document.getElementById('hiddenDriveId').value = pendingDriveId;
  document.getElementById('hiddenApplyForm').submit();
}

// Close modal on overlay click
document.getElementById('applyModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

</body>
</html>
<?php include '../includes/footer.php'; ?>

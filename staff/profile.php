<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';

// Auto-add staff_code column if missing
try {
    $conn->query("SELECT staff_code FROM department_staff LIMIT 1");
} catch (Exception $e) {
    $conn->exec("ALTER TABLE department_staff ADD COLUMN staff_code VARCHAR(50) UNIQUE DEFAULT NULL AFTER staff_id");
}

$user_staff_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get staff data
$stmt = $conn->prepare("SELECT * FROM department_staff WHERE staff_id = ?");
$stmt->execute([$user_staff_id]);
$staff = $stmt->fetch();

if (!$staff) { header('Location: login.php'); exit; }

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current hash
    $stmt = $conn->prepare("SELECT password FROM department_staff WHERE staff_id = ?");
    $stmt->execute([$user_staff_id]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $conn->prepare("UPDATE department_staff SET password = ? WHERE staff_id = ?")->execute([$newHash, $user_staff_id]);
        $success = 'Password changed successfully!';
    }
}

// Handle profile pic upload
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
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = 'staff_' . $user_staff_id . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Delete old pic if exists
            if ($staff['profile_pic'] && file_exists($uploadDir . $staff['profile_pic'])) {
                unlink($uploadDir . $staff['profile_pic']);
            }
            $conn->prepare("UPDATE department_staff SET profile_pic = ? WHERE staff_id = ?")->execute([$filename, $user_staff_id]);
            $success = 'Profile picture updated!';
            $staff['profile_pic'] = $filename;
        } else {
            $error = 'Failed to upload file.';
        }
    }
}

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
                <?php if ($staff['profile_pic']): ?>
                    <img src="/campuss/uploads/profiles/<?= htmlspecialchars($staff['profile_pic']) ?>" alt="Profile Picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= strtoupper(substr($staff['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($staff['name']) ?></h3>
                <p style="color:var(--gray);margin:0.25rem 0;">Staff Code: <strong><?= htmlspecialchars($staff['staff_code'] ?? 'N/A') ?></strong> &bull; Department Staff</p>
            </div>
        </div>
    </div>

    <!-- Profile Completion Bar -->
    <?php
    $staff_fields = [
        'name'        => $staff['name'] ?? '',
        'email'       => $staff['email'] ?? '',
        'profile_pic' => $staff['profile_pic'] ?? '',
    ];
    $sf_filled = count(array_filter($staff_fields, fn($v) => $v !== '' && $v !== null));
    $sf_total  = count($staff_fields);
    $sf_pct    = round(($sf_filled / $sf_total) * 100);
    $sf_color  = $sf_pct >= 80 ? '#22c55e' : ($sf_pct >= 50 ? '#f59e0b' : '#ef4444');
    ?>
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">📋 Profile Completion</div>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="background:#e5e7eb;border-radius:20px;height:18px;overflow:hidden;">
                    <div style="width:<?= $sf_pct ?>%;height:100%;background:<?= $sf_color ?>;border-radius:20px;transition:width 0.5s ease;"></div>
                </div>
                <p style="margin-top:0.4rem;font-size:0.85rem;color:var(--gray);"><?= $sf_filled ?>/<?= $sf_total ?> fields filled</p>
            </div>
            <div style="font-size:2rem;font-weight:900;color:<?= $sf_color ?>;"><?= $sf_pct ?>%</div>
        </div>
        <?php if ($sf_pct < 100): ?>
        <p style="margin-top:0.5rem;font-size:0.85rem;color:#6b7280;">
            💡 <strong>To improve:</strong>
            <?php
            $sm = [];
            if (empty($staff['profile_pic'])) $sm[] = 'Upload Profile Photo';
            if (empty($staff['email']))       $sm[] = 'Add Email';
            echo implode(', ', $sm);
            ?>
        </p>
        <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- Profile Info Display -->
        <div class="card">
            <div class="card-header">ℹ️ Profile Information</div>
            <div style="padding:1rem;">
                <p><strong>Staff ID:</strong> <?= htmlspecialchars($staff['staff_id']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($staff['name']) ?></p>
                <p><strong>Department:</strong> IT</p>
                <p style="color:var(--gray);margin-top:1rem;font-size:0.9rem;">⚠️ Contact your department administrator to update name or other information.</p>
            </div>
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
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>

            <!-- Profile Picture -->
            <div class="card">
                <div class="card-header">📸 Profile Picture</div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Upload New Picture</label>
                        <input type="file" name="profile_pic" class="form-control" accept="image/*">
                        <small style="color:var(--gray);">Max 2MB, JPG/PNG/GIF only</small>
                    </div>
                    <button type="submit" class="btn btn-secondary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

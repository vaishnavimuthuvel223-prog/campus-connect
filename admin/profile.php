<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$admin_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get admin data
$stmt = $conn->prepare("SELECT * FROM placement_admin WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) { header('Location: login.php'); exit; }

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check email uniqueness (excluding self)
        $chk = $conn->prepare("SELECT COUNT(*) FROM placement_admin WHERE email = ? AND admin_id != ?");
        $chk->execute([$email, $admin_id]);
        if ($chk->fetchColumn() > 0) {
            $error = 'Email already used by another admin.';
        } else {
            $stmt = $conn->prepare("UPDATE placement_admin SET name = ?, email = ? WHERE admin_id = ?");
            $stmt->execute([$name, $email, $admin_id]);
            $_SESSION['name'] = $name;
            $success = 'Profile updated successfully!';
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM placement_admin WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current hash
    $stmt = $conn->prepare("SELECT password FROM placement_admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $conn->prepare("UPDATE placement_admin SET password = ? WHERE admin_id = ?")->execute([$newHash, $admin_id]);
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
        $filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Delete old pic if exists
            if ($admin['profile_pic'] && file_exists($uploadDir . $admin['profile_pic'])) {
                unlink($uploadDir . $admin['profile_pic']);
            }
            $conn->prepare("UPDATE placement_admin SET profile_pic = ? WHERE admin_id = ?")->execute([$filename, $admin_id]);
            $success = 'Profile picture updated!';
            $admin['profile_pic'] = $filename;
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
                <?php if ($admin['profile_pic']): ?>
                    <img src="/campuss/uploads/profiles/<?= htmlspecialchars($admin['profile_pic']) ?>" alt="Profile Picture" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= strtoupper(substr($admin['name'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($admin['name']) ?></h3>
                <p style="color:var(--gray);margin:0.25rem 0;">Admin &bull; Placement Admin</p>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- Edit Personal Info -->
        <div class="card">
            <div class="card-header">✏️ Edit Information</div>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
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

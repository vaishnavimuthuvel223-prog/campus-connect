<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { 
    header('Location: login.php'); 
    exit; 
}
require_once '../config/db.php';

$user_staff_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current staff data
$stmt = $conn->prepare("SELECT * FROM department_staff WHERE staff_id = ?");
$stmt->execute([$user_staff_id]);
$staff = $stmt->fetch();

if (!$staff) { 
    header('Location: login.php'); 
    exit; 
}

// If profile already complete, redirect to dashboard
if ($staff['profile_completed']) {
    $_SESSION['name'] = $staff['name'];
    header('Location: dashboard.php', true, 302);
    exit;
}

// Get department name
$dept = $conn->query("SELECT dept_name FROM departments WHERE dept_id = {$staff['dept_id']}")->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Update basic info
    if ($_POST['action'] === 'update_info') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);

        if (empty($name) || empty($email)) {
            $error = '❌ Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '❌ Invalid email address.';
        } else {
            // Check email uniqueness (excluding self)
            $chk = $conn->prepare("SELECT COUNT(*) FROM department_staff WHERE email = ? AND staff_id != ?");
            $chk->execute([$email, $user_staff_id]);
            if ($chk->fetchColumn() > 0) {
                $error = '❌ Email already used by another staff.';
            } else {
                // Update profile
                $stmt = $conn->prepare("UPDATE department_staff SET name = ?, email = ?, profile_completed = 1 WHERE staff_id = ?");
                $stmt->execute([$name, $email, $user_staff_id]);
                
                // Update session
                $_SESSION['name'] = $name;
                $success = '✅ Profile updated successfully! Redirecting to dashboard...';
                
                // Refresh data
                $stmt = $conn->prepare("SELECT * FROM department_staff WHERE staff_id = ?");
                $stmt->execute([$user_staff_id]);
                $staff = $stmt->fetch();
                
                // Auto-redirect to dashboard after 2 seconds
                echo '<meta http-equiv="refresh" content="2;url=dashboard.php" />';
            }
        }
    }
    
    // Change password
    if ($_POST['action'] === 'change_password') {
        $current = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        // Verify current password
        if (!password_verify($current, $staff['password'])) {
            $error = '❌ Current password is incorrect.';
        } elseif (strlen($new_pass) < 6) {
            $error = '❌ New password must be at least 6 characters.';
        } elseif ($new_pass !== $confirm) {
            $error = '❌ New passwords do not match.';
        } else {
            // Update password
            $newHash = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $conn->prepare("UPDATE department_staff SET password = ? WHERE staff_id = ?")->execute([$newHash, $user_staff_id]);
            $success = '✅ Password changed successfully!';
            
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM department_staff WHERE staff_id = ?");
            $stmt->execute([$user_staff_id]);
            $staff = $stmt->fetch();
        }
    }
}
$pageTitle = 'Complete Profile - Staff Portal';
$showNav = true;
$currentPage = 'profile';
include '../includes/header.php';
?>
<style>
<div class="profile-container">
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .profile-wrapper {
            max-width: 600px;
            width: 100%;
        }
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .profile-card h2 {
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 1rem;
        }
        .info-box {
            background: #f0f4ff;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        .info-box p {
            margin: 0.5rem 0;
            color: #333;
        }
        .info-box strong {
            color: #667eea;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .small-text {
            color: #999;
            display: block;
            margin-top: 0.3rem;
            font-size: 0.85rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

<div class="profile-container">
    <div class="profile-wrapper">
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Profile Info Card -->
        <div class="profile-card">
            <h2>👤 Your Profile</h2>
            
            <div class="info-box">
                <p><strong>Staff ID:</strong> <?= htmlspecialchars($staff['staff_code']) ?></p>
                <p><strong>Department:</strong> <?= htmlspecialchars($dept['dept_name']) ?></p>
                <p><strong>Status:</strong> <?= $staff['profile_completed'] ? '✅ Complete' : '⏳ Incomplete' ?></p>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="update_info">
                
                <div class="form-group">
                    <label for="name">👤 Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= htmlspecialchars($staff['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">📧 Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($staff['email']) ?>" required>
                    <span class="small-text">Used for notifications and communication</span>
                </div>

                <button type="submit" class="btn">💾 Save Profile</button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="profile-card">
            <h2>🔒 Change Password</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn">🔄 Change Password</button>
            </form>
        </div>

        <!-- Dashboard Link -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="dashboard.php" class="btn btn-secondary">→ Go to Dashboard</a>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php if (session_status() === PHP_SESSION_NONE) session_start();
// Load notification count for students
$_notifCount = 0;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && isset($_SESSION['user_id'])) {
    try {
        if (!isset($conn)) { require_once __DIR__ . '/../config/db.php'; }
        require_once __DIR__ . '/../config/notification_helper.php';
        $_notifCount = getUnreadCount($conn, $_SESSION['user_id']);
    } catch (Exception $e) { $_notifCount = 0; }
}

// Load profile picture
$_profilePic = null;
if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
    try {
        if (!isset($conn)) { require_once __DIR__ . '/../config/db.php'; }
        if ($_SESSION['role'] === 'student') {
            $stmt = $conn->prepare("SELECT profile_pic FROM students WHERE student_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_profilePic = $stmt->fetchColumn();
        } elseif ($_SESSION['role'] === 'staff') {
            $stmt = $conn->prepare("SELECT profile_pic FROM department_staff WHERE staff_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_profilePic = $stmt->fetchColumn();
        } elseif ($_SESSION['role'] === 'admin') {
            $stmt = $conn->prepare("SELECT profile_pic FROM placement_admin WHERE admin_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_profilePic = $stmt->fetchColumn();
        }
    } catch (Exception $e) {
        $_profilePic = null;
    }
}

$_module = isset($_SESSION['role']) ? $_SESSION['role'] . '-module' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Campus Recruitment System' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/campuss/css/style.css">
    <link rel="stylesheet" href="/campuss/css/modern-design.css">
    <link rel="stylesheet" href="/campuss/css/sidebar-fix.css">
    <link rel="stylesheet" href="/campuss/css/all-modules.css">
    <link rel="stylesheet" href="/campuss/css/admin-dashboard.css">
    <link rel="stylesheet" href="/campuss/css/campus-redesign.css">
    <link rel="stylesheet" href="/campuss/css/layout-fix.css">
</head>
<body class="<?= $_module ?>">

<script src="/campuss/js/theme-manager.js"></script>
<script src="/campuss/js/ui-enhancements.js" defer></script>

<?php if (isset($showNav) && $showNav): ?>

<!-- ═══════════════════════════════════════════
     MOBILE HAMBURGER BUTTON
════════════════════════════════════════════ -->
<button class="sidebar-hamburger" id="sidebarHamburger"
        onclick="toggleSidebar()" aria-label="Open navigation">
    <span></span><span></span><span></span>
</button>

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ═══════════════════════════════════════════
     VERTICAL SIDEBAR
════════════════════════════════════════════ -->
<aside class="sidebar" id="mainSidebar">

    <!-- Logo / College Name -->
    <div class="sidebar-header">
        <a class="logo-box" href="/campuss/">
            <div class="logo-row">
                <div class="logo-icon glitter-roll">
                    <img src="/campuss/uploads/MKCE-Logo.jpg" alt="MKCE Logo" style="width: 40px; height: 40px; border: none;">
                </div>
                <div class="logo-name">MKCE</div>
            </div>
            <div class="logo-subtitle">CampusConnect</div>
        </a>
    </div>

    <!-- User Profile Strip -->
    <?php if (isset($_SESSION['role'])): ?>
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <?php if ($_profilePic): ?>
                <img src="/campuss/uploads/profiles/<?= htmlspecialchars($_profilePic) ?>"
                     alt="Profile">
            <?php else: ?>
                👤
            <?php endif; ?>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">
                <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
            </div>
            <div class="sidebar-user-role">
                <?= ucfirst($_SESSION['role'] ?? '') ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation Links -->
    <nav class="sidebar-nav">

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>

            <div class="nav-section-label">Main</div>

            <a href="/campuss/student/dashboard.php"
               class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="/campuss/student/drives.php"
               class="nav-item <?= ($currentPage ?? '') === 'drives' ? 'active' : '' ?>">
                <span class="nav-icon">🏢</span>
                <span class="nav-text">Placement Drives</span>
            </a>

            <a href="/campuss/student/results.php"
               class="nav-item <?= ($currentPage ?? '') === 'results' ? 'active' : '' ?>">
                <span class="nav-icon">📈</span>
                <span class="nav-text">My Results</span>
            </a>

            <div class="nav-section-label">Prepare</div>

            <a href="/campuss/student/calendar.php"
               class="nav-item <?= ($currentPage ?? '') === 'calendar' ? 'active' : '' ?>">
                <span class="nav-icon">📅</span>
                <span class="nav-text">Calendar</span>
            </a>

            <a href="/campuss/leaderboard.php"
               class="nav-item <?= ($currentPage ?? '') === 'leaderboard' ? 'active' : '' ?>">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Leaderboard</span>
            </a>

            <div class="nav-section-label">Account</div>

            <a href="/campuss/student/profile.php"
               class="nav-item <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">My Profile</span>
            </a>

            <a href="/campuss/student/notifications.php"
               class="nav-item <?= ($currentPage ?? '') === 'notifications' ? 'active' : '' ?>">
                <span class="nav-icon">🔔</span>
                <span class="nav-text">Notifications</span>
                <?php if ($_notifCount > 0): ?>
                    <span class="nav-badge">
                        <?= $_notifCount > 99 ? '99+' : $_notifCount ?>
                    </span>
                <?php endif; ?>
            </a>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>

            <div class="nav-section-label">Main</div>

            <a href="/campuss/staff/dashboard.php"
               class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="/campuss/staff/students.php"
               class="nav-item <?= ($currentPage ?? '') === 'students' ? 'active' : '' ?>">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Students</span>
            </a>

            <a href="/campuss/staff/applications.php"
               class="nav-item <?= ($currentPage ?? '') === 'applications' ? 'active' : '' ?>">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Applications</span>
            </a>

            <a href="/campuss/staff/drive-results.php"
               class="nav-item <?= ($currentPage ?? '') === 'drive-results' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Drive Results</span>
            </a>

            <a href="/campuss/staff/stats.php"
               class="nav-item <?= ($currentPage ?? '') === 'stats' ? 'active' : '' ?>">
                <span class="nav-icon">📈</span>
                <span class="nav-text">Statistics</span>
            </a>

            <a href="/campuss/leaderboard.php"
               class="nav-item <?= ($currentPage ?? '') === 'leaderboard' ? 'active' : '' ?>">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Leaderboard</span>
            </a>

            <div class="nav-section-label">Account</div>


            <?php
            require_once __DIR__ . '/../config/db.php';
            require_once __DIR__ . '/../config/notification_helper.php';
            $staff_id = $_SESSION['user_id'];
            $notif_count = getStaffUnreadCount($conn, $staff_id);
            ?>
            <a href="/campuss/staff/notifications.php"
               class="nav-item <?= ($currentPage ?? '') === 'notifications' ? 'active' : '' ?>">
                <span class="nav-icon">🔔</span>
                <span class="nav-text">Notifications<?php if ($notif_count > 0): ?><span class="notif-badge" style="background:#f59e0b;color:#fff;border-radius:10px;padding:2px 7px;font-size:0.8em;margin-left:6px;vertical-align:middle;display:inline-block;min-width:18px;text-align:center;"><?= $notif_count ?></span><?php endif; ?></span>
            </a>

            <a href="/campuss/staff/profile.php"
               class="nav-item <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">My Profile</span>
            </a>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>

            <div class="nav-section-label">Management</div>

            <a href="/campuss/admin/dashboard.php"
               class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="/campuss/admin/departments.php"
               class="nav-item <?= ($currentPage ?? '') === 'departments' ? 'active' : '' ?>">
                <span class="nav-icon">🏢</span>
                <span class="nav-text">Departments</span>
            </a>

            <a href="/campuss/admin/staff.php"
               class="nav-item <?= ($currentPage ?? '') === 'staff' ? 'active' : '' ?>">
                <span class="nav-icon">👨‍🏫</span>
                <span class="nav-text">Staff</span>
            </a>

            <a href="/campuss/admin/companies.php"
               class="nav-item <?= ($currentPage ?? '') === 'companies' ? 'active' : '' ?>">
                <span class="nav-icon">🏭</span>
                <span class="nav-text">Companies</span>
            </a>

            <a href="/campuss/admin/drives.php"
               class="nav-item <?= ($currentPage ?? '') === 'drives' ? 'active' : '' ?>">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Drives</span>
            </a>

            <div class="nav-section-label">Reports</div>

            <a href="/campuss/admin/applications.php"
               class="nav-item <?= ($currentPage ?? '') === 'applications' ? 'active' : '' ?>">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Applications</span>
            </a>

            <a href="/campuss/admin/announcements.php"
               class="nav-item <?= ($currentPage ?? '') === 'announcements' ? 'active' : '' ?>">
                <span class="nav-icon">📢</span>
                <span class="nav-text">Announcements</span>
            </a>

            <a href="/campuss/admin/reports.php"
               class="nav-item <?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Reports</span>
            </a>

            <a href="/campuss/leaderboard.php"
               class="nav-item <?= ($currentPage ?? '') === 'leaderboard' ? 'active' : '' ?>">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Leaderboard</span>
            </a>

            <div class="nav-section-label">Account</div>

            <a href="/campuss/admin/profile.php"
               class="nav-item <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">My Profile</span>
            </a>

        <?php endif; ?>

    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="/campuss/logout.php" class="logout-btn">
            <span>🚪</span> Logout
        </a>
    </div>

</aside>

<script>
function toggleSidebar() {
    document.getElementById('mainSidebar').classList.toggle('mobile-open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
    document.getElementById('sidebarHamburger').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('mainSidebar').classList.remove('mobile-open');
    document.getElementById('sidebarOverlay').classList.remove('show');
    document.getElementById('sidebarHamburger').classList.remove('open');
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sidebar .nav-item').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
});
</script>

<?php endif; ?>

<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$success = ''; $error = '';

// Create announcements table if not exists
$conn->exec("
    CREATE TABLE IF NOT EXISTS announcements (
        announcement_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        target ENUM('all','students','staff') NOT NULL DEFAULT 'all',
        priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
        created_by INT NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Handle toggle active/inactive
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->prepare("UPDATE announcements SET is_active = NOT is_active WHERE announcement_id = ?")->execute([$id]);
    $success = 'Announcement status updated.';
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $target = $_POST['target'] ?? 'all';
    $priority = $_POST['priority'] ?? 'normal';

    if ($title === '' || $message === '') {
        $error = 'Title and message are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO announcements (title, message, target, priority, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message, $target, $priority, $_SESSION['user_id']]);
        $success = 'Announcement created successfully!';
    }
}

// Fetch all announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Announcements';
$showNav = true;
$currentPage = 'announcements';
include '../includes/header.php';
?>

<div class="main-content-wrapper">
<div class="container">
    <div class="page-header">
        <h2>📢 Announcements</h2>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Create Announcement Form -->
    <div class="card">
        <div class="card-header">Create New Announcement</div>
        <form method="POST">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Announcement title" required maxlength="200">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label>Target Audience</label>
                        <select name="target" class="form-control">
                            <option value="all">Everyone</option>
                            <option value="students">Students Only</option>
                            <option value="staff">Staff Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <option value="normal">Normal</option>
                            <option value="important">Important</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="3" placeholder="Write your announcement here..." required></textarea>
            </div>
            <button type="submit" name="create_announcement" class="btn btn-primary">📢 Post Announcement</button>
        </form>
    </div>

    <!-- Announcements List -->
    <div class="card">
        <div class="card-header">All Announcements <span class="badge badge-info"><?= count($announcements) ?></span></div>
        <?php if (count($announcements) === 0): ?>
            <p class="text-center" style="padding:2rem;color:var(--gray);">No announcements yet.</p>
        <?php else: ?>
            <?php foreach ($announcements as $a): ?>
            <div style="border:1px solid var(--border);border-radius:var(--radius);padding:1rem;margin-bottom:0.75rem;opacity:<?= $a['is_active'] ? '1' : '0.5' ?>;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem;flex-wrap:wrap;">
                            <strong style="font-size:1.05rem;"><?= htmlspecialchars($a['title']) ?></strong>
                            <?php
                                $prClass = $a['priority'] === 'urgent' ? 'badge-danger' : ($a['priority'] === 'important' ? 'badge-warning' : 'badge-secondary');
                                $tgtClass = $a['target'] === 'students' ? 'badge-info' : ($a['target'] === 'staff' ? 'badge-success' : 'badge-secondary');
                            ?>
                            <span class="badge <?= $prClass ?>"><?= ucfirst($a['priority']) ?></span>
                            <span class="badge <?= $tgtClass ?>"><?= $a['target'] === 'all' ? 'Everyone' : ucfirst($a['target']) ?></span>
                            <?php if (!$a['is_active']): ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin:0.35rem 0;color:#374151;font-size:0.9rem;"><?= nl2br(htmlspecialchars($a['message'])) ?></p>
                        <small style="color:var(--gray);">Posted <?= date('d M Y h:i A', strtotime($a['created_at'])) ?></small>
                    </div>
                    <div class="action-btns" style="flex-shrink:0;">
                        <a href="?toggle=<?= $a['announcement_id'] ?>" class="btn btn-sm <?= $a['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                            <?= $a['is_active'] ? 'Disable' : 'Enable' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

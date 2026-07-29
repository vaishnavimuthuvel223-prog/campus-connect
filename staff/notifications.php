<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') { header('Location: login.php'); exit; }
require_once '../config/db.php';
require_once '../config/notification_helper.php';

$staff_id = $_SESSION['user_id'];
$limit = 30;
$offset = 0;

// Fetch notifications for staff from staff_notifications table
$notifications = getStaffNotifications($conn, $staff_id, $limit, $offset);

$pageTitle = 'Notifications';
$showNav = true;
$currentPage = 'notifications';
include '../includes/header.php';
?>
<div class="main-content-wrapper">
<div class="apps-page">
  <h2>Notifications</h2>
  <?php if (count($notifications) === 0): ?>
    <div style="padding:2rem;text-align:center;color:#94a3b8;">No notifications yet.</div>
  <?php else: ?>
    <div class="tbl-wrap">
      <table class="atbl">
        <thead><tr><th>Type</th><th>Title</th><th>Message</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($notifications as $n): ?>
          <tr>
            <td><?= htmlspecialchars($n['type']) ?></td>
            <td><?= htmlspecialchars($n['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($n['message'])) ?></td>
            <td><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</div>
<?php include '../includes/footer.php'; ?>

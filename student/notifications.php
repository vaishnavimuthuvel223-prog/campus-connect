<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/notification_helper.php';

date_default_timezone_set('Asia/Kolkata');

$student_id  = $_SESSION['user_id'];
$studentName = $_SESSION['name'] ?? 'Student';

// ── Action handlers ───────────────────────────────────────────────────────────
if (isset($_GET['mark_all_read'])) {
    markAllNotificationsRead($conn, $student_id);
    header('Location: notifications.php');
    exit;
}
if (isset($_GET['read'], $_GET['redirect'])) {
    markNotificationRead($conn, (int)$_GET['read'], $student_id);
    header('Location: ' . $_GET['redirect']);
    exit;
}
if (isset($_GET['read'])) {
    markNotificationRead($conn, (int)$_GET['read'], $student_id);
    header('Location: notifications.php');
    exit;
}

// ── Pagination ────────────────────────────────────────────────────────────────
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

ensureNotificationsTable($conn);
$totalStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE student_id = ?");
$totalStmt->execute([$student_id]);
$totalNotifications = (int)$totalStmt->fetchColumn();
$totalPages         = max(1, ceil($totalNotifications / $perPage));

$notifications = getNotifications($conn, $student_id, $perPage, $offset);
$unreadCount   = getUnreadCount($conn, $student_id);

$pageTitle   = 'Notifications';
$showNav     = true;
$currentPage = 'notifications';
include '../includes/header.php';

// ── Helpers ───────────────────────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $tz   = new DateTimeZone('Asia/Kolkata');
    $now  = new DateTime('now', $tz);
    $then = new DateTime($datetime, $tz);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' year'  . ($diff->y  > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m  > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'   . ($diff->d  > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'  . ($diff->h  > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min'   . ($diff->i  > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
function exactTime(string $datetime): string {
    return (new DateTime($datetime, new DateTimeZone('Asia/Kolkata')))->format('d M Y, h:i A');
}
function buildMessage(array $n, string $studentName): array {
    $tl = strtolower($n['title']);
    $ml = strtolower($n['message']);
    $company = '';
    if (preg_match('/[—\-]\s*(.+)$/u', $n['title'], $m)) $company = trim($m[1]);
    $round = '';
    if (preg_match('/(round\s*\d+)/i', $n['title'], $m)) $round = $m[1];

    foreach (['selected','cleared','passed','congratulations','placed','offer'] as $kw) {
        if (str_contains($tl,$kw)||str_contains($ml,$kw)) {
            $cp = $company ? " at <strong>".htmlspecialchars($company)."</strong>" : '';
            $rp = $round   ? " You cleared <strong>".htmlspecialchars(ucwords($round))."</strong> successfully." : '';
            return ['message'=>"Dear <strong>".htmlspecialchars($studentName)."</strong>, 🎉 Congratulations! You have been selected".$cp.".".$rp." Our team will reach out with next steps. Best of luck! 🚀",'isSelected'=>true];
        }
    }
    foreach (['not cleared','not successful','unsuccessful','rejected'] as $kw) {
        if (str_contains($ml,$kw)||str_contains($tl,$kw)) {
            $cp = $company ? " for <strong>".htmlspecialchars($company)."</strong>" : '';
            $rp = $round   ? " in <strong>".htmlspecialchars(ucwords($round))."</strong>" : '';
            return ['message'=>"Dear <strong>".htmlspecialchars($studentName)."</strong>, Unfortunately, your application".$cp." was not successful".$rp." this time. Don't be discouraged — keep applying to upcoming drives. You've got this! 💪",'isSelected'=>false];
        }
    }
    return ['message'=>htmlspecialchars($n['message']),'isSelected'=>false];
}
?>

<style>
/* ── Notification cards — scoped styles only ─────────────────────────────── */
.nc-list { display:flex; flex-direction:column; gap:.85rem; }

.nc-card {
    display:flex; align-items:flex-start; gap:1rem;
    background:#fff;
    border:1.5px solid #e5e7eb;
    border-left:4px solid #d1d5db;
    border-radius:14px;
    padding:1.1rem 1.3rem;
    transition:box-shadow .18s, transform .18s;
}
.nc-card:hover { box-shadow:0 6px 22px rgba(0,0,0,.07); transform:translateY(-1px); }
.nc-card.is-unread { border-left-color:var(--nc-a,#4f46e5); background:var(--nc-bg,#fafafe); }

.nc-card.t-success { --nc-a:#16a34a; --nc-bg:#f0fdf4; --nc-ib:rgba(22,163,74,.12); }
.nc-card.t-danger  { --nc-a:#dc2626; --nc-bg:#fef2f2; --nc-ib:rgba(220,38,38,.12); }
.nc-card.t-warning { --nc-a:#d97706; --nc-bg:#fffbeb; --nc-ib:rgba(217,119,6,.12);  }
.nc-card.t-info    { --nc-a:#2563eb; --nc-bg:#eff6ff; --nc-ib:rgba(37,99,235,.12);  }

.nc-icon {
    flex-shrink:0; width:42px; height:42px; border-radius:50%;
    background:var(--nc-ib,rgba(79,70,229,.12));
    display:flex; align-items:center; justify-content:center; font-size:1.15rem;
}
.nc-body { flex:1; min-width:0; }

.nc-row {
    display:flex; align-items:baseline; justify-content:space-between;
    flex-wrap:wrap; gap:.25rem; margin-bottom:.35rem;
}
.nc-title {
    font-weight:700; font-size:.95rem; color:#111827;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%;
}
.nc-time { font-size:.77rem; color:#9ca3af; white-space:nowrap; }
.nc-time abbr { text-decoration:underline dotted; cursor:help; color:inherit; }

.nc-msg { font-size:.88rem; color:#4b5563; line-height:1.6; margin-bottom:.6rem; }
.nc-win {
    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
    border:1px solid #86efac; border-radius:10px;
    padding:.65rem .95rem; font-size:.88rem; color:#166534;
    line-height:1.65; margin-bottom:.6rem;
}
.nc-actions { display:flex; align-items:center; gap:.85rem; flex-wrap:wrap; }
.nc-link {
    font-size:.82rem; font-weight:700; color:var(--nc-a,#4f46e5);
    text-decoration:none; border-bottom:1px solid transparent;
    transition:border-color .15s;
}
.nc-link:hover { border-bottom-color:var(--nc-a,#4f46e5); }
.nc-readbtn {
    font-size:.77rem; color:#6b7280; text-decoration:none;
    padding:.18rem .55rem; border:1px solid #e5e7eb; border-radius:6px;
    transition:background .15s, color .15s;
}
.nc-readbtn:hover { background:#f3f4f6; color:#374151; }

/* Badge / mark-all button — sits inside .page-header */
.nc-controls { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.nc-badge {
    font-size:.78rem; font-weight:700; padding:.3rem .85rem;
    border-radius:999px; background:#4f46e5; color:#fff; white-space:nowrap;
}
.nc-badge.done { background:#64748b; }
.nc-markall {
    font-size:.82rem; font-weight:600; padding:.38rem 1rem;
    border-radius:9px; background:#fff; border:1.5px solid #d1d5db;
    color:#374151; text-decoration:none; white-space:nowrap;
    transition:border-color .15s, color .15s, background .15s;
}
.nc-markall:hover { border-color:#4f46e5; color:#4f46e5; background:#f5f4ff; }

/* Empty state */
.nc-empty {
    text-align:center; padding:3.5rem 1rem; background:#fff;
    border:1.5px dashed #e5e7eb; border-radius:16px;
}
.nc-empty-icon { font-size:3rem; margin-bottom:.75rem; }
.nc-empty h3 { font-size:1rem; font-weight:700; color:#374151; margin:0 0 .3rem; }
.nc-empty p  { font-size:.88rem; color:#9ca3af; margin:0; line-height:1.6; }

/* Pagination */
.nc-pages {
    display:flex; justify-content:center; align-items:center;
    gap:.6rem; margin-top:1.8rem; flex-wrap:wrap;
}
.nc-pages .pg {
    font-size:.84rem; font-weight:600; padding:.38rem 1rem; border-radius:8px;
    border:1.5px solid #d1d5db; background:#fff; color:#374151;
    text-decoration:none; transition:border-color .15s, color .15s;
}
.nc-pages .pg:hover { border-color:#4f46e5; color:#4f46e5; }
.nc-pages .pglabel { font-size:.84rem; color:#9ca3af; }

@media (max-width:600px) {
    .nc-title { max-width:100%; white-space:normal; }
    .nc-row { flex-direction:column; gap:.1rem; }
}
</style>

<!-- Uses the same stud-page › stud-container › page-header pattern as dashboard.php -->
<div class="stud-page">
<div class="stud-container">

    <!-- Page header — same div structure used in every other student page -->
    <div class="page-header">
        <h2>🔔 Notifications</h2>
        <div class="nc-controls">
            <?php if ($unreadCount > 0): ?>
                <a href="?mark_all_read=1" class="nc-markall">✓ Mark All Read</a>
                <span class="nc-badge"><?= $unreadCount ?> unread</span>
            <?php else: ?>
                <span class="nc-badge done">All caught up ✓</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Notification list ──────────────────────────────────── -->
    <?php if (empty($notifications)): ?>

        <div class="nc-empty">
            <div class="nc-empty-icon">🔔</div>
            <h3>No notifications yet</h3>
            <p>You'll be notified when your account is approved,<br>
               you apply for drives, or your application status changes.</p>
        </div>

    <?php else: ?>

        <div class="nc-list">
        <?php foreach ($notifications as $n):
            $tc = match($n['type'] ?? 'info') {
                'success' => 't-success', 'danger' => 't-danger',
                'warning' => 't-warning', default  => 't-info',
            };
            $icons = ['t-success'=>'✅','t-danger'=>'❌','t-warning'=>'⚠️','t-info'=>'ℹ️'];
            $icon     = $icons[$tc] ?? '🔔';
            $isUnread = !(bool)$n['is_read'];
            $built    = buildMessage($n, $studentName);
        ?>
        <div class="nc-card <?= $tc ?> <?= $isUnread ? 'is-unread' : '' ?>">

            <div class="nc-icon"><?= $icon ?></div>

            <div class="nc-body">
                <div class="nc-row">
                    <span class="nc-title" title="<?= htmlspecialchars($n['title']) ?>">
                        <?= htmlspecialchars($n['title']) ?>
                    </span>
                    <span class="nc-time">
                        <abbr title="<?= exactTime($n['created_at']) ?>"><?= timeAgo($n['created_at']) ?></abbr>
                    </span>
                </div>

                <?php if ($built['isSelected']): ?>
                    <div class="nc-win"><?= $built['message'] ?></div>
                <?php else: ?>
                    <div class="nc-msg"><?= $built['message'] ?></div>
                <?php endif; ?>

                <div class="nc-actions">
                    <?php if (!empty($n['link'])): ?>
                        <a href="?read=<?= (int)$n['notification_id'] ?>&redirect=<?= urlencode($n['link']) ?>"
                           class="nc-link">View Details →</a>
                    <?php endif; ?>
                    <?php if ($isUnread): ?>
                        <a href="?read=<?= (int)$n['notification_id'] ?>" class="nc-readbtn">Mark as read</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="nc-pages">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>" class="pg">← Prev</a>
            <?php endif; ?>
            <span class="pglabel">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>" class="pg">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div><!-- /.stud-container -->
</div><!-- /.stud-page -->

<?php include '../includes/footer.php'; ?>

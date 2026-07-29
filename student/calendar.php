<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$student = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student->execute([$student_id]);
$student = $student->fetch();

// Month navigation
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int)date('t', $firstDay);
$startDayOfWeek = (int)date('w', $firstDay);
$monthName = date('F', $firstDay);

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

// Get drives for this month
$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

$stmt = $conn->prepare("
    SELECT dr.drive_id, dr.drive_date, dr.last_date, dr.status, c.company_name, c.package,
           (SELECT COUNT(*) FROM drive_departments WHERE drive_id = dr.drive_id AND dept_id = ?) AS is_eligible
    FROM drives dr JOIN companies c ON dr.company_id = c.company_id
    WHERE dr.drive_date BETWEEN ? AND ?
    ORDER BY dr.drive_date
");
$stmt->execute([$student['dept_id'], $startDate, $endDate]);
$drives = $stmt->fetchAll();

// Map drives by day
$drivesByDay = [];
foreach ($drives as $d) {
    $day = (int)date('j', strtotime($d['drive_date']));
    $drivesByDay[$day][] = $d;
}

// Also get deadline days
$deadlinesByDay = [];
$stmt2 = $conn->prepare("
    SELECT dr.drive_id, dr.last_date, c.company_name
    FROM drives dr JOIN companies c ON dr.company_id = c.company_id
    WHERE dr.last_date BETWEEN ? AND ?
");
$stmt2->execute([$startDate, $endDate]);
foreach ($stmt2->fetchAll() as $dl) {
    $day = (int)date('j', strtotime($dl['last_date']));
    $deadlinesByDay[$day][] = $dl;
}

$today = (int)date('j');
$isCurrentMonth = ($month == (int)date('n') && $year == (int)date('Y'));

$pageTitle = 'Drive Calendar';
$showNav = true;
$currentPage = 'calendar';
include '../includes/header.php';
?>

<div class="main-content-wrapper">
<div class="container">
    <div class="page-header">
        <h2>📅 Drive Calendar</h2>
        <a href="?month=<?= (int)date('n') ?>&year=<?= (int)date('Y') ?>" class="btn btn-primary btn-sm">Today</a>
    </div>

    <div class="card">
        <div class="cal-nav">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-secondary btn-sm">← <?= date('M', mktime(0,0,0,$prevMonth,1,$prevYear)) ?></a>
            <h3 class="cal-title"><?= $monthName ?> <?= $year ?></h3>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-secondary btn-sm"><?= date('M', mktime(0,0,0,$nextMonth,1,$nextYear)) ?> →</a>
        </div>

        <div class="cal-legend">
            <span><span class="cal-dot cal-dot-upcoming"></span> Upcoming</span>
            <span><span class="cal-dot cal-dot-ongoing"></span> Ongoing</span>
            <span><span class="cal-dot cal-dot-completed"></span> Completed</span>
            <span><span class="cal-dot cal-dot-deadline"></span> Deadline</span>
            <span><span style="color:var(--success);font-weight:700;">✓</span> You're eligible</span>
        </div>

        <div class="cal-grid">
            <div class="cal-day-header">Sun</div>
            <div class="cal-day-header">Mon</div>
            <div class="cal-day-header">Tue</div>
            <div class="cal-day-header">Wed</div>
            <div class="cal-day-header">Thu</div>
            <div class="cal-day-header">Fri</div>
            <div class="cal-day-header">Sat</div>

            <?php for ($i = 0; $i < $startDayOfWeek; $i++): ?>
                <div class="cal-cell cal-empty"></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                $hasDrive = isset($drivesByDay[$day]);
                $hasDeadline = isset($deadlinesByDay[$day]);
                $isToday = $isCurrentMonth && $day === $today;
            ?>
                <div class="cal-cell <?= $isToday ? 'cal-today' : '' ?> <?= $hasDrive ? 'cal-has-event' : '' ?>">
                    <span class="cal-day-num <?= $isToday ? 'cal-today-num' : '' ?>"><?= $day ?></span>
                    <?php if ($hasDrive): foreach ($drivesByDay[$day] as $d): ?>
                        <div class="cal-event cal-event-<?= $d['status'] ?>" title="<?= htmlspecialchars($d['company_name']) ?> — <?= htmlspecialchars($d['package']) ?>">
                            <?= htmlspecialchars($d['company_name']) ?>
                            <?php if ($d['is_eligible']): ?><span class="cal-eligible">✓</span><?php endif; ?>
                        </div>
                    <?php endforeach; endif; ?>
                    <?php if ($hasDeadline): foreach ($deadlinesByDay[$day] as $dl): ?>
                        <div class="cal-event cal-event-deadline" title="Deadline: <?= htmlspecialchars($dl['company_name']) ?>">
                            ⏰ <?= htmlspecialchars($dl['company_name']) ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            <?php endfor; ?>

            <?php
            $totalCells = $startDayOfWeek + $daysInMonth;
            $remaining = (7 - ($totalCells % 7)) % 7;
            for ($i = 0; $i < $remaining; $i++): ?>
                <div class="cal-cell cal-empty"></div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Drives Table for this month -->
    <?php if (count($drives) > 0): ?>
    <div class="card mt-2">
        <div class="card-header">📋 Drives in <?= $monthName ?> <?= $year ?></div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Company</th><th>Package</th><th>Drive Date</th><th>Deadline</th><th>Status</th><th>Eligible</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($drives as $d): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($d['company_name']) ?></strong></td>
                        <td><?= htmlspecialchars($d['package']) ?></td>
                        <td><?= date('d M Y', strtotime($d['drive_date'])) ?></td>
                        <td><?= $d['last_date'] ? date('d M Y', strtotime($d['last_date'])) : '—' ?></td>
                        <td><span class="badge badge-<?= $d['status'] === 'upcoming' ? 'info' : ($d['status'] === 'ongoing' ? 'success' : 'secondary') ?>"><?= ucfirst($d['status']) ?></span></td>
                        <td><?= $d['is_eligible'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card mt-2 text-center" style="padding:2rem;">
        <p style="color:var(--gray);">No drives scheduled for <?= $monthName ?> <?= $year ?>.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

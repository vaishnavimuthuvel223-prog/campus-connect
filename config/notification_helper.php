<?php
/**
 * In-App Notification System
 * Stores notifications in the database — always works, no external dependencies.
 */

/**
 * Auto-create notifications table if it doesn't exist
 */
function ensureNotificationsTable($conn) {
    static $checked = false;
    if ($checked) return;
    $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'info',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $checked = true;
}

/**
 * Create a new in-app notification
 */
function createNotification($conn, $studentId, $type, $title, $message, $link = null) {
    ensureNotificationsTable($conn);
    $stmt = $conn->prepare("INSERT INTO notifications (student_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$studentId, $type, $title, $message, $link]);
}

/**
 * Get unread notification count for a student
 */
function getUnreadCount($conn, $studentId) {
    ensureNotificationsTable($conn);
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE student_id = ? AND is_read = 0");
    $stmt->execute([$studentId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get notifications for a student (paginated)
 */
function getNotifications($conn, $studentId, $limit = 20, $offset = 0) {
    ensureNotificationsTable($conn);
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $studentId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Mark a single notification as read
 */
function markNotificationRead($conn, $notificationId, $studentId) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND student_id = ?");
    return $stmt->execute([$notificationId, $studentId]);
}

/**
 * Mark all notifications as read for a student
 */
function markAllNotificationsRead($conn, $studentId) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0");
    return $stmt->execute([$studentId]);
}

// =============================================
// Pre-built notification creators (in-app)
// =============================================

function notifyInApp_AccountApproved($conn, $studentId, $studentName) {
    return createNotification($conn, $studentId, 'success',
        'Account Approved ✅',
        "Great news, {$studentName}! Your account has been verified and approved. You can now apply for placement drives.",
        '/campuss/student/drives.php'
    );
}

function notifyInApp_AccountRejected($conn, $studentId, $studentName) {
    return createNotification($conn, $studentId, 'danger',
        'Account Not Approved ❌',
        "Dear {$studentName}, your account verification has been rejected. Please contact your department staff for details.",
        null
    );
}

function notifyInApp_Selected($conn, $studentId, $studentName, $companyName, $package) {
    return createNotification($conn, $studentId, 'success',
        "🎉 Selected by {$companyName}!",
        "Congratulations {$studentName}! You have been selected by {$companyName} with a package of {$package}. Check your results for details.",
        '/campuss/student/results.php'
    );
}

function notifyInApp_RejectedDrive($conn, $studentId, $studentName, $companyName) {
    return createNotification($conn, $studentId, 'warning',
        "Drive Result — {$companyName}",
        "Dear {$studentName}, your application for {$companyName} was not successful this time. Keep applying to upcoming drives!",
        '/campuss/student/drives.php'
    );
}

function notifyInApp_RoundUpdate($conn, $studentId, $studentName, $companyName, $roundLabel, $status) {
    $passed = ($status === 'pass');
    $icon = $passed ? '✅' : '❌';
    $statusText = $passed ? 'Passed' : 'Not Cleared';
    return createNotification($conn, $studentId, $passed ? 'success' : 'danger',
        "{$roundLabel} Result {$icon} — {$companyName}",
        "Dear {$studentName}, your {$roundLabel} result for {$companyName}: {$statusText}." .
        ($passed ? ' Prepare well for the next round!' : ' Keep your spirits up and continue applying!'),
        '/campuss/student/results.php'
    );
}

function notifyInApp_Applied($conn, $studentId, $studentName, $companyName, $driveDate) {
    return createNotification($conn, $studentId, 'info',
        "Application Submitted — {$companyName}",
        "Dear {$studentName}, your application for {$companyName} (Drive: {$driveDate}) has been submitted successfully. Stay tuned for updates!",
        '/campuss/student/results.php'
    );
}

function notifyInApp_StaffApproved($conn, $adminId, $studentName, $companyName) {
    // Notify admin (adminId) that a student application was approved by staff
    // You may want to use a separate admin notifications table or extend this for admin
    // For now, this is a placeholder for extensibility
}

// Staff notification system (reuses notifications table, using staff_id as student_id for staff)
function ensureStaffNotificationsTable($conn) {
    static $staffChecked = false;
    if ($staffChecked) return;
    $conn->exec("CREATE TABLE IF NOT EXISTS staff_notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'info',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (staff_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $staffChecked = true;
}

function createStaffNotification($conn, $staffId, $type, $title, $message, $link = null) {
    ensureStaffNotificationsTable($conn);
    $stmt = $conn->prepare("INSERT INTO staff_notifications (staff_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$staffId, $type, $title, $message, $link]);
}

function getStaffUnreadCount($conn, $staffId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM staff_notifications WHERE staff_id = ? AND is_read = 0");
    $stmt->execute([$staffId]);
    return (int)$stmt->fetchColumn();
}

function getStaffNotifications($conn, $staffId, $limit = 20, $offset = 0) {
    $stmt = $conn->prepare("SELECT * FROM staff_notifications WHERE staff_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function markStaffNotificationRead($conn, $notificationId, $staffId) {
    $stmt = $conn->prepare("UPDATE staff_notifications SET is_read = 1 WHERE notification_id = ? AND staff_id = ?");
    return $stmt->execute([$notificationId, $staffId]);
}

function markAllStaffNotificationsRead($conn, $staffId) {
    $stmt = $conn->prepare("UPDATE staff_notifications SET is_read = 1 WHERE staff_id = ? AND is_read = 0");
    return $stmt->execute([$staffId]);
}

function notifyInApp_StaffNewApplication($conn, $deptId, $studentName, $companyName) {
    // Notify all staff in the department about a new application
    $staffStmt = $conn->prepare("SELECT staff_id FROM department_staff WHERE dept_id = ?");
    $staffStmt->execute([$deptId]);
    foreach ($staffStmt->fetchAll() as $staff) {
        createStaffNotification(
            $conn,
            $staff['staff_id'],
            'info',
            'New Application Pending Approval',
            "Student {$studentName} has applied for {$companyName}. Please review and approve/reject the application.",
            '/campuss/staff/applications.php'
        );
    }
}

function notifyInApp_StaffResultUpdate($conn, $staffId, $studentName, $companyName, $result) {
    // Notify staff when a student from their department is selected/rejected
    $type = $result === 'selected' ? 'success' : 'warning';
    $title = $result === 'selected' ? "Student Selected: {$studentName}" : "Student Not Selected: {$studentName}";
    $message = $result === 'selected'
        ? "{$studentName} from your department has been selected by {$companyName}."
        : "{$studentName} from your department was not selected by {$companyName}.";
    $link = '/campuss/staff/stats.php';
    return createStaffNotification($conn, $staffId, $type, $title, $message, $link);
}

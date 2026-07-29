<?php
/**
 * Notifications API Endpoint
 * Handles: get count, mark read, mark all read
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/db.php';
require_once '../config/notification_helper.php';

$student_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'count':
        echo json_encode(['count' => getUnreadCount($conn, $student_id)]);
        break;

    case 'mark_read':
        $id = (int)($_POST['notification_id'] ?? 0);
        if ($id > 0) {
            markNotificationRead($conn, $id, $student_id);
            echo json_encode(['success' => true, 'count' => getUnreadCount($conn, $student_id)]);
        } else {
            echo json_encode(['error' => 'Invalid ID']);
        }
        break;

    case 'mark_all_read':
        markAllNotificationsRead($conn, $student_id);
        echo json_encode(['success' => true, 'count' => 0]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}

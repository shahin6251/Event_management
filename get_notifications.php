<?php
/**
 * get_notifications.php
 * API endpoint to fetch customer notifications
 * Returns unread notifications for the logged-in customer
 */

session_start();
include 'db.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$customer_id = $_SESSION['user_id'];

// Get action from query string
$action = $_GET['action'] ?? '';

if ($action === 'get_unread') {
    // Get count of unread notifications
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM order_notifications 
        WHERE customer_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'unread_count' => $row['unread_count']]);
    $stmt->close();
    
} elseif ($action === 'get_all') {
    // Get all notifications with order details
    $stmt = $conn->prepare("
        SELECT 
            on_table.id,
            on_table.order_id,
            on_table.status,
            on_table.is_read,
            on_table.created_at,
            o.event_details,
            o.event_date,
            u.name as organizer_name
        FROM order_notifications on_table
        JOIN orders o ON on_table.order_id = o.order_id
        JOIN users u ON o.organizer_id = u.user_id
        WHERE on_table.customer_id = ?
        ORDER BY on_table.created_at DESC
        LIMIT 50
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $event_details = json_decode($row['event_details'], true);
        $notifications[] = [
            'id' => $row['id'],
            'order_id' => $row['order_id'],
            'status' => $row['status'],
            'is_read' => $row['is_read'],
            'created_at' => $row['created_at'],
            'event_type' => $event_details['event_type'] ?? 'Event',
            'event_date' => $row['event_date'],
            'organizer_name' => $row['organizer_name']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    $stmt->close();
    
} elseif ($action === 'mark_read') {
    // Mark a notification as read
    $notification_id = (int)($_GET['id'] ?? 0);
    
    if ($notification_id > 0) {
        $stmt = $conn->prepare("
            UPDATE order_notifications 
            SET is_read = 1 
            WHERE id = ? AND customer_id = ?
        ");
        $stmt->bind_param("ii", $notification_id, $customer_id);
        $success = $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        $stmt->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    }
    
} elseif ($action === 'mark_all_read') {
    // Mark all notifications as read
    $stmt = $conn->prepare("
        UPDATE order_notifications 
        SET is_read = 1 
        WHERE customer_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $customer_id);
    $success = $stmt->execute();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    $stmt->close();
}

$conn->close();
?>

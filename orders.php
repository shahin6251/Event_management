<?php
/*
 * order_track_record.php
 * Organizer's page to view and manage customer orders.
 */

// --- 1. Include the Gatekeeper ---
// This file checks if the user is logged in
include 'check_session.php';

// --- 2. Check for Specific Role ---
// This function is inside 'check_session.php'
require_role('organizer');

// --- 3. Include Database ---
include 'db.php';

// --- 4. Handle status updates ---
$update_success = null;
$update_status = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['orderId']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['orderId'];
    $status = $_POST['status'];
    
    // First, get the customer_id for this order
    $get_customer_stmt = $conn->prepare("SELECT customer_id FROM orders WHERE order_id = ? AND organizer_id = ?");
    $get_customer_stmt->bind_param("ii", $orderId, $user_id);
    $get_customer_stmt->execute();
    $result = $get_customer_stmt->get_result();
    $order_data = $result->fetch_assoc();
    $get_customer_stmt->close();
    
    if ($order_data) {
        $customer_id = $order_data['customer_id'];
        
        // Update the order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND organizer_id = ?");
        $stmt->bind_param("sii", $status, $orderId, $user_id);
        $update_success = $stmt->execute();
        $stmt->close();
        
        // Create notification for the customer
        if ($update_success && in_array($status, ['approved', 'rejected'])) {
            error_log("Creating notification for customer $customer_id, order $orderId, status $status");
            
            // Check if a notification already exists for this order and status
            $check_stmt = $conn->prepare("SELECT id FROM order_notifications WHERE order_id = ? AND customer_id = ? AND status = ?");
            $check_stmt->bind_param("iis", $orderId, $customer_id, $status);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $existing = $check_result->fetch_assoc();
            $check_stmt->close();
            
            if (!$existing) {
                $notif_stmt = $conn->prepare("INSERT INTO order_notifications (order_id, customer_id, status) VALUES (?, ?, ?)");
                $notif_stmt->bind_param("iis", $orderId, $customer_id, $status);
                $notif_success = $notif_stmt->execute();
                error_log("Notification creation success: " . ($notif_success ? 'true' : 'false'));
                if (!$notif_success) {
                    error_log("Notification error: " . $notif_stmt->error);
                }
                $notif_stmt->close();
            } else {
                error_log("Notification already exists for order $orderId, customer $customer_id, status $status");
            }
        }
        
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(["success" => $update_success, "status" => $status]);
            exit;
        }
        $update_status = $status;
    } else {
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(["success" => false, "error" => "Order not found"]);
            exit;
        }
    }
}

// --- 5. Fetch all orders for this organizer (for page load) ---
$orders = [];

// --- CORRECTED SELECT QUERY ---
// This joins 'orders' (for event details) with 'users' (to get customer name)
// It filters where 'organizer_id' is the logged-in user.
$stmt = $conn->prepare("SELECT o.order_id, o.event_details, o.event_date, o.status, u.name AS customer_name
                       FROM orders o
                       JOIN users u ON o.customer_id = u.user_id
                       WHERE o.organizer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Orders</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">Customer Orders</h1>
      <a href="organizer_dashboard.php" class="text-gray-700 hover:text-gray-900">← Back to Dashboard</a>
    </div>
  </header>

  <main class="max-w-6xl mx-auto p-4">
  
    <!-- This div is for showing success or error messages -->
    <div id="message-container" class="hidden mb-4">
      <div id="message-content" class="rounded-md p-4 text-sm"></div>
    </div>
  
    <?php if ($update_success !== null): ?>
      <div class="mb-4 px-4 py-3 rounded-md <?php echo $update_success ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
        <?php echo $update_success ? 'Status updated to ' . htmlspecialchars($update_status) : 'Failed to update status'; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-600">You have no orders yet.</div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($orders as $o): ?>
          <?php $details = json_decode($o['event_details'] ?? '', true); ?>
          <?php
            $type = isset($details['event_type']) ? $details['event_type'] : '';
            $loc = isset($details['location']) ? $details['location'] : '';
            $guests = isset($details['guest_count']) ? (int)$details['guest_count'] : null;
            $budget = isset($details['budget']) ? $details['budget'] : '';
            $time = isset($details['event_time']) ? $details['event_time'] : '';
            $desc = isset($details['description']) ? $details['description'] : '';
            $status = strtolower($o['status']);
            $badgeClass = 'bg-gray-100 text-gray-800 border border-gray-200';
            if ($status === 'approved') { $badgeClass = 'bg-green-100 text-green-800 border border-green-200'; }
            elseif ($status === 'rejected') { $badgeClass = 'bg-red-100 text-red-800 border border-red-200'; }
          ?>
          <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex flex-col">
            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="text-sm text-gray-500">Order #<?php echo htmlspecialchars($o['order_id']); ?></div>
                <div class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($o['customer_name']); ?></div>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($o['status'])); ?></span>
            </div>
            <div class="space-y-2 text-sm text-gray-700">
              <?php if ($type): ?><div><span class="font-medium">Type:</span> <?php echo htmlspecialchars($type); ?></div><?php endif; ?>
              <div><span class="font-medium">Date:</span> <?php echo htmlspecialchars($o['event_date']); ?><?php echo $time ? ' · ' . htmlspecialchars($time) : ''; ?></div>
              <?php if ($loc): ?><div><span class="font-medium">Location:</span> <?php echo htmlspecialchars($loc); ?></div><?php endif; ?>
              <?php if ($guests): ?><div><span class="font-medium">Guests:</span> <?php echo htmlspecialchars($guests); ?></div><?php endif; ?>
              <?php if ($budget): ?><div><span class="font-medium">Budget:</span> <?php echo htmlspecialchars($budget); ?></div><?php endif; ?>
              <?php if ($desc): ?><div class="text-gray-600"><span class="font-medium">Notes:</span> <?php echo htmlspecialchars(mb_strimwidth($desc, 0, 120, '…')); ?></div><?php endif; ?>
            </div>
            <div class="mt-4 flex items-center gap-3">
              <form method="POST">
                <input type="hidden" name="orderId" value="<?php echo htmlspecialchars($o['order_id']); ?>">
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
              </form>
              <form method="POST">
                <input type="hidden" name="orderId" value="<?php echo htmlspecialchars($o['order_id']); ?>">
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  
</body>
</html>

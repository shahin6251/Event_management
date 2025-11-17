<?php
/**
 * customer_dashboard.php
 * Customer dashboard with integrated notification system
 */

session_start();
include 'check_session.php';
require_role('customer');
include 'db.php';

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['name'] ?? 'Customer';

// Get customer's orders
$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.event_details,
        o.event_date,
        o.status,
        u.name as organizer_name,
        op.page_title as organizer_page
    FROM orders o
    JOIN users u ON o.organizer_id = u.user_id
    LEFT JOIN organizer_pages op ON op.user_id = u.user_id
    WHERE o.customer_id = ?
    ORDER BY o.order_id DESC
    LIMIT 10
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Get unread notification count
$notif_stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM order_notifications 
    WHERE customer_id = ? AND is_read = 0
");
$notif_stmt->bind_param("i", $customer_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$unread_count = $notif_row['unread_count'];
$notif_stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="notifications.css">
</head>
<body class="bg-gray-50">
  <!-- Header with Notification System -->
  <header class="bg-white shadow-sm border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($customer_name); ?></p>
      </div>
      
      <div class="flex items-center gap-4">
        <!-- Notification System Component -->
        <?php include 'notification_component.php'; ?>
        
        <!-- User Menu -->
        <div class="flex items-center gap-3">
          <a href="profile.php" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium">Profile</a>
          <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-8 text-white">
        <h2 class="text-3xl font-bold mb-2">Welcome to Your Dashboard</h2>
        <p class="text-blue-100">Manage your orders and track their status in real-time</p>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Total Orders</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo count($orders); ?></p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Pending Orders</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">
              <?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?>
            </p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Unread Notifications</p>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $unread_count; ?></p>
          </div>
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
      </div>
      
      <?php if (empty($orders)): ?>
        <div class="p-8 text-center text-gray-500">
          <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
          </svg>
          <p class="text-lg font-medium">No orders yet</p>
          <p class="text-sm mt-1">Start by placing an order with an organizer</p>
          <a href="index.php" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Browse Organizers</a>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Order ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Organizer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Event Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <?php 
                  $status = strtolower($order['status']);
                  $statusClass = 'bg-gray-100 text-gray-800';
                  if ($status === 'approved') $statusClass = 'bg-green-100 text-green-800';
                  elseif ($status === 'rejected') $statusClass = 'bg-red-100 text-red-800';
                  elseif ($status === 'pending') $statusClass = 'bg-yellow-100 text-yellow-800';
                ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?php echo $order['order_id']; ?></td>
                  <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($order['organizer_page'] ?: $order['organizer_name']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-700"><?php echo $order['event_date']; ?></td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                      <?php echo ucfirst($status); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Scripts -->
  <script src="notifications.js"></script>
</body>
</html>

<?php
require_once 'check_session.php';
require_once 'db.php';
require_role('admin');

$message = '';
$error = '';

// Handle user deletion
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $user_id_to_delete = (int)$_GET['delete_user'];
    
    // Prevent admin from deleting themselves
    if ($user_id_to_delete === $_SESSION['user_id']) {
        $error = 'You cannot delete your own account.';
    } else {
        // Delete user
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $delete_stmt->bind_param("i", $user_id_to_delete);
        if ($delete_stmt->execute()) {
            $message = 'User deleted successfully.';
        } else {
            $error = 'Error deleting user.';
        }
        $delete_stmt->close();
    }
}

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$stats['total_customers'] = $result->fetch_assoc()['total'];

// Total organizers
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'organizer'");
$stats['total_organizers'] = $result->fetch_assoc()['total'];

// Total orders (if table exists)
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['total'];
} catch (Exception $e) {
    $stats['total_orders'] = 0;
}

// Get all users (not just recent)
$all_users = [];
$result = $conn->query("SELECT user_id, name, email, role FROM users ORDER BY user_id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_users[] = $row;
    }
}

// Recent users
$recent_users = array_slice($all_users, 0, 5);

$orders = [];
$stmt = $conn->prepare("SELECT o.order_id, o.event_details, o.event_date, o.status,
                               cu.name AS customer_name, cu.email AS customer_email,
                               og.user_id AS organizer_id, og.name AS organizer_name,
                               op.page_title AS organizer_page
                        FROM orders o
                        JOIN users cu ON o.customer_id = cu.user_id
                        JOIN users og ON o.organizer_id = og.user_id
                        LEFT JOIN organizer_pages op ON op.user_id = og.user_id
                        ORDER BY o.order_id DESC
                        LIMIT 12");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | Event Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body class="bg-gray-100 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="text-gray-600 mt-1">Manage your event management system</p>
        </div>
        <nav class="flex space-x-4">
          <a href="index.php" class="text-blue-600 hover:text-blue-800 font-medium">🏠 Home</a>
          <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium">🚪 Logout</a>
        </nav>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Messages -->
    <?php if ($message): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
        ✓ <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
        ✗ <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="mb-8">
      <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <h2 class="text-2xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?>! 👋</h2>
        <p class="text-purple-100">Here's what's happening with your platform today.</p>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="stat-card rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white text-opacity-80 text-sm font-medium">Total Users</p>
            <p class="text-3xl font-bold"><?php echo $stats['total_users']; ?></p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-full p-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="stat-card rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white text-opacity-80 text-sm font-medium">Customers</p>
            <p class="text-3xl font-bold"><?php echo $stats['total_customers']; ?></p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-full p-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="stat-card rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white text-opacity-80 text-sm font-medium">Organizers</p>
            <p class="text-3xl font-bold"><?php echo $stats['total_organizers']; ?></p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-full p-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="stat-card rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white text-opacity-80 text-sm font-medium">Total Orders</p>
            <p class="text-3xl font-bold"><?php echo $stats['total_orders']; ?></p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-full p-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-4">
          <button onclick="openRecentOrdersModal()" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200 cursor-pointer">
            <div class="bg-blue-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">Recent Orders</p>
              <p class="text-sm text-gray-600">View all orders</p>
            </div>
          </button>

          <button onclick="openUserManagementModal()" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200 cursor-pointer">
            <div class="bg-purple-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">User Management</p>
              <p class="text-sm text-gray-600">Manage users</p>
            </div>
          </button>

          <a href="list.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
            <div class="bg-blue-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">View Organizers</p>
              <p class="text-sm text-gray-600">Browse all organizers</p>
            </div>
          </a>

          <a href="role_select.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
            <div class="bg-green-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">Add Users</p>
              <p class="text-sm text-gray-600">Create new accounts</p>
            </div>
          </a>

          <a href="index.php" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
            <div class="bg-purple-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">View Site</p>
              <p class="text-sm text-gray-600">Go to main site</p>
            </div>
          </a>

          <a href="login.php" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition duration-200">
            <div class="bg-orange-500 rounded-full p-2 mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">Login Page</p>
              <p class="text-sm text-gray-600">Access login form</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Recent Users -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Recent Users</h3>
        <div class="space-y-4">
          <?php if (empty($recent_users)): ?>
            <p class="text-gray-500 text-center py-4">No users found</p>
          <?php else: ?>
            <?php foreach ($recent_users as $user): ?>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                  <div class="bg-<?php echo $user['role'] === 'organizer' ? 'blue' : ($user['role'] === 'admin' ? 'red' : 'green'); ?>-500 rounded-full p-2 mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                  </div>
                </div>
                <div class="text-right">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $user['role'] === 'organizer' ? 'blue' : ($user['role'] === 'admin' ? 'red' : 'green'); ?>-100 text-<?php echo $user['role'] === 'organizer' ? 'blue' : ($user['role'] === 'admin' ? 'red' : 'green'); ?>-800">
                    <?php echo ucfirst($user['role']); ?>
                  </span>
                  <p class="text-xs text-gray-500 mt-1"><?php echo $user['join_date'] ?? 'Unknown'; ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- System Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h3 class="text-xl font-bold text-gray-900 mb-4">System Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
          <div class="bg-blue-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h4 class="font-semibold text-gray-900">System Status</h4>
          <p class="text-green-600 font-medium">Online</p>
        </div>
        <div class="text-center">
          <div class="bg-green-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
            </svg>
          </div>
          <h4 class="font-semibold text-gray-900">Database</h4>
          <p class="text-green-600 font-medium">Connected</p>
        </div>
        <div class="text-center">
          <div class="bg-purple-100 rounded-full p-4 w-16 h-16 mx-auto mb-3 flex items-center justify-center">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
          </div>
          <h4 class="font-semibold text-gray-900">Security</h4>
          <p class="text-green-600 font-medium">Secure</p>
        </div>
      </div>
    </div>

    <div class="mt-8">
      <h3 class="text-2xl font-bold text-gray-900 mb-4">Recent Orders</h3>
      <?php if (empty($orders)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-600">No orders found</div>
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
                  <div class="text-lg font-semibold text-gray-900">Customer: <?php echo htmlspecialchars($o['customer_name']); ?></div>
                  <div class="text-sm text-gray-700">Organizer: <?php echo htmlspecialchars($o['organizer_page'] ?: $o['organizer_name']); ?></div>
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
              <div class="mt-4">
                <a href="organizer_view.php?id=<?php echo (int)$o['organizer_id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Organizer →</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </main>

  <!-- Recent Orders Modal -->
  <div id="recentOrdersModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-96 overflow-y-auto">
      <div class="sticky top-0 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-gray-200 p-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Recent Orders</h2>
        <button onclick="closeRecentOrdersModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      
      <div class="p-6">
        <?php if (empty($orders)): ?>
          <div class="text-center py-8 text-gray-500">No orders found</div>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($orders as $o): ?>
              <?php $details = json_decode($o['event_details'] ?? '', true); ?>
              <?php
                $type = isset($details['event_type']) ? $details['event_type'] : '';
                $loc = isset($details['location']) ? $details['location'] : '';
                $guests = isset($details['guest_count']) ? (int)$details['guest_count'] : null;
                $budget = isset($details['budget']) ? $details['budget'] : '';
                $status = strtolower($o['status']);
                $badgeClass = 'bg-gray-100 text-gray-800';
                if ($status === 'approved') { $badgeClass = 'bg-green-100 text-green-800'; }
                elseif ($status === 'rejected') { $badgeClass = 'bg-red-100 text-red-800'; }
                elseif ($status === 'pending') { $badgeClass = 'bg-yellow-100 text-yellow-800'; }
              ?>
              <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                  <div>
                    <p class="font-semibold text-gray-900">Order #<?php echo htmlspecialchars($o['order_id']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($o['customer_name']); ?> → <?php echo htmlspecialchars($o['organizer_name']); ?></p>
                  </div>
                  <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $badgeClass; ?>">
                    <?php echo ucfirst($status); ?>
                  </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-gray-600">
                  <?php if ($type): ?><div><strong>Type:</strong> <?php echo htmlspecialchars($type); ?></div><?php endif; ?>
                  <div><strong>Date:</strong> <?php echo htmlspecialchars($o['event_date']); ?></div>
                  <?php if ($loc): ?><div><strong>Location:</strong> <?php echo htmlspecialchars($loc); ?></div><?php endif; ?>
                  <?php if ($guests): ?><div><strong>Guests:</strong> <?php echo htmlspecialchars($guests); ?></div><?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- User Management Modal -->
  <div id="userManagementModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-96 overflow-y-auto">
      <div class="sticky top-0 bg-gradient-to-r from-purple-50 to-blue-50 border-b border-gray-200 p-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
        <button onclick="closeUserManagementModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      
      <div class="p-6">
        <!-- Filter Tabs -->
        <div class="flex gap-4 mb-6 border-b border-gray-200">
          <button onclick="filterUsersInModal('all')" class="filter-tab-modal active px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600">All Users (<?php echo count($all_users); ?>)</button>
          <button onclick="filterUsersInModal('customer')" class="filter-tab-modal px-4 py-2 font-medium text-gray-600 hover:text-gray-900">Customers (<?php echo $stats['total_customers']; ?>)</button>
          <button onclick="filterUsersInModal('organizer')" class="filter-tab-modal px-4 py-2 font-medium text-gray-600 hover:text-gray-900">Organizers (<?php echo $stats['total_organizers']; ?>)</button>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Role</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php if (empty($all_users)): ?>
                <tr>
                  <td colspan="4" class="px-4 py-8 text-center text-gray-500">No users found</td>
                </tr>
              <?php else: ?>
                <?php foreach ($all_users as $user): ?>
                  <tr class="user-row-modal hover:bg-gray-50 transition" data-role="<?php echo htmlspecialchars($user['role']); ?>">
                    <td class="px-4 py-3">
                      <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-sm font-semibold">
                          <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span class="ml-2 font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></span>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                        <?php 
                          if ($user['role'] === 'organizer') echo 'bg-blue-100 text-blue-800';
                          elseif ($user['role'] === 'customer') echo 'bg-green-100 text-green-800';
                          else echo 'bg-red-100 text-red-800';
                        ?>">
                        <?php echo ucfirst($user['role']); ?>
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                        <button onclick="confirmDelete(<?php echo (int)$user['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($user['name'])); ?>')" 
                                class="px-3 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 font-medium text-xs transition">
                          🗑️ Remove
                        </button>
                      <?php else: ?>
                        <span class="text-gray-400 text-xs">You (Admin)</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    function openRecentOrdersModal() {
      document.getElementById('recentOrdersModal').classList.remove('hidden');
    }

    function closeRecentOrdersModal() {
      document.getElementById('recentOrdersModal').classList.add('hidden');
    }

    function openUserManagementModal() {
      document.getElementById('userManagementModal').classList.remove('hidden');
    }

    function closeUserManagementModal() {
      document.getElementById('userManagementModal').classList.add('hidden');
    }

    function filterUsersInModal(role) {
      // Update active tab
      document.querySelectorAll('.filter-tab-modal').forEach(tab => {
        tab.classList.remove('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
        tab.classList.add('text-gray-600', 'hover:text-gray-900');
      });
      event.target.classList.add('active', 'text-blue-600', 'border-b-2', 'border-blue-600');
      event.target.classList.remove('text-gray-600', 'hover:text-gray-900');

      // Filter rows
      document.querySelectorAll('.user-row-modal').forEach(row => {
        if (role === 'all' || row.dataset.role === role) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    function confirmDelete(userId, userName) {
      if (confirm(`Are you sure you want to delete ${userName}? This action cannot be undone.`)) {
        window.location.href = `admin_dashboard.php?delete_user=${userId}`;
      }
    }

    // Close modal when clicking outside
    document.getElementById('recentOrdersModal').addEventListener('click', function(e) {
      if (e.target === this) closeRecentOrdersModal();
    });

    document.getElementById('userManagementModal').addEventListener('click', function(e) {
      if (e.target === this) closeUserManagementModal();
    });
  </script>
</body>
</html>


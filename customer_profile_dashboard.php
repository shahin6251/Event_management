<?php
/**
 * customer_profile_dashboard.php
 * Beautiful customer profile and dashboard page
 */

session_start();
include 'check_session.php';
require_role('customer');
include 'db.php';

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['name'] ?? 'Customer';
$customer_email = $_SESSION['email'] ?? '';
$customer_profile_pic = '';

// Get customer's profile picture
$pic_stmt = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
$pic_stmt->bind_param("i", $customer_id);
$pic_stmt->execute();
$pic_result = $pic_stmt->get_result();
$pic_row = $pic_result->fetch_assoc();
if ($pic_row && !empty($pic_row['profile_pic']) && file_exists($pic_row['profile_pic'])) {
    $customer_profile_pic = $pic_row['profile_pic'];
}
$pic_stmt->close();

// Get customer's orders with stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_orders,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_orders
    FROM orders
    WHERE customer_id = ?
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get recent orders
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

// Get unread notifications
$notif_stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM order_notifications 
    WHERE customer_id = ? AND is_read = 0
");
$notif_stmt->bind_param("i", $customer_id);
$notif_stmt->execute();
$unread = $notif_stmt->get_result()->fetch_assoc();
$unread_count = $unread['unread_count'];
$notif_stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Dashboard - Event Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="notifications.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 0;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
      position: sticky;
      top: 0;
      z-index: 50;
    }
    
    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .header h1 {
      font-size: 28px;
      font-weight: 700;
    }
    
    .header-actions {
      display: flex;
      gap: 15px;
      align-items: center;
    }
    
    .header-actions a, .header-actions button {
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      font-size: 14px;
    }
    
    .btn-primary {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .btn-primary:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
    }
    
    .btn-danger {
      background: #ef4444;
      color: white;
    }
    
    .btn-danger:hover {
      background: #dc2626;
      transform: translateY(-2px);
    }
    
    .main-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    
    .profile-card {
      background: white;
      border-radius: 16px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(102, 126, 234, 0.1);
    }
    
    .profile-header {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 32px;
      font-weight: bold;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .profile-info h2 {
      font-size: 24px;
      color: #111827;
      margin-bottom: 5px;
    }
    
    .profile-info p {
      color: #6b7280;
      font-size: 14px;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border-left: 4px solid #667eea;
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    }
    
    .stat-card.pending {
      border-left-color: #f59e0b;
    }
    
    .stat-card.approved {
      border-left-color: #10b981;
    }
    
    .stat-card.rejected {
      border-left-color: #ef4444;
    }
    
    .stat-label {
      color: #6b7280;
      font-size: 13px;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }
    
    .stat-value {
      font-size: 32px;
      font-weight: 700;
      color: #111827;
    }
    
    .section-title {
      font-size: 20px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .section-title::before {
      content: '';
      width: 4px;
      height: 24px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 2px;
    }
    
    .orders-table {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border: 1px solid #e5e7eb;
    }
    
    .table-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 16px;
      display: grid;
      grid-template-columns: 1fr 2fr 1fr 1fr;
      gap: 15px;
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .table-row {
      display: grid;
      grid-template-columns: 1fr 2fr 1fr 1fr;
      gap: 15px;
      padding: 16px;
      border-bottom: 1px solid #f3f4f6;
      align-items: center;
      transition: all 0.2s ease;
    }
    
    .table-row:hover {
      background: #f9fafb;
    }
    
    .table-row:last-child {
      border-bottom: none;
    }
    
    .order-id {
      font-weight: 600;
      color: #667eea;
    }
    
    .organizer-name {
      color: #374151;
      font-weight: 500;
    }
    
    .event-date {
      color: #6b7280;
      font-size: 13px;
    }
    
    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: capitalize;
    }
    
    .status-pending {
      background: #fef3c7;
      color: #92400e;
    }
    
    .status-approved {
      background: #d1fae5;
      color: #065f46;
    }
    
    .status-rejected {
      background: #fee2e2;
      color: #7f1d1d;
    }
    
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #6b7280;
    }
    
    .empty-state svg {
      width: 64px;
      height: 64px;
      margin: 0 auto 16px;
      opacity: 0.5;
    }
    
    .empty-state p {
      font-size: 14px;
      margin-bottom: 16px;
    }
    
    .btn-browse {
      display: inline-block;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 10px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn-browse:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .footer {
      background: white;
      border-top: 1px solid #e5e7eb;
      padding: 20px;
      text-align: center;
      color: #6b7280;
      font-size: 13px;
      margin-top: 40px;
    }
    
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        gap: 15px;
      }
      
      .table-header, .table-row {
        grid-template-columns: 1fr;
      }
      
      .profile-header {
        flex-direction: column;
        text-align: center;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="header">
    <div class="header-content">
      <h1>📊 My Dashboard</h1>
      <div class="header-actions">
        <?php include 'notification_component.php'; ?>
        <a href="index.php" class="btn-primary">← Back Home</a>
        <a href="logout.php" class="btn-danger">Logout</a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-container">
    <!-- Profile Card -->
    <div class="profile-card">
      <div class="profile-header">
        <?php if (!empty($customer_profile_pic)): ?>
          <img src="<?php echo htmlspecialchars($customer_profile_pic); ?>" alt="Profile Picture" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
        <?php else: ?>
          <div class="profile-avatar"><?php echo strtoupper(substr($customer_name, 0, 1)); ?></div>
        <?php endif; ?>
        <div class="profile-info">
          <h2><?php echo htmlspecialchars($customer_name); ?></h2>
          <p><?php echo htmlspecialchars($customer_email); ?></p>
          <p style="margin-top: 8px; font-size: 12px; color: #9ca3af;">Customer Account</p>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
      </div>
      <div class="stat-card pending">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?php echo $stats['pending_orders'] ?? 0; ?></div>
      </div>
      <div class="stat-card approved">
        <div class="stat-label">Approved</div>
        <div class="stat-value"><?php echo $stats['approved_orders'] ?? 0; ?></div>
      </div>
      <div class="stat-card rejected">
        <div class="stat-label">Rejected</div>
        <div class="stat-value"><?php echo $stats['rejected_orders'] ?? 0; ?></div>
      </div>
    </div>

    <!-- Orders Section -->
    <div style="margin-bottom: 30px;">
      <h3 class="section-title">Recent Orders</h3>
      
      <?php if (empty($orders)): ?>
        <div class="orders-table">
          <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <p>No orders yet</p>
            <a href="index.php" class="btn-browse">Browse Organizers</a>
          </div>
        </div>
      <?php else: ?>
        <div class="orders-table">
          <div class="table-header">
            <div>Order ID</div>
            <div>Organizer</div>
            <div>Event Date</div>
            <div>Status</div>
          </div>
          
          <?php foreach ($orders as $order): ?>
            <?php 
              $status = strtolower($order['status']);
              $status_class = 'status-' . $status;
              $event_details = json_decode($order['event_details'], true);
              $event_type = $event_details['event_type'] ?? 'Event';
            ?>
            <div class="table-row">
              <div class="order-id">#<?php echo $order['order_id']; ?></div>
              <div>
                <div class="organizer-name"><?php echo htmlspecialchars($order['organizer_page'] ?: $order['organizer_name']); ?></div>
                <div class="event-date"><?php echo $event_type; ?></div>
              </div>
              <div class="event-date"><?php echo $order['event_date']; ?></div>
              <div>
                <span class="status-badge <?php echo $status_class; ?>">
                  <?php echo ucfirst($status); ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);">
      <h3 class="section-title">Quick Actions</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="index.php" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
          🔍 Browse Organizers
        </a>
        <a href="customer_profile_edit.php" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(16, 185, 129, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
          👤 Edit Profile
        </a>
        <a href="index.php#contact" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 16px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(245, 158, 11, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
          📞 Contact Support
        </a>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <p>&copy; 2025 Event Management Portal. All rights reserved.</p>
  </div>

  <!-- Scripts -->
  <script src="notifications.js"></script>
</body>
</html>

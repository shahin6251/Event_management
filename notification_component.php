<?php
/**
 * notification_component.php
 * Include this file in any customer page to add the notification system
 * 
 * Usage in your PHP file:
 * <?php include 'notification_component.php'; ?>
 * 
 * Then add this in your HTML header:
 * <link rel="stylesheet" href="notifications.css">
 * 
 * And before closing body tag:
 * <script src="notifications.js"></script>
 */
?>

<!-- Notification System Component -->
<div class="notification-system">
  <!-- Notification Bell Icon (Top Right) -->
  <div class="notification-bell" id="notificationBell" title="Notifications">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
    </svg>
    <div class="notification-badge" id="notificationBadge" style="display: none;">0</div>
  </div>

  <!-- Notification Dropdown Panel -->
  <div class="notification-dropdown" id="notificationDropdown">
    <!-- Header -->
    <div class="notification-header">
      <h3>Notifications</h3>
      <button class="mark-all-read">Mark all as read</button>
    </div>

    <!-- Notification List -->
    <ul class="notification-list" id="notificationList">
      <div class="notification-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <p>Loading notifications...</p>
      </div>
    </ul>

    <!-- Footer -->
    <div class="notification-footer">
      <a href="orders.php">View all orders →</a>
    </div>
  </div>

  <!-- Overlay (for closing dropdown when clicking outside) -->
  <div class="notification-overlay" id="notificationOverlay"></div>
</div>

<!-- Styles for positioning notification bell in header -->
<style>
  .notification-system {
    position: relative;
  }

  /* If you want to position it in top-right corner of page */
  body > .notification-system {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1001;
  }

  /* Alternative: position in header */
  header .notification-system {
    display: flex;
    align-items: center;
  }
</style>

/**
 * notifications.js
 * Beautiful notification system for order status updates
 */

class NotificationSystem {
  constructor() {
    this.notificationBell = document.getElementById('notificationBell');
    this.notificationDropdown = document.getElementById('notificationDropdown');
    this.notificationOverlay = document.getElementById('notificationOverlay');
    this.notificationBadge = document.getElementById('notificationBadge');
    this.notificationList = document.getElementById('notificationList');
    this.markAllReadBtn = document.querySelector('.mark-all-read');
    
    this.isOpen = false;
    this.notifications = [];
    
    this.init();
  }

  init() {
    // Event listeners
    if (this.notificationBell) {
      this.notificationBell.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleDropdown();
      });
    }

    if (this.notificationOverlay) {
      this.notificationOverlay.addEventListener('click', () => {
        this.closeDropdown();
      });
    }

    if (this.markAllReadBtn) {
      this.markAllReadBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.markAllAsRead();
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (this.isOpen && !e.target.closest('.notification-bell') && !e.target.closest('.notification-dropdown')) {
        this.closeDropdown();
      }
    });

    // Initial load
    this.loadNotifications();
    
    // Refresh notifications every 10 seconds
    setInterval(() => this.loadNotifications(), 10000);
  }

  toggleDropdown() {
    if (this.isOpen) {
      this.closeDropdown();
    } else {
      this.openDropdown();
    }
  }

  openDropdown() {
    this.isOpen = true;
    this.notificationDropdown.classList.add('active');
    this.notificationOverlay.classList.add('active');
    this.loadNotifications();
  }

  closeDropdown() {
    this.isOpen = false;
    this.notificationDropdown.classList.remove('active');
    this.notificationOverlay.classList.remove('active');
  }

  async loadNotifications() {
    try {
      const response = await fetch('get_notifications.php?action=get_all');
      const data = await response.json();

      if (data.success) {
        this.notifications = data.notifications;
        this.updateBadge();
        this.renderNotifications();
      }
    } catch (error) {
      console.error('Error loading notifications:', error);
    }
  }

  async updateBadge() {
    try {
      const response = await fetch('get_notifications.php?action=get_unread');
      const data = await response.json();

      if (data.success) {
        const unreadCount = data.unread_count;
        
        if (unreadCount > 0) {
          this.notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
          this.notificationBadge.style.display = 'flex';
        } else {
          this.notificationBadge.style.display = 'none';
        }
      }
    } catch (error) {
      console.error('Error updating badge:', error);
    }
  }

  renderNotifications() {
    if (this.notifications.length === 0) {
      this.notificationList.innerHTML = `
        <div class="notification-empty">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
          </svg>
          <p>No notifications yet</p>
        </div>
      `;
      return;
    }

    this.notificationList.innerHTML = this.notifications.map(notif => this.createNotificationItem(notif)).join('');

    // Add click listeners to notification items
    this.notificationList.querySelectorAll('.notification-item').forEach((item, index) => {
      item.addEventListener('click', () => {
        this.markAsRead(this.notifications[index].id, index);
      });
    });
  }

  createNotificationItem(notif) {
    const isUnread = notif.is_read === 0 || notif.is_read === false;
    const statusClass = notif.status.toLowerCase();
    const icon = notif.status === 'approved' ? '✓' : '✕';
    const timeAgo = this.getTimeAgo(notif.created_at);

    return `
      <li class="notification-item ${isUnread ? 'unread' : ''}">
        <div class="notification-content">
          <div class="notification-icon ${statusClass}">
            ${icon}
          </div>
          <div class="notification-text">
            <p class="notification-title">
              Order #${notif.order_id}
              <span class="notification-status ${statusClass}">
                ${notif.status.charAt(0).toUpperCase() + notif.status.slice(1)}
              </span>
            </p>
            <div class="notification-meta">
              <span class="notification-organizer">${notif.organizer_name}</span>
              <span class="notification-time">${timeAgo}</span>
            </div>
            <div class="notification-meta" style="margin-top: 6px; color: #6b7280;">
              ${notif.event_type} • ${notif.event_date}
            </div>
          </div>
        </div>
      </li>
    `;
  }

  async markAsRead(notificationId, index) {
    try {
      const response = await fetch(`get_notifications.php?action=mark_read&id=${notificationId}`);
      const data = await response.json();

      if (data.success) {
        this.notifications[index].is_read = 1;
        this.renderNotifications();
        this.updateBadge();
      }
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  }

  async markAllAsRead() {
    try {
      const response = await fetch('get_notifications.php?action=mark_all_read');
      const data = await response.json();

      if (data.success) {
        this.notifications.forEach(notif => notif.is_read = 1);
        this.renderNotifications();
        this.updateBadge();
      }
    } catch (error) {
      console.error('Error marking all as read:', error);
    }
  }

  getTimeAgo(timestamp) {
    const now = new Date();
    const notifTime = new Date(timestamp);
    const diffMs = now - notifTime;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return notifTime.toLocaleDateString();
  }
}

// Initialize notification system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new NotificationSystem();
});

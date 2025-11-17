<?php
session_start();
include 'db.php';

// If this page is requested with ?event=..., return organizers as JSON
if (isset($_GET['fetchOrganizers']) && isset($_GET['event'])) {
    $eventType = $_GET['event'];

    $stmt = $conn->prepare("SELECT user_id, name, email, phone, location, profile_pic 
                            FROM users 
                            WHERE role = 'organizer' 
                            AND user_id IN (SELECT organizer_id FROM events WHERE caption LIKE ?)");
    $like = "%$eventType%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();

    $organizers = [];
    while ($row = $result->fetch_assoc()) {
        $organizers[] = $row;
    }
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode($organizers);
    exit;
}

// Mark notifications as read
if (isset($_GET['markNotificationsRead']) && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    $customerId = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE order_notifications SET is_read = TRUE WHERE customer_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode(["success" => true]);
    exit;
}

if (isset($_GET['fetchNotifications']) && isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    $customerId = (int)$_SESSION['user_id'];
    
    // Debug: Check if customer has notifications
    error_log("Fetching notifications for customer ID: " . $customerId);
    
    // Get unread notifications first, then recent orders
    $stmt = $conn->prepare("SELECT DISTINCT o.order_id, o.status, o.event_date, og.user_id AS organizer_id, COALESCE(op.page_title, og.name) AS organizer_name, on2.created_at as notification_time
                            FROM order_notifications on2
                            JOIN orders o ON on2.order_id = o.order_id
                            JOIN users og ON o.organizer_id = og.user_id
                            LEFT JOIN organizer_pages op ON op.user_id = og.user_id
                            WHERE on2.customer_id = ? AND on2.is_read = FALSE
                            ORDER BY on2.created_at DESC
                            LIMIT 10");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($r = $result->fetch_assoc()) { $rows[] = $r; }
    $stmt->close();
    
    error_log("Unread notifications found: " . count($rows));
    
    // If no unread notifications, get recent orders
    if (empty($rows)) {
        $stmt = $conn->prepare("SELECT DISTINCT o.order_id, o.status, o.event_date, og.user_id AS organizer_id, COALESCE(op.page_title, og.name) AS organizer_name, o.order_id as notification_time
                                FROM orders o
                                JOIN users og ON o.organizer_id = og.user_id
                                LEFT JOIN organizer_pages op ON op.user_id = og.user_id
                                WHERE o.customer_id = ? AND o.status IN ('approved', 'rejected')
                                ORDER BY o.order_id DESC
                                LIMIT 5");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($r = $result->fetch_assoc()) { $rows[] = $r; }
        $stmt->close();
        
        error_log("Recent orders found: " . count($rows));
    }
    
    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Management Portal</title>
  <link rel="stylesheet" href="MainStyle.css">
</head>
<body>

  <!-- Header -->
  <header style="position: relative;">
    <div style="position:absolute; top:8px; left:8px; z-index:10;">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php" style="padding:4px 10px; border:1px solid #fff; border-radius:9999px; font-size:12px; color:#fff; text-decoration:none; background:rgba(0,0,0,0.2);">Logout</a>
      <?php endif; ?>
    </div>
    <h2 style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Timeless Moments</h2>
    <nav class="navbar">
      <ul>
        <li><a href="#intro" class="active">Home</a></li>
        <li><a href="#event">Event</a></li>
        <li><a href="#about">About Us</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </nav>
    <div style="position:absolute; top:16px; right:16px; display:flex; align-items:center; gap:12px;">
      <?php if (isset($_SESSION['user_id']) && isset($_SESSION['role'])): ?>
        <?php if ($_SESSION['role'] === 'customer'): ?>
          <a href="customer_profile_dashboard.php" style="position:relative; width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.3s; box-shadow:0 4px 15px rgba(102, 126, 234, 0.3); text-decoration:none;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'" title="Dashboard">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          </a>
        <?php elseif ($_SESSION['role'] === 'organizer'): ?>
          <a href="organizer_dashboard.php" style="position:relative; padding:8px 16px; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:white; border-radius:8px; cursor:pointer; transition:all 0.3s; box-shadow:0 4px 15px rgba(16, 185, 129, 0.3); text-decoration:none; font-weight:600; font-size:13px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(16, 185, 129, 0.3)'" title="Dashboard">
            📊 Dashboard
          </a>
        <?php elseif ($_SESSION['role'] === 'admin'): ?>
          <a href="admin_dashboard.php" style="position:relative; padding:8px 16px; background:linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color:white; border-radius:8px; cursor:pointer; transition:all 0.3s; box-shadow:0 4px 15px rgba(220, 38, 38, 0.3); text-decoration:none; font-weight:600; font-size:13px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(220, 38, 38, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(220, 38, 38, 0.3)'" title="Admin Panel">
            ⚙️ Admin
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <p>Welcome to Timeless Moments – Where Every Event Becomes Memorable
    ... (your intro text unchanged) ...
    </p>
  </header>

  <?php if (!isset($_SESSION['user_id'])): ?>
  <section style="padding:8px 12px; margin:10px 0 20px; display:flex; align-items:center; justify-content:space-between; max-width:900px; margin-left:auto; margin-right:auto;">
    <div style="font-size:14px; color:#ffffff;">Welcome to Timeless Moments</div>
    <div style="display:flex; gap:8px;">
      <a href="login.php" style="padding:4px 10px; border:1px solid #fff; border-radius:9999px; font-size:12px; color:#fff; text-decoration:none; background:rgba(0,0,0,0.2);">Login</a>
      <a href="role_select.php" style="padding:4px 10px; background:#ffffff; color:#005f87; border-radius:9999px; font-size:12px; text-decoration:none; font-weight:500;">Sign Up</a>
    </div>
  </section>
  <?php else: ?>
  <section class="hero-welcome" style="text-align: center; padding: 35px 20px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(59, 130, 246, 0.1)); margin: 30px 0; border-radius: 20px;">
    <h2 style="font-size: 2rem; font-weight: bold; color: #ffffff; margin-bottom: 15px;">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
    <p style="font-size: 1rem; color: #e0f7ff; margin-bottom: 25px; max-width: 600px; margin-left: auto; margin-right: auto;">Ready to plan your next amazing event? Browse our categories below.</p>
  </section>
  <?php endif; ?>

  <!-- Event Categories -->
  <section id="event" class="event category-section">
    <div class="category-card" onclick="viewOrganizers('wedding')">
      <img src="img/wedding.jpeg" alt="Wedding Event">
      <h3>Wedding Events </h3>
    </div>
    <div class="category-card" onclick="viewOrganizers('corporate')">
      <img src="img/corporate_event.jpeg" alt="Corporate Event">
      <h3>Corporate Events </h3>
    </div>
    <div class="category-card" onclick="viewOrganizers('birthday')">
      <img src="img/birthday.jpg" alt="Birthday Event">
      <h3>Birthday Parties </h3>
    </div>
    <div class="category-card" onclick="viewOrganizers('concert')">
      <img src="img/concert.jpg" alt="Concert Event">
      <h3>Concerts </h3>
    </div>
    <div class="category-card" onclick="viewOrganizers('conference')">
      <img src="img/conference.jpg" alt="Conference Event">
      <h3>Conferences </h3>
    </div>
    <div class="category-card" onclick="viewOrganizers('kids')">
      <img src="img/kids.jpg" alt="Kids Event">
      <h3>Kids Events </h3>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about">
    <h2>About Us</h2>
    <p>Welcome to Timeless Moments, your trusted partner in creating unforgettable events. Whether it's a wedding, corporate gathering, birthday celebration, or concert, we connect you with the best event organizers who bring your vision to life. Our platform makes it easy to browse, compare, and book professional event services tailored to your needs. With a commitment to excellence and attention to detail, we ensure every moment becomes a cherished memory. Let us help you celebrate life's special occasions with style and elegance.</p>
  </section>

  <section id="about" class="about">
    <h2>Follow Us</h2>
    <div class="social-icons">
      <a href="https://www.facebook.com/share/1Cxm8KoWdE/"><img src="img/fb.jpeg" alt="Facebook"></a>
      <a href="https://www.instagram.com/radiyabarsha?igsh=aGdpODA4ZWx1bXBs"><img src="img/instagram.webp" alt="Instagram"></a>
      <a href="#"><img src="img/twitter.jpeg" alt="Twitter"></a>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="contact about">
    <h2>Contact Us</h2>
    <p>Have questions?</p>
    <p>Email: radiyabarsha0702@gmail.com | Phone: +880 1878-499435</p>
  </section>

  <!-- Background Video -->
  <video autoplay muted loop id="bgVideo">
    <source src="video/corporte.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <!-- Footer -->
  <footer class="site-footer">
    <p class="footer-copy">&copy; 2025 Event Management Portal. All rights reserved.</p>
  </footer>

  <!-- Modal -->
  <div id="modal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImg">
  </div>

  <!-- JS -->
  <script src="Mainpage.js"></script>
</body>
</html>


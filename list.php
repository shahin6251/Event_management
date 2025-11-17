<?php
session_start();
include 'db.php';

// Get event type from query string
$eventType = isset($_GET['event']) ? trim($_GET['event']) : '';

// --- 1. AJAX BACKEND LOGIC ---
// Check if this is an AJAX fetch request
if (isset($_GET['fetch']) && $_GET['fetch'] == 1) {
    
    $organizers = [];
    if ($eventType) {
        // --- THIS IS THE CORRECTED QUERY ---
        // We join 'users' and 'organizer_pages' to get public info
        // We search the 'page_title' and 'description' for the event type
        
        // Get organizers with their public profile information
        $stmt = $conn->prepare("SELECT u.user_id, op.page_title, op.profile_pic, u.email, u.phone, op.description
                                FROM users u
                                JOIN organizer_pages op ON u.user_id = op.user_id
                                WHERE u.role = 'organizer' AND (op.page_title LIKE ? OR op.description LIKE ?)");
        
        $like = "%" . $eventType . "%";
        $stmt->bind_param("ss", $like, $like);
        
        if (!$stmt->execute()) {
            // Send error details for debugging
            header('Content-Type: application/json');
            echo json_encode(["error" => "Query failed: " . $stmt->error]);
            exit;
        }
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $organizers[] = $row;
        }
        $stmt->close();
    }
    
    // --- Send JSON Response ---
    // This is the data your JavaScript will receive
    header('Content-Type: application/json');
    
    // Add debug info
    $debug_info = [
        'organizers' => $organizers,
        'event_type' => $eventType,
        'query_executed' => true,
        'organizer_count' => count($organizers)
    ];
    
    echo json_encode($debug_info);
    exit; // Stop the script from sending the HTML below
}

// --- 2. HTML PAGE LOGIC ---
// If it's not a fetch request, just show the HTML page.
// The JavaScript will load and then call THIS SAME FILE to get the data.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- The title can be dynamic -->
  <title><?php echo $eventType ? 'Organizers for ' . htmlspecialchars($eventType) : 'Organizers'; ?></title>
  <link rel="stylesheet" href="list.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
  <!-- Header -->
  <header class="top-header">
    <div class="logo">Event<span>Co</span></div>
    <!-- Fixed link to go back to the customer dashboard -->
    <a href="index.php" class="backBtn">Back to Home</a>
  </header>

  <main class="max-w-4xl mx-auto p-4">
    <h2 class="page-title">
      <?php if ($eventType) { ?>
        Available Organizers for "<?php echo htmlspecialchars($eventType); ?>"
      <?php } else { ?>
        Select an event type to see organizers
      <?php } ?>
    </h2>
    
    <!-- 
      This list is now empty.
      'organizer_list.js' will fill it with data.
    -->
    <ul id="organizerList" class="organizer-list">
      <!-- JavaScript will populate this... -->
      <p id="loading-text" style="text-align:center;color:#888;">Loading organizers...</p>
    </ul>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <p>&copy; 2025 Event Management Portal. All rights reserved.</p>
  </footer>
  
  <!-- We link to the JS file at the end -->
  <script src="list.js"></script>
</body>
</html>

<?php
session_start();
include 'db.php';

// Ensure only logged-in organizers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: organizer_login.php");
    exit;
}

// Handle AJAX requests for events
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = ["success" => false];

    if ($action === "add") {
        $caption = $_POST['caption'];
        $date = $_POST['date'];
        $venuePrice = $_POST['venuePrice'];
        $foodPrice = $_POST['foodPrice'];
        $servicesPrice = $_POST['servicesPrice'];
        $decorationPrice = $_POST['decorationPrice'];
        $image = $_POST['image']; // base64 string

        $stmt = $conn->prepare("INSERT INTO events (organizer_id, caption, date, image, venue_price, food_price, services_price, decoration_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiiii", $_SESSION['user_id'], $caption, $date, $image, $venuePrice, $foodPrice, $servicesPrice, $decorationPrice);

        if ($stmt->execute()) {
            $response = ["success" => true, "message" => "Event added successfully"];
        } else {
            $response = ["success" => false, "message" => "Error: " . $stmt->error];
        }
        $stmt->close();
    }

    if ($action === "delete") {
        $eventId = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
        $stmt->bind_param("ii", $eventId, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $response = ["success" => true, "message" => "Event deleted"];
        } else {
            $response = ["success" => false, "message" => "Error: " . $stmt->error];
        }
        $stmt->close();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Fetch events for this organizer
$events = [];
$stmt = $conn->prepare("SELECT * FROM events WHERE organizer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Organizer Dashboard</title>
  <link rel="stylesheet" href="organizer.css">
</head>
<body>
<header class="top-header">
  <div class="logo">Event<span>Co</span></div>
  <div class="profile-btn">
    <a href="profile.php"><button>My Profile</button></a>
  </div>
</header>

<aside class="sidebar">
  <ul class="nav-links">
    <li><a href="#worksSection">Events</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="#aboutSection">About</a></li>
    <li><a href="#contactSection">Contact</a></li>
  </ul>
</aside>

<main class="main-content">
  <section class="percentages">
    <h2>Event Progress</h2>
    <div id="progressCircles" class="progress-circles"></div>
  </section>

  <section class="edit-prices-section">
    <button id="togglePricesBtn">Edit Prices</button>
    <div id="editPricesContainer" class="prices-container" style="display:none;"></div>
  </section>

  <section id="worksSection">
    <h2>My Events</h2>
    <input type="file" id="eventInput" accept="image/*" hidden>
    <input type="text" id="eventCaption" placeholder="Enter event caption..." hidden>
    <input type="date" id="eventDate" hidden>
  </section>
  <section id="worksSection">
    <button id="addEventBtn">+ Add New Event</button>
    <div id="worksGallery"></div>
  </section>
</main>

<!-- Pass PHP events to JS -->
<script>
  const initialEvents = <?php echo json_encode($events); ?>;
</script>
<script src="organizer.js"></script>
</body>
</html>

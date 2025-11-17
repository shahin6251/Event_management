<?php
include 'check_session.php';
include 'db.php';
require_role('customer');

$customer_id = $_SESSION['user_id'];
$organizer_id = isset($_GET['org_id']) ? (int)$_GET['org_id'] : 0;

if (!$organizer_id) {
    header('Location: list.php');
    exit;
}

// Get organizer details
$stmt = $conn->prepare("SELECT u.name, u.email, u.phone, op.page_title, op.description, op.event_types
                        FROM users u
                        JOIN organizer_pages op ON u.user_id = op.user_id
                        WHERE u.user_id = ? AND u.role = 'organizer'");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$organizer = $result->fetch_assoc();
$stmt->close();

if (!$organizer) {
    header('Location: list.php');
    exit;
}

// Get customer details
$customer_stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer = $customer_stmt->get_result()->fetch_assoc();
$customer_stmt->close();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_type = trim($_POST['event_type'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $guest_count = (int)($_POST['guest_count'] ?? 0);
    $budget = trim($_POST['budget'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $special_requirements = trim($_POST['special_requirements'] ?? '');
    
    // Validation
    if (empty($event_type) || empty($event_date) || empty($location) || $guest_count <= 0 || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Insert order into database (match current orders schema)
        try {
            $details = [
                'event_type' => $event_type,
                'event_time' => $event_time,
                'location' => $location,
                'guest_count' => $guest_count,
                'budget' => $budget,
                'description' => $description,
                'special_requirements' => $special_requirements
            ];
            $event_details_json = json_encode($details);

            $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, organizer_id, event_details, event_date, status) VALUES (?, ?, ?, ?, 'pending')");
            $order_stmt->bind_param("iiss", $customer_id, $organizer_id, $event_details_json, $event_date);
            
            if ($order_stmt->execute()) {
                $order_id = $conn->insert_id;
                $message = "Your order has been placed successfully! Order ID: #$order_id. The organizer will contact you soon.";
                
                // Clear form data
                $event_type = $event_date = $event_time = $location = $description = $special_requirements = $budget = '';
                $guest_count = 0;
            } else {
                $error = 'Error placing order. Please try again.';
            }
            $order_stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Place Order - <?php echo htmlspecialchars($organizer['page_title']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .form-section {
      animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .organizer-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Place Order</h1>
          <p class="text-gray-600 mt-1">Book your event with <?php echo htmlspecialchars($organizer['page_title']); ?></p>
        </div>
        <div class="flex space-x-4">
          <a href="list.php" class="text-gray-600 hover:text-gray-900">← Back to Organizers</a>
          <a href="organizer_view.php?id=<?php echo $organizer_id; ?>" class="text-blue-600 hover:text-blue-800">View Profile</a>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Organizer Info Card -->
      <div class="lg:col-span-1">
        <div class="organizer-card rounded-xl shadow-lg p-6 text-white sticky top-8">
          <div class="text-center mb-6">
            <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($organizer['page_title']); ?></h3>
            <p class="text-white text-opacity-90 text-sm mt-2"><?php echo htmlspecialchars($organizer['name']); ?></p>
          </div>
          
          <div class="space-y-3 text-sm">
            <div class="flex items-center">
              <svg class="w-4 h-4 mr-3 text-white text-opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              <span><?php echo htmlspecialchars($organizer['email']); ?></span>
            </div>
            <div class="flex items-center">
              <svg class="w-4 h-4 mr-3 text-white text-opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </svg>
              <span><?php echo htmlspecialchars($organizer['phone']); ?></span>
            </div>
          </div>
          
          <div class="mt-6 pt-6 border-t border-white border-opacity-20">
            <h4 class="font-semibold mb-2">Specializes in:</h4>
            <p class="text-white text-opacity-90 text-sm"><?php echo htmlspecialchars($organizer['event_types']); ?></p>
          </div>
        </div>
      </div>

      <!-- Order Form -->
      <div class="lg:col-span-2">
        <?php if ($message): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 form-section">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <?php echo htmlspecialchars($message); ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 form-section">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <?php echo htmlspecialchars($error); ?>
            </div>
          </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 form-section">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Event Details</h2>
          
          <!-- Customer Info (Read-only) -->
          <div class="mb-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Your Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-700">Name:</span>
                <span class="ml-2 text-gray-900"><?php echo htmlspecialchars($customer['name']); ?></span>
              </div>
              <div>
                <span class="font-medium text-gray-700">Email:</span>
                <span class="ml-2 text-gray-900"><?php echo htmlspecialchars($customer['email']); ?></span>
              </div>
              <div>
                <span class="font-medium text-gray-700">Phone:</span>
                <span class="ml-2 text-gray-900"><?php echo htmlspecialchars($customer['phone'] ?? 'Not provided'); ?></span>
              </div>
            </div>
          </div>

          <!-- Event Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">Event Type *</label>
              <select id="event_type" name="event_type" required 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Event Type</option>
                <option value="Wedding" <?php echo ($event_type ?? '') === 'Wedding' ? 'selected' : ''; ?>>Wedding</option>
                <option value="Corporate Events" <?php echo ($event_type ?? '') === 'Corporate Events' ? 'selected' : ''; ?>>Corporate Events</option>
                <option value="Birthday Parties" <?php echo ($event_type ?? '') === 'Birthday Parties' ? 'selected' : ''; ?>>Birthday Parties</option>
                <option value="Concerts" <?php echo ($event_type ?? '') === 'Concerts' ? 'selected' : ''; ?>>Concerts</option>
                <option value="Conferences" <?php echo ($event_type ?? '') === 'Conferences' ? 'selected' : ''; ?>>Conferences</option>
                <option value="Kids Events" <?php echo ($event_type ?? '') === 'Kids Events' ? 'selected' : ''; ?>>Kids Events</option>
                <option value="Other" <?php echo ($event_type ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
              </select>
            </div>

            <div>
              <label for="guest_count" class="block text-sm font-medium text-gray-700 mb-2">Expected Guests *</label>
              <input type="number" id="guest_count" name="guest_count" required min="1"
                     value="<?php echo htmlspecialchars($guest_count ?? ''); ?>"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="e.g., 50">
            </div>

            <div>
              <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Event Date *</label>
              <input type="date" id="event_date" name="event_date" required 
                     value="<?php echo htmlspecialchars($event_date ?? ''); ?>"
                     min="<?php echo date('Y-m-d'); ?>"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
              <label for="event_time" class="block text-sm font-medium text-gray-700 mb-2">Event Time</label>
              <input type="time" id="event_time" name="event_time" 
                     value="<?php echo htmlspecialchars($event_time ?? ''); ?>"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-2">
              <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Event Location *</label>
              <input type="text" id="location" name="location" required 
                     value="<?php echo htmlspecialchars($location ?? ''); ?>"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="e.g., Grand Ballroom, Downtown Hotel, New York">
            </div>

            <div class="md:col-span-2">
              <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">Budget Range</label>
              <select id="budget" name="budget" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Budget Range</option>
                <option value="Under $1,000" <?php echo ($budget ?? '') === 'Under $1,000' ? 'selected' : ''; ?>>Under $1,000</option>
                <option value="$1,000 - $5,000" <?php echo ($budget ?? '') === '$1,000 - $5,000' ? 'selected' : ''; ?>>$1,000 - $5,000</option>
                <option value="$5,000 - $10,000" <?php echo ($budget ?? '') === '$5,000 - $10,000' ? 'selected' : ''; ?>>$5,000 - $10,000</option>
                <option value="$10,000 - $25,000" <?php echo ($budget ?? '') === '$10,000 - $25,000' ? 'selected' : ''; ?>>$10,000 - $25,000</option>
                <option value="$25,000 - $50,000" <?php echo ($budget ?? '') === '$25,000 - $50,000' ? 'selected' : ''; ?>>$25,000 - $50,000</option>
                <option value="Over $50,000" <?php echo ($budget ?? '') === 'Over $50,000' ? 'selected' : ''; ?>>Over $50,000</option>
                <option value="Discuss with organizer" <?php echo ($budget ?? '') === 'Discuss with organizer' ? 'selected' : ''; ?>>Discuss with organizer</option>
              </select>
            </div>
          </div>

          <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Event Description *</label>
            <textarea id="description" name="description" required rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Describe your event vision, theme, specific requirements, etc."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
          </div>

          <div class="mb-8">
            <label for="special_requirements" class="block text-sm font-medium text-gray-700 mb-2">Special Requirements</label>
            <textarea id="special_requirements" name="special_requirements" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Any special requests, dietary restrictions, accessibility needs, etc."><?php echo htmlspecialchars($special_requirements ?? ''); ?></textarea>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-4">
            <a href="organizer_view.php?id=<?php echo $organizer_id; ?>" 
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
              Cancel
            </a>
            <button type="submit" 
                    class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
              Place Order
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

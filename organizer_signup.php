<?php
/*
 * organizer_signup.php
 * This file now does two things:
 * 1. If it receives a POST request (from the JS), it acts as a backend API.
 * 2. If it's loaded in a browser (GET request), it displays the HTML signup form.
 */

// Include the database connection
include 'db.php';

// --- 1. AJAX BACKEND LOGIC ---
// Check if this is an AJAX POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Prepare a JSON response to send back to the JavaScript
    header('Content-Type: application/json');
    $response = ["success" => false, "message" => "Signup failed"];

    // --- Get Data from Form ---
    $name     = $_POST['fullName'] ?? null;
    $email    = $_POST['email'] ?? null;
    $phone    = $_POST['phone'] ?? null;
    $company  = $_POST['companyName'] ?? null; // For organizer_pages
    $website  = $_POST['companyWebsite'] ?? null; // For organizer_pages
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $confirm  = $_POST['confirmPassword'] ?? null;

    // --- Server-Side Validation ---
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        $response["message"] = "All required fields must be filled";
        echo json_encode($response);
        exit;
    }
    if ($password !== $confirm) {
        $response["message"] = "Passwords do not match";
        echo json_encode($response);
        exit;
    }

    // --- Check if user already exists ---
    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
    $stmt_check->bind_param("ss", $email, $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $response["message"] = "Email or username already taken";
        $stmt_check->close();
        $conn->close();
        echo json_encode($response);
        exit;
    }
    $stmt_check->close();

    // --- Start a Database Transaction ---
    $conn->begin_transaction();

    try {
        // --- Query 1: Create the user in 'users' table ---
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt_users = $conn->prepare("INSERT INTO users (name, email, password_hash, role, phone, username) VALUES (?, ?, ?, 'organizer', ?, ?)");
        $stmt_users->bind_param("sssss", $name, $email, $passwordHash, $phone, $username);
        
        if (!$stmt_users->execute()) {
            throw new Exception("Error creating user: " . $stmt_users->error);
        }

        // Get the new user_id we just created
        $new_user_id = $conn->insert_id;
        $stmt_users->close();

        // --- Query 2: Create their page in 'organizer_pages' ---
        $page_title = $company ?: $name; // Use company name, or full name as fallback
        $description = $website ?: ""; // Use website, or empty string
        $event_types = $_POST['eventTypes'] ?? 'Wedding, Corporate Events, Birthday Parties, Concerts'; // Default event types
        
        $stmt_pages = $conn->prepare("INSERT INTO organizer_pages (user_id, page_title, description, event_types) VALUES (?, ?, ?, ?)");
        $stmt_pages->bind_param("isss", $new_user_id, $page_title, $description, $event_types);

        if (!$stmt_pages->execute()) {
            throw new Exception("Error creating organizer page: " . $stmt_pages->error);
        }
        $stmt_pages->close();

        // --- If we get here, both queries worked! ---
        $conn->commit();
        $response = ["success" => true, "message" => "Organizer account created successfully"];

    } catch (Exception $e) {
        // --- Something went wrong, roll back ---
        $conn->rollback();
        $response = ["success" => false, "message" => $e->getMessage()];
    }

    $conn->close();
    
    // --- Send JSON Response ---
    // This 'exit' is crucial. It stops the script from
    // accidentally sending the HTML below as part of the JSON response.
    echo json_encode($response);
    exit;
}

// --- 2. HTML PAGE LOGIC ---
// If it's NOT a POST request, the script continues and shows the HTML form.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organizer Sign Up</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    /* A simple style for our input fields */
    .input-field {
      @apply mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500;
    }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 font-[Inter,sans-serif]">
  <div class="min-h-screen flex flex-col items-center justify-center p-4">
    <div class="max-w-xl w-full bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg space-y-6">
      <header class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Create Organizer Account</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Join our platform and start managing your events.</p>
      </header>

      <!-- Signup Form -->
      <form id="signup-form" class="space-y-4">
        
        <!-- This div is for showing success or error messages from JavaScript -->
        <div id="message-container" class="hidden">
          <div id="message-content" class="rounded-md p-4 text-sm"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="full-name" class="block text-sm font-medium">Full Name <span class="text-red-500">*</span></label>
            <input type="text" id="full-name" name="full-name" class="input-field" required>
          </div>
          <div>
            <label for="email" class="block text-sm font-medium">Email <span class="text-red-500">*</span></label>
            <input type="email" id="email" name="email" class="input-field" required>
          </div>
          <div>
            <label for="phone" class="block text-sm font-medium">Phone</label>
            <input type="tel" id="phone" name="phone" class="input-field">
          </div>
          <div>
            <label for="company-name" class="block text-sm font-medium">Company Name</label>
            <input type="text" id="company-name" name="company-name" class="input-field">
          </div>
        </div>

        <div>
          <label for="company-website" class="block text-sm font-medium">Company Website</label>
          <input type="url" id="company-website" name="company-website" class="input-field" placeholder="https://www.example.com">
        </div>

        <div>
          <label for="event-types" class="block text-sm font-medium">Event Specializations</label>
          <div class="grid grid-cols-2 gap-2 mt-2">
            <label class="flex items-center">
              <input type="checkbox" name="eventTypes[]" value="Wedding" class="mr-2" checked>
              <span class="text-sm">Wedding Events</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" name="eventTypes[]" value="Corporate Events" class="mr-2" checked>
              <span class="text-sm">Corporate Events</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" name="eventTypes[]" value="Birthday Parties" class="mr-2" checked>
              <span class="text-sm">Birthday Parties</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" name="eventTypes[]" value="Concerts" class="mr-2" checked>
              <span class="text-sm">Concerts</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" name="eventTypes[]" value="Conferences" class="mr-2">
              <span class="text-sm">Conferences</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" name="eventTypes[]" value="Kids Events" class="mr-2">
              <span class="text-sm">Kids Events</span>
            </label>
          </div>
        </div>

        <div>
          <label for="username" class="block text-sm font-medium">Username <span class="text-red-500">*</span></label>
          <input type="text" id="username" name="username" class="input-field" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="password" class="block text-sm font-medium">Password <span class="text-red-500">*</span></label>
            <input type="password" id="password" name="password" class="input-field" required>
          </div>
          <div>
            <label for="confirm-password" class="block text-sm font-medium">Confirm Password <span class="text-red-500">*</span></label>
            <input type="password" id="confirm-password" name="confirm-password" class="input-field" required>
          </div>
        </div>

        <div class="flex items-start pt-2">
          <input id="terms-conditions" type="checkbox" class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" required>
          <label for="terms-conditions" class="ml-2 block text-sm">I agree to the Terms and Conditions <span class="text-red-500">*</span></label>
        </div>

        <button type="submit" id="submit-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-md shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition duration-200">
          Create Account
        </button>
      </form>

      <footer class="text-center">
        <p class="text-sm">Already have an account? <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Sign In</a></p>
      </footer>
    </div>
  </div>

  <!-- External JS -->
  <script src="organizer_signup.js" defer></script>
</body>
</html>

<?php
/*
 * customer_signup.php
 * This file now does two things:
 * 1. If it receives a POST request (from the JS), it acts as a backend API.
 * 2. If it's loaded in a browser (GET request), it displays the HTML signup form.
 */

// Include the database connection
include 'db.php';

// --- 1. AJAX BACKEND LOGIC ---
// Check if this is an AJAX POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // We are in "API mode"
    
    // Get data from the 'fetch' request body
    $name     = $_POST['fullName'] ?? null;
    $email    = $_POST['email'] ?? null;
    $phone    = $_POST['phone'] ?? null;
    $dob      = $_POST['dob'] ?? null;
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $confirm  = $_POST['confirmPassword'] ?? null;

    // Prepare a JSON response to send back to the JavaScript
    $response = ["success" => false, "message" => "Signup failed"];

    // --- Server-Side Validation ---
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        $response["message"] = "All required fields must be filled";
    } elseif ($password !== $confirm) {
        $response["message"] = "Passwords do not match";
    } else {
        
        // Check if email or username already exists
        $stmt_check = $conn->prepare("SELECT email FROM users WHERE email = ? OR username = ?");
        $stmt_check->bind_param("ss", $email, $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response["message"] = "Email or username already taken";
        } else {
            // --- Create User ---
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // This INSERT matches your 'database_setup.sql' table structure
            $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password_hash, role, phone, username) VALUES (?, ?, ?, 'customer', ?, ?)");
            
            // "sssss" - 5 parameters (name, email, hash, phone, username)
            $stmt_insert->bind_param("sssss", $name, $email, $passwordHash, $phone, $username);

            if ($stmt_insert->execute()) {
                $response = ["success" => true, "message" => "Account created successfully"];
            } else {
                $response = ["success" => false, "message" => "Error: " . $stmt_insert->error];
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }

    $conn->close();
    
    // --- Send JSON Response ---
    // This is the end of the line for an AJAX request.
    // The HTML form below will NOT be sent.
    header('Content-Type: application/json');
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
  <title>Customer Sign Up</title>
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
  <div class="min-h-screen flex flex-col items-center justify-center py-8 px-4">
    <div class="max-w-xl w-full bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg space-y-6">
      <header class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Create your Account</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Let's get you started!</p>
      </header>

      <!-- Signup Form -->
      <!-- 
        The 'action' and 'method' are no longer needed 
        because customer_signup.js will handle the submission.
      -->
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
            <label for="dob" class="block text-sm font-medium">Date of Birth</label>
            <input type="date" id="dob" name="dob" class="input-field">
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

        <div class="space-y-2 pt-2">
          <div class="flex items-start">
            <input id="terms-conditions" name="terms-conditions" type="checkbox" class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" required>
            <label for="terms-conditions" class="ml-2 block text-sm">I agree to the Terms and Conditions <span class="text-red-500">*</span></label>
          </div>
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

  <!-- 
  This links to the external customer_signup.js file.
  'defer' tells the browser to run the script after the HTML is loaded.
  -->
  <script src="customer_signup.js" defer></script>
</body>
</html>

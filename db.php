<?php
/*
 * db.php
 * Database connection file (using MySQLi to match your code)
 */

// --- Database Configuration ---
// !! IMPORTANT !!
// Replace these with your actual database credentials.
$servername = "localhost";   // Or your db host (e.g., 127.0.0.1)
$username   = "root"; // Your MySQL username
$password   = ""; // Your MySQL password
$dbname     = "event_management"; // Your MySQL database name

// --- Create Connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// --- Check Connection ---
if ($conn->connect_error) {
    // Stop the script and report the error
    // We send a JSON error because the frontend expects it.
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

// Set the charset to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

?>


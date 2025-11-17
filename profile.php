<?php
/*
 * profile.php
 * Organizer's page to edit their *private* user info (name, email)
 * and their *public* profile picture.
 */

// --- 1. Include the Gatekeeper ---
include 'check_session.php';
require_role('organizer'); // Only organizers

// --- 2. Include Database ---
include 'db.php';

$message = "";

// --- 3. Handle Form Submission (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Get data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact']; // This is the 'phone' column

    // Use a transaction for safety
    $conn->begin_transaction();
    try {
        // --- Query 1: Update the 'users' table ---
        $stmt_users = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE user_id=?");
        $stmt_users->bind_param("sssi", $name, $email, $contact, $user_id);
        if (!$stmt_users->execute()) {
            throw new Exception("Error updating user: " . $stmt_users->error);
        }
        $stmt_users->close();

        // --- Query 2: Handle Profile Picture Upload ---
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            // Create a unique filename
            $fileName = $user_id . "_" . time() . "_" . basename($_FILES["profileImage"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {
                // --- Query 3: Update the 'organizer_pages' table ---
                $stmt_pages = $conn->prepare("UPDATE organizer_pages SET profile_pic=? WHERE user_id=?");
                $stmt_pages->bind_param("si", $targetFile, $user_id);
                if (!$stmt_pages->execute()) {
                    throw new Exception("Error updating profile picture: " . $stmt_pages->error);
                }
                $stmt_pages->close();
            } else {
                throw new Exception("Error uploading file.");
            }
        }
        
        // If we get here, all queries worked
        $conn->commit();
        $message = "Profile updated successfully!";
        
    } catch (Exception $e) {
        // Something went wrong
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }
}

// --- 4. Fetch Current Organizer Data (for GET or after POST) ---
// --- CORRECTED JOIN QUERY ---
$stmt = $conn->prepare("SELECT u.name, u.email, u.phone, op.profile_pic 
                       FROM users u
                       JOIN organizer_pages op ON u.user_id = op.user_id
                       WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// --- 5. Render the HTML ---
// This HTML will be sent on the initial GET request
// OR it will be sent as the response to the 'fetch' call
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Organizer Profile</title>
  <link rel="stylesheet" href="profile.css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
  
  <header class="profile-header">
    <h1>Edit Profile</h1>
    <a href="organizer_dashboard.php" class="backBtn">Back to Dashboard</a>
  </header>

  <div class="profile-container">
    <?php if (!empty($message)): ?>
      <!-- This message will show after the JS reloads the content -->
      <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="profile.php">
      <div class="profile-pic">
        <img id="profileImage" src="<?php echo htmlspecialchars($user['profile_pic'] ?: 'https://placehold.co/150x150/E2E8F0/94A3B8?text=Profile'); ?>" alt="Profile Picture">
        <div class="overlay">
          <label for="fileInput" class="upload-btn">Change</label>
          <input type="file" id="fileInput" name="profileImage" accept="image/*" hidden>
        </div>
      </div>

      <div class="profile-info">
        <div class="info-field">
          <label>Name:</label>
          <!-- Inputs are disabled by default -->
          <input type="text" id="nameInput" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required disabled>
        </div>
        <div class="info-field">
          <label>Email:</label>
          <input type="email" id="emailInput" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required disabled>
        </div>
        <div class="info-field">
          <label>Contact:</label>
          <input type="text" id="contactInput" name="contact" value="<?php echo htmlspecialchars($user['phone']); ?>" required disabled>
        </div>

        <!-- 
          The 'type' is 'button' so it doesn't submit the form.
          JavaScript will handle the click.
        -->
        <button type="button" id="editBtn" class="edit-btn">Edit Profile</button>
      </div>
    </form>
  </div>
  
  <!-- Link to the JS file -->
  <script src="profile.js"></script>
</body>
</html>

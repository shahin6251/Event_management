<?php
include 'check_session.php';
include 'db.php';
require_role('organizer');

$organizer_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_type = $_POST['event_type'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $client_name = trim($_POST['client_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'published';
    
    // Validation
    if (empty($title) || empty($description) || empty($event_type) || empty($event_date)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle file uploads
        $uploaded_images = [];
        $uploaded_videos = [];
        
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/portfolio/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $filename) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $allowed_images = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_ext, $allowed_images)) {
                        $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_path)) {
                            $uploaded_images[] = $upload_path;
                        }
                    }
                }
            }
        }
        
        // Handle video uploads
        if (!empty($_FILES['videos']['name'][0])) {
            foreach ($_FILES['videos']['name'] as $key => $filename) {
                if ($_FILES['videos']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $allowed_videos = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
                    
                    if (in_array($file_ext, $allowed_videos)) {
                        $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['videos']['tmp_name'][$key], $upload_path)) {
                            $uploaded_videos[] = $upload_path;
                        }
                    }
                }
            }
        }
        
        // Insert into database
        try {
            $stmt = $conn->prepare("INSERT INTO organizer_portfolio (organizer_id, title, description, event_type, event_date, client_name, location, images, videos, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $images_json = json_encode($uploaded_images);
            $videos_json = json_encode($uploaded_videos);
            
            $stmt->bind_param("issssssssss", $organizer_id, $title, $description, $event_type, $event_date, $client_name, $location, $images_json, $videos_json, $featured, $status);
            
            if ($stmt->execute()) {
                $message = 'Portfolio item added successfully!';
                // Clear form data
                $title = $description = $event_type = $event_date = $client_name = $location = '';
                $featured = 0;
            } else {
                $error = 'Error adding portfolio item. Please try again.';
            }
            $stmt->close();
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
  <title>Add Portfolio Item</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .file-upload-area {
      border: 2px dashed #d1d5db;
      transition: all 0.3s ease;
    }
    .file-upload-area:hover {
      border-color: #6366f1;
      background-color: #f8fafc;
    }
    .file-upload-area.dragover {
      border-color: #6366f1;
      background-color: #eff6ff;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Add Portfolio Item</h1>
          <p class="text-gray-600 mt-1">Showcase your completed event work</p>
        </div>
        <div class="flex space-x-4">
          <a href="organizer_dashboard.php" class="text-gray-600 hover:text-gray-900">← Dashboard</a>
          <a href="portfolio_manage.php" class="text-blue-600 hover:text-blue-800">Manage Portfolio</a>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($message): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
      <!-- Basic Information -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Event Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Event Title *</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($title ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Elegant Beach Wedding">
          </div>

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
            <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Event Date *</label>
            <input type="date" id="event_date" name="event_date" required 
                   value="<?php echo htmlspecialchars($event_date ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
            <input type="text" id="client_name" name="client_name" 
                   value="<?php echo htmlspecialchars($client_name ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Sarah & Michael Johnson">
          </div>

          <div class="md:col-span-2">
            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Event Location</label>
            <input type="text" id="location" name="location" 
                   value="<?php echo htmlspecialchars($location ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Malibu Beach, CA">
          </div>
        </div>

        <div class="mt-6">
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Event Description *</label>
          <textarea id="description" name="description" required rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Describe the event, what services you provided, highlights, etc."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>
      </div>

      <!-- Media Upload -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Media Files</h2>
        
        <!-- Images Upload -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Event Images</label>
          <div class="file-upload-area rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-gray-600 mb-2">Click to upload images or drag and drop</p>
            <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB each</p>
            <input type="file" name="images[]" multiple accept="image/*" 
                   class="mt-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
          </div>
        </div>

        <!-- Videos Upload -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Event Videos (Optional)</label>
          <div class="file-upload-area rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <p class="text-gray-600 mb-2">Click to upload videos or drag and drop</p>
            <p class="text-sm text-gray-500">MP4, AVI, MOV up to 50MB each</p>
            <input type="file" name="videos[]" multiple accept="video/*" 
                   class="mt-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
          </div>
        </div>
      </div>

      <!-- Settings -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Settings</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select id="status" name="status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="published" <?php echo ($status ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
              <option value="draft" <?php echo ($status ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
          </div>

          <div class="flex items-center">
            <input type="checkbox" id="featured" name="featured" value="1" 
                   <?php echo ($featured ?? 0) ? 'checked' : ''; ?>
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="featured" class="ml-2 block text-sm text-gray-700">
              Feature this work (show prominently to customers)
            </label>
          </div>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="flex justify-end space-x-4">
        <a href="organizer_dashboard.php" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
          Cancel
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
          Add Portfolio Item
        </button>
      </div>
    </form>
  </div>
</body>
</html>

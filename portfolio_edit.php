<?php
include 'check_session.php';
include 'db.php';
require_role('organizer');

$organizer_id = $_SESSION['user_id'];
$portfolio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$portfolio_id) {
    header('Location: portfolio_manage.php');
    exit;
}

// Get portfolio item
$stmt = $conn->prepare("SELECT * FROM organizer_portfolio WHERE id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $portfolio_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$portfolio = $result->fetch_assoc();
$stmt->close();

if (!$portfolio) {
    header('Location: portfolio_manage.php');
    exit;
}

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
    if (empty($title) || empty($description)) {
        $error = 'Title and description are required.';
    } else {
        // Handle file uploads
        $uploaded_images = json_decode($portfolio['images'], true) ?: [];
        $uploaded_videos = json_decode($portfolio['videos'], true) ?: [];
        
        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/portfolio/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Handle new image uploads
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
        
        // Handle new video uploads
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
        
        // Handle file deletions
        if (isset($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $image_to_delete) {
                if (($key = array_search($image_to_delete, $uploaded_images)) !== false) {
                    if (file_exists($image_to_delete)) {
                        unlink($image_to_delete);
                    }
                    unset($uploaded_images[$key]);
                }
            }
            $uploaded_images = array_values($uploaded_images); // Reindex array
        }
        
        if (isset($_POST['delete_videos'])) {
            foreach ($_POST['delete_videos'] as $video_to_delete) {
                if (($key = array_search($video_to_delete, $uploaded_videos)) !== false) {
                    if (file_exists($video_to_delete)) {
                        unlink($video_to_delete);
                    }
                    unset($uploaded_videos[$key]);
                }
            }
            $uploaded_videos = array_values($uploaded_videos); // Reindex array
        }
        
        // Update database
        try {
            $stmt = $conn->prepare("UPDATE organizer_portfolio SET title = ?, description = ?, event_type = ?, event_date = ?, client_name = ?, location = ?, images = ?, videos = ?, featured = ?, status = ? WHERE id = ? AND organizer_id = ?");
            
            $images_json = json_encode($uploaded_images);
            $videos_json = json_encode($uploaded_videos);
            
            $stmt->bind_param("ssssssssssii", $title, $description, $event_type, $event_date, $client_name, $location, $images_json, $videos_json, $featured, $status, $portfolio_id, $organizer_id);
            
            if ($stmt->execute()) {
                $message = 'Portfolio item updated successfully!';
                // Refresh portfolio data
                $portfolio['title'] = $title;
                $portfolio['description'] = $description;
                $portfolio['event_type'] = $event_type;
                $portfolio['event_date'] = $event_date;
                $portfolio['client_name'] = $client_name;
                $portfolio['location'] = $location;
                $portfolio['featured'] = $featured;
                $portfolio['status'] = $status;
                $portfolio['images'] = $images_json;
                $portfolio['videos'] = $videos_json;
            } else {
                $error = 'Error updating portfolio item. Please try again.';
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$existing_images = json_decode($portfolio['images'], true) ?: [];
$existing_videos = json_decode($portfolio['videos'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Portfolio Item</title>
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
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Edit Portfolio Item</h1>
          <p class="text-gray-600 mt-1">Update your work showcase</p>
        </div>
        <div class="flex space-x-4">
          <a href="portfolio_manage.php" class="text-gray-600 hover:text-gray-900">← Back to Portfolio</a>
          <a href="organizer_dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 form-section">
      <!-- Basic Information -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Basic Information</h2>
        
        <div class="mb-6">
          <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Project Title *</label>
          <input type="text" id="title" name="title" required 
                 value="<?php echo htmlspecialchars($portfolio['title']); ?>"
                 class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 placeholder="e.g., Elegant Beach Wedding">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div>
            <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
            <select id="event_type" name="event_type" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select Event Type</option>
              <option value="Wedding" <?php echo ($portfolio['event_type'] ?? '') === 'Wedding' ? 'selected' : ''; ?>>Wedding</option>
              <option value="Corporate Events" <?php echo ($portfolio['event_type'] ?? '') === 'Corporate Events' ? 'selected' : ''; ?>>Corporate Events</option>
              <option value="Birthday Parties" <?php echo ($portfolio['event_type'] ?? '') === 'Birthday Parties' ? 'selected' : ''; ?>>Birthday Parties</option>
              <option value="Concerts" <?php echo ($portfolio['event_type'] ?? '') === 'Concerts' ? 'selected' : ''; ?>>Concerts</option>
              <option value="Conferences" <?php echo ($portfolio['event_type'] ?? '') === 'Conferences' ? 'selected' : ''; ?>>Conferences</option>
              <option value="Kids Events" <?php echo ($portfolio['event_type'] ?? '') === 'Kids Events' ? 'selected' : ''; ?>>Kids Events</option>
              <option value="Other" <?php echo ($portfolio['event_type'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>

          <div>
            <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
            <input type="date" id="event_date" name="event_date" 
                   value="<?php echo htmlspecialchars($portfolio['event_date'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
            <input type="text" id="client_name" name="client_name" 
                   value="<?php echo htmlspecialchars($portfolio['client_name'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Sarah & Michael Johnson">
          </div>

          <div>
            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Event Location</label>
            <input type="text" id="location" name="location" 
                   value="<?php echo htmlspecialchars($portfolio['location'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Malibu Beach, CA">
          </div>
        </div>

        <div class="mb-6">
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Event Description *</label>
          <textarea id="description" name="description" required rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Describe the event, what services you provided, highlights, etc."><?php echo htmlspecialchars($portfolio['description']); ?></textarea>
        </div>
      </div>

      <!-- Existing Media -->
      <?php if (!empty($existing_images) || !empty($existing_videos)): ?>
        <div class="mb-8">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Current Media</h2>
          
          <?php if (!empty($existing_images)): ?>
            <div class="mb-6">
              <h3 class="text-lg font-medium text-gray-900 mb-3">Images</h3>
              <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($existing_images as $image): ?>
                  <div class="relative group">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Portfolio image" class="w-full h-32 object-cover rounded-lg">
                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                      <label class="flex items-center text-white text-sm cursor-pointer">
                        <input type="checkbox" name="delete_images[]" value="<?php echo htmlspecialchars($image); ?>" class="mr-2">
                        Delete
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($existing_videos)): ?>
            <div class="mb-6">
              <h3 class="text-lg font-medium text-gray-900 mb-3">Videos</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($existing_videos as $video): ?>
                  <div class="relative group">
                    <video class="w-full h-32 object-cover rounded-lg" controls>
                      <source src="<?php echo htmlspecialchars($video); ?>" type="video/mp4">
                    </video>
                    <div class="absolute top-2 right-2">
                      <label class="flex items-center bg-black bg-opacity-70 text-white text-sm px-2 py-1 rounded cursor-pointer">
                        <input type="checkbox" name="delete_videos[]" value="<?php echo htmlspecialchars($video); ?>" class="mr-2">
                        Delete
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Add New Media -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Add New Media</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Add Images</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, WebP (max 10MB each)</p>
          </div>

          <div>
            <label for="videos" class="block text-sm font-medium text-gray-700 mb-2">Add Videos</label>
            <input type="file" id="videos" name="videos[]" multiple accept="video/*"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500 mt-1">MP4, AVI, MOV, WMV, WebM (max 50MB each)</p>
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
              <option value="published" <?php echo ($portfolio['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
              <option value="draft" <?php echo ($portfolio['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
          </div>

          <div class="flex items-center">
            <input type="checkbox" id="featured" name="featured" value="1" 
                   <?php echo ($portfolio['featured'] ?? 0) ? 'checked' : ''; ?>
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="featured" class="ml-2 block text-sm text-gray-700">
              Feature this work (show prominently to customers)
            </label>
          </div>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="flex justify-end space-x-4">
        <a href="portfolio_manage.php" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
          Cancel
        </a>
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
          Update Portfolio Item
        </button>
      </div>
    </form>
  </div>
</body>
</html>

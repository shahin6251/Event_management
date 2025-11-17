<?php
include 'check_session.php';
include 'db.php';
require_role('organizer');

$organizer_id = $_SESSION['user_id'];
$message = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $portfolio_id = (int)$_GET['delete'];
    
    // Get file paths before deletion
    $stmt = $conn->prepare("SELECT images, videos FROM organizer_portfolio WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $portfolio_id, $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $portfolio = $result->fetch_assoc();
    $stmt->close();
    
    if ($portfolio) {
        // Delete files
        $images = json_decode($portfolio['images'], true);
        $videos = json_decode($portfolio['videos'], true);
        
        if ($images) {
            foreach ($images as $image) {
                if (file_exists($image)) {
                    unlink($image);
                }
            }
        }
        
        if ($videos) {
            foreach ($videos as $video) {
                if (file_exists($video)) {
                    unlink($video);
                }
            }
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM organizer_portfolio WHERE id = ? AND organizer_id = ?");
        $stmt->bind_param("ii", $portfolio_id, $organizer_id);
        if ($stmt->execute()) {
            $message = 'Portfolio item deleted successfully.';
        }
        $stmt->close();
    }
}

// Handle toggle featured
if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $portfolio_id = (int)$_GET['toggle_featured'];
    $stmt = $conn->prepare("UPDATE organizer_portfolio SET featured = NOT featured WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $portfolio_id, $organizer_id);
    if ($stmt->execute()) {
        $message = 'Featured status updated.';
    }
    $stmt->close();
}

// Get all portfolio items
$portfolio_items = [];
$portfolio_table_exists = false;
try {
    $stmt = $conn->prepare("SELECT * FROM organizer_portfolio WHERE organizer_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $portfolio_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $portfolio_table_exists = true;
} catch (mysqli_sql_exception $e) {
    // Portfolio table doesn't exist yet
    $portfolio_table_exists = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Portfolio</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .portfolio-card {
      transition: all 0.3s ease;
    }
    .portfolio-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .line-clamp-1 {
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
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
    .animate-fadeInUp {
      animation: fadeInUp 0.6s ease-out;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Manage Portfolio</h1>
          <p class="text-gray-600 mt-1">Edit and organize your work showcase</p>
        </div>
        <div class="flex space-x-4">
          <a href="organizer_dashboard.php" class="text-gray-600 hover:text-gray-900">← Dashboard</a>
          <a href="portfolio_add.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add New Work</a>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if ($message): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if (!$portfolio_table_exists): ?>
      <!-- Setup Required Message -->
      <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Portfolio Setup Required</h3>
            <div class="mt-2 text-sm text-yellow-700">
              <p>The portfolio feature requires a database table that hasn't been created yet. Please run the following SQL in phpMyAdmin:</p>
              <div class="mt-3 bg-white p-4 rounded border border-yellow-200 overflow-x-auto">
                <code class="text-xs">
CREATE TABLE IF NOT EXISTS `organizer_portfolio` (<br>
&nbsp;&nbsp;`id` INT NOT NULL AUTO_INCREMENT,<br>
&nbsp;&nbsp;`organizer_id` INT NOT NULL,<br>
&nbsp;&nbsp;`title` VARCHAR(200) NOT NULL,<br>
&nbsp;&nbsp;`description` TEXT,<br>
&nbsp;&nbsp;`event_type` VARCHAR(100),<br>
&nbsp;&nbsp;`event_date` DATE,<br>
&nbsp;&nbsp;`client_name` VARCHAR(100),<br>
&nbsp;&nbsp;`location` VARCHAR(150),<br>
&nbsp;&nbsp;`images` TEXT,<br>
&nbsp;&nbsp;`videos` TEXT,<br>
&nbsp;&nbsp;`featured` BOOLEAN DEFAULT FALSE,<br>
&nbsp;&nbsp;`status` ENUM('draft', 'published') DEFAULT 'published',<br>
&nbsp;&nbsp;`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,<br>
&nbsp;&nbsp;`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,<br>
&nbsp;&nbsp;PRIMARY KEY (`id`),<br>
&nbsp;&nbsp;KEY `idx_organizer_portfolio` (`organizer_id`),<br>
&nbsp;&nbsp;CONSTRAINT `fk_portfolio_organizer`<br>
&nbsp;&nbsp;&nbsp;&nbsp;FOREIGN KEY (`organizer_id`) REFERENCES `users`(`user_id`)<br>
&nbsp;&nbsp;&nbsp;&nbsp;ON DELETE CASCADE<br>
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                </code>
              </div>
              <p class="mt-3">After running this SQL, refresh this page to start adding portfolio items.</p>
            </div>
          </div>
        </div>
      </div>
    <?php elseif (empty($portfolio_items)): ?>
      <!-- Empty State -->
      <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200">
        <svg class="w-24 h-24 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">No Portfolio Items Yet</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">Start building your portfolio by adding your completed events. Showcase your work to attract more customers and grow your business.</p>
        <a href="portfolio_add.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium inline-flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Your First Work
        </a>
      </div>
    <?php else: ?>
      <!-- Portfolio Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($portfolio_items as $item): ?>
          <div class="portfolio-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fadeInUp">
            <?php 
            $images = json_decode($item['images'], true);
            $first_image = !empty($images) ? $images[0] : null;
            ?>
            
            <!-- Image -->
            <div class="relative">
              <?php if ($first_image): ?>
                <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
              <?php else: ?>
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                  <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              <?php endif; ?>
              
              <!-- Status Badges -->
              <div class="absolute top-3 left-3 flex space-x-2">
                <?php if ($item['featured']): ?>
                  <span class="bg-yellow-500 text-white text-xs font-medium px-2 py-1 rounded-full">⭐ Featured</span>
                <?php endif; ?>
                <?php if ($item['status'] === 'draft'): ?>
                  <span class="bg-gray-500 text-white text-xs font-medium px-2 py-1 rounded-full">Draft</span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Content -->
            <div class="p-4">
              <div class="flex items-start justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900 line-clamp-1"><?php echo htmlspecialchars($item['title']); ?></h3>
              </div>
              
              <div class="space-y-1 text-sm text-gray-600 mb-3">
                <p><strong>Type:</strong> <?php echo htmlspecialchars($item['event_type']); ?></p>
                <p><strong>Date:</strong> <?php echo date('M j, Y', strtotime($item['event_date'])); ?></p>
                <?php if ($item['client_name']): ?>
                  <p><strong>Client:</strong> <?php echo htmlspecialchars($item['client_name']); ?></p>
                <?php endif; ?>
                <?php if ($item['location']): ?>
                  <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                <?php endif; ?>
              </div>
              
              <p class="text-sm text-gray-700 line-clamp-2 mb-4"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</p>
              
              <!-- Media Count -->
              <div class="flex items-center space-x-4 text-xs text-gray-500 mb-4">
                <?php 
                $image_count = $images ? count($images) : 0;
                $videos = json_decode($item['videos'], true);
                $video_count = $videos ? count($videos) : 0;
                ?>
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <?php echo $image_count; ?> images
                </span>
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                  </svg>
                  <?php echo $video_count; ?> videos
                </span>
              </div>

              <!-- Actions -->
              <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                  <a href="portfolio_edit.php?id=<?php echo $item['id']; ?>" 
                     class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                  <a href="?toggle_featured=<?php echo $item['id']; ?>" 
                     class="text-yellow-600 hover:text-yellow-800 text-sm font-medium"
                     onclick="return confirm('Toggle featured status?')">
                     <?php echo $item['featured'] ? 'Unfeature' : 'Feature'; ?>
                  </a>
                </div>
                <a href="?delete=<?php echo $item['id']; ?>" 
                   class="text-red-600 hover:text-red-800 text-sm font-medium"
                   onclick="return confirm('Are you sure you want to delete this portfolio item? This action cannot be undone.')">Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Add More Button -->
      <div class="text-center mt-8">
        <a href="portfolio_add.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium inline-flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Another Work
        </a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

<?php
include 'check_session.php';
include 'db.php';
require_role('organizer');

$organizer_id = $_SESSION['user_id'];

// Initialize default values
$stats = ['total_works' => 0, 'featured_works' => 0, 'recent_works' => 0];
$recent_works = [];
$portfolio_table_exists = false;

// Check if portfolio table exists and get stats
try {
    $portfolio_stmt = $conn->prepare("SELECT COUNT(*) as total_works, 
                                             COUNT(CASE WHEN featured = 1 THEN 1 END) as featured_works,
                                             COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_works
                                      FROM organizer_portfolio WHERE organizer_id = ?");
    $portfolio_stmt->bind_param("i", $organizer_id);
    $portfolio_stmt->execute();
    $result = $portfolio_stmt->get_result()->fetch_assoc();
    if ($result) {
        $stats = $result;
    }
    $portfolio_stmt->close();
    $portfolio_table_exists = true;
    
    // Get recent portfolio items
    $recent_stmt = $conn->prepare("SELECT * FROM organizer_portfolio WHERE organizer_id = ? ORDER BY created_at DESC LIMIT 3");
    $recent_stmt->bind_param("i", $organizer_id);
    $recent_stmt->execute();
    $recent_works = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $recent_stmt->close();
} catch (mysqli_sql_exception $e) {
    // Portfolio table doesn't exist yet - this is okay, we'll show a setup message
    $portfolio_table_exists = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organizer Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="organizer_dashboard.css">
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Organizer Dashboard</h1>
          <p class="text-gray-600 mt-1">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
        <div class="flex space-x-4">
          <a href="index.php" class="text-gray-600 hover:text-gray-900">Home</a>
          <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Works</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_works']; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Featured Works</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['featured_works']; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Recent Works</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['recent_works']; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center">
          <div class="p-2 bg-purple-100 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Manage Orders</p>
            <a href="orders.php" class="text-lg font-bold text-purple-600 hover:text-purple-800">View →</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Quick Actions</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="dashboard-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <div class="flex items-center mb-4">
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">Add New Work</h3>
              <p class="text-gray-600">Upload your latest project</p>
            </div>
          </div>
          <a href="portfolio_add.php" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Portfolio Item
          </a>
        </div>

        <div class="dashboard-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <div class="flex items-center mb-4">
            <div class="p-3 bg-green-100 rounded-lg">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">Manage Portfolio</h3>
              <p class="text-gray-600">Edit your existing works</p>
            </div>
          </div>
          <a href="portfolio_manage.php" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200 flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Manage Works
          </a>
        </div>

        <div class="dashboard-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
          <div class="flex items-center mb-4">
            <div class="p-3 bg-orange-100 rounded-lg">
              <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">Profile Settings</h3>
              <p class="text-gray-600">Update your information</p>
            </div>
          </div>
          <a href="profile.php" class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 transition duration-200 flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Profile
          </a>
        </div>
      </div>
    </div>

    <!-- Recent Works -->
    <div>
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Works</h2>
      <?php if (empty($recent_works)): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-200 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">No portfolio items yet</h3>
          <p class="text-gray-600 mb-6">Start building your portfolio by adding your completed events and showcasing your work to attract more customers.</p>
          <a href="portfolio_add.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">Add Your First Work</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($recent_works as $work): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition duration-200">
              <?php 
              $images = json_decode($work['images'], true);
              $first_image = !empty($images) ? $images[0] : null;
              ?>
              <?php if ($first_image): ?>
                <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($work['title']); ?>" class="w-full h-48 object-cover">
              <?php else: ?>
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                  <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              <?php endif; ?>
              <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                  <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($work['title']); ?></h3>
                  <?php if ($work['featured']): ?>
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">⭐ Featured</span>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($work['event_type']); ?></p>
                <p class="text-sm text-gray-500 mb-3"><?php echo date('M j, Y', strtotime($work['event_date'])); ?></p>
                <p class="text-sm text-gray-700 line-clamp-2"><?php echo htmlspecialchars(substr($work['description'], 0, 100)); ?>...</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-6">
          <a href="portfolio_manage.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 font-medium">View All Works</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>


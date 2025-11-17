<?php
session_start();
include 'db.php';

// Get organizer ID from URL
$organizer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$organizer_id) {
    header('Location: index.php');
    exit;
}

// Fetch organizer details
$stmt = $conn->prepare("SELECT u.name, u.email, u.phone, op.page_title, op.description, op.profile_pic, op.event_types
                        FROM users u
                        JOIN organizer_pages op ON u.user_id = op.user_id
                        WHERE u.user_id = ? AND u.role = 'organizer'");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$organizer = $result->fetch_assoc();
$stmt->close();

// Fetch portfolio items
$portfolio_items = [];
try {
    $portfolio_stmt = $conn->prepare("SELECT * FROM organizer_portfolio WHERE organizer_id = ? AND status = 'published' ORDER BY featured DESC, created_at DESC LIMIT 6");
    $portfolio_stmt->bind_param("i", $organizer_id);
    $portfolio_stmt->execute();
    $portfolio_items = $portfolio_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $portfolio_stmt->close();
} catch (Exception $e) {
    // Portfolio table doesn't exist yet
    $portfolio_items = [];
}

if (!$organizer) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($organizer['page_title']); ?> - Organizer Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="organizer_view.css">
</head>
<body class="bg-gray-50">
  <!-- Header -->
  <header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Event Management</h1>
      <a href="index.php" class="text-blue-600 hover:text-blue-800 font-medium">← Back to Home</a>
    </div>
  </header>

  <!-- Profile Section -->
  <div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="profile-header rounded-2xl p-8 text-white mb-8">
      <div class="flex items-center space-x-6">
        <div class="w-32 h-32 rounded-full overflow-hidden bg-white/20 flex items-center justify-center">
          <?php if ($organizer['profile_pic']): ?>
            <img src="<?php echo htmlspecialchars($organizer['profile_pic']); ?>" alt="Profile" class="w-full h-full object-cover">
          <?php else: ?>
            <div class="text-4xl font-bold"><?php echo substr($organizer['name'], 0, 1); ?></div>
          <?php endif; ?>
        </div>
        <div>
          <h1 class="text-4xl font-bold mb-2"><?php echo htmlspecialchars($organizer['page_title']); ?></h1>
          <p class="text-xl opacity-90 mb-4"><?php echo htmlspecialchars($organizer['description']); ?></p>
          <?php if ($organizer['event_types']): ?>
            <div class="flex flex-wrap gap-2">
              <?php foreach (explode(',', $organizer['event_types']) as $type): ?>
                <span class="bg-white/20 px-3 py-1 rounded-full text-sm"><?php echo trim(htmlspecialchars($type)); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="grid md:grid-cols-2 gap-8">
      <!-- Contact Details -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900">Contact Information</h2>
        <div class="space-y-4">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="font-medium"><?php echo htmlspecialchars($organizer['email']); ?></p>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Phone</p>
              <p class="font-medium"><?php echo htmlspecialchars($organizer['phone']); ?></p>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Location</p>
              <p class="font-medium"><?php echo htmlspecialchars($organizer['location']); ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Card -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900">Book This Organizer</h2>
        <p class="text-gray-600 mb-6">Ready to plan your event? Get in touch with <?php echo htmlspecialchars($organizer['name']); ?> to discuss your requirements.</p>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
          <a href="place_order.php?org_id=<?php echo $organizer_id; ?>" 
             class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition duration-300 flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v2a2 2 0 002 2h6a2 2 0 002-2v-2"></path>
            </svg>
            Place Order
          </a>
        <?php else: ?>
          <a href="login.php" 
             class="w-full bg-gray-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-gray-700 transition duration-300 flex items-center justify-center">
            Login to Place Order
          </a>
        <?php endif; ?>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
          <p class="text-sm text-gray-500 text-center">
            Contact directly: 
            <a href="mailto:<?php echo htmlspecialchars($organizer['email']); ?>" class="text-blue-600 hover:text-blue-800">
              <?php echo htmlspecialchars($organizer['email']); ?>
            </a>
          </p>
        </div>
      </div>
    </div>

    <!-- Portfolio Section -->
    <?php if (!empty($portfolio_items)): ?>
    <div class="mt-16 relative">
      <!-- Section Header -->
      <div class="text-center mb-12">
        <div class="inline-block">
          <h2 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-4">
            Our Portfolio
          </h2>
          <div class="w-24 h-1 bg-gradient-to-r from-purple-600 to-blue-600 mx-auto rounded-full"></div>
        </div>
        <p class="text-xl text-gray-600 mt-6 max-w-2xl mx-auto">
          Discover the magic we create for our clients. Each event tells a unique story of celebration, joy, and unforgettable moments.
        </p>
      </div>
      
      <!-- Portfolio Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($portfolio_items as $item): ?>
          <div class="portfolio-card bg-white rounded-2xl shadow-xl overflow-hidden group">
            <?php 
            $images = json_decode($item['images'], true);
            $first_image = !empty($images) ? $images[0] : null;
            ?>
            
            <!-- Image Container -->
            <div class="relative h-64 overflow-hidden">
              <?php if ($first_image): ?>
                <img src="<?php echo htmlspecialchars($first_image); ?>" 
                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                     class="portfolio-image w-full h-full object-cover">
              <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-purple-100 via-blue-50 to-indigo-100 flex items-center justify-center">
                  <div class="text-center">
                    <svg class="w-20 h-20 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-400 text-sm font-medium">Event Gallery</p>
                  </div>
                </div>
              <?php endif; ?>
              
              <!-- Overlay -->
              <div class="portfolio-overlay absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              
              <!-- Featured Badge -->
              <?php if ($item['featured']): ?>
                <div class="absolute top-4 right-4 z-10">
                  <span class="featured-badge text-black text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    Featured
                  </span>
                </div>
              <?php endif; ?>
              
              <!-- Event Type Badge -->
              <div class="absolute bottom-4 left-4 z-10">
                <span class="glass-effect text-white text-sm font-semibold px-3 py-2 rounded-full shadow-lg">
                  <?php echo htmlspecialchars($item['event_type']); ?>
                </span>
              </div>
              
              <!-- View Details Button -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 z-10">
                <button onclick="openPortfolioModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                        class="bg-white text-gray-900 px-6 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                  View Details
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 relative z-20">
              <!-- Title -->
              <div class="mb-4">
                <h3 class="text-xl font-bold text-gray-900 mb-2 leading-tight"><?php echo htmlspecialchars($item['title']); ?></h3>
                <div class="w-12 h-0.5 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full"></div>
              </div>
              
              <!-- Event Details -->
              <div class="space-y-3 mb-4">
                <div class="flex items-center text-sm text-gray-600">
                  <div class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm0 0v2a2 2 0 002 2h6a2 2 0 002-2v-2"></path>
                    </svg>
                  </div>
                  <span class="font-medium"><?php echo date('F j, Y', strtotime($item['event_date'])); ?></span>
                </div>
                
                <?php if ($item['client_name']): ?>
                  <div class="flex items-center text-sm text-gray-600">
                    <div class="w-8 h-8 bg-green-50 rounded-full flex items-center justify-center mr-3">
                      <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <span class="font-medium"><?php echo htmlspecialchars($item['client_name']); ?></span>
                  </div>
                <?php endif; ?>
                
                <?php if ($item['location']): ?>
                  <div class="flex items-center text-sm text-gray-600">
                    <div class="w-8 h-8 bg-purple-50 rounded-full flex items-center justify-center mr-3">
                      <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </div>
                    <span class="font-medium"><?php echo htmlspecialchars($item['location']); ?></span>
                  </div>
                <?php endif; ?>
              </div>
              
              <!-- Description -->
              <p class="text-gray-700 text-sm leading-relaxed mb-6 line-clamp-3"><?php echo htmlspecialchars($item['description']); ?></p>
              
              <!-- Media Stats & Action -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="flex items-center space-x-4">
                  <?php 
                  $image_count = $images ? count($images) : 0;
                  $videos = json_decode($item['videos'], true);
                  $video_count = $videos ? count($videos) : 0;
                  ?>
                  <div class="flex items-center text-xs text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                    <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium"><?php echo $image_count; ?> photos</span>
                  </div>
                  <?php if ($video_count > 0): ?>
                    <div class="flex items-center text-xs text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                      <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                      </svg>
                      <span class="font-medium"><?php echo $video_count; ?> videos</span>
                    </div>
                  <?php endif; ?>
                </div>
                
                <button onclick="openPortfolioModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                        class="text-blue-600 hover:text-blue-800 font-semibold text-sm flex items-center group">
                  <span>Explore</span>
                  <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Portfolio Modal -->
  <div id="portfolioModal" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden shadow-2xl transform transition-all duration-300 scale-95 opacity-0" id="modalContainer">
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-6 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
        <div class="relative z-10 flex justify-between items-start">
          <div>
            <h3 id="modalTitle" class="text-3xl font-bold mb-2"></h3>
            <div class="w-16 h-1 bg-white bg-opacity-50 rounded-full"></div>
          </div>
          <button onclick="closePortfolioModal()" class="text-white hover:text-gray-200 transition-colors duration-200 p-2 hover:bg-white hover:bg-opacity-20 rounded-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Modal Content -->
      <div class="max-h-[calc(90vh-120px)] overflow-y-auto">
        <div id="modalContent" class="p-8">
          <!-- Content will be populated by JavaScript -->
        </div>
      </div>
    </div>
  </div>

  <script src="organizer_view.js"></script>
</body>
</html>

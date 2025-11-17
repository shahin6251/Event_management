<?php
/**
 * organizer_profile_view.php
 * Customer view of organizer profile with portfolio
 */

session_start();
include 'check_session.php';
include 'db.php';

// Get organizer ID from URL
$organizer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($organizer_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get organizer info
$organizer_stmt = $conn->prepare("
    SELECT u.user_id, u.name, u.email, u.phone, u.profile_pic, op.page_title, op.description
    FROM users u
    LEFT JOIN organizer_pages op ON op.user_id = u.user_id
    WHERE u.user_id = ? AND u.role = 'organizer'
");
$organizer_stmt->bind_param("i", $organizer_id);
$organizer_stmt->execute();
$organizer_result = $organizer_stmt->get_result();

if ($organizer_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$organizer = $organizer_result->fetch_assoc();
$organizer_stmt->close();

// Get portfolio items
$portfolio_items = [];
try {
    $portfolio_stmt = $conn->prepare("
        SELECT id, title, description, event_type, event_date, client_name, location, images, videos, featured, created_at
        FROM organizer_portfolio
        WHERE organizer_id = ? AND status = 'published'
        ORDER BY featured DESC, created_at DESC
    ");
    $portfolio_stmt->bind_param("i", $organizer_id);
    $portfolio_stmt->execute();
    $portfolio_items = $portfolio_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $portfolio_stmt->close();
} catch (Exception $e) {
    $portfolio_items = [];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($organizer['page_title'] ?? $organizer['name']); ?> - Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            display: flex;
            gap: 30px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            min-width: 120px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            min-width: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            flex-shrink: 0;
        }

        .profile-info {
            flex: 1;
            min-width: 250px;
        }

        .profile-info h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .profile-info p {
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .profile-info strong {
            color: #374151;
            font-weight: 600;
        }

        .rating {
            color: #f59e0b;
            font-size: 14px;
            margin-top: 12px;
            font-weight: 500;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .portfolio-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }

        .portfolio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .portfolio-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f3f4f6;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .portfolio-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .portfolio-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .portfolio-meta {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .portfolio-description {
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
            margin-bottom: 12px;
            flex: 1;
        }

        .portfolio-badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            opacity: 0.5;
            color: #d1d5db;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 14px;
        }

        .btn-back {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }

            .profile-info {
                width: 100%;
            }

            .portfolio-grid {
                grid-template-columns: 1fr;
            }

            .header {
                padding: 20px;
            }

            .section-title {
                font-size: 20px;
            }

            .section-title::before {
                height: 24px;
            }
        }

        @media (max-width: 480px) {
            .profile-card {
                padding: 20px;
            }

            .profile-info h1 {
                font-size: 22px;
            }

            .section-title {
                font-size: 18px;
            }

            .portfolio-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <a href="index.php" class="btn-back">← Back to Home</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container" style="padding-top: 40px; padding-bottom: 40px;">
        <!-- Profile Card -->
        <div class="profile-card">
            <?php if (!empty($organizer['profile_pic']) && file_exists($organizer['profile_pic'])): ?>
                <img src="<?php echo htmlspecialchars($organizer['profile_pic']); ?>" alt="<?php echo htmlspecialchars($organizer['name']); ?>" class="profile-pic">
            <?php else: ?>
                <div class="profile-avatar"><?php echo strtoupper(substr($organizer['name'], 0, 1)); ?></div>
            <?php endif; ?>

            <div class="profile-info">
                <h1><?php echo htmlspecialchars($organizer['page_title'] ?? $organizer['name']); ?></h1>
                <p><strong>Organizer:</strong> <?php echo htmlspecialchars($organizer['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($organizer['email']); ?></p>
                <?php if (!empty($organizer['phone'])): ?>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($organizer['phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($organizer['rating'])): ?>
                    <div class="rating">⭐ Rating: <?php echo htmlspecialchars($organizer['rating']); ?>/5</div>
                <?php endif; ?>
                <?php if (!empty($organizer['description'])): ?>
                    <p style="margin-top: 16px; color: #374151; line-height: 1.6;">
                        <?php echo htmlspecialchars($organizer['description']); ?>
                    </p>
                <?php endif; ?>
                <div style="margin-top: 20px; display: flex; gap: 12px;">
                    <a href="place_order.php?org_id=<?php echo $organizer_id; ?>" 
                       style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(102, 126, 234, 0.4)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)';">
                        📋 Place Order
                    </a>
                </div>
            </div>
        </div>

        <!-- Portfolio Section -->
        <div>
            <h2 class="section-title">Portfolio & Works</h2>

            <?php if (empty($portfolio_items)): ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 8px;">No Portfolio Items Yet</h3>
                    <p style="color: #6b7280;">This organizer hasn't added any portfolio items yet.</p>
                </div>
            <?php else: ?>
                <div class="portfolio-grid">
                    <?php foreach ($portfolio_items as $work): ?>
                        <div class="portfolio-card">
                            <?php 
                            $images = json_decode($work['images'], true);
                            $first_image = !empty($images) ? $images[0] : null;
                            ?>
                            <?php if ($first_image && file_exists($first_image)): ?>
                                <img src="<?php echo htmlspecialchars($first_image); ?>" alt="<?php echo htmlspecialchars($work['title']); ?>" class="portfolio-image">
                            <?php else: ?>
                                <div class="portfolio-image" style="display: flex; align-items: center; justify-content: center;">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <div class="portfolio-content">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 8px; margin-bottom: 8px;">
                                    <h3 class="portfolio-title"><?php echo htmlspecialchars($work['title']); ?></h3>
                                    <?php if ($work['featured']): ?>
                                        <span class="portfolio-badge">⭐ Featured</span>
                                    <?php endif; ?>
                                </div>

                                <div class="portfolio-meta">
                                    <strong><?php echo htmlspecialchars($work['event_type']); ?></strong> • 
                                    <?php echo date('M j, Y', strtotime($work['event_date'])); ?>
                                </div>

                                <?php if (!empty($work['client_name'])): ?>
                                    <div class="portfolio-meta">
                                        <strong>Client:</strong> <?php echo htmlspecialchars($work['client_name']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($work['location'])): ?>
                                    <div class="portfolio-meta">
                                        <strong>Location:</strong> <?php echo htmlspecialchars($work['location']); ?>
                                    </div>
                                <?php endif; ?>

                                <p class="portfolio-description">
                                    <?php echo htmlspecialchars(substr($work['description'], 0, 150)); ?>
                                    <?php if (strlen($work['description']) > 150): ?>...<?php endif; ?>
                                </p>

                                <?php 
                                $videos = json_decode($work['videos'], true);
                                if (!empty($videos)): 
                                ?>
                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                                        <span style="font-size: 12px; color: #6b7280;">📹 <?php echo count($videos); ?> video(s)</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

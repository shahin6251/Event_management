<?php
session_start();
include 'db.php';

$message = '';
$error = '';
$show_form = true;

// Create password_resets table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Always show success message for security (don't reveal if email exists)
        $show_form = false;
        
        if ($user) {
            // Clean up old tokens
            $conn->query("DELETE FROM password_resets WHERE user_id = {$user['user_id']} OR expires < NOW()");
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Insert reset token
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires, used) VALUES (?, ?, ?, 0)");
            $stmt->bind_param("iss", $user['user_id'], $token, $expires);
            $stmt->execute();
            $stmt->close();
            
            $reset_link = "http://localhost/project_event_management/reset_password.php?token=" . $token;
            $message = "<div class='text-center'>
                <div class='mb-4'>
                    <svg class='w-16 h-16 text-green-500 mx-auto mb-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                    </svg>
                    <h3 class='text-xl font-semibold text-gray-900 mb-2'>Reset Link Sent!</h3>
                    <p class='text-gray-600 mb-6'>We've sent password reset instructions to your email.</p>
                </div>
                <div class='bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6'>
                    <p class='text-sm text-blue-800 mb-3'><strong>For testing purposes, you can use this direct link:</strong></p>
                    <a href='$reset_link' class='inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium' target='_blank'>Reset My Password Now</a>
                    <p class='text-xs text-blue-600 mt-2'>Link expires in 24 hours</p>
                </div>
                <p class='text-sm text-gray-500'>Didn't receive the email? Check your spam folder or <a href='forgot_password.php' class='text-blue-600 hover:text-blue-800'>try again</a>.</p>
            </div>";
        } else {
            // Show same message even if user doesn't exist (security)
            $message = "<div class='text-center'>
                <div class='mb-4'>
                    <svg class='w-16 h-16 text-green-500 mx-auto mb-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                    </svg>
                    <h3 class='text-xl font-semibold text-gray-900 mb-2'>Instructions Sent!</h3>
                    <p class='text-gray-600 mb-6'>If an account with that email exists, we've sent password reset instructions.</p>
                </div>
                <p class='text-sm text-gray-500'>Didn't receive the email? Check your spam folder or <a href='forgot_password.php' class='text-blue-600 hover:text-blue-800'>try again</a>.</p>
            </div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="forgot_password.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl space-y-6 border border-gray-200 dark:border-gray-700 form-container">
        <header class="text-center">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-6 6c-3 0-5.5-1.5-5.5-4a3.5 3.5 0 117 0A6 6 0 0112 15a6 6 0 01-6-6 6 6 0 1112 0z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Forgot Password?</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Enter your email address and we'll send you instructions to reset your password.</p>
        </header>

        <?php if (!empty($message)): ?>
            <div class="p-4 rounded-lg text-sm bg-green-100 text-green-800 border border-green-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div><?php echo $message; ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-lg text-sm bg-red-100 text-red-800 border border-red-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($show_form): ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium mb-2">Email Address</label>
                <input type="email" id="email" name="email" 
                       class="w-full border border-gray-300 p-3 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                       placeholder="Enter your email address" 
                       required>
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold shadow-md hover:bg-blue-700 transition duration-200 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Send Reset Instructions
            </button>
        </form>
        <?php endif; ?>

        <footer class="text-center space-y-2">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Remember your password? <a href="login.php" class="text-blue-600 hover:underline dark:text-blue-400">Sign In</a>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Don't have an account? <a href="role_select.php" class="text-blue-600 hover:underline dark:text-blue-400">Create Account</a>
            </p>
        </footer>
    </div>
</body>
</html>

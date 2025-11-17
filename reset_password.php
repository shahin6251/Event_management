<?php
session_start();
include 'db.php';

$token = $_GET['token'] ?? '';
$message = '';
$error = '';
$valid_token = false;
$user_data = null;

// Validate token
if ($token) {
    // Clean up expired tokens first
    $conn->query("DELETE FROM password_resets WHERE expires < NOW()");
    
    // Get token data
    $stmt = $conn->prepare("SELECT pr.*, u.name, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.user_id WHERE pr.token = ? AND pr.used = 0 AND pr.expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
    
    if ($user_data) {
        $valid_token = true;
    } else {
        $error = 'This reset link is invalid, expired, or has already been used. Please request a new password reset.';
    }
} else {
    $error = 'No reset token provided. Please request a new password reset.';
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("si", $password_hash, $user_data['user_id']);
        
        if ($stmt->execute()) {
            // Mark token as used
            $stmt2 = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt2->bind_param("s", $token);
            $stmt2->execute();
            $stmt2->close();
            
            $message = 'Password reset successfully! You can now login with your new password.';
            $valid_token = false; // Hide form after successful reset
        } else {
            $error = 'Error updating password. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="reset_password.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl space-y-6 border border-gray-200 dark:border-gray-700 form-container">
        <header class="text-center">
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Reset Password</h2>
            <?php if ($valid_token && $user_data): ?>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Setting new password for: <strong><?php echo htmlspecialchars($user_data['email']); ?></strong>
                </p>
            <?php else: ?>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Enter your new password below.</p>
            <?php endif; ?>
        </header>

        <?php if (!empty($message)): ?>
            <div class="p-4 rounded-lg text-sm bg-green-100 text-green-800 border border-green-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <?php echo htmlspecialchars($message); ?>
                        <div class="mt-3">
                            <a href="login.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Go to Login
                            </a>
                        </div>
                    </div>
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
                <?php if (!$valid_token): ?>
                    <div class="mt-3">
                        <a href="forgot_password.php" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Request New Reset
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($valid_token && empty($message)): ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . htmlspecialchars($token); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium mb-2">New Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full border border-gray-300 p-3 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" 
                           placeholder="Enter new password" 
                           minlength="6"
                           required>
                    <div class="mt-2">
                        <div class="password-strength bg-gray-200" id="strengthBar"></div>
                        <p class="text-xs text-gray-500 mt-1" id="strengthText">Password must be at least 6 characters</p>
                    </div>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium mb-2">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="w-full border border-gray-300 p-3 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" 
                           placeholder="Confirm new password" 
                           minlength="6"
                           required>
                </div>
                
                <button type="submit" 
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold shadow-md hover:bg-green-700 transition duration-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Update Password
                </button>
            </form>
        <?php endif; ?>

        <footer class="text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Remember your password? <a href="login.php" class="text-blue-600 hover:underline dark:text-blue-400">Sign In</a>
            </p>
        </footer>
    </div>

    <script src="reset_password.js"></script>
</body>
</html>

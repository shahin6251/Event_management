<?php
session_start();

// Check if already logged in (but don't auto-redirect)
$already_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['role']);
$current_user = $already_logged_in ? $_SESSION['name'] : null;
$current_role = $already_logged_in ? $_SESSION['role'] : null;

include 'db.php';

$login_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn instanceof mysqli) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $login_message = 'Please enter both email and password.';
    } else {
        if ($stmt = $conn->prepare("SELECT user_id, name, role, password_hash FROM users WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if ($row && !empty($row['password_hash']) && password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = (int)$row['user_id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];

                if ($row['role'] === 'organizer') {
                    header('Location: organizer_dashboard.php'); exit;
                } elseif ($row['role'] === 'customer') {
                    header('Location: index.php'); exit;
                } elseif ($row['role'] === 'admin') {
                    header('Location: admin_dashboard.php'); exit;
                } else {
                    header('Location: index.php'); exit;
                }
            } else {
                $login_message = 'Invalid email or password.';
            }
        } else {
            $login_message = 'Database error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Login</title>
 <script src="https://cdn.tailwindcss.com"></script>
 <link rel="stylesheet" href="login.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col items-center justify-center p-4">
  <div class="max-w-md w-full bg-white dark:bg-gray-800 p-8 rounded-xl shadow-2xl space-y-6 border border-gray-200 dark:border-gray-700">
    <header class="text-center">
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Sign In</h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Welcome back! Please sign in to continue.</p>
    </header>

    <?php if ($already_logged_in): ?>
      <div class="p-4 rounded-lg text-sm bg-blue-100 text-blue-800 border border-blue-200">
        <div class="flex justify-between items-center">
          <div>
            <strong>Already logged in as:</strong> <?php echo htmlspecialchars($current_user); ?> (<?php echo ucfirst($current_role); ?>)
          </div>
          <div class="space-x-2">
            <?php if ($current_role === 'customer'): ?>
              <a href="index.php" class="text-blue-600 hover:text-blue-800 font-medium">Go to Homepage</a>
            <?php elseif ($current_role === 'organizer'): ?>
              <a href="organizer_dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">Go to Dashboard</a>
            <?php elseif ($current_role === 'admin'): ?>
              <a href="admin_dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">Go to Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium">Logout</a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($login_message)): ?>
      <div class="p-3 rounded-lg text-sm font-medium bg-red-100 text-red-700">
        <?php echo htmlspecialchars($login_message); ?>
      </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6">
      <div>
        <label for="email" class="block text-sm font-medium mb-1">Email</label>
        <input type="email" id="email" name="email" class="w-full border p-3 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="you@example.com" required>
      </div>
      <div>
        <div class="flex justify-between items-center">
          <label for="password" class="block text-sm font-medium mb-1">Password</label>
          <a href="forgot_password.php" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">Forgot Password?</a>
        </div>
        <input type="password" id="password" name="password" class="w-full border p-3 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
      </div>
      <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold shadow-md hover:bg-indigo-700">Sign In</button>
    </form>

    <footer class="text-center space-y-2">
      <p class="text-sm text-gray-500 dark:text-gray-400">
        New here? <a href="role_select.php" class="text-indigo-600 hover:underline dark:text-indigo-400">Create Account</a>
      </p>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Need help? <a href="password_reset_guide.php" class="text-indigo-600 hover:underline dark:text-indigo-400">Password Reset Guide</a>
      </p>
    </footer>
  </div>
</body>
</html>



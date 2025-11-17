<?php
/**
 * customer_profile_edit.php
 * Customer profile edit page
 */

session_start();
include 'check_session.php';
require_role('customer');
include 'db.php';

$customer_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get current customer info
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else {
        
        // If no error, update customer info
        if (empty($error)) {
            // Update customer info
            $update_stmt = $conn->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ? 
                WHERE user_id = ?
            ");
            $update_stmt->bind_param("sssi", $name, $email, $phone, $customer_id);
            
            if ($update_stmt->execute()) {
                $message = 'Profile updated successfully!';
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                // Refresh customer data
                $customer['name'] = $name;
                $customer['email'] = $email;
                $customer['phone'] = $phone;
            } else {
                $error = 'Error updating profile: ' . $update_stmt->error;
            }
            $update_stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - Event Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 0;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .container {
      max-width: 600px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .card {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(102, 126, 234, 0.1);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #111827;
      font-size: 14px;
    }
    
    input, textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      font-size: 14px;
      font-family: inherit;
      transition: all 0.3s ease;
    }
    
    input:focus, textarea:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .button-group {
      display: flex;
      gap: 12px;
      margin-top: 30px;
    }
    
    button, a.button {
      flex: 1;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      text-decoration: none;
      text-align: center;
      transition: all 0.3s ease;
      font-size: 14px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-secondary {
      background: #f3f4f6;
      color: #667eea;
      border: 1px solid #e5e7eb;
    }
    
    .btn-secondary:hover {
      background: #e5e7eb;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }
    
    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }
    
    .alert-error {
      background: #fee2e2;
      color: #7f1d1d;
      border: 1px solid #fecaca;
    }
    
    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .header h1 {
      font-size: 24px;
      font-weight: 700;
    }
    
    .header a {
      color: white;
      text-decoration: none;
      padding: 8px 16px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 6px;
      transition: all 0.3s ease;
    }
    
    .header a:hover {
      background: rgba(255, 255, 255, 0.3);
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="header">
    <div class="header-content">
      <h1>✏️ Edit Profile</h1>
      <a href="customer_profile_dashboard.php">← Back to Dashboard</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="card">
      <?php if ($message): ?>
        <div class="alert alert-success">✓ <?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-error">✗ <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="name">Full Name *</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
        </div>

        <div class="button-group">
          <button type="submit" class="btn-primary">Save Changes</button>
          <a href="customer_profile_dashboard.php" class="btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>

<?php
include_once 'common/config.php';
requireLogin();

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($phone) || empty($email)) {
        $error = "All fields are required";
    } else {
        // Check if email exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = "Email already exists";
        } else {
            // Update user
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $email, $hashedPassword, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $email, $_SESSION['user_id']]);
            }
            
            $success = "Profile updated successfully";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="p-4">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <?php if (isset($success)): ?>
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="Enter new password" class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
            </div>
            
            <button type="submit" class="w-full bg-sky-600 text-white py-3 rounded font-semibold">
                Update Profile
            </button>
        </form>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
            <a href="login.php?logout=1" class="block w-full bg-red-600 text-white text-center py-3 rounded font-semibold">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>

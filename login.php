<?php
include_once 'common/config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle AJAX login/signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
        exit;
    }
    
    if ($action === 'signup') {
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        // Create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $phone, $email, $hashedPassword])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
        exit;
    }
}

include_once 'common/header.php';
?>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Welcome to <?php echo $settings['app_name']; ?></h2>
            </div>
            
            <!-- Tab Buttons -->
            <div class="flex mb-6">
                <button onclick="showTab('login')" id="loginTab" class="flex-1 py-2 px-4 bg-sky-600 text-white rounded-l">Login</button>
                <button onclick="showTab('signup')" id="signupTab" class="flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-r">Sign Up</button>
            </div>
            
            <!-- Login Form -->
            <form id="loginForm" class="space-y-4">
                <div>
                    <input type="email" name="email" placeholder="Email" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                </div>
                <div>
                    <input type="password" name="password" placeholder="Password" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                </div>
                <button type="submit" class="w-full bg-sky-600 text-white py-3 rounded font-semibold">Login</button>
            </form>
            
            <!-- Signup Form -->
            <form id="signupForm" class="space-y-4 hidden">
                <div>
                    <input type="text" name="name" placeholder="Full Name" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                </div>
                <div>
                    <input type="tel" name="phone" placeholder="Phone Number" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                </div>
                <div>
                    <input type="password" name="password" placeholder="Password" required class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                </div>
                <button type="submit" class="w-full bg-sky-600 text-white py-3 rounded font-semibold">Sign Up</button>
            </form>
            
            <div id="message" class="mt-4 text-center"></div>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const loginTab = document.getElementById('loginTab');
    const signupTab = document.getElementById('signupTab');
    
    if (tab === 'login') {
        loginForm.classList.remove('hidden');
        signupForm.classList.add('hidden');
        loginTab.classList.add('bg-sky-600', 'text-white');
        loginTab.classList.remove('bg-gray-200', 'text-gray-700');
        signupTab.classList.add('bg-gray-200', 'text-gray-700');
        signupTab.classList.remove('bg-sky-600', 'text-white');
    } else {
        signupForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
        signupTab.classList.add('bg-sky-600', 'text-white');
        signupTab.classList.remove('bg-gray-200', 'text-gray-700');
        loginTab.classList.add('bg-gray-200', 'text-gray-700');
        loginTab.classList.remove('bg-sky-600', 'text-white');
    }
}

// Handle login form
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'login');
    
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.success) {
            messageDiv.innerHTML = '<p class="text-green-600">' + data.message + '</p>';
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        } else {
            messageDiv.innerHTML = '<p class="text-red-600">' + data.message + '</p>';
        }
    });
});

// Handle signup form
document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'signup');
    
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.success) {
            messageDiv.innerHTML = '<p class="text-green-600">' + data.message + '</p>';
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        } else {
            messageDiv.innerHTML = '<p class="text-red-600">' + data.message + '</p>';
        }
    });
});
</script>

<?php include_once 'common/bottom.php'; ?>

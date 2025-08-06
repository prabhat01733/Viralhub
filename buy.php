<?php
include_once 'common/config.php';
include_once 'common/paytm.php';
requireLogin();

$courseId = $_GET['course_id'] ?? 0;

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: course.php');
    exit;
}

// Check if already purchased - handle both old and new column names
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND course_id = ? AND status = 'success'");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    if ($stmt->fetch()) {
        header('Location: watch.php?course_id=' . $courseId);
        exit;
    }
} catch (PDOException $e) {
    // If there's a database error, log it but continue
    error_log("Database error in buy.php: " . $e->getMessage());
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    header('Content-Type: application/json');
    
    $orderId = $_POST['ORDERID'] ?? '';
    $txnId = $_POST['TXNID'] ?? '';
    $checksum = $_POST['CHECKSUMHASH'] ?? '';
    
    if (empty($orderId) || empty($txnId) || empty($checksum)) {
        echo json_encode(['success' => false, 'message' => 'Missing payment parameters']);
        exit;
    }
    
    // Check if Paytm is configured
    if (empty($settings['paytm_merchant_id']) || empty($settings['paytm_merchant_key'])) {
        echo json_encode(['success' => false, 'message' => 'Payment gateway not configured']);
        exit;
    }
    
    // Initialize Paytm
    $paytm = new PaytmHelper(
        $settings['paytm_merchant_id'],
        $settings['paytm_merchant_key'],
        $settings['paytm_environment'] ?? 'staging'
    );
    
    // Verify checksum
    $params = $_POST;
    unset($params['verify_payment']);
    $checksumHash = $params['CHECKSUMHASH'];
    unset($params['CHECKSUMHASH']);
    
    if ($paytm->verifyChecksum($params, $checksumHash)) {
        // Get transaction status from Paytm
        $statusResponse = $paytm->getTransactionStatus($orderId);
        
        if ($statusResponse && $statusResponse['STATUS'] === 'TXN_SUCCESS') {
            // Update order status - handle both column names
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'success' WHERE paytm_order_id = ? AND user_id = ?");
                $stmt->execute([$orderId, $_SESSION['user_id']]);
            } catch (PDOException $e) {
                // Try with old column name if new one doesn't exist
                try {
                    $stmt = $pdo->prepare("UPDATE orders SET status = 'success' WHERE razorpay_order_id = ? AND user_id = ?");
                    $stmt->execute([$orderId, $_SESSION['user_id']]);
                } catch (PDOException $e2) {
                    echo json_encode(['success' => false, 'message' => 'Database update failed']);
                    exit;
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Payment successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment failed or pending']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
    }
    exit;
}

// Check if Paytm is configured
if (empty($settings['paytm_merchant_id']) || empty($settings['paytm_merchant_key'])) {
    include_once 'common/header.php';
    include_once 'common/sidebar.php';
    ?>
    <div class="p-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <strong>Payment Gateway Not Configured!</strong><br>
            Please contact administrator to configure Paytm payment gateway.
        </div>
    </div>
    <?php
    include_once 'common/bottom.php';
    exit;
}

// Create Paytm order
$orderId = 'ORDER_' . time() . '_' . $_SESSION['user_id'];
$amount = number_format($course['price'], 2, '.', '');

// Store order in database - handle both column names
try {
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, course_id, amount, paytm_order_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $courseId, $course['price'], $orderId]);
} catch (PDOException $e) {
    // Try with old column name if new one doesn't exist
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, course_id, amount, razorpay_order_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $courseId, $course['price'], $orderId]);
    } catch (PDOException $e2) {
        // Show error page
        include_once 'common/header.php';
        include_once 'common/sidebar.php';
        ?>
        <div class="p-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <strong>Database Error!</strong><br>
                Unable to create order. Please run the database migration script.
                <br><br>
                <code>Error: <?php echo htmlspecialchars($e2->getMessage()); ?></code>
            </div>
        </div>
        <?php
        include_once 'common/bottom.php';
        exit;
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Prepare Paytm parameters
$paytmParams = array(
    'MID' => $settings['paytm_merchant_id'],
    'ORDER_ID' => $orderId,
    'CUST_ID' => 'CUST_' . $_SESSION['user_id'],
    'TXN_AMOUNT' => $amount,
    'CHANNEL_ID' => 'WEB',
    'INDUSTRY_TYPE_ID' => 'Retail',
    'WEBSITE' => ($settings['paytm_environment'] === 'production') ? 'DEFAULT' : 'WEBSTAGING',
    'CALLBACK_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/buy.php?course_id=' . $courseId
);

// Generate checksum
$paytm = new PaytmHelper(
    $settings['paytm_merchant_id'],
    $settings['paytm_merchant_key'],
    $settings['paytm_environment'] ?? 'staging'
);

$checksum = $paytm->generateChecksum($paytmParams);
$paytmParams['CHECKSUMHASH'] = $checksum;

$paytmUrl = ($settings['paytm_environment'] === 'production') 
    ? 'https://securegw.paytm.in/order/process'
    : 'https://securegw-stage.paytm.in/order/process';

include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-center">Complete Your Purchase</h1>
        
        <!-- Course Summary -->
        <div class="border rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-4">
                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-20 h-20 object-cover rounded">
                <div class="flex-1">
                    <h3 class="font-semibold"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <div class="flex items-center space-x-2 mt-2">
                        <span class="text-2xl font-bold text-sky-600">₹<?php echo number_format($course['price']); ?></span>
                        <?php if ($course['mrp'] > $course['price']): ?>
                        <span class="text-gray-500 line-through">₹<?php echo number_format($course['mrp']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Button -->
        <form id="paytmForm" method="post" action="<?php echo $paytmUrl; ?>">
            <?php foreach ($paytmParams as $key => $value): ?>
            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
            <?php endforeach; ?>
            
            <button type="submit" class="w-full bg-sky-600 text-white py-4 rounded-lg font-semibold text-lg">
                <i class="fas fa-credit-card mr-2"></i>Pay ₹<?php echo number_format($course['price']); ?> with Paytm
            </button>
        </form>
        
        <!-- Security Info -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <i class="fas fa-shield-alt mr-1"></i>
            Your payment is secured with Paytm's secure payment gateway
        </div>
    </div>
</div>

<script>
// Handle payment response (when redirected back from Paytm)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check if this is a callback from Paytm
    if (urlParams.has('ORDERID') && urlParams.has('TXNID')) {
        // Create form data from URL parameters
        const formData = new FormData();
        formData.append('verify_payment', '1');
        
        // Add all Paytm response parameters
        for (const [key, value] of urlParams.entries()) {
            if (key !== 'course_id') {
                formData.append(key, value);
            }
        }
        
        // Verify payment
        fetch(window.location.pathname + '?course_id=' + (urlParams.get('course_id') || '<?php echo $courseId; ?>'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payment successful! Redirecting to your courses...');
                window.location.href = 'mycourses.php?success=1';
            } else {
                alert('Payment failed: ' + data.message);
                // Clean URL and show payment form again
                window.history.replaceState({}, document.title, window.location.pathname + '?course_id=<?php echo $courseId; ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error verifying payment. Please contact support.');
        });
    }
});
</script>

<?php include_once 'common/bottom.php'; ?>

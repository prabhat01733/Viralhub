<?php
include_once 'common/config.php';
requireLogin();

$courseId = $_GET['id'] ?? 0;

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: course.php');
    exit;
}

// Check if user has purchased this course
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND course_id = ? AND status = 'success'");
$stmt->execute([$_SESSION['user_id'], $courseId]);
$hasPurchased = $stmt->fetch(PDO::FETCH_ASSOC);

include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="pb-20">
    <!-- Course Image -->
    <div class="relative">
        <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full h-64 object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
            <i class="fas fa-play-circle text-white text-6xl opacity-80"></i>
        </div>
    </div>
    
    <!-- Course Details -->
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <!-- Price Section -->
        <div class="flex items-center space-x-4 mb-6">
            <span class="text-3xl font-bold text-sky-600">₹<?php echo number_format($course['price']); ?></span>
            <?php if ($course['mrp'] > $course['price']): ?>
            <span class="text-xl text-gray-500 line-through">₹<?php echo number_format($course['mrp']); ?></span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                <?php echo round((($course['mrp'] - $course['price']) / $course['mrp']) * 100); ?>% OFF
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Description -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-3">Course Description</h2>
            <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
        </div>
        
        <!-- Course Features -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-3">What you'll get</h2>
            <div class="space-y-2">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span>Lifetime access to course content</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span>High-quality video lessons</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span>Learn at your own pace</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span>Mobile and desktop access</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sticky Bottom Button -->
<div class="fixed bottom-16 left-0 right-0 p-4 bg-white border-t border-gray-200">
    <?php if ($hasPurchased): ?>
    <a href="watch.php?course_id=<?php echo $course['id']; ?>" class="block w-full bg-green-600 text-white text-center py-4 rounded-lg font-semibold text-lg">
        <i class="fas fa-play mr-2"></i>Start Learning
    </a>
    <?php else: ?>
    <a href="buy.php?course_id=<?php echo $course['id']; ?>" class="block w-full bg-sky-600 text-white text-center py-4 rounded-lg font-semibold text-lg">
        <i class="fas fa-shopping-cart mr-2"></i>Buy Now - ₹<?php echo number_format($course['price']); ?>
    </a>
    <?php endif; ?>
</div>

<?php include_once 'common/bottom.php'; ?>

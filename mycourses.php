<?php
include_once 'common/config.php';
requireLogin();

// Get user's purchased courses
$stmt = $pdo->prepare("
    SELECT c.*, o.created_at as purchase_date 
    FROM courses c 
    JOIN orders o ON c.id = o.course_id 
    WHERE o.user_id = ? AND o.status = 'success' 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="p-4">
    <?php if (isset($_GET['success'])): ?>
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>
        Payment successful! You can now access your course.
    </div>
    <?php endif; ?>
    
    <h1 class="text-2xl font-bold mb-6">My Courses</h1>
    
    <?php if (empty($courses)): ?>
    <div class="text-center py-12">
        <i class="fas fa-graduation-cap text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No courses yet</h3>
        <p class="text-gray-500 mb-6">Start learning by purchasing your first course</p>
        <a href="course.php" class="bg-sky-600 text-white px-6 py-3 rounded-lg font-semibold">
            Browse Courses
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($courses as $course): ?>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center space-x-4">
                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-20 h-20 object-cover rounded">
                <div class="flex-1">
                    <h3 class="font-semibold mb-1"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="text-gray-600 text-sm mb-2">Purchased on <?php echo date('M d, Y', strtotime($course['purchase_date'])); ?></p>
                    <a href="watch.php?course_id=<?php echo $course['id']; ?>" class="inline-flex items-center bg-sky-600 text-white px-4 py-2 rounded text-sm font-semibold">
                        <i class="fas fa-play mr-2"></i>Start Learning
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include_once 'common/bottom.php'; ?>

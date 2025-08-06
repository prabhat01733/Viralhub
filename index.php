<?php
include_once 'common/config.php';
requireLogin();

// Get banners
$banners = $pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get latest courses
$latestCourses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="p-4">
    <!-- Search Bar -->
    <div class="mb-6">
        <div class="relative">
            <input type="text" placeholder="Search courses..." class="w-full p-3 pl-10 border border-gray-300 rounded-full focus:outline-none focus:border-sky-600">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
    
    <!-- Banner Slider -->
    <?php if (!empty($banners)): ?>
    <div class="mb-6">
        <div id="bannerSlider" class="relative overflow-hidden rounded-lg">
            <div class="flex transition-transform duration-500" id="bannerContainer">
                <?php foreach ($banners as $banner): ?>
                <div class="w-full flex-shrink-0">
                    <img src="uploads/banners/<?php echo $banner['image']; ?>" alt="Banner" class="w-full h-48 object-cover">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Latest Courses -->
    <div class="mb-6">
        <h2 class="text-xl font-bold mb-4">Latest Courses</h2>
        <div class="flex space-x-4 overflow-x-auto hide-scrollbar pb-4">
            <?php foreach ($latestCourses as $course): ?>
            <div class="flex-shrink-0 w-64 bg-white rounded-lg shadow">
                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full h-36 object-cover rounded-t-lg">
                <div class="p-4">
                    <h3 class="font-semibold text-sm mb-2 line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sky-600 font-bold">₹<?php echo number_format($course['price']); ?></span>
                        <?php if ($course['mrp'] > $course['price']): ?>
                        <span class="text-gray-500 line-through text-sm">₹<?php echo number_format($course['mrp']); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="block mt-3 bg-sky-600 text-white text-center py-2 rounded text-sm">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Top Courses Grid -->
    <div>
        <h2 class="text-xl font-bold mb-4">Top Courses</h2>
        <div class="grid grid-cols-2 gap-4">
            <?php foreach (array_slice($latestCourses, 0, 6) as $course): ?>
            <div class="bg-white rounded-lg shadow">
                <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full h-24 object-cover rounded-t-lg">
                <div class="p-3">
                    <h3 class="font-semibold text-sm mb-2 line-clamp-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sky-600 font-bold text-sm">₹<?php echo number_format($course['price']); ?></span>
                        <?php if ($course['mrp'] > $course['price']): ?>
                        <span class="text-gray-500 line-through text-xs">₹<?php echo number_format($course['mrp']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
// Banner slider
let currentSlide = 0;
const banners = <?php echo json_encode($banners); ?>;

if (banners.length > 1) {
    setInterval(() => {
        currentSlide = (currentSlide + 1) % banners.length;
        const container = document.getElementById('bannerContainer');
        if (container) {
            container.style.transform = `translateX(-${currentSlide * 100}%)`;
        }
    }, 3000);
}
</script>

<?php include_once 'common/bottom.php'; ?>

<?php
include_once 'common/config.php';
requireLogin();

// Get all courses with filters
$where = "1=1";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where .= " AND title LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
}

$orderBy = "created_at DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_low':
            $orderBy = "price ASC";
            break;
        case 'price_high':
            $orderBy = "price DESC";
            break;
        case 'latest':
            $orderBy = "created_at DESC";
            break;
    }
}

$stmt = $pdo->prepare("SELECT * FROM courses WHERE $where ORDER BY $orderBy");
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="p-4">
    <!-- Filters -->
    <div class="mb-6 space-y-4">
        <div class="flex space-x-4">
            <select onchange="filterCourses()" id="sortSelect" class="flex-1 p-3 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
                <option value="">Sort by</option>
                <option value="latest" <?php echo ($_GET['sort'] ?? '') === 'latest' ? 'selected' : ''; ?>>Latest</option>
                <option value="price_low" <?php echo ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>
        
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Search courses..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="w-full p-3 pl-10 border border-gray-300 rounded focus:outline-none focus:border-sky-600">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
    
    <!-- Course Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($courses as $course): ?>
        <div class="bg-white rounded-lg shadow">
            <img src="uploads/courses/<?php echo $course['image']; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full h-48 object-cover rounded-t-lg">
            <div class="p-4">
                <h3 class="font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h3>
                <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <span class="text-sky-600 font-bold text-lg">₹<?php echo number_format($course['price']); ?></span>
                        <?php if ($course['mrp'] > $course['price']): ?>
                        <span class="text-gray-500 line-through">₹<?php echo number_format($course['mrp']); ?></span>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                            <?php echo round((($course['mrp'] - $course['price']) / $course['mrp']) * 100); ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="block bg-sky-600 text-white text-center py-2 rounded font-semibold">View Details</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($courses)): ?>
    <div class="text-center py-12">
        <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No courses found</h3>
        <p class="text-gray-500">Try adjusting your search or filters</p>
    </div>
    <?php endif; ?>
</div>

<script>
function filterCourses() {
    const sort = document.getElementById('sortSelect').value;
    const search = document.getElementById('searchInput').value;
    
    let url = 'course.php?';
    const params = [];
    
    if (sort) params.push('sort=' + encodeURIComponent(sort));
    if (search) params.push('search=' + encodeURIComponent(search));
    
    window.location.href = url + params.join('&');
}

// Search on enter
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        filterCourses();
    }
});
</script>

<?php include_once 'common/bottom.php'; ?>

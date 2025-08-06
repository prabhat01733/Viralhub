<?php
include_once 'common/config.php';
requireLogin();

$courseId = $_GET['course_id'] ?? 0;

// Check if user has purchased this course
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND course_id = ? AND status = 'success'");
$stmt->execute([$_SESSION['user_id'], $courseId]);
if (!$stmt->fetch()) {
    header('Location: course_detail.php?id=' . $courseId);
    exit;
}

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

// Get chapters and videos
$stmt = $pdo->prepare("
    SELECT c.*, v.id as video_id, v.title as video_title, v.filename 
    FROM chapters c 
    LEFT JOIN videos v ON c.id = v.chapter_id 
    WHERE c.course_id = ? 
    ORDER BY c.id, v.id
");
$stmt->execute([$courseId]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize data
$chapters = [];
foreach ($results as $row) {
    if (!isset($chapters[$row['id']])) {
        $chapters[$row['id']] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'videos' => []
        ];
    }
    if ($row['video_id']) {
        $chapters[$row['id']]['videos'][] = [
            'id' => $row['video_id'],
            'title' => $row['video_title'],
            'filename' => $row['filename']
        ];
    }
}

$currentVideo = null;
if (isset($_GET['video_id'])) {
    foreach ($chapters as $chapter) {
        foreach ($chapter['videos'] as $video) {
            if ($video['id'] == $_GET['video_id']) {
                $currentVideo = $video;
                break 2;
            }
        }
    }
}

include_once 'common/header.php';
?>

<div class="flex h-screen pt-16">
    <!-- Video Player -->
    <div class="flex-1 bg-black flex items-center justify-center">
        <?php if ($currentVideo): ?>
        <div class="w-full h-full relative">
            <video id="videoPlayer" class="w-full h-full" controls controlsList="nodownload" oncontextmenu="return false;">
                <source src="uploads/videos/<?php echo $currentVideo['filename']; ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="absolute inset-0 pointer-events-none"></div>
        </div>
        <?php else: ?>
        <div class="text-white text-center">
            <i class="fas fa-play-circle text-6xl mb-4 opacity-50"></i>
            <p class="text-xl">Select a video to start learning</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar with chapters -->
    <div class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-bold text-lg"><?php echo htmlspecialchars($course['title']); ?></h2>
        </div>
        
        <div class="p-4">
            <?php foreach ($chapters as $chapter): ?>
            <div class="mb-4">
                <button onclick="toggleChapter(<?php echo $chapter['id']; ?>)" class="w-full text-left p-3 bg-gray-100 rounded font-semibold flex items-center justify-between">
                    <span><?php echo htmlspecialchars($chapter['title']); ?></span>
                    <i class="fas fa-chevron-down transform transition-transform" id="chevron-<?php echo $chapter['id']; ?>"></i>
                </button>
                
                <div id="chapter-<?php echo $chapter['id']; ?>" class="mt-2 space-y-1">
                    <?php foreach ($chapter['videos'] as $video): ?>
                    <a href="watch.php?course_id=<?php echo $courseId; ?>&video_id=<?php echo $video['id']; ?>" 
                       class="block p-3 hover:bg-gray-50 rounded <?php echo ($currentVideo && $currentVideo['id'] == $video['id']) ? 'bg-sky-50 border-l-4 border-sky-600' : ''; ?>">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-play-circle text-sky-600"></i>
                            <span class="text-sm"><?php echo htmlspecialchars($video['title']); ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function toggleChapter(chapterId) {
    const content = document.getElementById('chapter-' + chapterId);
    const chevron = document.getElementById('chevron-' + chapterId);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        chevron.classList.remove('rotate-180');
    } else {
        content.style.display = 'none';
        chevron.classList.add('rotate-180');
    }
}

// Disable video download
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('videoPlayer');
    if (video) {
        video.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        // Disable keyboard shortcuts
        video.addEventListener('keydown', function(e) {
            e.preventDefault();
        });
    }
});

// Initialize - show first chapter expanded
document.addEventListener('DOMContentLoaded', function() {
    const firstChapter = document.querySelector('[id^="chapter-"]');
    if (firstChapter) {
        firstChapter.style.display = 'block';
    }
});
</script>

</body>
</html>

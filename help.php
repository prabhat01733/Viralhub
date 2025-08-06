<?php
include_once 'common/config.php';
include_once 'common/header.php';
include_once 'common/sidebar.php';
?>

<div class="p-4">
    <h1 class="text-2xl font-bold mb-6">Help & Support</h1>
    
    <div class="space-y-6">
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Contact Us</h2>
            
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-sky-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-envelope text-sky-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Email Support</h3>
                        <p class="text-gray-600"><?php echo $settings['support_email']; ?></p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-sky-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-phone text-sky-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Phone Support</h3>
                        <p class="text-gray-600"><?php echo $settings['support_phone']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Frequently Asked Questions</h2>
            
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-semibold mb-2">How do I access my purchased courses?</h3>
                    <p class="text-gray-600">After successful payment, you can access your courses from the "My Courses" section in the bottom navigation or sidebar menu.</p>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-semibold mb-2">Can I download videos for offline viewing?</h3>
                    <p class="text-gray-600">No, videos are streaming-only to protect content security. You need an internet connection to watch the videos.</p>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-semibold mb-2">What payment methods are accepted?</h3>
                    <p class="text-gray-600">We accept all major credit cards, debit cards, net banking, and UPI payments through Razorpay.</p>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="font-semibold mb-2">Is there a refund policy?</h3>
                    <p class="text-gray-600">Please contact our support team for refund requests. Each case is reviewed individually.</p>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-2">How long do I have access to purchased courses?</h3>
                    <p class="text-gray-600">You have lifetime access to all purchased courses. You can learn at your own pace without any time restrictions.</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <a href="course.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-book text-2xl text-sky-600 mb-2"></i>
                    <span class="text-sm font-medium">Browse Courses</span>
                </a>
                
                <a href="mycourses.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-graduation-cap text-2xl text-sky-600 mb-2"></i>
                    <span class="text-sm font-medium">My Courses</span>
                </a>
                
                <a href="profile.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-user text-2xl text-sky-600 mb-2"></i>
                    <span class="text-sm font-medium">Edit Profile</span>
                </a>
                
                <a href="mailto:<?php echo $settings['support_email']; ?>" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-envelope text-2xl text-sky-600 mb-2"></i>
                    <span class="text-sm font-medium">Email Us</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>

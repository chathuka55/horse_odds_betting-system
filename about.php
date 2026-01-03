<?php
require_once 'includes/config.php';
$pageTitle = "About Us";
require_once 'components/navbar.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-emerald-800 to-emerald-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4">About RacingPro Analytics</h1>
        <p class="text-xl text-emerald-100">Advanced AI-powered horse racing predictions and analytics platform</p>
    </div>
</section>

<!-- Mission Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">Our Mission</h2>
                <p class="text-lg text-gray-600 mb-4">
                    RacingPro Analytics leverages cutting-edge artificial intelligence and machine learning to provide accurate, data-driven predictions for horse racing enthusiasts worldwide.
                </p>
                <p class="text-lg text-gray-600 mb-4">
                    We believe that informed decisions lead to better outcomes. Our platform analyzes thousands of data points including form, track conditions, jockey performance, trainer statistics, and historical patterns to deliver predictions with unprecedented accuracy.
                </p>
                <p class="text-lg text-gray-600">
                    Whether you're a casual enthusiast or a serious bettor, RacingPro provides the insights you need to make confident decisions.
                </p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-8">
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-emerald-600 text-white">
                                <i class="fas fa-brain"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-900">Advanced AI</h3>
                            <p class="mt-2 text-gray-600">Machine learning algorithms that learn and improve continuously</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-emerald-600 text-white">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-900">Big Data Analysis</h3>
                            <p class="mt-2 text-gray-600">Analysis of millions of race records and performance metrics</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-emerald-600 text-white">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-900">Secure & Private</h3>
                            <p class="mt-2 text-gray-600">Bank-level encryption and data privacy protection</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold text-emerald-600 mb-2">95%</div>
                <div class="text-gray-600">Average Accuracy</div>
            </div>
            <div>
                <div class="text-5xl font-bold text-emerald-600 mb-2">10M+</div>
                <div class="text-gray-600">Race Records Analyzed</div>
            </div>
            <div>
                <div class="text-5xl font-bold text-emerald-600 mb-2">100K+</div>
                <div class="text-gray-600">Active Users</div>
            </div>
            <div>
                <div class="text-5xl font-bold text-emerald-600 mb-2">24/7</div>
                <div class="text-gray-600">Live Support</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">Why Choose RacingPro?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl">
                <div class="text-4xl mb-4"><i class="fas fa-chart-line text-emerald-600"></i></div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Real-Time Analytics</h3>
                <p class="text-gray-700">Get live odds updates, form analysis, and instant predictions for every race</p>
            </div>
            
            <div class="p-8 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-4xl mb-4"><i class="fas fa-mobile-alt text-blue-600"></i></div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Mobile First</h3>
                <p class="text-gray-700">Access predictions and analysis on any device, anytime, anywhere</p>
            </div>
            
            <div class="p-8 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                <div class="text-4xl mb-4"><i class="fas fa-users text-purple-600"></i></div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Expert Community</h3>
                <p class="text-gray-700">Share insights and learn from thousands of racing enthusiasts</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">Our Team</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php $team = [
                ['name' => 'John Smith', 'role' => 'Founder & CEO', 'bio' => 'Former racing analyst with 20+ years in the industry'],
                ['name' => 'Sarah Johnson', 'role' => 'CTO', 'bio' => 'AI and machine learning expert from leading tech companies'],
                ['name' => 'Mike Davis', 'role' => 'Head of Operations', 'bio' => 'Experienced operations manager with racing industry background'],
            ]; 
            foreach ($team as $member): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition">
                <div class="h-48 bg-gradient-to-r from-emerald-500 to-emerald-700"></div>
                <div class="p-6 text-center">
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo $member['name']; ?></h3>
                    <p class="text-emerald-600 font-semibold mb-3"><?php echo $member['role']; ?></p>
                    <p class="text-gray-600"><?php echo $member['bio']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 bg-gradient-to-r from-emerald-600 to-emerald-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold mb-6">Get in Touch</h2>
        <p class="text-xl text-emerald-100 mb-8">Have questions? We'd love to hear from you</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <div class="text-3xl mb-3"><i class="fas fa-envelope"></i></div>
                <p class="font-semibold mb-2">Email</p>
                <a href="mailto:info@racingpro.com" class="text-emerald-200 hover:text-white transition">info@racingpro.com</a>
            </div>
            <div>
                <div class="text-3xl mb-3"><i class="fas fa-phone"></i></div>
                <p class="font-semibold mb-2">Phone</p>
                <a href="tel:+1234567890" class="text-emerald-200 hover:text-white transition">+1 (234) 567-8900</a>
            </div>
            <div>
                <div class="text-3xl mb-3"><i class="fas fa-map-marker-alt"></i></div>
                <p class="font-semibold mb-2">Location</p>
                <p class="text-emerald-200">New York, NY, USA</p>
            </div>
        </div>
        
        <?php if (!isLoggedIn()): ?>
        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 px-8 rounded-lg inline-block transition">
            Join Our Community
        </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'components/footer.php'; ?>

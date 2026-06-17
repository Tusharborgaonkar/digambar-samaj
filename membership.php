<?php include 'includes/header.php'; ?>

<section class="py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold text-center text-dark mb-4" data-aos="fade-up">Membership Plans</h1>
            <p class="text-center text-gray-600 mb-12" data-aos="fade-up" data-aos-delay="100">Choose the perfect plan for your matrimony journey</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Basic Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="0">
                    <div class="bg-primary text-white text-center py-4"><h3 class="text-xl font-bold">Basic</h3></div>
                    <div class="p-6 text-center"><div class="text-4xl font-bold text-primary">₹999</div><p class="text-gray-500">/ 3 months</p></div>
                    <div class="p-6 border-t"><ul class="space-y-3"><li><i class="fas fa-check text-primary mr-2"></i> Create Profile</li><li><i class="fas fa-check text-primary mr-2"></i> View Matches</li><li><i class="fas fa-check text-primary mr-2"></i> Basic Support</li><li class="text-gray-400"><i class="fas fa-times mr-2"></i> Priority Support</li></ul></div>
                    <div class="p-6"><button class="w-full bg-primary text-white py-2 rounded-lg hover:bg-opacity-90">Select Plan</button></div>
                </div>
                
                <!-- Premium Plan -->
                <div class="bg-white rounded-lg shadow-xl overflow-hidden transform scale-105 border-2 border-primary" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-primary text-white text-center py-4 relative"><div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-accent text-dark px-3 py-1 rounded-full text-xs font-bold">POPULAR</div><h3 class="text-xl font-bold">Premium</h3></div>
                    <div class="p-6 text-center"><div class="text-4xl font-bold text-primary">₹1,999</div><p class="text-gray-500">/ 6 months</p></div>
                    <div class="p-6 border-t"><ul class="space-y-3"><li><i class="fas fa-check text-primary mr-2"></i> All Basic Features</li><li><i class="fas fa-check text-primary mr-2"></i> Advanced Filters</li><li><i class="fas fa-check text-primary mr-2"></i> Priority Support</li><li><i class="fas fa-check text-primary mr-2"></i> Highlighted Profile</li></ul></div>
                    <div class="p-6"><button class="w-full bg-primary text-white py-2 rounded-lg hover:bg-opacity-90">Select Plan</button></div>
                </div>
                
                <!-- Elite Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-primary text-white text-center py-4"><h3 class="text-xl font-bold">Elite</h3></div>
                    <div class="p-6 text-center"><div class="text-4xl font-bold text-primary">₹3,499</div><p class="text-gray-500">/ 12 months</p></div>
                    <div class="p-6 border-t"><ul class="space-y-3"><li><i class="fas fa-check text-primary mr-2"></i> All Premium Features</li><li><i class="fas fa-check text-primary mr-2"></i> Dedicated Matchmaker</li><li><i class="fas fa-check text-primary mr-2"></i> 24/7 Priority Support</li><li><i class="fas fa-check text-primary mr-2"></i> Profile Verification</li></ul></div>
                    <div class="p-6"><button class="w-full bg-primary text-white py-2 rounded-lg hover:bg-opacity-90">Select Plan</button></div>
                </div>
            </div>
            
            <!-- Login Form -->
            <div class="mt-16 max-w-md mx-auto bg-white rounded-lg shadow-lg p-8" data-aos="fade-up">
                <h2 class="text-2xl font-bold text-center text-dark mb-6">Login to Your Account</h2>
                <form>
                    <div class="mb-4"><input type="email" placeholder="Email Address" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-primary"></div>
                    <div class="mb-4"><input type="password" placeholder="Password" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-primary"></div>
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-opacity-90">Login</button>
                    <p class="text-center text-gray-600 mt-4">Don't have an account? <a href="pre-register.php" class="text-primary">Register Now</a></p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
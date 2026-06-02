<?php include 'includes/header.php'; ?>

<!-- Page Banner -->
<section class="bg-primary py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold text-white mb-4" data-aos="fade-up">Contact Us</h1>
        <p class="text-white/80 text-lg max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">We are always here to help you. Reach out to us for any queries, support, or feedback.</p>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 bg-light">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <!-- Contact Info Cards -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Phone -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-start gap-4 hover:shadow-md transition" data-aos="fade-up" data-aos-delay="0">
                    <div class="bg-primary/10 p-4 rounded-full text-primary">
                        <i class="fas fa-phone-alt text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-dark mb-1">Call Us</h4>
                        <p class="text-gray-600 mb-1">We are available 9 AM - 6 PM</p>
                        <p class="font-bold text-primary">+91 98765 43210</p>
                    </div>
                </div>

                <!-- Email -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-start gap-4 hover:shadow-md transition" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-primary/10 p-4 rounded-full text-primary">
                        <i class="fas fa-envelope text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-dark mb-1">Email Us</h4>
                        <p class="text-gray-600 mb-1">Drop us a line anytime</p>
                        <p class="font-bold text-primary">info@jaindigambarmatrimony.com</p>
                    </div>
                </div>

                <!-- Address -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-start gap-4 hover:shadow-md transition" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-primary/10 p-4 rounded-full text-primary">
                        <i class="fas fa-map-marker-alt text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-dark mb-1">Visit Us</h4>
                        <p class="text-gray-600 mb-1">Our Corporate Office</p>
                        <p class="font-bold text-gray-800">123, Jain Temple Road, Borivali West, Mumbai, Maharashtra 400092</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white p-8 md:p-10 rounded-xl shadow-md border border-gray-200" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="text-2xl font-bold text-dark mb-6">Send Us A Message</h3>
                    
                    <form action="#" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Your Name <span class="text-red-500">*</span></label>
                                <input type="text" placeholder="John Doe" required class="w-full border-gray-300 border rounded-lg p-3 bg-gray-50 focus:border-primary focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" placeholder="john@example.com" required class="w-full border-gray-300 border rounded-lg p-3 bg-gray-50 focus:border-primary focus:outline-none transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" placeholder="+91 90000 00000" class="w-full border-gray-300 border rounded-lg p-3 bg-gray-50 focus:border-primary focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                                <select required class="w-full border-gray-300 border rounded-lg p-3 bg-gray-50 focus:border-primary focus:outline-none transition">
                                    <option value="">Select a Subject</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Profile Assistance">Profile Assistance</option>
                                    <option value="Membership Query">Membership Query</option>
                                    <option value="Report an Issue">Report an Issue</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Your Message <span class="text-red-500">*</span></label>
                            <textarea rows="5" placeholder="How can we help you today?" required class="w-full border-gray-300 border rounded-lg p-3 bg-gray-50 focus:border-primary focus:outline-none transition resize-none"></textarea>
                        </div>

                        <button type="submit" class="bg-primary text-white font-bold text-lg px-8 py-3 rounded-lg hover:bg-opacity-90 transition shadow-lg w-full md:w-auto">
                            <i class="far fa-paper-plane mr-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Map Section -->
<section class="h-96 w-full bg-gray-200">
    <!-- Using an iframe for Google Maps -->
    <iframe src="https://maps.google.com/maps?q=Borivali%20West,%20Mumbai,%20Maharashtra&t=&z=13&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>

<?php include 'includes/footer.php'; ?>

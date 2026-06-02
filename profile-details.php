<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
include 'includes/header.php';
?>

<div class="bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
            <a href="index.php" class="hover:text-primary transition">Home</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="profiles.php" class="hover:text-primary transition">Search Profiles</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-dark font-medium">Pooja Ajay Dagli [MID: 23815]</span>
        </nav>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-8">
            <!-- Profile Header -->
            <div class="flex flex-col md:flex-row">
                <div class="w-full md:w-1/3 relative h-96 md:h-auto">
                    <img src="https://images.unsplash.com/photo-1594751543129-6701ad444259?w=800" alt="Profile Image" class="w-full h-full object-cover">
                    <div class="absolute top-4 left-4 bg-primary text-white text-xs font-bold px-3 py-1 rounded-full shadow">Verified Profile</div>
                </div>
                
                <div class="w-full md:w-2/3 p-6 md:p-10 flex flex-col justify-center">
                    <div class="flex justify-between items-start mb-2">
                        <h1 class="text-3xl font-bold text-dark mb-2">Pooja Ajay Dagli <span class="text-lg text-primary font-medium ml-2">[MID: 23815]</span></h1>
                        <button class="text-gray-400 hover:text-red-500 transition tooltip" title="Shortlist"><i class="far fa-heart text-2xl"></i></button>
                    </div>
                    
                    <p class="text-gray-600 mb-6 text-lg"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Mumbai, Maharashtra, India</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700 font-medium mb-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="flex items-center gap-3"><i class="far fa-calendar-alt text-primary w-5 text-center"></i> 37 Years, 5' 3" (160cm)</div>
                        <div class="flex items-center gap-3"><i class="fas fa-graduation-cap text-primary w-5 text-center"></i> MBA, MCA</div>
                        <div class="flex items-center gap-3"><i class="fas fa-briefcase text-primary w-5 text-center"></i> Corporate Manager</div>
                        <div class="flex items-center gap-3"><i class="fas fa-om text-primary w-5 text-center"></i> Sthanakwas (Swetamber)</div>
                        <div class="flex items-center gap-3"><i class="fas fa-language text-primary w-5 text-center"></i> Gujrathi</div>
                        <div class="flex items-center gap-3"><i class="fas fa-ring text-primary w-5 text-center"></i> Never Married</div>
                    </div>

                    <div class="flex flex-wrap gap-4 mt-auto">
                        <button class="bg-primary text-white px-8 py-3 rounded-md font-bold hover:bg-opacity-90 transition shadow-lg flex items-center"><i class="fas fa-phone-alt mr-2"></i> View Contact</button>
                        <button class="bg-white border-2 border-primary text-primary px-8 py-3 rounded-md font-bold hover:bg-primary hover:text-white transition shadow-sm flex items-center"><i class="far fa-envelope mr-2"></i> Send Interest</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="far fa-user-circle text-primary mr-3 text-2xl"></i> About Pooja</h3>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Hello, I am Pooja. I come from a traditional yet open-minded Jain family based in Mumbai. I have completed my MBA and MCA and am currently working as a Manager in a reputed MNC. I am a fun-loving, independent, and caring person who values family bonds deeply.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        I enjoy reading, traveling to new places, and exploring different cuisines. Looking for a partner who is well-educated, settled, understanding, and shares similar values. Let's connect if you find my profile suitable.
                    </p>
                </div>

                <!-- Personal Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="far fa-id-card text-primary mr-3 text-2xl"></i> Personal Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Name</span>
                            <span class="text-dark font-semibold">Pooja Ajay Dagli</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Date of Birth</span>
                            <span class="text-dark font-semibold">15 Aug 1989</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Marital Status</span>
                            <span class="text-dark font-semibold">Never Married</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Height</span>
                            <span class="text-dark font-semibold">5' 3" (160cm)</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Weight</span>
                            <span class="text-dark font-semibold">55 kg</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Diet</span>
                            <span class="text-dark font-semibold">Strictly Vegetarian (Jain Diet)</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Blood Group</span>
                            <span class="text-dark font-semibold">O+</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Complexion</span>
                            <span class="text-dark font-semibold">Fair</span>
                        </div>
                    </div>
                </div>

                <!-- Religious Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-om text-primary mr-3 text-2xl"></i> Religious & Astro Background</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Religion</span>
                            <span class="text-dark font-semibold">Jain</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Community / Sect</span>
                            <span class="text-dark font-semibold">Sthanakwas (Swetamber)</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mother Tongue</span>
                            <span class="text-dark font-semibold">Gujrathi</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Gotra</span>
                            <span class="text-dark font-semibold">Garg</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Manglik Status</span>
                            <span class="text-dark font-semibold text-green-600">Non-Manglik</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Time of Birth</span>
                            <span class="text-dark font-semibold">10:45 AM</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Place of Birth</span>
                            <span class="text-dark font-semibold">Mumbai</span>
                        </div>
                    </div>
                </div>

                <!-- Education & Career -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-user-graduate text-primary mr-3 text-2xl"></i> Education & Career</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Highest Education</span>
                            <span class="text-dark font-semibold">MBA, MCA</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Employed In</span>
                            <span class="text-dark font-semibold">Private Sector</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Occupation</span>
                            <span class="text-dark font-semibold">Corporate Manager</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Annual Income</span>
                            <span class="text-dark font-semibold">Rs. 10,00,001 - above</span>
                        </div>
                    </div>
                </div>

                <!-- Family Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <h3 class="text-xl font-bold text-dark border-b-2 border-gray-100 pb-3 mb-5 flex items-center"><i class="fas fa-users text-primary mr-3 text-2xl"></i> Family Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Family Status</span>
                            <span class="text-dark font-semibold">Upper Middle Class</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Family Type</span>
                            <span class="text-dark font-semibold">Nuclear Family</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Family Values</span>
                            <span class="text-dark font-semibold">Moderate</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Father's Occupation</span>
                            <span class="text-dark font-semibold">Business</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Mother's Occupation</span>
                            <span class="text-dark font-semibold">Homemaker</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Siblings</span>
                            <span class="text-dark font-semibold">1 Brother, 1 Sister</span>
                        </div>
                    </div>
                </div>

                <!-- Mandir Verification & References -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                    <div class="flex justify-between items-center border-b-2 border-gray-100 pb-3 mb-5">
                        <h3 class="text-xl font-bold text-dark flex items-center">
                            <i class="fas fa-gopuram text-primary mr-3 text-2xl"></i> Mandir Verification Details
                        </h3>
                        <span class="bg-green-50 text-green-700 border border-green-200 text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1">
                            <i class="fas fa-shield-alt"></i> Samaj Verified
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mb-6">
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Subcast (उपजाति)</span>
                            <span class="text-dark font-semibold">Parwar (परवार)</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500 mb-1">Registered Mandir (मंदिर)</span>
                            <span class="text-dark font-semibold text-sm">Shri Digambar Jain Lal Mandir, Chandni Chowk, Delhi</span>
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">Community Reference Persons</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Ref 1 -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-xs font-bold text-primary uppercase mb-2">Reference Person 1</p>
                            <div class="space-y-1 text-sm text-gray-700">
                                <p><span class="text-gray-500">Name:</span> <span class="font-semibold text-dark">Pandit Suresh Shastri</span></p>
                                <p><span class="text-gray-500">Mobile:</span> <span class="font-semibold text-dark">+91 ******3221</span> <span class="text-[10px] text-gray-400 font-normal">(Masked for privacy)</span></p>
                                <p><span class="text-gray-500">Relation:</span> <span class="font-semibold text-dark">Panditji / Temple Priest</span></p>
                            </div>
                        </div>
                        <!-- Ref 2 -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-xs font-bold text-primary uppercase mb-2">Reference Person 2</p>
                            <div class="space-y-1 text-sm text-gray-700">
                                <p><span class="text-gray-500">Name:</span> <span class="font-semibold text-dark">Manoj Kumar Jain</span></p>
                                <p><span class="text-gray-500">Mobile:</span> <span class="font-semibold text-dark">+91 ******3222</span> <span class="text-[10px] text-gray-400 font-normal">(Masked for privacy)</span></p>
                                <p><span class="text-gray-500">Relation:</span> <span class="font-semibold text-dark">Mandir Trustee</span></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Partner Preferences -->
                <div class="bg-light rounded-xl shadow-sm border border-primary/20 p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-primary border-b border-primary/20 pb-3 mb-4"><i class="fas fa-heart mr-2"></i> Partner Preferences</h3>
                    
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <div>
                                <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Age</span>
                                <span class="text-dark font-medium text-sm">37 to 42 Years</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <div>
                                <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Height</span>
                                <span class="text-dark font-medium text-sm">5' 5" to 6' 0"</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <div>
                                <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Marital Status</span>
                                <span class="text-dark font-medium text-sm">Never Married</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <div>
                                <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Education</span>
                                <span class="text-dark font-medium text-sm">Bachelors / Masters</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <div>
                                <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Religion / Sect</span>
                                <span class="text-dark font-medium text-sm">Jain (Any Sect)</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                            <div>
                                <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Location</span>
                                <span class="text-dark font-medium text-sm">Mumbai, Pune, Gujarat, NRI</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

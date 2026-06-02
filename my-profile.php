<?php include 'includes/header.php'; ?>

<section class="py-12 md:py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8" data-aos="fade-up">
                <h1 class="text-3xl md:text-4xl font-bold text-dark">My Profile</h1>
                <div class="mt-4 md:mt-0 flex gap-4">
                    <button class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-opacity-90 shadow-md transition font-medium flex items-center gap-2">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    <button class="bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-50 transition font-medium flex items-center gap-2 shadow-sm">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Left Column (Profile Summary) -->
                <div class="w-full lg:w-1/3 space-y-8" data-aos="fade-up" data-aos-delay="100">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                        <div class="bg-primary h-32 relative">
                            <!-- Optional cover pattern/image could go here -->
                        </div>
                        <div class="px-6 pb-6 relative">
                            <div class="w-28 h-28 rounded-full border-4 border-white bg-gray-100 mx-auto -mt-14 flex items-center justify-center shadow-md overflow-hidden relative z-10">
                                <i class="fas fa-user text-5xl text-gray-400"></i>
                            </div>
                            <div class="text-center mt-4">
                                <h2 class="text-2xl font-bold text-dark">Rahul Kumar Jain</h2>
                                <p class="text-gray-500 font-medium">Software Engineer</p>
                                <div class="flex items-center justify-center gap-2 mt-2 text-gray-600 text-sm">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    <span>Jaipur, Rajasthan</span>
                                </div>
                            </div>
                            <hr class="my-6 border-gray-100">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-birthday-cake w-4 text-center text-gray-400"></i> Age / Height</span>
                                    <span class="font-medium text-dark">30 Yrs / 5'8"</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-om w-4 text-center text-gray-400"></i> Religion</span>
                                    <span class="font-medium text-dark">Digambar Jain</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-users w-4 text-center text-gray-400"></i> Gotra</span>
                                    <span class="font-medium text-dark">Porwal</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-language w-4 text-center text-gray-400"></i> Language</span>
                                    <span class="font-medium text-dark">Hindi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-dark mb-4 flex items-center gap-2">
                            <i class="fas fa-address-book text-primary"></i> Contact Details
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Mobile Number</p>
                                    <p class="font-medium text-dark">+91 98765 43210</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Email Address</p>
                                    <p class="font-medium text-dark">rahul@example.com</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2.5 rounded-lg text-primary mt-0.5">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-0.5">Current Address</p>
                                    <p class="font-medium text-dark text-sm leading-snug">123, Jain Nagar, Jaipur<br>Pin: 302001</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Detailed Info) -->
                <div class="w-full lg:w-2/3 space-y-8" data-aos="fade-up" data-aos-delay="200">
                    
                    <!-- Personal & Physical Details -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-info-circle"></i> Personal Details
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Date of Birth</p>
                                <p class="font-medium text-dark">15 Aug 1995</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Time of Birth</p>
                                <p class="font-medium text-dark">10:30 AM</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Place of Birth</p>
                                <p class="font-medium text-dark">Jaipur</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Manglik</p>
                                <p class="font-medium text-dark">No</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Weight</p>
                                <p class="font-medium text-dark">70 kg</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Marital Status</p>
                                <p class="font-medium text-dark">Never Married</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Mama Gotra</p>
                                <p class="font-medium text-dark">Khandelwal</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Handicapped</p>
                                <p class="font-medium text-dark">No</p>
                            </div>
                            <div class="sm:col-span-2 md:col-span-3">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Hobbies & Interests</p>
                                <p class="font-medium text-dark">Reading, Meditation, Traveling</p>
                            </div>
                        </div>
                    </div>

                    <!-- Education & Career -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-graduation-cap"></i> Education & Career
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Highest Education</p>
                                <p class="font-medium text-dark">MBA</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Occupation</p>
                                <p class="font-medium text-dark">Private Job</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Company</p>
                                <p class="font-medium text-dark">Tech Solutions Pvt. Ltd.</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Designation</p>
                                <p class="font-medium text-dark">Software Engineer</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Monthly Income</p>
                                <p class="font-medium text-dark">₹1,00,000</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Partner Preferences</p>
                                <p class="font-medium text-dark">Educated, Family-oriented</p>
                            </div>
                        </div>
                    </div>

                    <!-- Family Details -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-home"></i> Family Details
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Father's Name</p>
                                <p class="font-medium text-dark">Suresh Jain</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Father's Occupation & Income</p>
                                <p class="font-medium text-dark">Business (₹2,00,000 / month)</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Mother's Name</p>
                                <p class="font-medium text-dark">Pushpa Jain</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Mother's Occupation</p>
                                <p class="font-medium text-dark">House Wife</p>
                            </div>
                            
                            <div class="sm:col-span-2 mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">Siblings Info</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-5 rounded-xl border border-gray-100">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-primary">0</p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Brothers<br>Married</p>
                                    </div>
                                    <div class="text-center border-l border-gray-200">
                                        <p class="text-2xl font-bold text-primary">1</p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Brothers<br>Unmarried</p>
                                    </div>
                                    <div class="text-center md:border-l border-gray-200 pt-4 md:pt-0 border-t md:border-t-0 mt-2 md:mt-0">
                                        <p class="text-2xl font-bold text-primary">1</p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Sisters<br>Married</p>
                                    </div>
                                    <div class="text-center border-l border-gray-200 pt-4 md:pt-0 border-t md:border-t-0 mt-2 md:mt-0">
                                        <p class="text-2xl font-bold text-primary">0</p>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">Sisters<br>Unmarried</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mandir Verification & References -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <i class="fas fa-gopuram text-primary"></i> Mandir Verification Details
                            </h3>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1">
                                <i class="fas fa-check-circle"></i> Samaj Verified
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 mb-6">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Subcast (उपजाति)</p>
                                <p class="font-medium text-dark">Parwar (परवार)</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Registered Mandir (मंदिर)</p>
                                <p class="font-medium text-dark">Shri Digambar Jain Lal Mandir, Chandni Chowk, Delhi</p>
                            </div>
                        </div>

                        <h4 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">Reference Persons (from same Mandir/Community)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Ref 1 -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs font-bold text-primary uppercase mb-2">Reference Person 1</p>
                                <div class="space-y-1 text-sm text-gray-700">
                                    <p><span class="text-gray-500">Name:</span> <span class="font-medium text-dark">Pandit Suresh Shastri</span></p>
                                    <p><span class="text-gray-500">Mobile:</span> <span class="font-medium text-dark">+91 98765 43221</span></p>
                                    <p><span class="text-gray-500">Relation:</span> <span class="font-medium text-dark">Panditji / Temple Priest</span></p>
                                </div>
                            </div>
                            <!-- Ref 2 -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <p class="text-xs font-bold text-primary uppercase mb-2">Reference Person 2</p>
                                <div class="space-y-1 text-sm text-gray-700">
                                    <p><span class="text-gray-500">Name:</span> <span class="font-medium text-dark">Manoj Kumar Jain</span></p>
                                    <p><span class="text-gray-500">Mobile:</span> <span class="font-medium text-dark">+91 98765 43222</span></p>
                                    <p><span class="text-gray-500">Relation:</span> <span class="font-medium text-dark">Mandir Trustee / Committee Member</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- Page Banner -->
<section class="relative h-72 md:h-[500px] bg-cover bg-center"
    style="background-image: url('assets/images/about-us-img.jpeg');">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative h-full flex items-center justify-center text-center px-4">
        <!-- <div>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4" data-aos="fade-up">About Us</h1>
            <p class="text-white text-lg max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">Preserving our timeless traditions by connecting hearts within the Digambar Jain Samaj.</p>
        </div> -->
    </div>
</section>

<!-- Language Toggle -->
<div class="container mx-auto px-4 max-w-6xl mt-8 flex justify-end">
    <button id="langToggleBtn" onclick="toggleLanguage()"
        class="bg-primary text-white font-bold py-2 px-6 rounded-lg shadow hover:bg-opacity-90 transition flex items-center gap-2">
        <i class="fas fa-language"></i> <span id="langToggleText">Translate to English</span>
    </button>
</div>

<!-- About Content -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <div class="relative">
                    <img src="assets/images/about-us-img1.jpg" alt="Indian Wedding Tradition"
                        class="rounded-xl shadow-xl mx-auto block max-w-full border-4 border-white">
                    <div
                        class="absolute -bottom-6 -right-6 bg-white p-4 rounded-xl shadow-lg border border-gray-100 hidden md:block">
                        <div class="text-primary font-bold text-3xl">10k+</div>
                        <div class="text-gray-600 text-sm font-semibold">Happy Marriages</div>
                    </div>
                </div>
            </div>
            <div data-aos="fade-left">
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" id="aboutTitleHi">हमारे बारे में</h2>
                <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4 hidden" id="aboutTitleEn">About Us</h2>
                <div class="w-16 h-1 bg-primary mb-6"></div>

                <!-- Hindi Content -->
                <div id="aboutContentHi" class="text-gray-600 leading-relaxed text-lg space-y-4">
                    <p>धर्म, समाज, संस्क्रति और राष्ट्र की प्रतिष्ठा को बनाये रखने मे विवाह की महत्वपूर्ण भूमिका रहती
                        है। विवाह न केवल दो व्यक्तियों का मिलन है बल्कि आगामी पीढियो के निर्माण मे महत्वपूर्ण भूमिका का
                        निर्वाह करता है | यदि उचित समय पर सही वर वधु का चयन होकर विवाह सम्पन्न हो जावे तो निश्चित ही
                        समाज और देश के लिये एक स्वस्थ वन सकेगा|</p>
                    <p>हमारी संस्था द्वारा विगत 5 वर्षो से समग्र दिगम्बर जैन समाज के युवक युवतियो के परिचय सम्मेलन का
                        आयोजन बहुत ही सफलता पूर्वक अहमदाबाद मे किया जा रहा है गुजरात राज्य का यह सबसे सफल आयोजन होता है|
                        आज समाज मे योग्य वर वधु का चयन बहुत ही जटिल और कठिन हो गया है इसी को ध्यान मे रखकर समिति द्वारा
                        यह कार्यक्रम आयोजित किया जाता है। उसी श्रंखला मे हमारी संस्था द्वारा एक और कदम आगे बदकर इस
                        वेबसाईट का निर्माण किया गया है जिससे कि समाज को युवक – युवतियों के लिये सुयोग्य जीवनसाथी हमेशा
                        उपलब्ध हो सकें हमे पूर्ण विश्वास है कि इस वेबसाईट के माध्यम से हम आपकी आकांछाओ को पूर्ण करने मे
                        सफल होंगे |</p>
                    <p>मे इस वेबसाईट पर रजिस्टर होने वाले सभी अभिभावक और स्नेहिल युवक-युवतियो के उज्ज्वल भविष्य के प्रति
                        मंगल कामनाये प्रेषित करता हूँ और आशा करता हूँ कि सभी के जीवन साथी की तलाश इस वेबसाईट के माध्यम
                        से जरूर पूर्ण हो |</p>
                    <p>हमारा मुख्य उद्देश्य यही है कि जैन की शादी जैन मे हो और हमारे वच्चो मे जैन धर्म के संस्कार बने
                        रहें</p>
                </div>

                <!-- English Content -->
                <div id="aboutContentEn" class="text-gray-600 leading-relaxed text-lg space-y-4 hidden">
                    <p>Marriage plays an important role in maintaining the prestige of religion, society, culture, and
                        the nation. Marriage is not just a union of two individuals but also plays a vital role in
                        building future generations. If the right bride and groom are selected at the right time and the
                        marriage takes place, it will certainly create a healthy environment for society and the
                        country.</p>
                    <p>For the past 5 years, our organization has been very successfully organizing the Parichay
                        Sammelan for the young men and women of the entire Digambar Jain community in Ahmedabad. This is
                        the most successful event in the state of Gujarat. Today, selecting a suitable bride and groom
                        in society has become very complex and difficult. Keeping this in mind, this program is
                        organized by the committee. In the same series, taking a step forward, our organization has
                        created this website so that a suitable life partner is always available for the young men and
                        women of the society. We have full faith that through this website we will be successful in
                        fulfilling your aspirations.</p>
                    <p>I convey my best wishes for the bright future of all the parents and loving young men and women
                        registering on this website and hope that everyone's search for a life partner will definitely
                        be fulfilled through this website.</p>
                    <p>Our main objective is that a Jain's marriage should happen within Jainism and Jain religious
                        values should be maintained in our children.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Disclosure Section -->
<section class="py-12 bg-gray-50 border-t border-gray-100">
    <div class="container mx-auto px-4 max-w-4xl text-center" data-aos="fade-up">
        <h2 class="text-2xl font-bold text-dark mb-4" id="disclosureTitleHi">महत्वपूर्ण सूचना (Disclosure)</h2>
        <h2 class="text-2xl font-bold text-dark mb-4 hidden" id="disclosureTitleEn">Disclosure</h2>
        <div class="w-16 h-1 bg-primary mx-auto mb-6"></div>

        <!-- Hindi Disclosure -->
        <div id="disclosureContentHi"
            class="text-gray-600 leading-relaxed space-y-4 bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-100 text-left">
            <p>दिगम्बर जैन परिचय वेबसाईट पर प्रत्याशियों से सम्बंधित सभी जानकारियों की सत्यता आप स्वयं अपने व्यक्तिगत
                सूत्रो से अवश्य प्राप्त करें| इस वेबसाईट मे दी गयी सभी जानकारी प्रत्याशियों द्वारा डिजिटली स्वयं भरी गयी
                है वेबसाईट मे दिये गये विवरण के लिये प्रत्याशी और अभिभावक स्वयं जिम्मेवार है, अतः प्रत्याशी के किसी भी
                विवरण के लिये समिति उत्तरदायी नही होगी|</p>
            <p>आयोजन समिति द्वारा यह कार्य समाज के हित मे पूर्ण निस्वार्थ भाव से किया जा रहा है। हमारा पूर्ण प्रयास रहा
                है कि किसी भी समाज जन को कोई भी परेशानी नही हो और हमारी सिर्फ यही भावना है की सभी को उचित वर – वधु शीघ्र
                मिलें</p>
            <p class="font-bold text-primary mt-4">दिगम्बर जैन परिचय सम्मेलन समिति अहमदाबाद</p>
        </div>

        <!-- English Disclosure -->
        <div id="disclosureContentEn"
            class="text-gray-600 leading-relaxed space-y-4 bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-100 text-left hidden">
            <p>You must verify the authenticity of all the information related to the candidates on the Digambar Jain
                Parichay website through your own personal sources. All the information provided on this website has
                been filled digitally by the candidates themselves. The candidates and parents are themselves
                responsible for the details provided in the website, hence the committee will not be responsible for any
                details of the candidate.</p>
            <p>This work is being done by the organizing committee in the interest of the society completely selflessly.
                It has been our constant endeavor that no society member faces any trouble and our only sentiment is
                that everyone finds a suitable bride/groom soon.</p>
            <p class="font-bold text-primary mt-4">Digambar Jain Parichay Sammelan Samiti Ahmedabad</p>
        </div>
    </div>
</section>

<script>
    let currentLang = 'hi';
    function toggleLanguage() {
        const toggleBtnText = document.getElementById('langToggleText');

        const hiElements = [
            document.getElementById('aboutTitleHi'),
            document.getElementById('aboutContentHi'),
            document.getElementById('disclosureTitleHi'),
            document.getElementById('disclosureContentHi')
        ];

        const enElements = [
            document.getElementById('aboutTitleEn'),
            document.getElementById('aboutContentEn'),
            document.getElementById('disclosureTitleEn'),
            document.getElementById('disclosureContentEn')
        ];

        if (currentLang === 'hi') {
            hiElements.forEach(el => el.classList.add('hidden'));
            enElements.forEach(el => el.classList.remove('hidden'));
            toggleBtnText.innerText = 'हिंदी में अनुवाद करें';
            currentLang = 'en';
        } else {
            enElements.forEach(el => el.classList.add('hidden'));
            hiElements.forEach(el => el.classList.remove('hidden'));
            toggleBtnText.innerText = 'Translate to English';
            currentLang = 'hi';
        }
    }
</script>

<!-- Mission & Vision -->
<section class="py-16 bg-light">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-dark mb-3">Our Core Philosophy</h2>
            <p class="text-gray-600">Built on the foundation of trust, purity, and lifelong commitment.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-8 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition"
                data-aos="fade-up">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 text-primary">
                    <i class="fas fa-bullseye text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 text-dark">Our Mission</h3>
                <p class="text-gray-600 text-sm leading-relaxed">To provide a highly secure and culturally tailored
                    platform for Digambar Jains to find compatible life partners while strictly preserving our religious
                    heritage.</p>
            </div>

            <div class="text-center p-8 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition"
                data-aos="fade-up" data-aos-delay="100">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 text-primary">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 text-dark">Our Vision</h3>
                <p class="text-gray-600 text-sm leading-relaxed">To be the most trusted, respected, and globally
                    recognized matrimony platform exclusively serving the entire Digambar Jain diaspora across the
                    world.</p>
            </div>

            <div class="text-center p-8 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition"
                data-aos="fade-up" data-aos-delay="200">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 text-primary">
                    <i class="fas fa-praying-hands text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 text-dark">Our Values</h3>
                <p class="text-gray-600 text-sm leading-relaxed">We operate on the principles of Ahimsa (Non-violence),
                    Satya (Truth), and transparency, ensuring every profile is authenticated for a safe experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="bg-primary rounded-2xl p-8 md:p-12 text-white shadow-xl relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-white opacity-10 rounded-full"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white opacity-10 rounded-full"></div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-4">Why Choose Us?</h2>
                    <p class="mb-6 text-white/90">We go beyond simple matchmaking. We build communities and foster
                        relationships that last lifetimes.</p>
                    <ul class="space-y-4">
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-secondary"></i> 100%
                            Verified Mobile Numbers</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-secondary"></i> Exclusive
                            to Digambar Jain Community</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-secondary"></i> Advanced
                            Gotra & Astrological Filtering</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-secondary"></i> Complete
                            Privacy & Photo Protection</li>
                    </ul>
                    <a href="registration.php"
                        class="inline-block mt-8 bg-white text-primary font-bold px-8 py-3 rounded hover:bg-gray-100 transition shadow-lg">Register
                        Free Now</a>
                </div>
                <div>
                    <img src="assets/images/aboutus-img2.jpg" alt="Why Choose Us"
                        class="rounded-xl shadow-2xl border-4 border-white/20 transform rotate-3">
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Messages from Committee -->
<section class="py-16 bg-light border-t border-gray-100">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-dark mb-3">Messages from the Committee</h2>
            <div class="w-16 h-1 bg-primary mx-auto"></div>
        </div>

        <div class="grid grid-cols-1 gap-12">
            <!-- Message 1: CS Manoj Jain -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="p-8 md:p-12 flex flex-col justify-center">
                        <div class="prose max-w-none text-gray-600 text-sm md:text-base">
                            <p class="mb-4"><strong>Jai Jinendra,</strong></p>
                            <p class="mb-4">Marriage plays an important role in preserving the dignity and values of
                                religion, society, culture, and the nation. Marriage is not merely the union of two
                                individuals; it also contributes significantly to shaping future generations. If, at the
                                right time, a suitable bride and groom are selected and married, it undoubtedly helps
                                create a healthy environment for society and the country.</p>
                            <p class="mb-4">The organization of a matrimonial introduction conference serves as a useful
                                and meaningful platform for young men and women in selecting a suitable life partner. At
                                the same time, it is an important medium for mutual exchange of ideas and for
                                strengthening social relationships.</p>
                            <p class="mb-4">I extend my best wishes for a bright future to all the parents and unmarried
                                young men and women participating in this event. I hope that everyone will find a
                                suitable life partner through this matrimonial conference and achieve complete success
                                in life.</p>
                            <p class="mb-4">This year as well, we received a very encouraging response from both India
                                and abroad. Due to space limitations, we had to decline many application forms, for
                                which we sincerely seek forgiveness.</p>
                            <p class="mb-6">On behalf of the committee, I express my heartfelt gratitude to all the
                                supporters, donors, and everyone who directly or indirectly contributed to making this
                                program successful.</p>
                            <p class="mb-1"><strong>Jai Jinendra</strong></p>
                            <p class="mb-1 font-bold text-primary text-lg">CS Manoj Jain</p>
                            <p class="mb-1 text-gray-500">9825127221</p>
                            <p class="mb-1 text-gray-500">Ahmedabad</p>
                            <p class="text-gray-500">05-01-2026</p>
                        </div>
                    </div>
                    <div class="relative h-80 md:h-auto bg-gray-200">
                        <img src="assets/images/manoj jain.jpeg" alt="CS Manoj Jain"
                            class="w-full h-full object-cover object-[center_18%]">
                    </div>
                </div>
            </div>

            <!-- Message 2: Narendra Jain -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="relative h-80 md:h-auto bg-gray-200 order-2 md:order-1">
                        <img src="assets/images/Jitendra Shah.png" alt="Jitendra Shah"
                            class="w-full h-full object-cover object-top">
                    </div>
                    <div class="p-8 md:p-12 flex flex-col justify-center order-1 md:order-2">
                        <div class="prose max-w-none text-gray-600 text-sm md:text-base">
                            <p class="mb-4">For the past three years, the Digambar Jain Youth Introduction Conference
                                (Parichay Sammelan) has been successfully organized in Ahmedabad. In today’s society,
                                finding a suitable bride and groom has become quite difficult and complex. Keeping this
                                in mind, the committee has organized this program. I have full faith that, just as in
                                previous years, this year’s Youth Introduction Conference will also be organized
                                successfully by the committee with complete dedication and enthusiasm.</p>
                            <p class="mb-6">The success of this program has been possible only because of the support of
                                all of you and the tireless day-and-night efforts of the committee members. On behalf of
                                the committee, I express my heartfelt gratitude to all of you. I also congratulate the
                                organizers of the 11th January 2026 event and wish a bright and prosperous future to all
                                the young men and women participating in this Youth Introduction Conference.</p>
                            <p class="mb-1"><strong>Jai Jinendra</strong></p>
                            <p class="mb-1 font-bold text-primary text-lg">Jitendra Shah</p>
                            <p class="text-gray-500">9825041734</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
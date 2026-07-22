<?php
require_once 'includes/db.php';
$dynamic_about = '';
try {
    $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'about_us'");
    if ($stmt) {
        $dynamic_about = $stmt->fetchColumn();
    }
} catch (Exception $e) {}

include 'includes/header.php';
?>

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
        <div data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4" id="aboutTitleHi">हमारे बारे में</h2>
            <h2 class="text-3xl md:text-4xl font-bold text-dark mb-4 hidden" id="aboutTitleEn">About Us</h2>
            <div class="w-16 h-1 bg-primary mb-6"></div>

            <!-- Content -->
            <?php if (!empty($dynamic_about)): ?>
                <div class="text-gray-600 leading-relaxed text-lg space-y-4">
                    <?= $dynamic_about ?>
                </div>
            <?php else: ?>
                <!-- Hindi Content -->
                <div id="aboutContentHi" class="text-gray-600 leading-relaxed text-lg space-y-4 text-justify">
                    <p class="font-bold text-xl text-primary">सादर जय जिनेंद्र,</p>
                    <p>धर्म, समाज, संस्कृति और राष्ट्र की प्रतिष्ठा को अक्षुण्ण बनाए रखने में विवाह की अत्यंत महत्वपूर्ण भूमिका होती है। विवाह न केवल दो व्यक्तियों का मिलन है, बल्कि यह आगामी पीढ़ियों के निर्माण और सुदृढ़ीकरण का मुख्य आधार है। यदि उचित समय पर सही वर-वधू का चयन कर विवाह संपन्न हो, तो निश्चित ही समाज और राष्ट्र को एक स्वस्थ और सुसंस्कृत स्वरूप प्राप्त होगा।</p>
                    <p>हमारी संस्था द्वारा विगत 5 वर्षों से समग्र दिगंबर जैन समाज के योग्य युवक-युवतियों के लिए 'परिचय सम्मेलन' का आयोजन अहमदाबाद में अत्यंत सफलतापूर्वक किया जा रहा है। यह गुजरात राज्य का सबसे सफल और प्रतिष्ठित आयोजन माना जाता है। आज के आधुनिक परिवेश में योग्य वर-वधू का चयन बेहद जटिल और कठिन कार्य हो गया है, इसी बात को ध्यान में रखकर समिति निरंतर इस दिशा में कार्यरत है।</p>
                    <p>इसी श्रृंखला में एक कदम और आगे बढ़ाते हुए हमारी संस्था ने इस आधुनिक वेबसाइट का निर्माण किया है, ताकि समाज के युवक-युवतियों के लिए सुयोग्य जीवनसाथी की खोज हर समय सुलभ और आसान हो सके। हमें पूर्ण विश्वास है कि इस डिजिटल माध्यम से हम आपकी आकांक्षाओं को पूर्ण करने में पूरी तरह सफल होंगे।</p>
                    <p>मैं इस वेबसाइट पर पंजीकृत होने वाले सभी अभिभावकों और स्नेही युवक-युवतियों के उज्ज्वल भविष्य के लिए अपनी मंगलकामनाएं प्रेषित करता हूँ। आशा है कि सुयोग्य जीवनसाथी की आपकी तलाश इस मंच के माध्यम से अवश्य पूर्ण होगी।</p>
                    <p class="font-semibold text-primary">हमारा मुख्य उद्देश्य और संकल्प यही है कि— "जैन की शादी जैन में ही हो" ताकि हमारी आने वाली पीढ़ी में जैन धर्म के मूल संस्कार और संस्कृति जीवंत बनी रहे।</p>
                    <p class="font-bold mt-4">शुभकामनाओं सहित,</p>
                    <p class="font-bold text-primary">दिगम्बर जैन परिचय सम्मेलन समिति अहमदाबाद</p>
                    
                    <hr class="my-8 border-gray-200">
                    
                    <p>हमारी वेबसाइट “दिगम्बर जैन परिचय” केवल एक वैवाहिक परिचय का मंच नहीं है, बल्कि दिगम्बर जैन समाज की समृद्ध सांस्कृतिक विरासत एवं आध्यात्मिक मूल्यों पर आधारित एक विश्वसनीय परिवार है। हमारा मानना है कि विवाह केवल जीवनसाथी की खोज नहीं, बल्कि ऐसे दो व्यक्तित्वों का पवित्र मिलन है जिनके जीवन के लक्ष्य, संस्कार, मूल्य और विचार समान हों।</p>
                    <p>सार्थक एवं दीर्घकालिक वैवाहिक संबंध स्थापित करने की हमारी प्रतिबद्धता हमारी प्रत्येक सेवा में दिखाई देती है। सत्यापित एवं विस्तृत प्रोफ़ाइल डेटाबेस तथा पारदर्शी प्रक्रिया के माध्यम से हम आपको आपके लिए उपयुक्त जीवनसाथी खोजने के लिये एक माध्यम प्रदान कर रहे हैं। यह सिर्फ दिगम्बर जैन समाज के विवाह योग्य बच्चों के सम्बंध खोजने के लिये एकमात्र वेबसाइट है।</p>
                    <p>जीवनसाथी का चयन जीवन के सबसे महत्वपूर्ण निर्णयों में से एक है। “दिगम्बर जैन परिचय” वेबसाइट इस महत्वपूर्ण यात्रा के प्रत्येक चरण में आपके साथ खड़ी है। हम आपको 100% सत्यापित एवं विस्तृत प्रोफ़ाइल और डेटाबेस अपने समाज के विवाह योग्य बच्चो के इस माध्यम से उपलब्ध कराते हैं, जिनकी सहायता से आप ऐसा जीवनसाथी चुन सकें जो आपके मूल्यों, जीवन-दृष्टि और भविष्य के सपनों के अनुरूप हो। हमारा मुख्य उद्देश्य यही है कि जैन की शादी जैन मे हो और हमारे बच्चो मे दिगम्बर जैन परम्परा के संस्कार बने रहें और अक्षुण बनी रहे।</p>
                    <p>वर्तमान समय की परिस्थितियों एवं बदलते परिवेश में मनुष्य के पास समय का अभाव है। वह दिन-रात अपने परिवार के खुशहाल जीवन यापन एवं उनके भविष्य को उज्जवल बनाने के लिए प्रयासरत रहता है। जीवन की इसी भागदौड़ में वह यह भी भूल जाता है कि उसके बच्चे बड़े एवं विवाह योग्य हो गए हैं। जब उसे इस बात का ध्यान आता है বিন্দু तो वह अच्छे संबंध की तलाश करना शुरु करता है और यही से उसकी परेशानी शुरू होती है। आज का समय पहले जैसा नही रहा कि अपने परिचित / रिश्तेदार ही सम्बंध बता देते थे अब किसी का भी सहयोग इस कार्य मे नही के बराबर हो गया है।</p>
                    <p>हमारी संस्था ने सकल दिगम्बर जैन समाज के लिये ही यह बीड़ा उठाया है। हमारी संस्था का उद्देश्य केवल यही है कि समाज के बच्चों का विवाह समाज मे ही समय पर हो जाये, हमारी संस्था विगत 5 वर्षो से अहमदाबाद मे सकल दिगम्बर जैन समाज के विवाह योग्य युवक युवतियो का परिचय सम्मेलन बहुत ही सफलता पूर्वक आयोजित कर रही है। संस्था का उद्देश्य कभी भी पैसा कमाना नही रहा। समिति के सभी सद्स्य अपना अमूल्य समय देकर इस कार्य को समाज हित मे कर रहे हैं।</p>
                </div>

                <!-- English Content -->
                <div id="aboutContentEn" class="text-gray-600 leading-relaxed text-lg space-y-4 hidden">
                    <p>Marriage plays an important role in maintaining the prestige of religion, society, culture, and
                        the nation. Marriage is not just a union of two individuals but also plays a vital role in
                        building future generations. If the right girl and boy are selected at the right time and the
                        marriage takes place, it will certainly create a healthy environment for society and the
                        country.</p>
                    <p>For the past 5 years, our organization has been very successfully organizing the Parichay
                        Sammelan for the young men and women of the entire Digambar Jain community in Ahmedabad. This is
                        the most successful event in the state of Gujarat. Today, selecting a suitable girl and boy
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
            <?php endif; ?>
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
                that everyone finds a suitable girl/boy soon.</p>
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
            document.getElementById('disclosureContentHi'),
            document.getElementById('committeeQuoteHi'),
            document.getElementById('name1Hi'), document.getElementById('desc1Hi'),
            document.getElementById('name2Hi'), document.getElementById('desc2Hi'),
            document.getElementById('name3Hi'), document.getElementById('desc3Hi'),
            document.getElementById('name4Hi'), document.getElementById('desc4Hi'),
            document.getElementById('name5Hi'), document.getElementById('desc5Hi')
        ].filter(Boolean);

        const enElements = [
            document.getElementById('aboutTitleEn'),
            document.getElementById('aboutContentEn'),
            document.getElementById('disclosureTitleEn'),
            document.getElementById('disclosureContentEn'),
            document.getElementById('committeeQuoteEn'),
            document.getElementById('name1En'), document.getElementById('desc1En'),
            document.getElementById('name2En'), document.getElementById('desc2En'),
            document.getElementById('name3En'), document.getElementById('desc3En'),
            document.getElementById('name4En'), document.getElementById('desc4En'),
            document.getElementById('name5En'), document.getElementById('desc5En')
        ].filter(Boolean);

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


<!-- Committee Members -->
<section class="py-16 bg-light border-t border-gray-100">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-dark mb-3">Committee Members</h2>
            <div class="w-16 h-1 bg-primary mx-auto"></div>
            
            <div id="committeeQuoteHi">
                <p class="text-gray-600 mt-6 max-w-4xl mx-auto italic text-lg leading-relaxed font-medium">"स्थापना काल से ही समिति के पाँचों सदस्य इस संस्था को आगे ले जाने में जुटे हुए हैं। सभी सदस्यों के सामूहिक प्रयासों और आपसी तालमेल का ही परिणाम है कि संस्था आज इस गौरवशाली मुकाम पर खड़ी है। यह पारस्परिक सामंजस्य ही हमारी संस्था का मुख्य आधार स्तंभ है।"</p>
                <p class="text-primary font-bold mt-2">- दिगम्बर जैन परिचय सम्मेलन समिति अहमदाबाद</p>
            </div>
            
            <div id="committeeQuoteEn" class="hidden">
                <p class="text-gray-600 mt-6 max-w-4xl mx-auto italic text-lg leading-relaxed font-medium">"Since its inception, all five members of the committee have been dedicated to taking this organization forward. It is the result of their collective efforts and mutual coordination that the organization stands at this glorious stage today. This mutual harmony is the main pillar of our organization."</p>
                <p class="text-primary font-bold mt-2">- Digambar Jain Parichay Sammelan Samiti Ahmedabad</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-8">
            <!-- Member Narendra Jain -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 text-center p-6 flex flex-col h-full md:col-span-2 lg:col-span-2">
                <div class="w-32 h-32 mx-auto bg-gray-200 rounded-full mb-4 overflow-hidden border-4 border-primary shrink-0">
                    <img src="assets/images/narendra jain.png" alt="Narendra Jain" class="w-full h-full object-cover">
                </div>
                <h3 class="font-bold text-xl text-dark" id="name1Hi">नरेन्द्र जैन</h3>
                <h3 class="font-bold text-xl text-dark hidden" id="name1En">Narendra Jain</h3>
                <p class="text-primary font-semibold text-sm mb-2">Committee Member</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify" id="desc1Hi">केमीकल के सफल व्यवसायी, धार्मिक और बहुत सारी संस्थाओं से सम्बंधित श्री नरेंद्र जी जैन इस संस्था के बहुत ही मजबूत स्तम्भ मे से एक है इस संस्था के प्रारम्भ से ही वह अपना योगदान दे रहे हैं|</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify hidden" id="desc1En">A successful chemical businessman, religious and associated with many organizations, Mr. Narendra Jain is one of the very strong pillars of this organization. He has been contributing since the beginning.</p>
            </div>
            
            <!-- Member Manoj Jain -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 text-center p-6 flex flex-col h-full md:col-span-2 lg:col-span-2">
                <div class="w-32 h-32 mx-auto bg-gray-200 rounded-full mb-4 overflow-hidden border-4 border-primary shrink-0">
                    <img src="assets/images/manoj jain.jpeg" alt="Manoj Jain" class="w-full h-full object-cover">
                </div>
                <h3 class="font-bold text-xl text-dark" id="name2Hi">मनोज जैन <span class="text-xs font-normal text-gray-500 block mt-1">(M. COM, LLB, ACS)</span></h3>
                <h3 class="font-bold text-xl text-dark hidden" id="name2En">Manoj Jain <span class="text-xs font-normal text-gray-500 block mt-1">(M. COM, LLB, ACS)</span></h3>
                <p class="text-primary font-semibold text-sm mb-2">Committee Member</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify" id="desc2Hi">श्री मनोज जैन जी 30 वर्षों का अनुभव रखने वाले वरिष्ठ कंपनी सेक्रेटरी हैं, जो अहमदाबाद की एक रियल एस्टेट कंपनी में CFO और CS के रूप में कार्यरत हैं। सामाजिक कार्यों के प्रति समर्पित, श्री जैन इस संस्था से इसके शुरुआती दिनों से ही जुड़े हुए हैं। संस्था द्वारा दी गई हर जिम्मेदारी को उन्होंने हमेशा समय पर और सफलतापूर्वक पूरा किया है।</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify hidden" id="desc2En">Mr. Manoj Jain is a senior Company Secretary with 30 years of experience, currently working as CFO and CS in a real estate company in Ahmedabad. Dedicated to social work, Mr. Jain has been associated with this organization since its early days and successfully fulfills all responsibilities.</p>
            </div>

            <!-- Member Darshan Jain -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 text-center p-6 flex flex-col h-full md:col-span-2 lg:col-span-2">
                <div class="w-32 h-32 mx-auto bg-gray-200 rounded-full mb-4 overflow-hidden border-4 border-primary shrink-0">
                    <img src="assets/images/darshan jain.jpeg" alt="Darshan Jain Vakharia" class="w-full h-full object-cover">
                </div>
                <h3 class="font-bold text-xl text-dark" id="name3Hi">श्री दर्शन जैन वखारिया</h3>
                <h3 class="font-bold text-xl text-dark hidden" id="name3En">Darshan Jain Vakharia</h3>
                <p class="text-primary font-semibold text-sm mb-2">Committee Member</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify" id="desc3Hi">श्री दर्शन जी इमीग्रेशन वीसा कंसल्टेंट है और साथ मे बहुत ही सामजिक और धार्मिक व्यक्ति है वह बहुत सारी संस्थाओं से जुड़ें हुये है दिगम्बर जैन समाज के परिचय सम्मेलन का सपना उनका ही था जिसको यह संस्था उनके साथ प्रारम्भ से कर रही है</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify hidden" id="desc3En">An immigration visa consultant and a very social and religious person, he is associated with many organizations. The dream of the Parichay Sammelan was his, which this organization has been fulfilling with him since the beginning.</p>
            </div>

            <!-- Member Milesh Doshi -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 text-center p-6 flex flex-col h-full md:col-span-2 lg:col-start-2 lg:col-span-2">
                <div class="w-32 h-32 mx-auto bg-gray-200 rounded-full mb-4 overflow-hidden border-4 border-primary shrink-0">
                    <img src="assets/images/milesh.png" alt="Milesh Doshi" class="w-full h-full object-cover">
                </div>
                <h3 class="font-bold text-xl text-dark" id="name4Hi">मिलेश दोशी</h3>
                <h3 class="font-bold text-xl text-dark hidden" id="name4En">Milesh Doshi</h3>
                <p class="text-primary font-semibold text-sm mb-2">Committee Member</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify" id="desc4Hi">श्री मिलेशभाई कम्पुटर सोफ्ट्वेयर और हार्डवेयर व्यवसायी है, सभी धार्मिक कार्यो और मुनि भक्ति मे सबसे अग्रणी रहते है इस संस्था के प्रारम्भ से ही वह अपना योगदान दे रहे हैं|</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify hidden" id="desc4En">Mr. Mileshbhai is a computer software and hardware businessman. He is at the forefront of all religious activities and devotion to monks. He has been contributing to this organization since its inception.</p>
            </div>

            <!-- Member Jitendra Shah -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 text-center p-6 flex flex-col h-full md:col-start-2 md:col-span-2 lg:col-start-4 lg:col-span-2">
                <div class="w-32 h-32 mx-auto bg-gray-200 rounded-full mb-4 overflow-hidden border-4 border-primary shrink-0">
                    <img src="assets/images/Jitendra Shah.png" alt="Jitendra Shah" class="w-full h-full object-cover">
                </div>
                <h3 class="font-bold text-xl text-dark" id="name5Hi">जितेंद्र शाह</h3>
                <h3 class="font-bold text-xl text-dark hidden" id="name5En">Jitendra Shah</h3>
                <p class="text-primary font-semibold text-sm mb-2">Committee Member</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify" id="desc5Hi">श्री जितेंद्र जी का प्रिंटिंग का बहुत ही बड़ा कार्य है, सभी सामजिक और धार्मिक कार्यो मे हमेशा अपना योगदान देते है इस संस्था के प्रारम्भ से ही वह अपना योगदान दे रहे हैं|</p>
                <p class="text-gray-600 text-sm mb-4 grow text-justify hidden" id="desc5En">Mr. Jitendra has a large printing business. He always contributes to social and religious activities and has been contributing to this organization since the beginning.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


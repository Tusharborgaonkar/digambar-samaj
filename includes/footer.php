<?php
if (!isset($settings) || !is_array($settings)) {
    $settings = [];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {}
}
$contact_phone = $settings['contact_phone'] ?? '+91 7575005121';
$contact_email = $settings['contact_email'] ?? 'digambarjainparichay@gmail.com';
$contact_address = $settings['contact_address'] ?? '23-A, Shubhlaxmi Palace, Opp. Money Plant Junction, Bhuyangdev Cross Road, Sola Road, Ahmedabad-380061.';
// Ensure phone only has digits for whatsapp link
$whatsapp_number = preg_replace('/[^0-9]/', '', $contact_phone);
?>
<?php
$footer_ads = [];
if (isset($pdo)) {
    try {
        $stmtAds = $pdo->query("SELECT * FROM advertisements WHERE status = 1 AND position = 'footer' ORDER BY created_at DESC");
        $footer_ads = $stmtAds->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>
    </main>
    
    <!-- Footer Ads -->
    <?php if(!empty($footer_ads)): ?>
    <section class="container mx-auto px-4 md:px-8 mt-12 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= count($footer_ads) > 4 ? 4 : (count($footer_ads) > 0 ? count($footer_ads) : 1) ?> gap-4">
            <?php foreach($footer_ads as $ad): 
                $img_path = ltrim(str_replace('../', '', $ad['image']), '/\\');
            ?>
                <div class="w-full flex justify-center items-center">
                    <?php if(!empty($ad['link'])): ?>
                        <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="block w-full hover:opacity-90 transition">
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="w-full h-auto object-contain max-h-48 rounded shadow-md border border-gray-200">
                        </a>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($ad['title'] ?? '') ?>" class="w-full h-auto object-contain max-h-48 rounded shadow-md border border-gray-200">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white border-t border-white/10 mt-12">
        <div class="container mx-auto px-4 md:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold text-accent mb-4">Digambar Jain Parichay Sammelan Samiti, Ahmedabad</h3>
                    <p class="text-gray-300">Exclusive matrimony platform for the Digambar Jain Samaj. Find your perfect life partner within the community.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-300 hover:text-accent transition">About Us</a></li>
                        <li><a href="community.php" class="text-gray-300 hover:text-accent transition">Community</a></li>
                        <li><a href="success-stories.php" class="text-gray-300 hover:text-accent transition">Success Stories</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="contact.php" class="text-gray-300 hover:text-accent transition">Contact Us</a></li>
                        <li><a href="privacy.php" class="text-gray-300 hover:text-accent transition">Privacy Policy</a></li>
                        <li><a href="terms.php" class="text-gray-300 hover:text-accent transition">Terms & Conditions</a></li>
                        <li><a href="faq.php" class="text-gray-300 hover:text-accent transition">FAQs</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><strong>Digambar Jain Parichay Sammelan Samiti, Ahmedabad</strong></li>
                        <li><i class="fab fa-whatsapp mr-2"></i> WhatsApp: <?= htmlspecialchars($contact_phone) ?></li>
                        <li><i class="fas fa-envelope mr-2"></i> <?= htmlspecialchars($contact_email) ?></li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> <?= htmlspecialchars($contact_address) ?></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> Jain Digambar Matrimony. All rights reserved. Established 2026.</p>
            </div>
        </div>
    </footer>
    
    <!-- Sticky WhatsApp Button -->
    <a href="https://wa.me/<?= htmlspecialchars($whatsapp_number) ?>" target="_blank" class="fixed bottom-6 right-6 bg-green-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-2xl hover:bg-green-600 hover:scale-110 transition-all duration-300 z-50">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js"></script>
    
    <!-- Counter Script -->
    <script>
        // Counter function
        function startCounters() {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.innerText = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCounter();
            });
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            // Remove footer margin if the last section is dark to prevent white gap
            const main = document.querySelector('main');
            if (main && main.lastElementChild && main.lastElementChild.classList.contains('bg-dark')) {
                const footer = document.querySelector('footer');
                if (footer) {
                    footer.classList.remove('mt-20');
                    footer.classList.add('mt-0');
                }
            }

            // Initialize AOS
            AOS.init({
                duration: 1000,
                once: true,
                offset: 100
            });
            
            // Initialize Typed.js
            if(document.getElementById('typed-text')) {
                new Typed('#typed-text', {
                    strings: ['Find Your Perfect Life Partner', 'Within Digambar Jain Samaj', 'Trusted Since 2026'],
                    typeSpeed: 50,
                    backSpeed: 30,
                    loop: true
                });
            }
            
            // Initialize General Swiper (e.g. for Profiles)
            if(document.querySelector('.swiper:not(.hero-ad-swiper)')) {
                new Swiper('.swiper:not(.hero-ad-swiper)', {
                    slidesPerView: 1,
                    spaceBetween: 30,
                    loop: true,
                    autoplay: {
                        delay: 3000,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: { slidesPerView: 1 },
                        768: { slidesPerView: 2 },
                        1024: { slidesPerView: 3 },
                    }
                });
            }
            
            // Initialize Hero Ad Swiper
            if(document.querySelector('.hero-ad-swiper')) {
                new Swiper('.hero-ad-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    loop: true,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                });
            }
            
            // Start counters when in viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if(entry.isIntersecting) {
                        startCounters();
                        observer.disconnect();
                    }
                });
            });
            
            const counterSection = document.querySelector('.stats-section');
            if(counterSection) observer.observe(counterSection);
            
            // Hamburger Menu
            const hamburger = document.getElementById('hamburger');
            const mobileMenu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('overlay');
            
            if(hamburger) {
                hamburger.addEventListener('click', () => {
                    hamburger.classList.toggle('active');
                    mobileMenu.classList.toggle('active');
                    overlay.classList.toggle('active');
                    document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
                });
            }
            
            if(overlay) {
                overlay.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
            
            const closeBtn = document.getElementById('closeMobileMenu');
            if(closeBtn) {
                closeBtn.addEventListener('click', () => {
                    if (hamburger) hamburger.classList.remove('active');
                    if (mobileMenu) mobileMenu.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }
        });

        // Prevent Form Resubmission Warning on Refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
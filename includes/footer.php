    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white border-t border-white/10 mt-20">
        <div class="container mx-auto px-4 md:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold text-accent mb-4">Jain Digambar</h3>
                    <p class="text-gray-300">Exclusive matrimony platform for the Digambar Jain Samaj. Find your perfect life partner within the community.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="https://facebook.com" class="text-gray-300 hover:text-accent transition" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com" class="text-gray-300 hover:text-accent transition" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://twitter.com" class="text-gray-300 hover:text-accent transition" target="_blank"><i class="fab fa-twitter"></i></a>
                        <a href="https://youtube.com" class="text-gray-300 hover:text-accent transition" target="_blank"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-300 hover:text-accent transition">About Us</a></li>
                        <li><a href="community.php" class="text-gray-300 hover:text-accent transition">Community</a></li>
                        <li><a href="registration.php" class="text-gray-300 hover:text-accent transition">Registration</a></li>
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
                        <li><i class="fas fa-phone mr-2"></i> +91 7575005121</li>
                        <li><i class="fas fa-envelope mr-2"></i> digambarjainparichay@gmail.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 23-A, Shubhlaxmi Palace, Opp. Money Plant Junction, Bhuyangdev Cross Road, Sola Road, Ahmedabad-380061.</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 Jain Digambar Matrimony. All rights reserved. Established 2026.</p>
            </div>
        </div>
    </footer>
    
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
            
            // Initialize Swiper
            if(document.querySelector('.swiper')) {
                new Swiper('.swiper', {
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
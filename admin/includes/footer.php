    </main>
</div>
<!-- End Main Content Wrapper -->

<!-- Script for Mobile Menu Toggle -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('aside');
        
        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('absolute');
                sidebar.classList.toggle('h-full');
                sidebar.classList.toggle('shadow-2xl');
            });
        }
    });

    // Prevent Form Resubmission Warning on Refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
</body>
</html>

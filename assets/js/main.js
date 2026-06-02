// Anime.js animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate hero elements
    anime({
        targets: '.hero-title',
        translateY: [50, 0],
        opacity: [0, 1],
        duration: 800,
        easing: 'easeOutCubic'
    });
    
    anime({
        targets: '.hero-subtitle',
        translateY: [30, 0],
        opacity: [0, 1],
        duration: 800,
        delay: 200,
        easing: 'easeOutCubic'
    });
    
    anime({
        targets: '.hero-btn',
        scale: [0.8, 1],
        opacity: [0, 1],
        duration: 600,
        delay: 400,
        easing: 'spring(1, 80, 10, 0)'
    });
    
    // Animate floating elements
    anime({
        targets: '.float-element',
        translateY: [-10, 10],
        direction: 'alternate',
        loop: true,
        duration: 2000,
        easing: 'easeInOutQuad'
    });
});

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if(!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if(!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// Phone number formatting
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if(value.length > 10) value = value.slice(0, 10);
    if(value.length > 5) {
        value = value.slice(0, 5) + ' ' + value.slice(5);
    }
    input.value = value;
}

// File upload preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
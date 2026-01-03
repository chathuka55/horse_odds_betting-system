// Main JavaScript File

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if(mobileMenuButton) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Auto-update odds simulation
    if(document.querySelector('.odds-update')) {
        setInterval(updateOdds, 5000);
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation classes on scroll
    observeElements();
});

// Simulate odds updates
function updateOdds() {
    const oddsElements = document.querySelectorAll('.odds-value');
    oddsElements.forEach(element => {
        // Random small change in odds
        const currentOdds = parseFloat(element.textContent);
        const change = (Math.random() - 0.5) * 0.5;
        const newOdds = Math.max(1.1, currentOdds + change).toFixed(2);
        
        // Update with animation
        element.style.transition = 'color 0.3s';
        element.style.color = change > 0 ? '#10b981' : '#ef4444';
        element.textContent = newOdds;
        
        setTimeout(() => {
            element.style.color = '';
        }, 300);
    });
}

// Intersection Observer for animations
function observeElements() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.observe-animate').forEach(el => {
        observer.observe(el);
    });
}

// Live time update
function updateTime() {
    const timeElements = document.querySelectorAll('.live-time');
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    
    timeElements.forEach(element => {
        element.textContent = timeString;
    });
}

// Update time every second
setInterval(updateTime, 1000);

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-6 py-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    notification.classList.add(bgColor, 'text-white');
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if(!form) return false;
    
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if(!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    });
}

// Lazy loading for images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('fade-in');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
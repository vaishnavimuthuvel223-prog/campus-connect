/**
 * Campus Recruitment System - Modern UI JavaScript Enhancements
 * Provides animations, theme switching, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations on page load
    initializeAnimations();
    
    // Initialize form interactions
    initializeFormInteractions();
    
    // Initialize theme system
    initializeTheme();
    
    // Initialize scroll effects
    initializeScrollEffects();
});

/**
 * Initialize scroll-triggered animations
 */
function initializeAnimations() {
    // Animate stat counters on homepage
    const statElements = document.querySelectorAll('[data-count]');
    if (statElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    animateCounter(entry.target);
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.5 });
        
        statElements.forEach(el => observer.observe(el));
    }
    
    // Stagger card animations
    const cards = document.querySelectorAll('.card, .lp-portal-card, .lp-drive-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
}

/**
 * Animate counter numbers from 0 to target
 */
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-count')) || 0;
    const duration = 1800;
    const start = Date.now();
    
    function update() {
        const elapsed = Date.now() - start;
        const progress = Math.min(elapsed / duration, 1);
        
        // Ease-out cubic for smooth animation
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(eased * target);
        
        element.textContent = current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = target.toLocaleString();
        }
    }
    
    requestAnimationFrame(update);
}

/**
 * Initialize form input interactions
 */
function initializeFormInteractions() {
    // Add focus/blur effects to form inputs
    const inputs = document.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        // Add placeholder animation support
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
        
        // Animate input on value change
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
    });
    
    // Password visibility toggle
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const wrapper = input.parentElement;
        
        // Create toggle button
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.innerHTML = '👁️';
        toggle.style.cssText = `
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            opacity: 0.6;
            transition: opacity 0.2s;
        `;
        
        toggle.addEventListener('mouseout', () => toggle.style.opacity = '0.6');
        toggle.addEventListener('mouseover', () => toggle.style.opacity = '1');
        
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (input.type === 'password') {
                input.type = 'text';
                toggle.innerHTML = '🙈';
            } else {
                input.type = 'password';
                toggle.innerHTML = '👁️';
            }
        });
        
        wrapper.style.position = 'relative';
        wrapper.appendChild(toggle);
    });
}

/**
 * Initialize theme switching
 */
function initializeTheme() {
    // Theme switching and toggle button removed for student dashboard. Only light theme is used.
}

/**
 * Apply theme to page
 */
function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.querySelector('.theme-toggle')?.setAttribute('title', 'Switch to light mode');
        document.querySelector('.theme-toggle')?.setAttribute('aria-label', 'Switch to light mode');
    } else {
        document.documentElement.removeAttribute('data-theme');
        document.querySelector('.theme-toggle')?.setAttribute('title', 'Switch to dark mode');
        document.querySelector('.theme-toggle')?.setAttribute('aria-label', 'Switch to dark mode');
    }
}

/**
 * Smooth scroll effects
 */
function initializeScrollEffects() {
    // Navbar shadow on scroll
    const navbar = document.querySelector('.navbar, .lp-topbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });
    }
    
    // Parallax effect for hero section
    const hero = document.querySelector('.lp-hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const offset = window.scrollY;
            hero.style.backgroundPosition = `center ${offset * 0.5}px`;
        }, { passive: true });
    }
    
    // Fade-in on scroll for elements with data-animate
    const animatedElements = document.querySelectorAll('[data-animate]');
    if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        animatedElements.forEach(el => observer.observe(el));
    }
}

/**
 * Utility: Scroll to element smoothly
 */
window.scrollToElement = function(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

/**
 * Utility: Toggle visibility with animation
 */
window.toggleElement = function(selector) {
    const element = document.querySelector(selector);
    if (element) {
        if (element.style.display === 'none') {
            element.style.display = 'block';
            element.style.animation = 'slideUp 0.3s ease-out';
        } else {
            element.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                element.style.display = 'none';
            }, 300);
        }
    }
};

/**
 * Utility: Show notification toast
 */
window.showNotification = function(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `notification toast-${type}`;
    toast.innerHTML = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        background: ${type === 'success' ? '#d1fae5' : type === 'error' ? '#fde2e8' : '#dbeafe'};
        color: ${type === 'success' ? '#065f46' : type === 'error' ? '#991b1b' : '#1e40af'};
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        animation: slideDown 0.3s ease-out;
        z-index: 9999;
        font-weight: 600;
        max-width: 300px;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, duration);
};

// Export for use in other scripts
window.CampusUI = {
    animateCounter,
    applyTheme,
    scrollToElement,
    toggleElement,
    showNotification
};

// Optional: Add console branding
console.log('%c🎓 Campus Recruitment System - Modern UI v1.0', 'color: #3b82f6; font-size: 16px; font-weight: bold;');
console.log('%cPowered by Modern Glassmorphism Design', 'color: #06b6d4; font-size: 12px;');

/* ==========================================
   LOGIN PAGE ANIMATIONS & INTERACTIONS
   ========================================== */

class LoginAnimations {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.inputs = document.querySelectorAll('.form-input');
        this.button = document.querySelector('.login-btn');
        this.init();
    }

    init() {
        this.setupInputAnimations();
        this.setupButtonAnimations();
        this.setupFormSubmit();
        this.setupParticles();
    }

    // Input focus animations
    setupInputAnimations() {
        this.inputs.forEach(input => {
            // Floating label animation
            input.addEventListener('focus', () => {
                this.animateInputFocus(input);
            });

            input.addEventListener('blur', () => {
                if (!input.value) {
                    this.resetInputLabel(input);
                }
            });

            // Typing animation
            input.addEventListener('input', () => {
                this.typeAnimation(input);
            });
        });
    }

    animateInputFocus(input) {
        const wrapper = input.closest('.input-wrapper');
        const glow = wrapper.querySelector('.input-glow');
        
        // Add glow effect
        glow.style.opacity = '1';
        input.style.transform = 'scale(1.02)';
    }

    resetInputLabel(input) {
        input.style.transform = 'scale(1)';
    }

    typeAnimation(input) {
        // Subtle pulse on type
        input.style.animation = 'pulse 0.3s ease-out';
        setTimeout(() => {
            input.style.animation = '';
        }, 300);
    }

    // Button animations
    setupButtonAnimations() {
        if (!this.button) return;

        this.button.addEventListener('mouseenter', () => {
            this.button.style.transform = 'translateY(-3px)';
        });

        this.button.addEventListener('mouseleave', () => {
            this.button.style.transform = 'translateY(0)';
        });

        this.button.addEventListener('click', (e) => {
            this.rippleEffect(e);
        });
    }

    // Ripple effect on button click
    rippleEffect(e) {
        const button = e.target.closest('.login-btn');
        if (!button) return;

        const rect = button.getBoundingClientRect();
        const ripple = document.createElement('span');
        ripple.className = 'btn-ripple';
        
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';

        button.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    }

    // Form submission
    setupFormSubmit() {
        if (!this.form) return;

        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm();
        });
    }

    submitForm() {
        // Add loading animation
        const btnText = this.button.querySelector('.btn-text');
        const originalText = btnText.textContent;
        
        this.button.disabled = true;
        btnText.textContent = 'Signing in...';
        this.button.style.opacity = '0.7';

        // Simulate API call
        setTimeout(() => {
            this.button.disabled = false;
            btnText.textContent = originalText;
            this.button.style.opacity = '1';
            
            // Show success message
            this.showNotification('Welcome back! Redirecting...', 'success');
            
            // Simulate redirect after 2 seconds
            setTimeout(() => {
                window.location.href = 'modern-dashboard.html';
            }, 2000);
        }, 2000);
    }

    // Particle animation setup
    setupParticles() {
        const particles = document.querySelectorAll('.particle');
        particles.forEach((particle, index) => {
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = (Math.random() * 5) + 's';
        });
    }

    // Toast notification
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            font-weight: 500;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Add animation styles
const animStyle = document.createElement('style');
animStyle.innerHTML = `
    @keyframes pulse {
        0% {
            transform: scale(1.02);
        }
        50% {
            transform: scale(1.025);
        }
        100% {
            transform: scale(1.02);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }

    @keyframes ripple {
        0% {
            transform: scale(0);
            opacity: 1;
        }
        100% {
            transform: scale(1);
            opacity: 0;
        }
    }

    .btn-ripple {
        animation: ripple 0.6s ease-out;
    }
`;
document.head.appendChild(animStyle);

// Initialize login animations when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('loginForm')) {
            window.loginAnimations = new LoginAnimations();
        }
    });
} else {
    if (document.getElementById('loginForm')) {
        window.loginAnimations = new LoginAnimations();
    }
}

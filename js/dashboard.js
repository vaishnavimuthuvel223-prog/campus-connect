/* ==========================================
   DASHBOARD INTERACTIONS & ANIMATIONS
   ========================================== */

class Dashboard {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        this.statCards = document.querySelectorAll('.stat-value');
        this.navItems = document.querySelectorAll('.nav-item');
        this.userButton = document.querySelector('.user-button');
        this.isMobile = window.innerWidth < 768;
        
        this.init();
    }

    init() {
        this.setupSidebarToggle();
        this.setupNavigation();
        this.setupAnimatedCounters();
        this.setupUserMenu();
        this.setupResponsive();
        this.setupHoverEffects();
    }

    // Sidebar toggle for mobile
    setupSidebarToggle() {
        if (!this.sidebarToggle) return;

        this.sidebarToggle.addEventListener('click', () => {
            this.sidebar.classList.toggle('active');
            this.mobileMenuOverlay?.classList.toggle('active');
        });

        this.mobileMenuOverlay?.addEventListener('click', () => {
            this.sidebar.classList.remove('active');
            this.mobileMenuOverlay.classList.remove('active');
        });
    }

    // Navigation active state
    setupNavigation() {
        this.navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Remove active class from all items
                this.navItems.forEach(i => i.classList.remove('active'));
                // Add active class to clicked item
                item.classList.add('active');
                
                // Close sidebar on mobile
                if (this.isMobile) {
                    this.sidebar.classList.remove('active');
                    this.mobileMenuOverlay?.classList.remove('active');
                }

                // Smooth scroll to section (if using anchor links)
                const href = item.getAttribute('href');
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
    }

    // Animated counter effect
    setupAnimatedCounters() {
        this.statCards.forEach(card => {
            const target = parseInt(card.getAttribute('data-target')) || 0;
            const animated = this.animateCounter(card, target, 1200);
        });
    }

    animateCounter(element, target, duration) {
        const start = 0;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (target - start) * easeOut);
            
            element.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = target;
            }
        };

        animate();
    }

    // User menu dropdown
    setupUserMenu() {
        if (!this.userButton) return;

        this.userButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdown = this.userButton.nextElementSibling;
            if (dropdown) {
                dropdown.style.opacity = dropdown.style.opacity === '1' ? '0' : '1';
                dropdown.style.visibility = dropdown.style.visibility === 'visible' ? 'hidden' : 'visible';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            const dropdown = this.userButton?.nextElementSibling;
            if (dropdown) {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
            }
        });
    }

    // Responsive handling
    setupResponsive() {
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 768;
            
            if (!this.isMobile) {
                this.sidebar?.classList.remove('active');
                this.mobileMenuOverlay?.classList.remove('active');
            }
        });
    }

    // Hover effects for cards
    setupHoverEffects() {
        const cards = document.querySelectorAll('.stat-card, .event-card, .activity-item');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    }
}

// Action buttons for time period selection
class ActionButtons {
    constructor() {
        this.buttons = document.querySelectorAll('.action-btn');
        this.init();
    }

    init() {
        this.buttons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const group = btn.closest('.card-actions');
                if (group) {
                    group.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
                
                // Trigger data update animation
                this.animateDataUpdate();
            });
        });
    }

    animateDataUpdate() {
        const chart = document.querySelector('.chart-placeholder');
        if (chart) {
            chart.style.opacity = '0.5';
            setTimeout(() => {
                chart.style.opacity = '1';
            }, 300);
        }
    }
}

// Search functionality
class Search {
    constructor() {
        this.searchBox = document.querySelector('.search-box');
        this.searchInput = this.searchBox?.querySelector('input');
        this.init();
    }

    init() {
        if (!this.searchInput) return;

        this.searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            
            if (query.length > 0) {
                this.highlightResults(query);
            } else {
                this.clearHighlight();
            }
        });
    }

    highlightResults(query) {
        const navItems = document.querySelectorAll('.nav-item');
        const eventCards = document.querySelectorAll('.event-card');

        navItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(query)) {
                item.style.opacity = '1';
                item.style.transform = 'scale(1)';
            } else {
                item.style.opacity = '0.5';
                item.style.transform = 'scale(0.98)';
            }
        });

        eventCards.forEach(card => {
            const text = card.textContent.toLowerCase();
            if (text.includes(query)) {
                card.style.opacity = '1';
            } else {
                card.style.opacity = '0.5';
            }
        });
    }

    clearHighlight() {
        const navItems = document.querySelectorAll('.nav-item');
        const eventCards = document.querySelectorAll('.event-card');

        navItems.forEach(item => {
            item.style.opacity = '1';
            item.style.transform = 'scale(1)';
        });

        eventCards.forEach(card => {
            card.style.opacity = '1';
        });
    }
}

// Notification interactions
class Notifications {
    constructor() {
        this.notificationBtn = document.querySelector('.notification-btn');
        this.init();
    }

    init() {
        if (!this.notificationBtn) return;

        this.notificationBtn.addEventListener('click', () => {
            this.showNotificationPanel();
        });
    }

    showNotificationPanel() {
        const message = 'You have 3 unread notifications';
        this.createToast(message, 'info');
    }

    createToast(message, type) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 16px 24px;
            background: rgba(59, 130, 246, 0.9);
            color: white;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            font-weight: 500;
            max-width: 300px;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Add dashboard-specific animations
const dashboardStyle = document.createElement('style');
dashboardStyle.innerHTML = `
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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .stat-card {
        animation: fadeIn 0.6s ease-out backwards;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }

    .event-card {
        animation: fadeIn 0.6s ease-out backwards;
    }

    .event-card:nth-child(1) { animation-delay: 0.1s; }
    .event-card:nth-child(2) { animation-delay: 0.2s; }
    .event-card:nth-child(3) { animation-delay: 0.3s; }

    .mobile-menu-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        z-index: 99;
        transition: all 0.3s ease;
    }

    .mobile-menu-overlay.active {
        opacity: 1;
        visibility: visible;
    }
`;
document.head.appendChild(dashboardStyle);

// Initialize dashboard when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.querySelector('.sidebar')) {
            window.dashboard = new Dashboard();
            window.actionButtons = new ActionButtons();
            window.search = new Search();
            window.notifications = new Notifications();
        }
    });
} else {
    if (document.querySelector('.sidebar')) {
        window.dashboard = new Dashboard();
        window.actionButtons = new ActionButtons();
        window.search = new Search();
        window.notifications = new Notifications();
    }
}

/* ==========================================
   THEME MANAGER - LIGHT & DARK MODE SUPPORT
   Persistent Theme Storage
   ========================================== */

class ThemeManager {
    constructor() {
        this.STORAGE_KEY = 'campuss-theme';
        this.LIGHT_THEME = 'light';
        this.DARK_THEME = 'dark';
        this.init();
    }

    init() {
        // Load saved theme or default to light
        const savedTheme = this.LIGHT_THEME; // Force light theme, ignore saved preference
        this.setTheme(savedTheme);
        // Theme toggle button disabled - no createToggleButton() call
        // this.createToggleButton();
        // this.setupToggleListeners();
        console.log('✓ Theme Manager Initialized - Light Theme Only');
    }

    getSavedTheme() {
        const saved = localStorage.getItem(this.STORAGE_KEY);
        return saved || this.LIGHT_THEME;
    }

    setTheme(theme) {
        if (theme === this.DARK_THEME) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.add('dark-mode');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            document.body.classList.remove('dark-mode');
        }
        localStorage.setItem(this.STORAGE_KEY, theme);
    }

    toggleTheme() {
        const currentTheme = this.getSavedTheme();
        const newTheme = currentTheme === this.LIGHT_THEME ? this.DARK_THEME : this.LIGHT_THEME;
        this.setTheme(newTheme);
        this.updateToggleButton(newTheme);
        return newTheme;
    }

    createToggleButton() {
        if (document.getElementById('themeToggle')) return;
        
        const toggle = document.createElement('button');
        toggle.id = 'themeToggle';
        toggle.className = 'theme-toggle';
        toggle.setAttribute('aria-label', 'Toggle theme');
        toggle.title = 'Toggle Dark/Light Theme';
        
        const currentTheme = this.getSavedTheme();
        toggle.innerHTML = currentTheme === this.DARK_THEME 
            ? '☀️ Light' 
            : '🌙 Dark';
        
        // Append to body for availability across all pages
        document.body.appendChild(toggle);
    }

    updateToggleButton(theme) {
        const toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.innerHTML = theme === this.DARK_THEME 
                ? '☀️ Light' 
                : '🌙 Dark';
        }
    }

    setupToggleListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.id === 'themeToggle' || e.target.closest('#themeToggle')) {
                this.toggleTheme();
            }
        });
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}

# 📋 Modern UI Components - Copy-Paste Reference

Quick reference guide with copy-paste code snippets for common components.

---

## 🎯 Top Navigation Bar

### Basic Structure
```html
<header class="top-navbar">
    <div class="navbar-left">
        <h1 class="page-title">Dashboard</h1>
    </div>

    <div class="navbar-right">
        <!-- Search Box -->
        <div class="search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Search...">
        </div>

        <!-- Notifications -->
        <button class="icon-btn notification-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="notification-badge">3</span>
        </button>

        <!-- Theme Toggle -->
        <button class="icon-btn" id="themeToggle">
            <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>

        <!-- User Menu -->
        <div class="user-menu">
            <button class="user-button">
                <img src="https://via.placeholder.com/32" alt="User" class="user-avatar">
                <span class="user-name">John Doe</span>
            </button>
            <div class="user-dropdown">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <hr>
                <a href="#">Sign Out</a>
            </div>
        </div>
    </div>
</header>
```

---

## 📊 Statistics Card

### Basic Stat Card
```html
<div class="stat-card">
    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
            <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline>
            <line x1="12" y1="12" x2="20" y2="7.5"></line>
            <line x1="12" y1="12" x2="12" y2="21"></line>
            <line x1="12" y1="12" x2="4" y2="7.5"></line>
        </svg>
    </div>
    <div class="stat-content">
        <p class="stat-label">Applications</p>
        <h3 class="stat-value" data-target="24">0</h3>
        <span class="stat-change positive">+12% from last month</span>
    </div>
</div>
```

### With Different Gradients
```html
<!-- Pink to Red -->
<div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
<!-- Blue to Cyan -->
<div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
<!-- Green to Teal -->
<div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
```

---

## 🎴 Event Card

### Complete Event Card
```html
<div class="event-card">
    <div class="event-date">
        <span class="event-day">18</span>
        <span class="event-month">Mar</span>
    </div>
    <div class="event-content">
        <h4>Google Recruitment Drive</h4>
        <p class="event-company">Google India</p>
        <div class="event-details">
            <span>📍 Virtual</span>
            <span>⏰ 2:00 PM</span>
        </div>
    </div>
    <button class="event-btn">Register</button>
</div>
```

---

## 🔘 Button Variations

### Primary Button (Gradient)
```html
<button class="login-btn">
    <span class="btn-text">Sign In</span>
    <span class="btn-ripple"></span>
</button>
```

### Secondary Button
```html
<button class="social-btn">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <!-- SVG path -->
    </svg>
</button>
```

### Action Buttons
```html
<div class="card-actions">
    <button class="action-btn">Week</button>
    <button class="action-btn">Month</button>
    <button class="action-btn active">Year</button>
</div>
```

### Icon Button
```html
<button class="icon-btn">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <!-- SVG -->
    </svg>
</button>
```

---

## 📝 Form Elements

### Input with Floating Label
```html
<div class="form-group">
    <div class="input-wrapper">
        <input 
            type="email" 
            class="form-input" 
            id="email" 
            placeholder="Enter your email"
            required
        >
        <label class="floating-label" for="email">Email Address</label>
        <span class="input-glow"></span>
    </div>
</div>
```

### Role Selector
```html
<div class="role-selector">
    <label class="role-option">
        <input type="radio" name="role" value="student" checked>
        <span class="role-label">Student</span>
    </label>
    <label class="role-option">
        <input type="radio" name="role" value="staff">
        <span class="role-label">Staff</span>
    </label>
    <label class="role-option">
        <input type="radio" name="role" value="admin">
        <span class="role-label">Admin</span>
    </label>
</div>
```

### Checkbox
```html
<label class="checkbox-label">
    <input type="checkbox" name="remember">
    <span>Remember me</span>
</label>
```

---

## 🗂️ Sidebar Navigation

### Complete Sidebar
```html
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-box">
            <span class="logo-icon">◆</span>
            <span class="logo-text">CampusConnect</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="#" class="nav-item active">
            <span class="nav-icon">▊</span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">📋</span>
            <span class="nav-text">Drives</span>
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">📅</span>
            <span class="nav-text">Calendar</span>
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Results</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="#" class="logout-btn">Sign Out</a>
    </div>
</aside>
```

---

## 📈 Chart Card

### Simple Chart Container
```html
<section class="chart-card">
    <div class="card-header">
        <h3>Application Trend</h3>
        <div class="card-actions">
            <button class="action-btn">Week</button>
            <button class="action-btn active">Month</button>
            <button class="action-btn">Year</button>
        </div>
    </div>
    <div class="chart-placeholder">
        <svg viewBox="0 0 400 200" class="chart-svg">
            <path d="M 0 150 L 50 120 L 100 80 L 150 100 L 200 60 L 250 90 L 300 40 L 350 70 L 400 30" 
                  fill="none" stroke="url(#gradient1)" stroke-width="3" stroke-linecap="round"/>
            <defs>
                <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>
</section>
```

---

## 💬 Activity Feed

### Activity Items
```html
<div class="activity-list">
    <div class="activity-item">
        <div class="activity-icon">🚀</div>
        <div class="activity-content">
            <p class="activity-title">New application received</p>
            <p class="activity-time">2 hours ago</p>
        </div>
    </div>
    <div class="activity-item">
        <div class="activity-icon">📧</div>
        <div class="activity-content">
            <p class="activity-title">Interview scheduled</p>
            <p class="activity-time">5 hours ago</p>
        </div>
    </div>
    <div class="activity-item">
        <div class="activity-icon">✅</div>
        <div class="activity-content">
            <p class="activity-title">Profile updated</p>
            <p class="activity-time">1 day ago</p>
        </div>
    </div>
</div>
```

---

## 🌙 Theme Toggle

### HTML
```html
<button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
    <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="5"></circle>
        <!-- ... sun icon paths ... -->
    </svg>
    <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
</button>
```

### JavaScript
```javascript
// Already included in theme-manager.js
// Just add the HTML and it works automatically!

// Manual toggle:
document.body.classList.toggle('dark-mode');

// Check current theme:
const isDarkMode = document.body.classList.contains('dark-mode');
```

---

## 🎨 Custom Gradient Examples

### In CSS
```css
.custom-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### In HTML (Inline)
```html
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    Content
</div>
```

### Preset Gradients
```css
/* Use CSS variables */
background: var(--gradient-primary);     /* Purple-Blue */
background: var(--gradient-secondary);   /* Pink-Red */
background: var(--gradient-tertiary);    /* Blue-Cyan */
background: var(--gradient-quaternary);  /* Green-Teal */
background: var(--gradient-warm);        /* Orange-Yellow */
```

---

## 📱 Responsive Layout

### Grid System
```html
<!-- 4 columns on desktop, 2 on tablet, 1 on mobile -->
<div class="stats-grid">
    <div class="stat-card">...</div>
    <div class="stat-card">...</div>
    <div class="stat-card">...</div>
    <div class="stat-card">...</div>
</div>
```

### Two Column Layout
```html
<div class="main-grid">
    <section class="chart-card">...</section>
    <section class="activity-card">...</section>
</div>
```

---

## ✨ Utility Classes

### Text Effects
```html
<!-- Gradient text -->
<span class="gradient-text">Premium Design</span>

<!-- Glow effect -->
<div class="glow">Content with glow</div>

<!-- Glass effect -->
<div class="glass-effect">Glassmorphism content</div>
```

### Shadows
```css
box-shadow: var(--shadow-sm);   /* Small shadow */
box-shadow: var(--shadow-md);   /* Medium shadow */
box-shadow: var(--shadow-lg);   /* Large shadow */
box-shadow: var(--shadow-xl);   /* Extra large shadow */
```

### Spacing
```css
margin: var(--space-5);         /* 20px */
padding: var(--space-6);        /* 24px */
gap: var(--space-4);            /* 16px */
```

---

## 🎬 JavaScript Snippets

### Toggle Dark Mode programmatically
```javascript
function toggleDarkMode() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}
```

### Animated Counter
```javascript
function animateCounter(element, target, duration = 2000) {
    let current = 0;
    const increment = target / (duration / 16);
    
    const counter = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(counter);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}
```

### Toast Notification
```javascript
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px;
        padding: 16px 24px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 8px;
        z-index: 9999;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
```

---

## 🔄 Page Transitions

### Smooth Scroll
```javascript
// Already enabled in HTML:
html { scroll-behavior: smooth; }
```

### Fade In Animation
```css
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.card {
    animation: fadeIn 0.6s ease-out;
}
```

---

## 🎯 Common Use Cases

### Show Loading State
```html
<div class="skeleton" style="width: 100%; height: 20px; border-radius: 8px;"></div>
```

### Disabled Button
```html
<button class="login-btn" disabled style="opacity: 0.7;">
    Processing...
</button>
```

### Empty State
```html
<div style="text-align: center; padding: 48px;">
    <p style="color: var(--color-text-secondary); font-size: 1.1rem;">
        No data available
    </p>
</div>
```

---

## 📧 Copy-Paste Template - Complete Page

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Recruitment - Dashboard</title>
    <link rel="stylesheet" href="css/modern-design.css">
</head>
<body class="dashboard-page">
    <!-- Sidebar -->
    <aside class="sidebar">
        <!-- Sidebar content here -->
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <!-- Navbar content here -->
        </header>

        <!-- Page Content -->
        <div class="content-wrapper">
            <!-- Your content here -->
        </div>
    </main>

    <script src="js/theme-manager.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
```

---

## 🚀 Quick Integration Checklist

- [ ] Copy CSS files to `css/` folder
- [ ] Copy JS files to `js/` folder
- [ ] Link CSS in HTML `<head>`
- [ ] Add JS scripts before `</body>`
- [ ] Test dark mode toggle
- [ ] Test responsive layout mobile
- [ ] Test form interactions
- [ ] Verify all animations smooth
- [ ] Update content with real data
- [ ] Deploy to server

---

**Happy coding! Copy and paste these components to build your amazing interface! 🎨**

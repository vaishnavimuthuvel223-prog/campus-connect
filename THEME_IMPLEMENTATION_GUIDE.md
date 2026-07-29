# Campus Connect Theme System Implementation Guide

## Overview
A complete **Dark & Light Theme System** has been implemented for Campus Connect. This system provides:
- ✅ Persistent theme preference (stored in localStorage)
- ✅ Smooth transitions between themes
- ✅ Automatic theme toggle button on all pages
- ✅ Full coverage of all UI components

## How It Works

### Theme Manager (theme-manager.js)
The theme manager handles:
- Theme detection and loading on page load
- Theme switching with persistent storage
- Automatic creation of theme toggle button
- Storage key: `campuss-theme` (values: 'light' or 'dark')

### CSS Implementation
The system uses CSS variables that change based on theme:
- `data-theme` attribute on root element (`<html>`)
- `dark-mode` class on body element
- Consistent naming convention for variables

## Quick Start

### 1. Include Required Files
All pages need these references:

```html
<!-- In the <head> section -->
<link rel="stylesheet" href="/campuss/css/modern-design.css">
<link rel="stylesheet" href="/campuss/css/style.css">

<!-- Before </body> closing tag -->
<script src="/campuss/js/theme-manager.js"></script>
```

### 2. Theme Toggle Button
The toggle button is **automatically added** to every page by `theme-manager.js`. No additional code needed!

**Button appearance:**
- Light theme: 🌙 Dark button (bottom-right fixed position)
- Dark theme: ☀️ Light button (bottom-right fixed position)

### 3. CSS Variables for Custom Components

Use these variables for custom styling:

#### Light/Dark Aware Variables:
```css
--bg-primary          /* Primary background color */
--bg-secondary        /* Secondary background color */
--bg-tertiary         /* Tertiary background color */
--text-primary        /* Primary text color */
--text-secondary      /* Secondary text color */
--text-tertiary       /* Tertiary text color */
--card-bg             /* Card background */
--border-color        /* Border color */
```

#### Usage Example:
```css
.my-component {
    background: var(--card-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
```

## Component Updates by Module

### Admin Panel (/admin/)
✅ **admin/dashboard.php** - Auto-supported (uses style.css)
✅ **admin/students.php** - Auto-supported
✅ **admin/companies.php** - Auto-supported
✅ **admin/drives.php** - Auto-supported
✅ **admin/staff.php** - Auto-supported
✅ **admin/announcements.php** - Auto-supported
✅ **admin/reports.php** - Auto-supported

### Staff Portal (/staff/)
✅ **staff/dashboard.php** - Auto-supported
✅ **staff/students.php** - Auto-supported
✅ **staff/login.php** - Auto-supported
✅ **staff/register.php** - Auto-supported
✅ **staff/profile.php** - Auto-supported

### Student Portal (/student/)
✅ **student/dashboard.php** - Auto-supported
✅ **student/login.php** - Auto-supported
✅ **student/register.php** - Auto-supported
✅ **student/drives.php** - Auto-supported
✅ **student/results.php** - Auto-supported
✅ **student/calendar.php** - Auto-supported
✅ **student/notifications.php** - Auto-supported

### Homepage
✅ **index.php** - Already updated (Black text now visible)

## Theme Colors Reference

### Light Theme (Default)
```
Background Primary:  #ffffff
Background Secondary: #f8fafc
Background Tertiary: #f1f5f9
Text Primary:        #1e293b
Text Secondary:      #64748b
Text Tertiary:       #94a3b8
Borders:            #e2e8f0
Cards:              #ffffff
```

### Dark Theme
```
Background Primary:  #0f172a
Background Secondary: #1e293b
Background Tertiary: #334155
Text Primary:        #f1f5f9
Text Secondary:      #cbd5e1
Text Tertiary:       #94a3b8
Borders:            #334155
Cards:              #1e293b
```

## Advanced: Custom Dark Mode Styling

For specific components that need custom dark mode styling:

```css
/* Light theme specific */
:root[data-theme="light"] .my-component {
    background: #ffffff;
    color: #1e293b;
}

/* Dark theme specific */
:root[data-theme="dark"] .my-component {
    background: #1e293b;
    color: #f1f5f9;
}

/* Alternative syntax for body class */
body.dark-mode .my-component {
    background: #1e293b;
    color: #f1f5f9;
}
```

## JavaScript Integration

### Access Current Theme
```javascript
// Get current theme
const currentTheme = themeManager.getSavedTheme();
console.log(currentTheme); // 'light' or 'dark'

// Toggle theme programmatically
window.themeManager.toggleTheme();

// Set specific theme
window.themeManager.setTheme('dark');
```

### Listen for Theme Changes
```javascript
document.addEventListener('DOMContentLoaded', () => {
    // Run code based on current theme
    if (themeManager.getSavedTheme() === 'dark') {
        console.log('Dark theme is active');
    }
});
```

## Theme Toggle Button Styling

The button uses these CSS selectors:

```css
#themeToggle {
    /* Base styles - Light theme gradient */
}

:root[data-theme="dark"] #themeToggle {
    /* Dark theme gradient */
}
```

**Customization Example:**
```css
/* Change button position */
#themeToggle {
    bottom: 3rem;  /* Changed from 2rem */
    right: 3rem;   /* Changed from 2rem */
}

/* Change button size */
#themeToggle {
    width: 60px;   /* Changed from 55px */
    height: 60px;  /* Changed from 55px */
}
```

## Migrating Existing Pages

### Step 1: Update Page Head Section
Replace hardcoded color styles with CSS variables:

**Before:**
```css
.header {
    background: #ffffff;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
}
```

**After:**
```css
.header {
    background: var(--card-bg);
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}
```

### Step 2: Update All Color References
Search for and replace hardcoded colors:
- `#ffffff` → `var(--card-bg)`
- `#1e293b` → `var(--text-primary)`
- `#f1f5f9` → `var(--bg-secondary)`
- `#e2e8f0` → `var(--border-color)`
- `#64748b` → `var(--text-secondary)`

### Step 3: Verify Script Inclusion
Ensure this script is included before `</body>`:
```html
<script src="/campuss/js/theme-manager.js"></script>
```

## Troubleshooting

### Theme Button Not Appearing
1. Verify `theme-manager.js` is loaded
2. Check browser console for errors
3. Clear localStorage: `localStorage.clear()`

### Colors Not Changing
1. Verify CSS files are linked properly
2. Check that you're using CSS variables (not hardcoded colors)
3. Clear browser cache (Ctrl+F5)
4. Verify `data-theme` attribute is set on `<html>` element

### Override a Specific Variable
```css
:root {
    --text-primary: #1e293b;  /* Default */
}

:root[data-theme="dark"] {
    --text-primary: #f1f5f9;  /* Override */
}
```

## File Locations

| File | Purpose |
|------|---------|
| `/js/theme-manager.js` | Theme switching logic |
| `/css/style.css` | Global theme variables |
| `/css/modern-design.css` | Modern UI theme variables |

## Browser Compatibility

✅ Chrome/Edge 88+
✅ Firefox 85+
✅ Safari 14+
✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Theme Persistence

- User's theme choice is saved in `localStorage`
- Key: `campuss-theme`
- Values: `'light'` (default) or `'dark'`
- Persists across browser sessions

## Performance Notes

- Theme switching uses CSS variables (instant, no re-render needed)
- Minimal JavaScript - only runs on page load
- All transitions use CSS (GPU-accelerated)
- No impact on page load time

---

## Summary

The theme system is **fully automatic** and requires minimal integration:
1. Include the script tags in your pages
2. Use CSS variables for colors (modern-design.css and style.css do this automatically)
3. The toggle button appears automatically
4. User preference is saved persistently

No additional work needed - just ensure all pages link to the CSS and JS files!

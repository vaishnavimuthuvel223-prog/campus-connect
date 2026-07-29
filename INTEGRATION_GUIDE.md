# Modern Design System Integration Guide

## Overview

This guide explains how to integrate the modern design system into your existing CampusRM application. The system consists of:

1. **modern-design.css** - Complete CSS design system with variables, components, and utilities
2. **theme-manager.js** - Dark/Light mode toggle with localStorage persistence
3. **animations.js** - Micro-interactions, animations, and UI utilities
4. **Templates** - Login, Dashboard, and Component showcase templates

---

## Quick Start (5-Minute Setup)

### Step 1: Add CSS Link to Header

In your `header.php` (before any existing stylesheets):

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/campuss/css/modern-design.css">
```

### Step 2: Add JavaScript Files

At the end of your `footer.php`:

```html
<script src="/campuss/js/theme-manager.js"></script>
<script src="/campuss/js/animations.js"></script>
```

### Step 3: Replace Body Tag

Change your page's opening `<body>` tag to:

```html
<body data-theme="dark">
```

This enables theme switching. The `data-theme` attribute will automatically update when users toggle themes.

---

## Component Usage

### 1. Buttons

**Available Variants:**
- `.btn-primary` - Gradient primary button (default)
- `.btn-secondary` - Blue gradient button
- `.btn-outline` - Border only button
- `.btn-ghost` - Glass morphism button
- `.btn-soft` - Subtle button
- `.btn-success` - Green gradient button
- `.btn-danger` - Red gradient button

**Basic Usage:**

```html
<!-- Primary Button -->
<button class="btn btn-primary">Click Me</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Submit</button>

<!-- Outline Button -->
<button class="btn btn-outline">Cancel</button>

<!-- Ghost Button -->
<button class="btn btn-ghost">Download</button>

<!-- Different Sizes -->
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary btn-base">Normal</button>
<button class="btn btn-primary btn-lg">Large</button>

<!-- Full Width -->
<button class="btn btn-primary btn-block">Full Width Button</button>

<!-- Disabled State -->
<button class="btn btn-primary" disabled>Disabled</button>
```

**Automatic Features:**
- Ripple click effect (built-in)
- Hover glow effect
- Smooth transitions
- Loading state support (add `aria-busy="true"`)

---

### 2. Cards

**Basic Card Structure:**

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Card Title</h3>
    </div>
    <div class="card-body">
        <p>Card content goes here</p>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

**Glass Morphism Card:**

```html
<div class="card card-glass">
    <!-- content -->
</div>
```

**Gradient Card:**

```html
<div class="card card-gradient">
    <!-- content -->
</div>
```

**Stat Card (with counter animation):**

```html
<div class="card card-stat">
    <div class="stat-icon">📈</div>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-value" data-counter="50000">0</div>
    <div class="stat-change">+23% from last month</div>
</div>
```

---

### 3. Forms

**Input Field:**

```html
<!-- Basic Input -->
<div class="form-group">
    <label class="form-label" for="email">Email Address</label>
    <input type="email" id="email" class="form-control" placeholder="your@email.com">
</div>

<!-- With Floating Label -->
<div class="form-group">
    <input type="text" class="form-control" id="name" placeholder="">
    <label class="form-label" for="name">Full Name</label>
</div>

<!-- Password with Toggle -->
<div class="form-group">
    <label class="form-label" for="password">Password</label>
    <div class="password-group">
        <input type="password" id="password" class="form-control" placeholder="Enter password">
        <button type="button" class="password-toggle">👁️</button>
    </div>
</div>

<!-- Textarea -->
<div class="form-group">
    <label class="form-label" for="message">Message</label>
    <textarea class="form-control" id="message" rows="4" placeholder="Your message..."></textarea>
</div>

<!-- Select -->
<div class="form-group">
    <label class="form-label" for="department">Department</label>
    <select class="form-control" id="department">
        <option value="">Select a department</option>
        <option value="cs">Computer Science</option>
        <option value="ec">Electronics</option>
        <option value="me">Mechanical</option>
    </select>
</div>

<!-- Checkbox -->
<div class="form-group">
    <input type="checkbox" id="agree" class="form-checkbox">
    <label for="agree" class="form-label">I agree to the terms</label>
</div>

<!-- Radio Button -->
<div class="form-group">
    <input type="radio" id="male" name="gender" class="form-radio">
    <label for="male" class="form-label">Male</label>
    
    <input type="radio" id="female" name="gender" class="form-radio">
    <label for="female" class="form-label">Female</label>
</div>
```

**Form with Validation:**

```html
<form class="form" id="my-form">
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" required>
        <span class="form-error">Please enter a valid email</span>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block">Submit</button>
</form>

<script>
    document.getElementById('my-form').addEventListener('submit', (e) => {
        e.preventDefault();
        if (e.target.checkValidity()) {
            new Toast('Form submitted successfully!', 'success');
        } else {
            new Toast('Please fill in all required fields', 'error');
        }
    });
</script>
```

---

### 4. Tables

**Basic Table:**

```html
<div style="overflow-x: auto;">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Rajesh Kumar</td>
                <td>rajesh@example.com</td>
                <td>Computer Science</td>
                <td><span class="badge badge-success">Active</span></td>
                <td><button class="btn btn-sm btn-ghost">Edit</button></td>
            </tr>
            <tr>
                <td>Priya Singh</td>
                <td>priya@example.com</td>
                <td>Electronics</td>
                <td><span class="badge badge-warning">Pending</span></td>
                <td><button class="btn btn-sm btn-ghost">Edit</button></td>
            </tr>
        </tbody>
    </table>
</div>
```

**Table Features:**
- Striped rows (automatic)
- Hover effect (automatic)
- Responsive on mobile
- Works with badges for status

---

### 5. Badges

**Available Types:**

```html
<!-- Default -->
<span class="badge">Default</span>

<!-- Primary -->
<span class="badge badge-primary">Primary</span>

<!-- Secondary -->
<span class="badge badge-secondary">Secondary</span>

<!-- Success (Green) -->
<span class="badge badge-success">Success</span>

<!-- Warning (Yellow) -->
<span class="badge badge-warning">Warning</span>

<!-- Danger (Red) -->
<span class="badge badge-danger">Danger</span>

<!-- Info (Blue) -->
<span class="badge badge-info">Info</span>
```

**Usage in Tables/Lists:**

```html
<td>
    <span class="badge badge-success">Accepted</span>
</td>
```

---

### 6. Alerts

**Alert Display:**

```html
<!-- Success Alert -->
<div class="alert alert-success">
    ✓ Your application was submitted successfully!
</div>

<!-- Error Alert -->
<div class="alert alert-error">
    ✗ An error occurred. Please try again.
</div>

<!-- Warning Alert -->
<div class="alert alert-warning">
    ⚠ Please review your information before submitting.
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    ℹ New drives are available for applications.
</div>
```

---

### 7. Utility Classes

**Spacing:**

```html
<!-- Margin -->
<div class="mt-1">Margin top small</div>
<div class="mt-2">Margin top medium</div>
<div class="mt-3">Margin top large</div>

<!-- Padding -->
<div class="p-2">Padding all sides</div>
<div class="px-3">Padding horizontal</div>
<div class="py-1">Padding vertical</div>
```

**Flexbox:**

```html
<div class="flex items-center justify-between gap-2">
    <span>Left content</span>
    <span>Right content</span>
</div>

<div class="flex flex-col gap-1">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

**Grid:**

```html
<!-- 2 Column Grid -->
<div class="grid-2">
    <div class="card">Card 1</div>
    <div class="card">Card 2</div>
</div>

<!-- 3 Column Grid -->
<div class="grid-3">
    <div class="card">Card 1</div>
    <div class="card">Card 2</div>
    <div class="card">Card 3</div>
</div>

<!-- 4 Column Grid -->
<div class="grid-4">
    <!-- 4 cards -->
</div>

<!-- Auto-fit (responsive) -->
<div class="grid-auto">
    <!-- Cards will arrange based on available space -->
</div>
```

**Text:**

```html
<p class="text-center">Centered text</p>
<p class="text-right">Right aligned text</p>
<p class="text-primary">Primary color text</p>
<p class="text-secondary">Secondary color text</p>
<p class="text-xs">Extra small text</p>
<p class="text-lg">Large text</p>
<p class="font-bold">Bold text</p>
<p class="font-light">Light text</p>
```

---

## JavaScript Utilities

### Toast Notifications

```javascript
// Success message
new Toast('Operation completed!', 'success');

// Error message
new Toast('An error occurred', 'error');

// Warning message
new Toast('Please review your changes', 'warning');

// Info message
new Toast('New updates available', 'info');

// Auto-dismiss duration (default: 3000ms)
new Toast('Message', 'success', 4000);
```

### Modal Dialogs

```javascript
// Simple modal
new Modal(
    'Confirm Action',
    'Are you sure you want to proceed?',
    {
        primaryBtn: 'Yes',
        secondaryBtn: 'Cancel',
        onPrimary: () => {
            console.log('User clicked Yes');
        },
        onSecondary: () => {
            console.log('User clicked Cancel');
        }
    }
).show();

// Modal with custom content
const modal = new Modal('Title', 'Your custom HTML content');
modal.show();
```

### Counter Animations

```javascript
// Auto-animate counters (add to stat cards)
const counter = new CounterAnimation('target-element-id', 1000, 3000);
// Counts from 0 to 1000 over 3 seconds

// For multiple counters
document.querySelectorAll('[data-counter]').forEach(element => {
    const target = parseInt(element.dataset.counter);
    new CounterAnimation(element.id, target, 3000);
});
```

### Ripple Effect

```javascript
// Automatically applied to all .btn elements
// For custom elements:
const rippleBtn = new RippleButton(document.getElementById('my-button'));
```

### Animations

```javascript
// Shake element (for errors)
shakeElement(document.getElementById('form'));

// Format numbers
formatNumber(1000000); // Returns "1,000,000"

// Storage API
StorageAPI.set('key', 'value');
const value = StorageAPI.get('key');
StorageAPI.remove('key');
StorageAPI.clear();
```

---

## Theme System

### How It Works

The theme system automatically handles dark/light mode:

1. **Detection:**
   - Checks localStorage for saved preference
   - Falls back to system preference (OS dark mode)
   - Defaults to dark mode

2. **Switching:**
   - User clicks theme toggle button
   - Theme preference saved to localStorage
   - `data-theme` attribute updated on `<html>`
   - All colors update automatically via CSS variables

### Adding Theme Toggle

Add this to your navigation/header:

```html
<button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">
    🌙
</button>

<script>
    // Setup theme toggle
    const themeManager = new ThemeManager();
    themeManager.init();
    themeManager.setupToggleListener();
</script>
```

### CSS Variable Reference

All colors are defined as CSS variables for easy theming:

```css
/* Primary Color */
--primary-50: #f3e8ff;
--primary-100: #e9d5ff;
--primary-400: #c084fc;
--primary-500: #a855f7;
--primary-600: #9333ea;

/* Secondary Color (Blue) */
--secondary-400: #60a5fa;
--secondary-500: #3b82f6;

/* Accent Colors */
--accent-cyan: #00d4ff;
--accent-pink: #ff006e;
--accent-green: #00ff88;
--accent-yellow: #ffb703;

/* Semantic Colors */
--success: #00ff88;
--warning: #ffb703;
--danger: #ff006e;
--info: #0080ff;

/* Neutral Colors */
--text-primary: #ffffff;
--text-secondary: #a0aec0;
--text-tertiary: #718096;

--bg-primary: #0f0f23;
--bg-surface: #1a1a3c;
--bg-tertiary: #2d2d5f;
--bg-quaternary: #3d3d7f;

/* In light mode, these automatically invert */
```

---

## Migration Checklist

### Phase 1: Foundation (Week 1)
- [ ] Add `modern-design.css` link to `header.php`
- [ ] Add `theme-manager.js` and `animations.js` to `footer.php`
- [ ] Add `data-theme="dark"` to `<body>` tag
- [ ] Add theme toggle button to navbar
- [ ] Test: Verify theme toggle works

### Phase 2: Login Pages (Week 1)
- [ ] Update `/student/login.php` using `login-template.html` as reference
- [ ] Update `/staff/login.php`
- [ ] Update `/admin/login.php`
- [ ] Test: Verify all login pages work and look premium

### Phase 3: Dashboard Modernization (Week 2)
- [ ] Update `/student/dashboard.php` using `dashboard-template.html` as reference
- [ ] Update `/staff/dashboard.php`
- [ ] Update `/admin/dashboard.php`
- [ ] Add stat card counter animations
- [ ] Test: Verify dashboards display correctly, counters animate

### Phase 4: Component Updates (Week 2)
- [ ] Replace all buttons with `.btn` classes
- [ ] Update all tables with `.table` class
- [ ] Replace badges with `.badge` classes
- [ ] Update forms with `.form-control` class

### Phase 5: Page-by-Page (Week 3)
- [ ] Update `/student/drives.php`
- [ ] Update `/student/results.php`
- [ ] Update `/student/profile.php`
- [ ] Update `/student/notifications.php`
- [ ] Update staff pages similarly
- [ ] Update admin pages similarly

### Phase 6: Testing & Polish (Week 3)
- [ ] Test all pages on desktop (1920px, 1280px)
- [ ] Test all pages on tablet (768px)
- [ ] Test all pages on mobile (480px)
- [ ] Test dark/light mode on all pages
- [ ] Test all buttons, forms, and interactions
- [ ] Verify animations performance (60fps)

---

## Common Patterns

### Button + Toast

```html
<button class="btn btn-primary" id="save-btn">Save Changes</button>

<script>
    document.getElementById('save-btn').addEventListener('click', async (e) => {
        e.target.disabled = true;
        
        try {
            const response = await fetch('/api/save', { method: 'POST' });
            new Toast('Changes saved successfully!', 'success');
        } catch (error) {
            new Toast('Failed to save changes', 'error');
        } finally {
            e.target.disabled = false;
        }
    });
</script>
```

### Form with Validation

```html
<form id="application-form" class="form">
    <div class="form-group">
        <label class="form-label">Company</label>
        <select class="form-control" name="company" required>
            <option value="">Select a company</option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block">Submit Application</button>
</form>

<script>
    document.getElementById('application-form').addEventListener('submit', (e) => {
        e.preventDefault();
        
        if (!e.target.checkValidity()) {
            new Toast('Please fill all required fields', 'error');
            return;
        }
        
        // Submit form
        new Toast('Application submitted!', 'success');
    });
</script>
```

### Stat Cards with Counters

```html
<div class="grid-auto">
    <div class="card">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Total Students</div>
        <div class="stat-value" id="total-students" data-counter="2847">0</div>
    </div>
    
    <div class="card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Placed</div>
        <div class="stat-value" id="placed-count" data-counter="1234">0</div>
    </div>
</div>

<script>
    // Auto-trigger when element comes into view
    document.querySelectorAll('[data-counter]').forEach(el => {
        new CounterAnimation(el.id, parseInt(el.dataset.counter), 2000);
    });
</script>
```

### Delete Confirmation Modal

```javascript
function deleteItem(id) {
    new Modal(
        'Delete Confirmation',
        'Are you sure you want to delete this item? This action cannot be undone.',
        {
            primaryBtn: 'Delete',
            secondaryBtn: 'Cancel',
            onPrimary: () => {
                // Make delete request
                fetch(`/api/delete/${id}`, { method: 'DELETE' })
                    .then(() => new Toast('Deleted successfully', 'success'))
                    .catch(() => new Toast('Delete failed', 'error'));
            }
        }
    ).show();
}
```

---

## Browser Support

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support (14+)
- Mobile browsers: ✅ Full support

**Requirements:**
- CSS Grid & Flexbox
- CSS Custom Properties (Variables)
- Backdrop Filter (for glass effects)
- ES6+ JavaScript

---

## Performance Tips

1. **CSS Optimization:**
   - All animations use GPU acceleration (transform, opacity)
   - No layout thrashing
   - Smooth 60fps animations

2. **JavaScript:**
   - Micro-interactions debounced
   - IntersectionObserver for scroll animations
   - Event delegation for performance

3. **Images:**
   - Use SVG or Unicode symbols instead of PNG
   - Optimize gradients (CSS-based)

4. **Responsive:**
   - Mobile-first approach
   - Minimal CSS media queries
   - Fluid typography

---

## Troubleshooting

### Theme not switching?
- Ensure `theme-manager.js` is loaded
- Check browser console for errors
- Verify `data-theme` attribute exists on `<html>`

### Animations not working?
- Check if `animations.js` is loaded
- Ensure elements have correct classes/IDs
- Verify no CSS conflicts with existing styles

### Colors not showing?
- Make sure `modern-design.css` is linked first
- Check for CSS specificity conflicts
- Clear browser cache

### Mobile layout broken?
- Test with Chrome DevTools responsive mode
- Check media query breakpoints (480px, 768px)
- Verify no fixed widths on elements

---

## File References

| File | Purpose | Size |
|------|---------|------|
| `css/modern-design.css` | Complete design system | ~1050 lines |
| `js/theme-manager.js` | Theme switching | ~60 lines |
| `js/animations.js` | Animations & utilities | ~400 lines |
| `login-template.html` | Premium login demo | ~450 lines |
| `dashboard-template.html` | Dashboard demo | ~550 lines |

---

## Next Steps

1. Review the template files to understand structure
2. Follow Phase 1 of the migration checklist
3. Test theme toggle thoroughly
4. Gradually migrate pages following the phases
5. Gather feedback and iterate

---

**Questions or Need Help?**
- Check template files for reference implementations
- Review CSS variables for available colors/sizes
- Test JavaScript utilities in browser console


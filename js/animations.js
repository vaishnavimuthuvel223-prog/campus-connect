/* ============================================
   ANIMATIONS & MICRO-INTERACTIONS
   ============================================ */

// Counter animation for stat cards
class CounterAnimation {
  constructor(element, target, duration = 2000) {
    this.element = element;
    this.target = parseInt(target);
    this.duration = duration;
    this.current = 0;
    this.isRunning = false;
  }
  
  start() {
    if (this.isRunning) return;
    this.isRunning = true;
    
    const startTime = Date.now();
    const increment = this.target / (this.duration / 16);
    
    const animate = () => {
      const elapsed = Date.now() - startTime;
      this.current = Math.min(Math.floor(elapsed / this.duration * this.target), this.target);
      this.element.textContent = this.current.toLocaleString();
      
      if (this.current < this.target) {
        requestAnimationFrame(animate);
      } else {
        this.isRunning = false;
      }
    };
    
    requestAnimationFrame(animate);
  }
}

// Intersection Observer for animations
const AnimationObserver = {
  init() {
    const options = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-slideUp');
          
          // Start counter if it's a stat value
          if (entry.target.classList.contains('stat-value')) {
            const counter = new CounterAnimation(
              entry.target,
              entry.target.textContent,
              1500
            );
            counter.start();
          }
          
          observer.unobserve(entry.target);
        }
      });
    }, options);
    
    document.querySelectorAll('[data-animate]').forEach(el => {
      observer.observe(el);
    });
  }
};

// Ripple effect on buttons
class RippleButton {
  constructor(button) {
    this.button = button;
    this.init();
  }
  
  init() {
    this.button.addEventListener('click', (e) => this.createRipple(e));
  }
  
  createRipple(e) {
    const rect = this.button.getBoundingClientRect();
    const ripple = document.createElement('span');
    
    ripple.style.left = (e.clientX - rect.left) + 'px';
    ripple.style.top = (e.clientY - rect.top) + 'px';
    ripple.classList.add('ripple');
    
    const rippleSize = Math.max(rect.width, rect.height);
    ripple.style.width = rippleSize + 'px';
    ripple.style.height = rippleSize + 'px';
    
    this.button.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
  }
}

// Toast Notification
class Toast {
  constructor(message, type = 'info', duration = 3000) {
    this.message = message;
    this.type = type; // 'success', 'error', 'warning', 'info'
    this.duration = duration;
    this.element = null;
    this.show();
  }
  
  show() {
    this.element = document.createElement('div');
    this.element.className = `toast toast-${this.type}`;
    this.element.textContent = this.message;
    this.element.style.cssText = `
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      padding: 1rem 1.5rem;
      background: var(--bg-tertiary);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      color: var(--text-primary);
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      animation: slideUp 0.3s ease-out;
      z-index: 600;
      max-width: 300px;
    `;
    
    // Add type-specific styles
    if (this.type === 'success') {
      this.element.style.borderLeftColor = '#00ff88';
      this.element.style.borderLeftWidth = '4px';
    } else if (this.type === 'error') {
      this.element.style.borderLeftColor = '#ff006e';
      this.element.style.borderLeftWidth = '4px';
    } else if (this.type === 'warning') {
      this.element.style.borderLeftColor = '#ffb703';
      this.element.style.borderLeftWidth = '4px';
    } else if (this.type === 'info') {
      this.element.style.borderLeftColor = '#00d4ff';
      this.element.style.borderLeftWidth = '4px';
    }
    
    document.body.appendChild(this.element);
    
    setTimeout(() => this.hide(), this.duration);
  }
  
  hide() {
    this.element.style.animation = 'slideUp 0.3s ease-out reverse';
    setTimeout(() => this.element.remove(), 300);
  }
}

// Modal Dialog
class Modal {
  constructor(title, content, options = {}) {
    this.title = title;
    this.content = content;
    this.options = {
      primaryBtn: 'Confirm',
      secondaryBtn: 'Cancel',
      onPrimary: () => {},
      onSecondary: () => {},
      ...options
    };
    this.element = null;
    this.backdrop = null;
  }
  
  show() {
    // Backdrop
    this.backdrop = document.createElement('div');
    this.backdrop.className = 'modal-backdrop';
    this.backdrop.style.cssText = `
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      z-index: 500;
      animation: fadeIn 0.3s ease-out;
    `;
    this.backdrop.addEventListener('click', () => this.hide());
    
    // Modal
    this.element = document.createElement('div');
    this.element.className = 'modal';
    this.element.style.cssText = `
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      max-width: 500px;
      width: 90%;
      background: var(--bg-secondary);
      border-radius: var(--radius-xl);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      z-index: 510;
      animation: scaleIn 0.3s ease-out;
    `;
    
    this.element.innerHTML = `
      <div style="padding: 2rem;">
        <div style="margin-bottom: 1.5rem;">
          <h2 style="margin-bottom: 0.5rem; color: var(--text-primary);">${this.title}</h2>
          <p style="color: var(--text-secondary);">${this.content}</p>
        </div>
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
          <button class="btn btn-ghost" data-action="secondary">${this.options.secondaryBtn}</button>
          <button class="btn btn-primary" data-action="primary">${this.options.primaryBtn}</button>
        </div>
      </div>
    `;
    
    const primaryBtn = this.element.querySelector('[data-action="primary"]');
    const secondaryBtn = this.element.querySelector('[data-action="secondary"]');
    
    primaryBtn.addEventListener('click', () => {
      this.options.onPrimary();
      this.hide();
    });
    
    secondaryBtn.addEventListener('click', () => {
      this.options.onSecondary();
      this.hide();
    });
    
    document.body.appendChild(this.backdrop);
    document.body.appendChild(this.element);
  }
  
  hide() {
    this.element.style.animation = 'scaleIn 0.3s ease-out reverse';
    this.backdrop.style.animation = 'fadeIn 0.3s ease-out reverse';
    
    setTimeout(() => {
      this.element.remove();
      this.backdrop.remove();
    }, 300);
  }
}

// Shake animation for errors
function shakeElement(element) {
  element.classList.add('animate-shake');
  setTimeout(() => element.classList.remove('animate-shake'), 500);
}

// Smooth number formatting
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Add to LocalStorage
const StorageAPI = {
  set(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  },
  
  get(key) {
    const item = localStorage.getItem(key);
    return item ? JSON.parse(item) : null;
  },
  
  remove(key) {
    localStorage.removeItem(key);
  },
  
  clear() {
    localStorage.clear();
  }
};

// Initialize all animations
document.addEventListener('DOMContentLoaded', () => {
  // Start intersection observer for animations
  AnimationObserver.init();
  
  // Initialize ripple buttons
  document.querySelectorAll('.btn').forEach(btn => {
    if (!btn.classList.contains('no-ripple')) {
      new RippleButton(btn);
    }
  });
});

// Global exports
window.Toast = Toast;
window.Modal = Modal;
window.CounterAnimation = CounterAnimation;
window.shakeElement = shakeElement;
window.formatNumber = formatNumber;
window.StorageAPI = StorageAPI;

// Card tilt (3D transform) effect for .tilt-card
(function(){
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tilt-card').forEach(function(card) {
      card.addEventListener('mousemove', function(e) {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const xc = rect.width/2, yc = rect.height/2;
        const dx = (x-xc)/xc, dy = (y-yc)/yc;
        card.style.transform = `rotateY(${dx*10}deg) rotateX(${-dy*10}deg)`;
      });
      card.addEventListener('mouseleave', function() {
        card.style.transform = '';
      });
    });
  });
})();

// Parallax effect for .login-bg-parallax
(function(){
  document.addEventListener('mousemove', function(e) {
    const bg = document.querySelector('.login-bg-parallax');
    if (!bg) return;
    const x = e.clientX/window.innerWidth, y = e.clientY/window.innerHeight;
    bg.style.backgroundPosition = `${50+10*(x-0.5)}% ${50+10*(y-0.5)}%`;
  });
})();

// Lottie icon loader for .lottie-icon
(function(){
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.lottie-icon[data-src]').forEach(function(el){
      LottieLoader.load(el.getAttribute('data-src'), el, {autoplay:true, loop:true});
    });
  });
})();

// Skeleton loader utility
window.showSkeleton = function(selector, show=true) {
  document.querySelectorAll(selector).forEach(function(el){
    if (show) el.classList.add('skeleton');
    else el.classList.remove('skeleton');
  });
};

// Lottie loader for animated icons and loading states
// Usage: LottieLoader.load('path/to/animation.json', targetElement)
class LottieLoader {
  static load(src, target, options = {}) {
    if (!window.lottie) {
      const script = document.createElement('script');
      script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js';
      script.onload = () => LottieLoader._init(src, target, options);
      document.head.appendChild(script);
    } else {
      LottieLoader._init(src, target, options);
    }
  }
  static _init(src, target, options) {
    window.lottie.loadAnimation({
      container: typeof target === 'string' ? document.querySelector(target) : target,
      renderer: 'svg',
      loop: options.loop ?? true,
      autoplay: options.autoplay ?? true,
      path: src,
      ...options
    });
  }
}
window.LottieLoader = LottieLoader;
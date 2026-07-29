// GSAP loader and basic animation triggers for modern UI
// Usage: Add 'gsap-animate' and data-gsap attributes to elements
(function(){
  function loadGSAP(cb) {
    if (window.gsap) return cb();
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js';
    s.onload = cb;
    document.head.appendChild(s);
  }
  function animateAll() {
    document.querySelectorAll('.gsap-animate').forEach(function(el) {
      var anim = el.getAttribute('data-gsap') || 'fade-up';
      if (anim === 'fade-up') {
        gsap.fromTo(el, {opacity:0, y:40}, {opacity:1, y:0, duration:0.8, ease:'power2.out', scrollTrigger:{trigger:el, start:'top 90%'}});
      } else if (anim === 'fade-in') {
        gsap.fromTo(el, {opacity:0}, {opacity:1, duration:0.7, ease:'power1.out', scrollTrigger:{trigger:el, start:'top 95%'}});
      } else if (anim === 'scale-in') {
        gsap.fromTo(el, {scale:0.85, opacity:0}, {scale:1, opacity:1, duration:0.7, ease:'back.out(1.7)', scrollTrigger:{trigger:el, start:'top 90%'}});
      } else if (anim === 'slide-left') {
        gsap.fromTo(el, {x:60, opacity:0}, {x:0, opacity:1, duration:0.8, ease:'power2.out', scrollTrigger:{trigger:el, start:'top 90%'}});
      } else if (anim === 'slide-right') {
        gsap.fromTo(el, {x:-60, opacity:0}, {x:0, opacity:1, duration:0.8, ease:'power2.out', scrollTrigger:{trigger:el, start:'top 90%'}});
      }
    });
  }
  function loadScrollTrigger(cb) {
    if (window.ScrollTrigger) return cb();
    var s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js';
    s.onload = cb;
    document.head.appendChild(s);
  }
  document.addEventListener('DOMContentLoaded', function(){
    loadGSAP(function(){
      loadScrollTrigger(function(){
        gsap.registerPlugin(ScrollTrigger);
        animateAll();
      });
    });
  });
})();

(function () {
  const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateFeatures() {
    const items = document.querySelectorAll('.sfc-feature-item');

    if (!items.length) {
      return;
    }

    if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
      items.forEach((item) => {
        item.classList.add('is-visible');
        item.style.removeProperty('--sfc-delay');
      });
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.25 }
    );

    items.forEach((item, index) => {
      item.style.setProperty('--sfc-delay', `${index * 60}ms`);
      observer.observe(item);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', animateFeatures);
  } else {
    animateFeatures();
  }
})();

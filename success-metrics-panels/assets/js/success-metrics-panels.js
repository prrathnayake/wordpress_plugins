(function () {
  const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initReveal(scope) {
    const cards = scope.querySelectorAll('[data-animate]');

    if (!cards.length) {
      return;
    }

    if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
      cards.forEach((node) => {
        node.classList.add('is-visible');
        node.style.removeProperty('transition-delay');
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

    cards.forEach((node) => {
      const delay = node.style.getPropertyValue('--smp-delay') || '0ms';
      node.style.transitionDelay = delay;
      observer.observe(node);
    });
  }

  function initAll() {
    document.querySelectorAll('.smp-wrap').forEach((wrap) => {
      initReveal(wrap);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();

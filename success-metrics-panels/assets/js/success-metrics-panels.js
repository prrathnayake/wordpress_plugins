(function () {
  function initReveal(scope) {
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

    scope.querySelectorAll('[data-animate]').forEach((node) => {
      const delay = node.style.getPropertyValue('--smp-delay') || '0ms';
      node.style.transitionDelay = delay;
      observer.observe(node);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.smp-wrap').forEach((wrap) => {
      initReveal(wrap);
    });
  });
})();

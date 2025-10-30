(function () {
  function animateFeatures() {
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

    document.querySelectorAll('.sfc-feature-item').forEach((item, index) => {
      item.style.setProperty('--sfc-delay', `${index * 60}ms`);
      observer.observe(item);
    });
  }

  document.addEventListener('DOMContentLoaded', animateFeatures);
})();

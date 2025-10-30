(function () {
  document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.2 }
    );

    document.querySelectorAll('.ptp-plan').forEach((plan, index) => {
      plan.style.setProperty('--ptp-delay', `${index * 70}ms`);
      observer.observe(plan);
    });
  });
})();

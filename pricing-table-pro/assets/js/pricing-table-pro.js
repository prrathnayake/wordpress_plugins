(function () {
  const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function revealPlans() {
    const plans = document.querySelectorAll('.ptp-plan');

    if (!plans.length) {
      return;
    }

    if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
      plans.forEach((plan) => {
        plan.classList.add('is-visible');
        plan.style.removeProperty('--ptp-delay');
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
      { threshold: 0.2 }
    );

    plans.forEach((plan, index) => {
      plan.style.setProperty('--ptp-delay', `${index * 70}ms`);
      observer.observe(plan);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', revealPlans);
  } else {
    revealPlans();
  }
})();

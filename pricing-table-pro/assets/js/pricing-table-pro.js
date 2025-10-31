(function () {
  const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function applyTheme(target, theme) {
    if (!target) {
      return;
    }

    const prefixes = (target.dataset.themePrefixes || '')
      .split(',')
      .map((prefix) => prefix.trim())
      .filter(Boolean);

    const classes = Array.from(target.classList);

    prefixes.forEach((prefix) => {
      classes
        .filter((className) => className.startsWith(prefix))
        .forEach((className) => target.classList.remove(className));

      target.classList.add(`${prefix}${theme}`);
    });

    target.setAttribute('data-mkt-theme', theme);
  }

  function initThemeSwitchers() {
    const switchers = document.querySelectorAll('.mkt-theme-switcher select');

    switchers.forEach((select) => {
      const wrapper = select.closest('.mkt-theme-switcher');
      if (!wrapper) {
        return;
      }

      const targetId = wrapper.getAttribute('data-target');
      if (!targetId) {
        return;
      }

      const target = document.getElementById(targetId);
      if (!target) {
        return;
      }

      const currentTheme = target.getAttribute('data-mkt-theme');
      if (currentTheme) {
        select.value = currentTheme;
      }

      select.addEventListener('change', (event) => {
        applyTheme(target, event.target.value);
      });
    });
  }

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

  function init() {
    revealPlans();
    initThemeSwitchers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

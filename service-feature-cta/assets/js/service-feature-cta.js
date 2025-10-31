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

  function init() {
    animateFeatures();
    initThemeSwitchers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

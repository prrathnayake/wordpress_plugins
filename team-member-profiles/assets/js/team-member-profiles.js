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

    const classList = Array.from(target.classList);

    prefixes.forEach((prefix) => {
      classList
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

  function initProfiles() {
    const cards = document.querySelectorAll('.tmp-profile-card');

    if (!cards.length) {
      return;
    }

    if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
      cards.forEach((card) => {
        card.classList.add('is-visible');
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
      {
        threshold: 0.35,
      }
    );

    cards.forEach((card) => {
      observer.observe(card);
    });
  }

  function init() {
    initProfiles();
    initThemeSwitchers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

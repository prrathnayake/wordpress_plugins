(function () {
  const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function pad(value) {
    return String(value).padStart(2, '0');
  }

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

  function initRevealAnimations(root) {
    const units = root.querySelectorAll('.ect-countdown__unit');

    if (!units.length) {
      return;
    }

    if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
      units.forEach((unit) => {
        unit.classList.add('is-visible');
        unit.style.removeProperty('--ect-delay');
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
      { threshold: 0.3 }
    );

    units.forEach((unit, index) => {
      unit.style.setProperty('--ect-delay', `${index * 70}ms`);
      observer.observe(unit);
    });
  }

  function renderCountdown(container) {
    const dateString = container.dataset.targetDate;
    if (!dateString) {
      return;
    }

    const target = new Date(dateString);
    const units = {
      days: container.querySelector('[data-unit="days"] .ect-countdown__value'),
      hours: container.querySelector('[data-unit="hours"] .ect-countdown__value'),
      minutes: container.querySelector('[data-unit="minutes"] .ect-countdown__value'),
      seconds: container.querySelector('[data-unit="seconds"] .ect-countdown__value'),
    };

    if (Number.isNaN(target.getTime())) {
      Object.values(units).forEach((node) => {
        if (node) {
          node.textContent = '--';
        }
      });
      return;
    }

    function tick() {
      const now = new Date();
      const diff = Math.max(0, target.getTime() - now.getTime());
      const totalSeconds = Math.floor(diff / 1000);

      const days = Math.floor(totalSeconds / (60 * 60 * 24));
      const hours = Math.floor((totalSeconds / (60 * 60)) % 24);
      const minutes = Math.floor((totalSeconds / 60) % 60);
      const seconds = totalSeconds % 60;

      units.days.textContent = pad(days);
      units.hours.textContent = pad(hours);
      units.minutes.textContent = pad(minutes);
      units.seconds.textContent = pad(seconds);

      if (diff <= 0) {
        container.classList.add('ect-countdown--completed');
        clearInterval(interval);
      }
    }

    tick();
    const interval = setInterval(tick, prefersReducedMotion ? 2000 : 1000);
  }

  function init() {
    document.querySelectorAll('.ect-countdown').forEach((container) => {
      renderCountdown(container);
      initRevealAnimations(container);
    });
    initThemeSwitchers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

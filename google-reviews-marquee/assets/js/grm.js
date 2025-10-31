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

  function initSwiper(el) {
    const speedMap = { slow: 10000, normal: 7000, fast: 4500 };
    const speedKey = (el.getAttribute('data-speed') || 'normal').toLowerCase();
    const autoplay =
      !prefersReducedMotion && (el.getAttribute('data-autoplay') || 'true') === 'true';

    const swiper = new Swiper(el, {
      slidesPerView: 'auto',
      spaceBetween: 12,
      freeMode: true,
      loop: true,
      centeredSlides: false,
      allowTouchMove: true,
      autoplay: autoplay ? { delay: 0, disableOnInteraction: false } : false,
      speed: prefersReducedMotion ? speedMap.normal : speedMap[speedKey] || speedMap.normal,
    });

    if (autoplay) {
      el.addEventListener('mouseenter', function () {
        swiper.autoplay.stop();
      });
      el.addEventListener('mouseleave', function () {
        swiper.autoplay.start();
      });
    }
  }

  function init() {
    document.querySelectorAll('.grm-swiper').forEach(initSwiper);
    initThemeSwitchers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

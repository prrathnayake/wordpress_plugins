(function () {
  function initSwiper(container) {
    const autoplayEnabled = container.dataset.autoplay === 'true';
    const speed = parseInt(container.dataset.speed || '6000', 10);
    const interval = parseInt(container.dataset.interval || '0', 10);

    const options = {
      loop: true,
      spaceBetween: 28,
      slidesPerView: 1.1,
      centeredSlides: true,
      breakpoints: {
        768: { slidesPerView: 2.1 },
        1280: { slidesPerView: 3 }
      },
      navigation: {
        nextEl: container.querySelector('.csc-nav--next'),
        prevEl: container.querySelector('.csc-nav--prev')
      },
      pagination: {
        el: container.querySelector('.csc-pagination'),
        clickable: true
      },
      speed: speed,
      autoplay: autoplayEnabled
        ? {
            delay: interval > 0 ? interval : speed,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
          }
        : false
    };

    // eslint-disable-next-line no-undef
    new Swiper(container, options);
  }

  function initReveal(root) {
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

    root.querySelectorAll('[data-animate]').forEach((node, index) => {
      node.style.setProperty('--csc-delay', `${index * 60}ms`);
      observer.observe(node);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.csc-swiper').forEach((swiperEl) => {
      initSwiper(swiperEl);
      initReveal(swiperEl.closest('.csc-wrap'));
    });
  });
})();

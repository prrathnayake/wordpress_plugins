(function(){
    const prefersReducedMotion =
        typeof window !== 'undefined' &&
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function init(el){
        var speedMap = { slow: 10000, normal: 7000, fast: 4500 };
        var speedKey = (el.getAttribute('data-speed') || 'normal').toLowerCase();
        var autoplay = !prefersReducedMotion && (el.getAttribute('data-autoplay') || 'true') === 'true';
        var swiper = new Swiper(el, {
            slidesPerView: 'auto',
            spaceBetween: 12,
            freeMode: true,
            loop: true,
            centeredSlides: false,
            allowTouchMove: true,
            autoplay: autoplay ? { delay: 0, disableOnInteraction: false } : false,
            speed: prefersReducedMotion ? speedMap.normal : (speedMap[speedKey] || speedMap.normal),
        });
        if (autoplay){
            el.addEventListener('mouseenter', function(){ swiper.autoplay.stop(); });
            el.addEventListener('mouseleave', function(){ swiper.autoplay.start(); });
        }
    }
    function initAll(){ document.querySelectorAll('.grm-swiper').forEach(init); }
    if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', initAll); } else { initAll(); }
})();
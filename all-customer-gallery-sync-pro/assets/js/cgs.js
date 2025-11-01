(function(){
    function applyTheme(target, theme){
        if(!target){ return; }
        var prefixes = (target.getAttribute('data-theme-prefixes') || '')
            .split(',')
            .map(function(prefix){ return prefix.trim(); })
            .filter(function(prefix){ return prefix.length > 0; });
        var classes = Array.prototype.slice.call(target.classList);
        prefixes.forEach(function(prefix){
            classes
                .filter(function(className){ return className.indexOf(prefix) === 0; })
                .forEach(function(className){ target.classList.remove(className); });
            target.classList.add(prefix + theme);
        });
        target.setAttribute('data-mkt-theme', theme);
    }

    function initThemeSwitchers(){
        var switchers = document.querySelectorAll('.mkt-theme-switcher select');
        Array.prototype.forEach.call(switchers, function(select){
            var wrapper = select.closest('.mkt-theme-switcher');
            if(!wrapper){ return; }
            var targetId = wrapper.getAttribute('data-target');
            if(!targetId){ return; }
            var target = document.getElementById(targetId);
            if(!target){ return; }
            var currentTheme = target.getAttribute('data-mkt-theme');
            if(currentTheme){ select.value = currentTheme; }
            select.addEventListener('change', function(event){
                applyTheme(target, event.target.value);
            });
        });
    }

    function ensureLightbox(){
        var lb = document.querySelector('.cgs-lightbox');
        if(lb) return lb;
        lb = document.createElement('div');
        lb.className = 'cgs-lightbox';
        lb.innerHTML = '<div class="cgs-lightbox-inner"><button class="cgs-lightbox-close" aria-label="Close"></button><img class="cgs-lightbox-media" alt=""/></div>';
        document.body.appendChild(lb);
        function close(){ lb.classList.remove('is-open'); }
        lb.addEventListener('click', function(e){ if(e.target === lb) close(); });
        lb.querySelector('.cgs-lightbox-close').addEventListener('click', close);
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
        return lb;
    }
    function openImage(src, alt){
        var lb = ensureLightbox();
        var img = lb.querySelector('img.cgs-lightbox-media');
        img.src = src; img.alt = alt || '';
        lb.classList.add('is-open');
    }
    function initSwiper(el){
        var columns = parseInt(el.getAttribute('data-columns') || '4', 10);
        var count = parseInt(el.getAttribute('data-count') || '0', 10);
        var breakpoints = {};
        try { breakpoints = JSON.parse(el.getAttribute('data-breakpoints') || '{}'); } catch(e){}
        var autoplay = (el.getAttribute('data-autoplay') || 'true') === 'true';
        var delay = parseInt(el.getAttribute('data-delay') || '2500', 10);
        var speed = parseInt(el.getAttribute('data-speed') || '600', 10);

        el.style.setProperty('--cgs-columns', String(columns));
        if (!isNaN(count) && count <= columns) {
            el.classList.add('cgs-static');
        } else {
            // eslint-disable-next-line no-undef
            var swiper = new Swiper(el, {
                loop: false,
                spaceBetween: 24,
                slidesPerView: columns,
                centeredSlides: false,
                navigation: { prevEl: el.querySelector('.cgs-nav-prev'), nextEl: el.querySelector('.cgs-nav-next') },
                pagination: { el: el.querySelector('.cgs-pagination'), clickable: true },
                breakpoints: breakpoints,
                grabCursor: true,
                allowTouchMove: true,
                autoplay: autoplay ? { delay: isNaN(delay) ? 2500 : delay, disableOnInteraction: false } : false,
                speed: isNaN(speed) ? 600 : speed
            });
            if (autoplay) {
                el.addEventListener('mouseenter', function(){ swiper.autoplay.stop(); });
                el.addEventListener('mouseleave', function(){ swiper.autoplay.start(); });
            }
        }

        // Lightbox on click
        el.addEventListener('click', function(e){
            var img = e.target.closest('img.cgs-img');
            if (!img) return;
            var full = img.getAttribute('data-full') || img.currentSrc || img.src;
            var alt = img.getAttribute('alt') || '';
            if (full) openImage(full, alt);
        });
    }
    function init(){
        document.querySelectorAll('.cgs-swiper').forEach(initSwiper);
        initThemeSwitchers();
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

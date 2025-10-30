(function(){
    function ensureLightbox(){
        var lb = document.querySelector('.cis-lightbox');
        if(lb) return lb;
        lb = document.createElement('div');
        lb.className = 'cis-lightbox';
        lb.innerHTML = '<div class="cis-lightbox-inner"><button class="cis-lightbox-close" aria-label="Close"></button><img class="cis-lightbox-media" type="image" alt=""/><iframe class="cis-lightbox-media" type="video" allowfullscreen frameborder="0" style="display:none;"></iframe></div>';
        document.body.appendChild(lb);

        function close(){
            lb.classList.remove('is-open');
            // stop video playback by clearing src
            var iframe = lb.querySelector('iframe.cis-lightbox-media');
            iframe.src = '';
        }
        lb.addEventListener('click', function(e){
            if(e.target === lb) close();
        });
        lb.querySelector('.cis-lightbox-close').addEventListener('click', close);
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape') close();
        });
        return lb;
    }

    function openImage(src, alt){
        var lb = ensureLightbox();
        var img = lb.querySelector('img.cis-lightbox-media');
        var iframe = lb.querySelector('iframe.cis-lightbox-media');
        iframe.style.display = 'none';
        iframe.src = '';
        img.style.display = 'block';
        img.src = src;
        img.alt = alt || '';
        lb.classList.add('is-open');
    }

    function openVideo(src){
        var lb = ensureLightbox();
        var img = lb.querySelector('img.cis-lightbox-media');
        var iframe = lb.querySelector('iframe.cis-lightbox-media');
        img.style.display = 'none';
        img.src = '';
        iframe.style.display = 'block';
        iframe.src = src;
        lb.classList.add('is-open');
    }

    function initSwiper(el){
        var type = el.getAttribute('data-type') || 'images';
        var columns = parseInt(el.getAttribute('data-columns') || '3', 10);
        var count = parseInt(el.getAttribute('data-count') || '0', 10);
        var spaceBetween = parseInt(el.getAttribute('data-space-between') || '16', 10);
        var breakpoints = {};
        try {
            breakpoints = JSON.parse(el.getAttribute('data-breakpoints') || '{}');
        } catch(e){}

        // If images slider and not more than columns, don't init Swiper. Use static grid.
        if (type === 'images') {
            el.style.setProperty('--cis-columns', String(columns));
            if (!isNaN(count) && count <= columns) {
                el.classList.add('cis-static');
            }
        }

        var navPrev = el.querySelector('.cis-nav-prev');
        var navNext = el.querySelector('.cis-nav-next');
        var pagination = el.querySelector('.cis-pagination');

        var isVideo = (type === 'video');

        var shouldInit = !(type === 'images' && !isNaN(count) && count <= columns);
        if (shouldInit) {
            var config = {
                loop: false,
                spaceBetween: isVideo ? spaceBetween : 24,
                slidesPerView: isVideo ? 1 : columns,
                centeredSlides: !isVideo,
                slidesOffsetBefore: isVideo ? 0 : 12,
                slidesOffsetAfter: isVideo ? 0 : 12,
                navigation: (navPrev && navNext) ? { prevEl: navPrev, nextEl: navNext } : false,
                pagination: pagination ? { el: pagination, clickable: true } : false,
                breakpoints: breakpoints,
                grabCursor: true,
                allowTouchMove: true
            };
            if (!isVideo) {
                config.effect = 'coverflow';
                config.coverflowEffect = { rotate: 0, stretch: 0, depth: 80, modifier: 1, slideShadows: false };
            }
            // eslint-disable-next-line no-undef
            new Swiper(el, config);
        }

        // Lightbox click handlers
        if (type === 'images') {
            el.addEventListener('click', function(e){
                var img = e.target.closest('img.cis-img');
                if (!img) return;
                var full = img.getAttribute('data-full') || img.currentSrc || img.src;
                var alt  = img.getAttribute('alt') || '';
                if (full) openImage(full, alt);
            });
        } else if (type === 'video') {
            el.addEventListener('click', function(e){
                var wrapper = e.target.closest('.cis-video-embed');
                if (!wrapper) return;
                var iframe = wrapper.querySelector('iframe');
                if (iframe && iframe.src) {
                    var src = iframe.src;
                    if (src.indexOf('autoplay=') === -1) {
                        src += (src.indexOf('?')>-1 ? '&' : '?') + 'autoplay=1';
                    }
                    openVideo(src);
                }
            });
        }
    }

    function initAll(){
        var sliders = document.querySelectorAll('.cis-swiper');
        sliders.forEach(initSwiper);
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();

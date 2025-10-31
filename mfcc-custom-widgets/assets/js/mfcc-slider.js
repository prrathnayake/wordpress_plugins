/**
 * Simple slider script for MFCC image sliders.
 * Supports horizontal and vertical orientations.
 */
jQuery(document).ready(function ($) {
  $(".mfcc-image-slider").each(function () {
    var $slider = $(this);
    var $inner = $slider.find('.mfcc-slider-inner');
    var $slides = $inner.find('.mfcc-slide');
    var orientation = $slider.hasClass('orientation-vertical') ? 'vertical' : 'horizontal';
    var currentIndex = 0;
    var slideCount = $slides.length;
    var autoplay = $slider.data('autoplay') === true || $slider.data('autoplay') === 'true';
    var interval = parseInt($slider.data('interval'), 10) || 5000;

    function goTo(index) {
      if (index < 0) {
        index = slideCount - 1;
      }
      if (index >= slideCount) {
        index = 0;
      }
      currentIndex = index;
      var translateValue;
      if (orientation === 'vertical') {
        translateValue = 'translateY(' + (-currentIndex * 100) + '%)';
      } else {
        translateValue = 'translateX(' + (-currentIndex * 100) + '%)';
      }
      $inner.css('transform', translateValue);
    }

    $slider.find('.mfcc-slide-next').on('click', function () {
      goTo(currentIndex + 1);
    });
    $slider.find('.mfcc-slide-prev').on('click', function () {
      goTo(currentIndex - 1);
    });

    if (autoplay) {
      setInterval(function () {
        goTo(currentIndex + 1);
      }, interval);
    }
  });
});
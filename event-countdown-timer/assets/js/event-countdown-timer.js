(function () {
  function pad(value) {
    return String(value).padStart(2, '0');
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
    const interval = setInterval(tick, 1000);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ect-countdown').forEach(renderCountdown);
  });
})();

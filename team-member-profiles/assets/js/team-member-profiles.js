(function () {
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

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tmp-profile-card').forEach((card) => {
      observer.observe(card);
    });
  });
})();

// Add smooth scrolling animation for better UX
document.addEventListener('DOMContentLoaded', function () {
  // Animate cards on scroll
  const cards = document.querySelectorAll('.exercise-card');

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }, index * 200);
        }
      });
    },
    {
      threshold: 0.1,
    },
  );

  cards.forEach((card) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(50px)';
    card.style.transition = 'all 0.6s ease';
    observer.observe(card);
  });

  // Add hover effect for tech badges
  const techBadges = document.querySelectorAll('.tech-badge');
  techBadges.forEach((badge) => {
    badge.addEventListener('mouseenter', function () {
      this.style.transform = 'scale(1.1)';
      this.style.transition = 'transform 0.2s ease';
    });

    badge.addEventListener('mouseleave', function () {
      this.style.transform = 'scale(1)';
    });
  });
});

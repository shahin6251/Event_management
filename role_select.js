// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
  const cards = document.querySelectorAll('.card-container');
  
  cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px)';
    });
    
    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
    });
  });
});

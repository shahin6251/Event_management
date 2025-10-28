document.addEventListener('DOMContentLoaded', () => {
  const organizerBtn = document.getElementById('organizer-login-btn');
  const customerBtn = document.getElementById('customer-login-btn');

  if (organizerBtn) {
    organizerBtn.addEventListener('click', () => {
      window.location.href = 'organizer_login.html';
    });
  }

  if (customerBtn) {
    customerBtn.addEventListener('click', () => {
      window.location.href = 'customer_login.html';
    });
  }
});
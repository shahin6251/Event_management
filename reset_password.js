// Password strength indicator
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

if (passwordInput) {
  passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    let text = '';
    let color = '';

    if (password.length >= 6) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    switch (strength) {
      case 0:
      case 1:
        text = 'Very weak password';
        color = 'bg-red-500';
        break;
      case 2:
        text = 'Weak password';
        color = 'bg-orange-500';
        break;
      case 3:
        text = 'Good password';
        color = 'bg-yellow-500';
        break;
      case 4:
        text = 'Strong password';
        color = 'bg-green-500';
        break;
      case 5:
        text = 'Very strong password';
        color = 'bg-green-600';
        break;
    }

    strengthBar.className = `password-strength ${color}`;
    strengthBar.style.width = `${(strength / 5) * 100}%`;
    strengthText.textContent = text;
  });

  // Password confirmation validation
  confirmInput.addEventListener('input', function() {
    if (this.value && passwordInput.value !== this.value) {
      this.setCustomValidity('Passwords do not match');
    } else {
      this.setCustomValidity('');
    }
  });
}

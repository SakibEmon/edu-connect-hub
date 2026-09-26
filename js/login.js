// Password Show/Hide Toggle
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');

togglePassword.addEventListener('click', function () {
  const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordField.setAttribute('type', type);

  // Change icon if needed (optional)
  this.textContent = type === 'password' ? '\u{1F441}' : '\u{1F441}\u{FE0F}'; // Eye Icon
});

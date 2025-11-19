/**
 * Password Toggle Functionality
 * Handles showing/hiding password fields with eye icon toggle
 */

document.addEventListener('DOMContentLoaded', function () {
  // Find all password toggle buttons
  const passwordToggles = document.querySelectorAll('.growfund-password-toggle');

  passwordToggles.forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();

      // Find the password input within the same wrapper
      const wrapper = this.closest('.growfund-password-wrapper');
      const passwordInput = wrapper.querySelector('input[type="password"], input[type="text"]');

      if (!passwordInput) return;

      // Toggle password visibility
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        wrapper.classList.add('show-password');
      } else {
        passwordInput.type = 'password';
        wrapper.classList.remove('show-password');
      }
    });
  });
});

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-password]').forEach(function (toggle) {
      toggle.addEventListener('click', function (event) {
        const filedWrapper = event.target.closest('.growfund-password-field-wrapper');
        const input = filedWrapper?.querySelector('.growfund-password-field');

        if (input) {
          input.type = input.type === 'password' ? 'text' : 'password';
        }
      });
    });
  });
})();

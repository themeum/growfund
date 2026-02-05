(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const checkboxInputs = document.querySelectorAll('.growfund-checkbox-input');

    checkboxInputs.forEach((input) => {
      input.addEventListener('change', function (event) {
        const input = event.target;
        if (input.checked) {
          input.value = 'true';
        } else {
          input.value = 'false';
        }
      });
    });
  });
})();

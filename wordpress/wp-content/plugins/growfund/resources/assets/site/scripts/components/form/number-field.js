(function () {
  document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('input', function (e) {
      if (e.target.classList.contains('growfund-number-field')) {
        let value = e.target.value;

        value = value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');

        e.target.value = value;
      }
    });
  });
})();

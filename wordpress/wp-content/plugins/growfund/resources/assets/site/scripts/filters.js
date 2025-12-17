document.addEventListener('DOMContentLoaded', function () {
  const filterForm = document.querySelector('.growfund-filters-form');
  if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
      // When the form is submitted, disable the mobile inputs
      // so they are not included in the URL query string.
      const mobileInputs = filterForm.querySelectorAll('#growfund-filter-modal [name]');
      mobileInputs.forEach((input) => {
        input.disabled = true;
      });
    });

    const inputs = filterForm.querySelectorAll('select, input[type="search"]');

    inputs.forEach((input) => {
      if (input.closest('#growfund-filter-modal')) {
        return; // Mobile inputs are handled by another script
      }

      if (input.tagName === 'SELECT') {
        input.addEventListener('change', () => {
          // Trigger AJAX search instead of form submission
          if (window.performAjaxSearch) {
            window.performAjaxSearch(1);
          }
        });
      }

      if (input.type === 'search') {
        // Fires when the 'x' is clicked to clear the input
        input.addEventListener('search', () => {
          // We need a slight delay for the input's value to be cleared
          setTimeout(() => {
            if (window.performAjaxSearch) {
              window.performAjaxSearch(1);
            }
          }, 0);
        });

        input.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            if (window.performAjaxSearch) {
              window.performAjaxSearch(1);
            }
          }
        });
      }
    });

    // Listen for the custom clear event from the dropdown
    filterForm.addEventListener('dropdown:clear', () => {
      if (window.performAjaxSearch) {
        window.performAjaxSearch(1);
      }
    });
  }
});

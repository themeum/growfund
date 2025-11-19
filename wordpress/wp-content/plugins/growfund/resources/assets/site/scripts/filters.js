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

    // Desktop Clear All button functionality
    const desktopClearButton = document.getElementById('growfund-desktop-filter-clear');
    if (desktopClearButton) {
      desktopClearButton.addEventListener('click', function () {
        // Clear search input
        const searchInput = filterForm.querySelector('input[name="search"]');
        if (searchInput) {
          searchInput.value = '';
        }

        // Clear category dropdown
        const categoryDropdown = filterForm.querySelector('[data-dropdown-key="category"]');
        if (categoryDropdown) {
          categoryDropdown.dataset.value = '';
          const categoryValueElement = categoryDropdown.querySelector('.growfund-dropdown__value');
          if (categoryValueElement) {
            categoryValueElement.textContent =
              categoryDropdown.getAttribute('data-placeholder') || 'Categories';
          }
        }

        // Clear location dropdown
        const locationDropdown = filterForm.querySelector('[data-dropdown-key="location"]');
        if (locationDropdown) {
          locationDropdown.dataset.value = '';
          const locationValueElement = locationDropdown.querySelector('.growfund-dropdown__value');
          if (locationValueElement) {
            locationValueElement.textContent =
              locationDropdown.getAttribute('data-placeholder') || 'Location';
          }
        }

        // Clear sort dropdown
        const sortDropdown = filterForm.querySelector('[data-dropdown-key="sort"]');
        if (sortDropdown) {
          sortDropdown.dataset.value = '';
          const sortValueElement = sortDropdown.querySelector('.growfund-dropdown__value');
          if (sortValueElement) {
            sortValueElement.textContent =
              sortDropdown.getAttribute('data-placeholder') || 'Sort by';
          }
        }

        // Clear URL parameters and redirect to base URL
        const baseUrl = window.location.href.split('?')[0];
        window.location.href = baseUrl;
      });
    }
  }
});

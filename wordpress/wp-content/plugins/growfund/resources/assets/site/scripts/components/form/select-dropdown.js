(function () {
  function initSelectDropdowns() {
    document.querySelectorAll('.growfund-select-dropdown').forEach((dropdown) => {
      if (dropdown.__initialized) return;
      dropdown.__initialized = true;

      const labelWrapper = dropdown.querySelector('.growfund-select-dropdown-label-wrapper');
      const labelText = dropdown.querySelector('.growfund-select-dropdown-label');
      const placeholder = dropdown.querySelector('.growfund-select-dropdown-placeholder');
      const arrowIcon = dropdown.querySelector('.growfund-select-dropdown-arrow-icon');
      const clearIcon = dropdown.querySelector('.growfund-select-dropdown-clear-icon');
      const menu = dropdown.querySelector('.growfund-select-dropdown-menu');
      const input = dropdown.querySelector('.growfund-select-dropdown-input');

      if (!input) return;

      function makeInputReactive(input) {
        const descriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');

        Object.defineProperty(input, 'value', {
          get() {
            return descriptor.get.call(this);
          },
          set(val) {
            const old = descriptor.get.call(this);
            descriptor.set.call(this, val);

            if (old !== val) {
              this.dispatchEvent(new Event('change', { bubbles: true }));
            }
          },
        });
      }

      makeInputReactive(input);

      function syncFromInput() {
        const value = String(input.value ?? '');

        const items = dropdown.querySelectorAll('.growfund-select-dropdown-item');
        const item = [...items].find((i) => String(i.dataset.value) === value);

        items.forEach((i) => i.classList.toggle('selected', i === item));

        if (item) {
          labelText.textContent = item.textContent.trim();
          labelText.classList.add('show');
          placeholder.classList.remove('show');
          clearIcon?.classList.add('active');
          arrowIcon?.classList.add('growfund-hidden');
        } else {
          labelText.classList.remove('show');
          placeholder.classList.add('show');
          clearIcon?.classList.remove('active');
          arrowIcon?.classList.remove('growfund-hidden');
        }

        dropdown.setAttribute('data-selected-value', value);
      }

      function emitChange(previous) {
        dropdown.dispatchEvent(
          new CustomEvent('growfund:select-change', {
            bubbles: true,
            detail: {
              name: input.name,
              value: input.value,
              previous,
              dropdown,
              input,
            },
          }),
        );
      }

      let lastValue = input.value;

      input.addEventListener('change', () => {
        if (lastValue === input.value) return;

        const previous = lastValue;
        lastValue = input.value;

        syncFromInput();
        emitChange(previous);
      });

      labelWrapper.addEventListener('click', (e) => {
        if (clearIcon && clearIcon.contains(e.target)) return;

        const isOpen = menu.classList.toggle('active');
        arrowIcon?.classList.toggle('is-open', isOpen);
      });

      menu.addEventListener('click', (e) => {
        const item = e.target.closest('.growfund-select-dropdown-item');
        if (!item) return;

        input.value = item.dataset.value;
        input.dispatchEvent(new Event('change', { bubbles: true }));

        menu.classList.remove('active');
        arrowIcon?.classList.remove('is-open');
      });

      clearIcon?.addEventListener('click', (e) => {
        e.stopPropagation();
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
          menu.classList.remove('active');
          arrowIcon?.classList.remove('is-open');
        }
      });

      syncFromInput();
    });
  }

  function initFilterableDropdowns() {
    document.querySelectorAll('.growfund-select-dropdown').forEach((dropdown) => {
      if (dropdown.__filterInitialized) return;
      dropdown.__filterInitialized = true;

      if (
        dropdown.hasAttribute('data-filterable') &&
        dropdown.getAttribute('data-filterable') === 'false'
      ) {
        return;
      }

      const menu = dropdown.querySelector('.growfund-select-dropdown-menu');
      const optionsWrapper = dropdown.querySelector('.growfund-select-dropdown-menu-options');

      if (!menu || !optionsWrapper) return;

      const searchInput = document.createElement('input');
      searchInput.type = 'text';
      searchInput.className = 'growfund-select-search';
      searchInput.placeholder = 'Search...';

      menu.prepend(searchInput);

      function filterItems(query) {
        const q = query.toLowerCase().trim();

        optionsWrapper.querySelectorAll('.growfund-select-dropdown-item').forEach((item) => {
          const text = item.textContent.toLowerCase();
          item.classList.toggle('is-hidden', !text.includes(q));
        });
      }

      searchInput.addEventListener('input', (e) => {
        filterItems(e.target.value);
      });

      dropdown.addEventListener('click', (e) => {
        const labelWrapper = dropdown.querySelector('.growfund-select-dropdown-label-wrapper');

        if (!labelWrapper.contains(e.target)) return;

        requestAnimationFrame(() => {
          searchInput.value = '';
          filterItems('');
          searchInput.focus();
        });
      });

      document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
          searchInput.value = '';
          filterItems('');
        }
      });

      const observer = new MutationObserver(() => {
        filterItems(searchInput.value);
      });

      observer.observe(optionsWrapper, {
        childList: true,
        subtree: true,
      });
    });
  }
  document.addEventListener('DOMContentLoaded', function () {
    initSelectDropdowns();
    initFilterableDropdowns();
  });
})();

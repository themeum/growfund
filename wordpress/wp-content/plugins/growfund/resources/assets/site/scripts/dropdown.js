/**
 * Dropdown Component JavaScript
 * Handles dropdown functionality including keyboard navigation, search, and accessibility
 */

class DropdownManager {
  constructor() {
    this.dropdowns = [];
    this.init();
  }

  init() {
    // Initialize all dropdowns on the page
    const dropdownElements = document.querySelectorAll('[data-dropdown]');

    dropdownElements.forEach((dropdown) => {
      this.initializeDropdown(dropdown);
    });
  }

  initializeDropdown(dropdown) {
    const dropdownInstance = new DropdownInstance(dropdown, this); // Pass manager instance
    this.dropdowns.push(dropdownInstance);
    return dropdownInstance;
  }

  // Public API method to create a new dropdown programmatically
  createDropdown(element) {
    return this.initializeDropdown(element);
  }

  // Public API method to destroy a dropdown
  destroyDropdown(dropdown) {
    const index = this.dropdowns.findIndex((d) => d.element === dropdown);
    if (index > -1) {
      this.dropdowns[index].destroy();
      this.dropdowns.splice(index, 1);
    }
  }

  // New method to close all other dropdowns except the given one
  closeAllExcept(currentDropdownInstance) {
    this.dropdowns.forEach((dropdown) => {
      if (dropdown !== currentDropdownInstance && dropdown.isOpen) {
        dropdown.close();
      }
    });
  }

  // New method to close all nested categories across all dropdowns except a specific one
  closeAllNestedCategoriesExcept(currentDropdownInstance, currentCategoryItem) {
    this.dropdowns.forEach((dropdownInstance) => {
      if (dropdownInstance === currentDropdownInstance) {
        dropdownInstance.closeAllCategoriesExcept(currentCategoryItem);
      } else {
        dropdownInstance.closeAllCategories();
      }
    });
  }
}

class DropdownInstance {
  constructor(element, manager) {
    this.element = element;
    this.manager = manager; // Store manager instance
    this.trigger = element.querySelector('.growfund-dropdown__trigger');
    this.panel = element.querySelector('.growfund-dropdown__panel');
    this.hiddenSelect = element.querySelector('select');
    this.options = element.querySelectorAll('.growfund-dropdown__option');
    this.searchInput = element.querySelector('.growfund-dropdown__search-input');
    this.categoryTriggers = element.querySelectorAll('.growfund-dropdown__category-trigger');
    this.clearAllButton = element.querySelector('.growfund-dropdown__clear-all'); // Get clear all button
    this.clearTrigger = element.querySelector('.growfund-dropdown__clear-icon');
    this.placeholder = this.element.dataset.placeholder || 'Select an option...';

    this.isOpen = false;
    this.focusedIndex = -1;
    this.isInitializing = true; // Flag to prevent re-saving on initial load

    this.bindEvents();
    this.loadInitialValue(); // Load saved value on initialization
    this.isInitializing = false; // Reset flag after initialization
  }

  bindEvents() {
    if (!this.trigger || !this.panel) return;

    // Toggle dropdown
    this.trigger.addEventListener('click', () => {
      if (this.element.hasAttribute('disabled')) return;
      this.toggle();
    });

    // Handle option selection
    this.options.forEach((option) => {
      option.addEventListener('click', () => {
        this.selectOption(option);
      });
    });

    // Search functionality
    if (this.searchInput) {
      this.searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        this.filterOptions(query);
      });

      this.searchInput.addEventListener('keydown', (e) => {
        this.handleSearchKeydown(e);
      });
    }

    // Handle category expansion/collapse
    this.categoryTriggers.forEach((trigger) => {
      trigger.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent dropdown from closing
        this.toggleCategory(trigger);
      });
    });

    // Keyboard navigation
    this.trigger.addEventListener('keydown', (e) => {
      this.handleTriggerKeydown(e);
    });

    this.element.addEventListener('keydown', (e) => {
      this.handleDropdownKeydown(e);
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (this.isOpen && !this.element.contains(e.target)) {
        this.close();
      }
    });

    // Handle clear all button click
    if (this.clearAllButton) {
      this.clearAllButton.addEventListener('click', (e) => {
        e.stopPropagation();
        this.clearSelection();
      });
    }

    if (this.clearTrigger) {
      this.clearTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        this.clearSelection();
      });
    }
  }

  toggle() {
    if (this.isOpen) {
      this.close();
    } else {
      this.open();
    }
  }

  open() {
    if (this.manager) {
      this.manager.closeAllExcept(this); // Close other dropdowns
    }

    this.isOpen = true;
    this.trigger.setAttribute('aria-expanded', 'true');
    this.element.classList.add('growfund-dropdown--open');

    if (this.searchInput) {
      // We use requestAnimationFrame twice to ensure the input is fully rendered and visible
      // before focusing it. The first frame lets the DOM update the dropdown's open state,
      // and the second ensures the browser has applied the styles, preventing focus issues.
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          this.searchInput.focus();
        });
      });
    } else {
      this.focusSelectedOption();
    }

    // Trigger custom event
    this.triggerEvent('dropdown:open');
  }

  close() {
    this.isOpen = false;
    this.trigger.setAttribute('aria-expanded', 'false');
    this.element.classList.remove('growfund-dropdown--open');
    this.focusedIndex = -1;
    this.trigger.focus();

    // Trigger custom event
    this.triggerEvent('dropdown:close');
  }

  selectOption(option) {
    const value = option.dataset.value;
    const label = option.dataset.label;

    // Update hidden select
    if (this.hiddenSelect) {
      if (option.querySelector('input[type="radio"]')) {
        // For nested options with radio buttons
        const radio = option.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = true;
          this.hiddenSelect.value = value;
        }
      } else {
        // For single-level dropdowns
        this.hiddenSelect.value = value;
      }
      // Only dispatch change event if not initializing to prevent loops
      if (!this.isInitializing) {
        this.hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    // Update display
    const valueElement = this.element.querySelector('.growfund-dropdown__value');
    if (valueElement) {
      valueElement.textContent = label;
    }
    this.element.dataset.value = value;

    // Update selected state
    this.options.forEach((opt) => opt.setAttribute('aria-selected', 'false'));
    option.setAttribute('aria-selected', 'true');

    this.close();

    if (!this.isInitializing) {
      this.saveValue(value); // Only save if not initializing
    }

    // Show clear all button if it exists
    if (this.clearAllButton) {
      this.clearAllButton.closest('.growfund-dropdown__footer').style.display = 'block';
    }

    // Always trigger custom event, even if value is the same
    this.triggerEvent('dropdown:select', { value, label, option });
  }

  focusSelectedOption() {
    const selected = this.element.querySelector('.growfund-dropdown__option[aria-selected="true"]');
    if (selected) {
      selected.focus();

      // If selected option is nested, expand its parent category
      const parentCategory = selected.closest('.growfund-dropdown__category-item');
      if (parentCategory) {
        const categoryTrigger = parentCategory.querySelector('.growfund-dropdown__category-trigger');
        const subList = parentCategory.querySelector('.growfund-dropdown__sub-list');
        if (
          categoryTrigger &&
          subList &&
          categoryTrigger.getAttribute('aria-expanded') === 'false'
        ) {
          categoryTrigger.setAttribute('aria-expanded', 'true');
          subList.style.display = 'block';
          parentCategory.classList.add('growfund-dropdown__category-item--open');
        }
      }
    } else if (this.options.length > 0) {
      this.options[0].focus();
    }
  }

  filterOptions(query) {
    this.options.forEach((option) => {
      const text = option.textContent.toLowerCase();
      const matches = text.includes(query);
      option.style.display = matches ? '' : 'none';

      // Also hide parent category if all children are hidden
      const parentCategory = option.closest('.growfund-dropdown__category-item');
      if (parentCategory) {
        const visibleChildren = Array.from(
          parentCategory.querySelectorAll('.growfund-dropdown__option--nested'),
        ).some((child) => child.style.display !== 'none');
        parentCategory.style.display = visibleChildren ? '' : 'none';
      }
    });

    // Reset focused index when filtering
    this.focusedIndex = -1;
  }

  // New method to close all nested categories within this dropdown instance
  closeAllCategories() {
    this.categoryTriggers.forEach((trigger) => {
      const categoryItem = trigger.closest('.growfund-dropdown__category-item');
      const subList = categoryItem.querySelector('.growfund-dropdown__sub-list');
      if (trigger.getAttribute('aria-expanded') === 'true') {
        trigger.setAttribute('aria-expanded', 'false');
        subList.style.display = 'none';
        categoryItem.classList.remove('growfund-dropdown__category-item--open');
      }
    });
  }

  // New method to close all nested categories within this dropdown instance except the excluded one
  closeAllCategoriesExcept(excludedCategoryItem) {
    this.categoryTriggers.forEach((trigger) => {
      const categoryItem = trigger.closest('.growfund-dropdown__category-item');
      if (categoryItem !== excludedCategoryItem) {
        const subList = categoryItem.querySelector('.growfund-dropdown__sub-list');
        if (trigger.getAttribute('aria-expanded') === 'true') {
          trigger.setAttribute('aria-expanded', 'false');
          subList.style.display = 'none';
          categoryItem.classList.remove('growfund-dropdown__category-item--open');
        }
      }
    });
  }

  // New method to toggle category expansion
  toggleCategory(trigger) {
    const categoryItem = trigger.closest('.growfund-dropdown__category-item');
    const subList = categoryItem.querySelector('.growfund-dropdown__sub-list');
    const isExpanded = trigger.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
      trigger.setAttribute('aria-expanded', 'false');
      subList.style.display = 'none';
      categoryItem.classList.remove('growfund-dropdown__category-item--open');
    } else {
      // Close all other categories (within this dropdown and other dropdowns)
      this.manager.closeAllNestedCategoriesExcept(this, categoryItem); // Pass the current dropdown instance and the category item to keep open

      trigger.setAttribute('aria-expanded', 'true');
      subList.style.display = 'block'; // Or 'flex' depending on desired layout
      categoryItem.classList.add('growfund-dropdown__category-item--open');
    }
  }

  handleTriggerKeydown(e) {
    switch (e.key) {
      case 'Enter':
      case ' ':
      case 'ArrowDown':
      case 'ArrowUp':
        e.preventDefault();
        if (!this.isOpen) {
          this.open();
        }
        break;
      case 'Escape':
        if (this.isOpen) {
          this.close();
        }
        break;
    }
  }

  handleSearchKeydown(e) {
    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        this.focusNextOption();
        break;
      case 'ArrowUp':
        e.preventDefault();
        this.focusPrevOption();
        break;
      case 'Enter':
        e.preventDefault();
        if (this.focusedIndex >= 0) {
          const visibleOptions = this.getVisibleOptions();
          if (visibleOptions[this.focusedIndex]) {
            this.selectOption(visibleOptions[this.focusedIndex]);
          }
        }
        break;
      case 'Escape':
        this.close();
        break;
    }
  }

  handleDropdownKeydown(e) {
    if (this.searchInput && e.target === this.searchInput) return;

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        this.focusNextOption();
        break;
      case 'ArrowUp':
        e.preventDefault();
        this.focusPrevOption();
        break;
      case 'Enter':
      case ' ':
        e.preventDefault();
        if (this.focusedIndex >= 0) {
          const visibleOptions = this.getVisibleOptions();
          if (visibleOptions[this.focusedIndex]) {
            this.selectOption(visibleOptions[this.focusedIndex]);
          }
        }
        break;
      case 'Escape':
        this.close();
        break;
    }
  }

  focusNextOption() {
    const visibleOptions = this.getVisibleOptions();
    this.focusedIndex = Math.min(this.focusedIndex + 1, visibleOptions.length - 1);
    if (visibleOptions[this.focusedIndex]) {
      visibleOptions[this.focusedIndex].focus();
    }
  }

  focusPrevOption() {
    const visibleOptions = this.getVisibleOptions();
    this.focusedIndex = Math.max(this.focusedIndex - 1, 0);
    if (visibleOptions[this.focusedIndex]) {
      visibleOptions[this.focusedIndex].focus();
    }
  }

  getVisibleOptions() {
    return Array.from(this.options).filter((opt) => opt.style.display !== 'none');
  }

  triggerEvent(eventName, detail = {}) {
    const event = new CustomEvent(eventName, {
      detail: {
        dropdown: this,
        element: this.element,
        ...detail,
      },
      bubbles: true,
    });

    this.element.dispatchEvent(event);
  }

  // New method to clear the selection
  clearSelection() {
    // Clear hidden select
    if (this.hiddenSelect) {
      this.hiddenSelect.value = '';
      if (!this.isInitializing) {
        this.hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    // Reset display
    const valueElement = this.element.querySelector('.growfund-dropdown__value');
    if (valueElement) {
      valueElement.textContent = this.placeholder;
    }
    this.element.dataset.value = '';

    // Unset selected state
    this.options.forEach((opt) => {
      opt.setAttribute('aria-selected', 'false');
      const radio = opt.querySelector('input[type="radio"]');
      if (radio) {
        radio.checked = false;
      }
    });

    // Hide clear all button
    if (this.clearAllButton) {
      const footer = this.clearAllButton.closest('.growfund-dropdown__footer');
      if (footer) {
        footer.style.display = 'none';
      }
    }

    // Trigger custom event
    this.triggerEvent('dropdown:clear');

    this.close();
  }

  getValue() {
    return this.element.dataset.value || (this.hiddenSelect ? this.hiddenSelect.value : null);
  }

  loadInitialValue() {
    const initialValue = this.element.dataset.initialValue;
    if (initialValue) {
      this.setValue(initialValue);
    }
  }

  setValue(value) {
    const optionToSelect = Array.from(this.options).find((opt) => opt.dataset.value === value);
    if (optionToSelect) {
      this.selectOption(optionToSelect);
    } else {
      // If value not found, clear selection explicitly without recursion
      if (this.hiddenSelect) this.hiddenSelect.value = '';
      const valueElement = this.element.querySelector('.growfund-dropdown__value');
      if (valueElement) valueElement.textContent = this.placeholder;
      this.element.dataset.value = '';
      this.options.forEach((opt) => {
        opt.setAttribute('aria-selected', 'false');
        const radio = opt.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = false;
        }
      });
      this.saveValue(''); // Clear saved value in localStorage
      this.close(); // Close the dropdown

      // Hide clear all button
      if (this.clearAllButton) {
        this.clearAllButton.closest('.growfund-dropdown__footer').style.display = 'none';
      }
    }
    this.isInitializing = false; // Reset the flag
  }

  saveValue(value) {
    // Save the selected value to localStorage if the dropdown has a name
    const name = this.element.getAttribute('name');
    if (name) {
      try {
        localStorage.setItem(`growfund-dropdown-${name}`, value);
      } catch (e) {
        // Silently handle localStorage errors
      }
    }
  }

  disable() {
    this.element.setAttribute('disabled', 'true');
    if (this.trigger) {
      this.trigger.setAttribute('disabled', 'true');
    }
    if (this.searchInput) {
      this.searchInput.setAttribute('disabled', 'true');
    }
    if (this.hiddenSelect) {
      this.hiddenSelect.setAttribute('disabled', 'true');
    }
  }

  enable() {
    this.element.removeAttribute('disabled');
    if (this.trigger) {
      this.trigger.removeAttribute('disabled');
    }
    if (this.searchInput) {
      this.searchInput.removeAttribute('disabled');
    }
    if (this.hiddenSelect) {
      this.hiddenSelect.removeAttribute('disabled');
    }
  }

  destroy() {
    // Remove event listeners and clean up
    if (this.trigger) {
      this.trigger.removeEventListener('click', this.toggle);
      this.trigger.removeEventListener('keydown', this.handleTriggerKeydown);
    }
    if (this.options) {
      this.options.forEach((option) => {
        option.removeEventListener('click', this.selectOption);
      });
    }
    if (this.searchInput) {
      this.searchInput.removeEventListener('input', this.filterOptions);
      this.searchInput.removeEventListener('keydown', this.handleSearchKeydown);
    }
    if (this.categoryTriggers) {
      this.categoryTriggers.forEach((trigger) => {
        trigger.removeEventListener('click', this.toggleCategory);
      });
    }
    document.removeEventListener('click', this.close);
    this.element.removeEventListener('keydown', this.handleDropdownKeydown);

    // Clear localStorage for this dropdown
    if (this.dropdownKey) {
      localStorage.removeItem(`growfund-dropdown-${this.dropdownKey}`);
    }

    // Remove references to DOM elements and manager
    this.element = null;
    this.manager = null;
    this.trigger = null;
    this.panel = null;
    this.hiddenSelect = null;
    this.options = null;
    this.searchInput = null;
    this.categoryTriggers = null;
    this.clearAllButton = null;
  }
}

// Initialize all dropdowns on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  // Ensure DropdownManager is a singleton
  if (!window.dropdownManager) {
    window.dropdownManager = new DropdownManager();
  }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { DropdownManager, DropdownInstance };
}

document.addEventListener('DOMContentLoaded', function () {
  const initializeWhenReady = () => {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    if (dropdowns.length > 0) {
      initializeLocationDropdowns();
      initializeShippingCalculation();
    } else {
      setTimeout(initializeWhenReady, 100);
    }
  };

  initializeWhenReady();

  const form = document.getElementById('growfund_payment_form');

  if (!form) {
    return;
  }

  const checkbox = document.getElementById('terms_agreement_checkbox');
  let pledgeButton = document.getElementById('growfund_pledge_button');

  if (!pledgeButton) {
    pledgeButton = form.querySelector('button[type="submit"]');
  }

  // Required fields
  const countrySelect = document.getElementById('growfund_country');
  const stateSelect = document.getElementById('growfund_state');
  const cityInput = document.getElementById('growfund_city');
  const address = document.getElementById('input-address');
  const zipInput = document.getElementById('input-zip_code');

  function validateForm() {
    const isChecked = checkbox ? checkbox.checked : true; // skip if not present
    const hasCountry = countrySelect ? countrySelect.value.trim() !== '' : true;
    const hasState = stateSelect ? stateSelect.value.trim() !== '' : true;
    const hasCity = cityInput ? cityInput.value.trim() !== '' : true;
    const hasZip = zipInput ? zipInput.value.trim() !== '' : true;
    const hasAddress = address ? address.value.trim() !== '' : true;

    const valid = isChecked && hasCountry && hasState && hasCity && hasZip && hasAddress;

    pledgeButton.disabled = !valid;
    pledgeButton.classList.toggle('growfund-btn--disabled', !valid);
  }

  if (pledgeButton) {
    // Initial state
    pledgeButton.disabled = true;
    pledgeButton.classList.add('growfund-btn--disabled');

    // Event listeners
    if (checkbox) {
      checkbox.addEventListener('change', validateForm);
    }
    if (countrySelect) {
      countrySelect.addEventListener('change', validateForm);
    }
    if (stateSelect) {
      stateSelect.addEventListener('change', validateForm);
    }
    if (cityInput) {
      cityInput.addEventListener('input', validateForm);
    }
    if (zipInput) {
      zipInput.addEventListener('input', validateForm);
    }
    if (address) {
      address.addEventListener('input', validateForm);
    }

    // Run once on load
    validateForm();
  }
});

/**
 * Initialize shipping cost calculation functionality
 */
function initializeShippingCalculation() {
  const countrySelect = document.getElementById('growfund_country');
  const shippingCostInput = document.getElementById('shipping_cost_input');

  if (countrySelect && shippingCostInput) {
    countrySelect.addEventListener('change', function () {
      const selectedCountry = this.value;
      const rewardIdInput = document.querySelector('input[name="reward_id"]');

      if (!rewardIdInput || !rewardIdInput.value) {
        return;
      }

      const rewardId = rewardIdInput.value;

      if (selectedCountry && rewardId) {
        calculateShippingCost(selectedCountry, rewardId);
      } else if (!selectedCountry) {
        shippingCostInput.value = 0;
        updateTotalAmount();
      }
    });

    document.addEventListener('dropdown:select', function (event) {
      if (
        event.detail &&
        event.detail.option &&
        event.detail.option.closest('[data-dropdown]') === countrySelect.closest('[data-dropdown]')
      ) {
        const selectedCountry = event.detail.value;
        const rewardIdInput = document.querySelector('input[name="reward_id"]');

        if (rewardIdInput && rewardIdInput.value && selectedCountry) {
          calculateShippingCost(selectedCountry, rewardIdInput.value);
        }
      }
    });
  }
}

/**
 * Calculate shipping cost via AJAX
 */
function calculateShippingCost(country, rewardId) {
  const countrySelect = document.getElementById('growfund_country');
  const shippingCostInput = document.getElementById('shipping_cost_input');

  const originalText = countrySelect.options[countrySelect.selectedIndex].text;
  countrySelect.options[countrySelect.selectedIndex].text = 'Calculating...';
  countrySelect.classList.add('growfund-country-calculating');

  countrySelect.disabled = true;

  const formData = new FormData();
  formData.append('action', 'growfund_calculate_shipping_cost');
  formData.append('_wpnonce', growfund.ajax_nonce);
  formData.append('reward_id', rewardId);
  formData.append('country', country);

  fetch(growfund.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const shippingCost = parseFloat(data.data.shipping_cost);
        shippingCostInput.value = shippingCost;
        updateTotalAmount();
      } else {
        shippingCostInput.value = 0;
        updateTotalAmount();
        showMessage('Error calculating shipping cost. Using default rate.', 'error');
      }
    })
    .catch((error) => {
      shippingCostInput.value = 0;
      updateTotalAmount();
      showMessage('Network error. Using default shipping rate.', 'error');
    })
    .finally(() => {
      countrySelect.options[countrySelect.selectedIndex].text = originalText;
      countrySelect.disabled = false;
      countrySelect.classList.remove('growfund-country-calculating');
    });
}

/**
 * Show feedback message for shipping calculation (errors only)
 */
function showMessage(message, type) {
  if (type !== 'error') {
    return;
  }

  const countrySelect = document.getElementById('growfund_country');

  const existingMessage = document.querySelector('.growfund-shipping-message');
  if (existingMessage) {
    existingMessage.remove();
  }

  const messageDiv = document.createElement('div');
  messageDiv.className = `growfund-shipping-message growfund-message-${type}`;
  messageDiv.textContent = message;
  messageDiv.style.cssText = `
    padding: 8px 12px;
    margin: 8px 0;
    border-radius: 4px;
    font-size: 14px;
    ${
      type === 'success'
        ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'
        : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
    }
  `;

  const countryField = countrySelect.closest('.growfund-form-field') || countrySelect.parentElement;
  countryField.parentNode.insertBefore(messageDiv, countryField.nextSibling);

  setTimeout(() => {
    if (messageDiv.parentNode) {
      messageDiv.remove();
    }
  }, 3000);
}

/**
 * Update total amount calculation
 */
function updateTotalAmount() {
  const rewardAmountInput = document.getElementById('reward_amount_input');
  const bonusAmountInput = document.getElementById('bonus_support_amount_input');
  const shippingCostInput = document.getElementById('shipping_cost_input');
  const totalAmountInput = document.getElementById('total_amount_input');

  const rewardAmount = parseFloat(rewardAmountInput.value) || 0;
  const bonusAmount = parseFloat(bonusAmountInput.value) || 0;
  const shippingCost = parseFloat(shippingCostInput.value) || 0;
  const newTotal = rewardAmount + bonusAmount + shippingCost;

  totalAmountInput.value = newTotal;
}

/**
 * Initialize location dropdowns (country, state, city)
 * This function handles the dynamic population of states and cities
 * based on country and state selection
 */
function initializeLocationDropdowns() {
  const countrySelect = document.getElementById('growfund_country');
  const stateSelect = document.getElementById('growfund_state');
  const cityInput = document.getElementById('growfund_city');

  if (!countrySelect || !stateSelect) {
    return;
  }

  let locationData = [];
  try {
    let dataAttribute = countrySelect.getAttribute('data-location-data');

    if (!dataAttribute) {
      const dropdownWrapper = countrySelect.closest('[data-dropdown]');
      if (dropdownWrapper) {
        dataAttribute = dropdownWrapper.getAttribute('data-location-data');
      }
    }

    if (dataAttribute) {
      locationData = JSON.parse(dataAttribute);
    } else {
      return;
    }
  } catch (error) {
    return;
  }

  function populateStates(countryCode, isInitialLoad = false) {
    if (!isInitialLoad) {
      while (stateSelect.options.length > 1) {
        stateSelect.remove(1);
      }

      stateSelect.value = '';

      const changeEvent = new Event('change', { bubbles: true });
      stateSelect.dispatchEvent(changeEvent);

      if (cityInput && cityInput.type === 'text') {
        cityInput.value = '';
      }
    }

    if (!countryCode) return;

    const country = locationData.find((c) => c.value === countryCode);

    if (country && country.states && country.states.length > 0) {
      country.states.forEach((state) => {
        const option = document.createElement('option');
        option.value = state.value;
        option.textContent = state.label;
        stateSelect.appendChild(option);
      });

      const stateDropdownWrapper = stateSelect.closest('[data-dropdown]');
      if (stateDropdownWrapper) {
        if (!isInitialLoad) {
          const existingOptions = stateDropdownWrapper.querySelectorAll('.growfund-dropdown__option');
          existingOptions.forEach((option, index) => {
            if (index > 0) {
              option.remove();
            }
          });

          const dropdownButton = stateDropdownWrapper.querySelector('.growfund-dropdown__button');
          if (dropdownButton) {
            const buttonText = dropdownButton.querySelector('.growfund-dropdown__button-text');
            if (buttonText) {
              buttonText.textContent = 'Select State';
            }
          }

          const dropdownInstance = window.dropdownManager?.dropdowns.find(
            (d) => d.element === stateDropdownWrapper,
          );
          if (dropdownInstance && dropdownInstance.resetSelection) {
            dropdownInstance.resetSelection();
          } else if (dropdownInstance && dropdownInstance.selectOption) {
            const firstOption = stateDropdownWrapper.querySelector('.growfund-dropdown__option');
            if (firstOption) {
              dropdownInstance.selectOption(firstOption);
            }
          }
        }

        const dropdownList = stateDropdownWrapper.querySelector('.growfund-dropdown__list');
        if (dropdownList) {
          country.states.forEach((state) => {
            const optionElement = document.createElement('li');
            optionElement.className = 'growfund-dropdown__option';
            optionElement.setAttribute('data-value', state.value);
            optionElement.setAttribute('data-label', state.label);
            optionElement.setAttribute('role', 'option');
            optionElement.setAttribute('tabindex', '0');

            optionElement.innerHTML = `
              <span class="growfund-dropdown__option-text">${state.label}</span>
            `;

            optionElement.addEventListener('click', function () {
              const dropdownInstance = window.dropdownManager?.dropdowns.find(
                (d) => d.element === stateDropdownWrapper,
              );
              if (dropdownInstance) {
                dropdownInstance.selectOption(this);
              }
            });

            dropdownList.appendChild(optionElement);
          });

          const dropdownInstance = window.dropdownManager?.dropdowns.find(
            (d) => d.element === stateDropdownWrapper,
          );
          if (dropdownInstance) {
            dropdownInstance.options =
              stateDropdownWrapper.querySelectorAll('.growfund-dropdown__option');

            const searchInput = stateDropdownWrapper.querySelector('.growfund-dropdown__search-input');
            if (searchInput) {
              searchInput.value = '';
              dropdownInstance.filterOptions('');
            }
          }
        }
      }
    }
  }

  countrySelect.addEventListener('change', function () {
    const selectedCountry = this.value;
    populateStates(selectedCountry, false);
  });

  document.addEventListener('dropdown:select', function (event) {
    if (
      event.detail &&
      event.detail.option &&
      event.detail.option.closest('[data-dropdown]') === countrySelect.closest('[data-dropdown]')
    ) {
      const selectedCountry = event.detail.value;
      populateStates(selectedCountry, false);
    }
  });

  const userCountry = countrySelect.value;
  if (userCountry) {
    populateStates(userCountry, true);
  }

  if (cityInput) {
    const userCity = cityInput.value || cityInput.getAttribute('data-user-city') || '';

    if (userCity) {
      cityInput.value = userCity;
    }
  }
}

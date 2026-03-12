(function () {
  function getStates(countryCode) {
    if (!countryCode) return [];

    const countries = window.growfundCountries ?? [];

    const country = countries.find((c) => c.value === countryCode);

    if (!country) {
      return [];
    }

    return country.states ?? [];
  }

  function makeStateDropdownItems(selectState, value) {
    const states = getStates(value);
    const labelText = selectState.querySelector('.growfund-select-dropdown-label');
    const placeholderText = selectState.querySelector('.growfund-select-dropdown-placeholder');

    labelText.classList.remove('show');
    placeholderText.classList.add('show');
    selectState.setAttribute('data-selected-value', '');

    const menu = selectState.querySelector('.growfund-select-dropdown-menu');
    const optionMenu = menu.querySelector('.growfund-select-dropdown-menu-options');
    optionMenu.remove();

    if (states.length > 0) {
      const optionMenu = document.createElement('div');
      optionMenu.classList.add('growfund-select-dropdown-menu-options');

      function getItem(value, label) {
        return `<div class="growfund-select-dropdown-item " data-value="${value}">${label}</div>`;
      }

      optionMenu.innerHTML = states.map((state) => getItem(state.value, state.label)).join('');

      menu.appendChild(optionMenu);
    }
  }

  function getAllAddressFields() {
    return {
      shippingField: {
        country: document.getElementById('shipping_country'),
        address: document.getElementById('shipping_address_1'),
        address_2: document.getElementById('shipping_address_2'),
        state: document.getElementById('shipping_state'),
        city: document.getElementById('shipping_city'),
        postalCode: document.getElementById('shipping_postal_code'),
      },
      sameAsShippingField: document.getElementById('is_billing_address_same'),
      billingField: {
        country: document.getElementById('billing_country'),
        address: document.getElementById('billing_address_1'),
        address_2: document.getElementById('billing_address_2'),
        state: document.getElementById('billing_state'),
        city: document.getElementById('billing_city'),
        postalCode: document.getElementById('billing_postal_code'),
      },
    };
  }

  function setBillingAddressSameAsShipping() {
    const { shippingField, sameAsShippingField, billingField } = getAllAddressFields();

    if (!sameAsShippingField?.checked) return;

    billingField.country.querySelector('.growfund-select-dropdown-input').value =
      shippingField.country.querySelector('.growfund-select-dropdown-input').value;

    billingField.state.querySelector('.growfund-select-dropdown-input').value =
      shippingField.state.querySelector('.growfund-select-dropdown-input').value;

    billingField.address.value = shippingField.address.value;
    billingField.address_2.value = shippingField.address_2.value;
    billingField.city.value = shippingField.city.value;
    billingField.postalCode.value = shippingField.postalCode.value;
  }

  function calculateTotalAmount() {
    const shippingAmountElement = document.getElementById('checkout_shipping_amount');
    const pledgeAmountInput = document.getElementById('growfund_pledge_amount_input');
    const totalAmountElement = document.getElementById('checkout_total_amount');
    let rewardAmount = 0;

    const rewardIdInput = document.getElementById('growfund_reward_id');
    if (rewardIdInput) {
      rewardAmount = rewardIdInput.getAttribute('data-pledge-amount')
        ? parseFloat(rewardIdInput.getAttribute('data-pledge-amount'))
        : 0;
    }

    const pledgeAmount = pledgeAmountInput?.value ? parseFloat(pledgeAmountInput.value) : 0;
    const shippingAmount = shippingAmountElement?.getAttribute('data-shipping-amount')
      ? parseFloat(shippingAmountElement.getAttribute('data-shipping-amount'))
      : 0;

    const totalAmount = rewardAmount + pledgeAmount + shippingAmount;
    totalAmountElement.textContent = growfundToCurrency(totalAmount.toFixed(2));
  }

  function calculateShippingCost(shippingCountry, deliveryOption = 'home-delivery') {
    let finalCost = 0;

    if (deliveryOption === 'home-delivery') {
      const checkoutShippingSection = document.getElementById('checkout_shipping_section');

      const shippingCostsJson = checkoutShippingSection.getAttribute('data-shipping-costs');
      const shippingCosts = JSON.parse(shippingCostsJson);

      let shippingCost = shippingCosts.find((shipping) => shipping.location === shippingCountry);

      if (!shippingCost) {
        shippingCost = shippingCosts.find((shipping) => shipping.location === 'rest-of-the-world');
      }

      finalCost = shippingCost ? Number(shippingCost.cost) : 0;
    }

    const shippingAmountElement = document.getElementById('checkout_shipping_amount');
    if (shippingAmountElement) {
      shippingAmountElement.setAttribute('data-shipping-amount', finalCost);
      shippingAmountElement.textContent = growfundToCurrency(finalCost.toFixed(2));
    }

    calculateTotalAmount();
  }

  document.addEventListener('DOMContentLoaded', function () {
    const pledgeAmountInput = document.getElementById('growfund_pledge_amount_input');

    if (pledgeAmountInput) {
      pledgeAmountInput.addEventListener('change', function () {
        calculateTotalAmount();
      });
      pledgeAmountInput.addEventListener('input', function () {
        calculateTotalAmount();
      });
    }

    document
      .getElementById('delivery_option')
      ?.addEventListener('growfund:select-change', function (event) {
        const { value: deliveryOption } = event.detail;
        if (deliveryOption === 'local-pickup') {
          document.getElementById('local_pickup_instructions')?.classList.remove('growfund-hidden');
          document.getElementById('shipping_address_section')?.classList.add('growfund-hidden');
          calculateShippingCost(null, deliveryOption);
        } else {
          document.getElementById('local_pickup_instructions')?.classList.add('growfund-hidden');
          document.getElementById('shipping_address_section')?.classList.remove('growfund-hidden');
          calculateShippingCost(
            shippingField.country?.getAttribute('data-selected-value'),
            deliveryOption,
          );
        }
      });

    const { shippingField, sameAsShippingField, billingField } = getAllAddressFields();

    shippingField.country?.addEventListener('growfund:select-change', function (event) {
      const { value: country } = event.detail;
      makeStateDropdownItems(shippingField.state, country);
      setBillingAddressSameAsShipping();
      calculateShippingCost(country);
    });

    shippingField.state?.addEventListener('growfund:select-change', function () {
      setBillingAddressSameAsShipping();
    });

    shippingField.address?.addEventListener('change', function () {
      setBillingAddressSameAsShipping();
    });

    shippingField.address_2?.addEventListener('change', function () {
      setBillingAddressSameAsShipping();
    });

    shippingField.city?.addEventListener('change', function () {
      setBillingAddressSameAsShipping();
    });

    shippingField.postalCode?.addEventListener('change', function () {
      setBillingAddressSameAsShipping();
    });

    billingField.country.addEventListener('growfund:select-change', function (event) {
      const { value } = event.detail;
      makeStateDropdownItems(billingField.state, value);
    });

    sameAsShippingField?.addEventListener('click', function (event) {
      const input = event.target;
      const billingSection = document.getElementById('checkout_billing_address_section');

      if (input.checked) {
        billingSection.classList.add('growfund-hidden');
        setBillingAddressSameAsShipping();
      } else {
        billingSection.classList.remove('growfund-hidden');
      }
    });

    const termsAgreementCheckbox = document.getElementById('terms_agreement_checkbox');
    termsAgreementCheckbox.addEventListener('click', function (event) {
      const pledgeButton = document.getElementById('growfund_checkout_page_pledge_button');
      pledgeButton.disabled = !event.target.checked;
    });
  });
})();

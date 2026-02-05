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
  function updateDonationAmount(amount) {
    const donationAmounts = document.querySelectorAll(
      '.growfund-donation-checkout-page-donation-amount',
    );

    donationAmounts.forEach(function (donationAmount) {
      if (donationAmount.value === amount) {
        donationAmount.checked = true;
        return;
      }
      donationAmount.checked = false;
    });

    const totalDonationAmount = document.getElementById('total_donation_amount');

    if (totalDonationAmount) totalDonationAmount.textContent = growfundToCurrency(amount);

    const totalDueAmount = document.getElementById('total_due_amount');

    if (totalDueAmount) totalDueAmount.textContent = growfundToCurrency(amount);
  }
  document.addEventListener('DOMContentLoaded', function () {
    const donationAmounts = document.querySelectorAll(
      '.growfund-donation-checkout-page-donation-amount',
    );

    donationAmounts.forEach(function (donationAmount) {
      donationAmount.addEventListener('click', function (event) {
        const amount = event.target.value;

        if (event.target.checked) {
          const donationAmountInput = document.getElementById('growfund_donation_custom_amount');

          if (donationAmountInput) donationAmountInput.value = amount;

          updateDonationAmount(amount);
        }
      });
    });

    const donationAmountInput = document.getElementById('growfund_donation_custom_amount');

    if (donationAmountInput) {
      donationAmountInput.addEventListener('input', function (event) {
        const amount = event.target.value;
        updateDonationAmount(amount);
      });

      donationAmountInput.addEventListener('change', function (event) {
        const amount = event.target.value;
        updateDonationAmount(amount);
      });
    }

    const growfundTributeCheckbox = document.getElementById('growfund_tribute_checkbox');
    const growfundTributeContent = document.getElementById('growfund_tribute_content');

    if (growfundTributeCheckbox) {
      growfundTributeCheckbox.addEventListener('click', function (event) {
        if (event.target.checked) {
          growfundTributeContent?.classList.remove('growfund-hidden');
          return;
        }

        growfundTributeContent?.classList.add('growfund-hidden');

        const dedicationOptionSection = document.querySelector(
          '.growfund-donation-checkout-page-tribute-dedication-type-options',
        );

        if (dedicationOptionSection) {
          dedicationOptionSection
            .querySelectorAll('.growfund-radio-input')
            .forEach((radioField) => {
              radioField.checked = false;
            });
        }

        const tributeNotificationType = document.querySelector('#tribute_notification_type');

        if (tributeNotificationType) {
          tributeNotificationType.querySelector('.growfund-select-dropdown-input').value = '';
        }
      });
    }

    const termsAgreementCheckbox = document.getElementById('terms_agreement_checkbox');
    termsAgreementCheckbox.addEventListener('click', function (event) {
      const donationButton = document.getElementById('growfund_checkout_page_donation_button');
      donationButton.disabled = !event.target.checked;
    });

    const billingCountry = document.getElementById('billing_country');
    if (billingCountry) {
      billingCountry.addEventListener('growfund:select-change', function (event) {
        const { value } = event.detail;
        const billingState = document.getElementById('billing_state');

        if (!billingState) return;

        makeStateDropdownItems(billingState, value);
      });
    }

    const recipientCountry = document.getElementById('recipient_country');
    if (recipientCountry) {
      recipientCountry.addEventListener('growfund:select-change', function (event) {
        const { value } = event.detail;
        const recipientState = document.getElementById('recipient_state');

        if (!recipientState) return;

        makeStateDropdownItems(recipientState, value);
      });
    }

    const tributeNotificationType = document.querySelector('#tribute_notification_type');

    if (tributeNotificationType) {
      tributeNotificationType.addEventListener('growfund:select-change', function (event) {
        const { value } = event.detail;

        const recipientAddress = document.querySelector(
          '.growfund-donation-checkout-page-tribute-recipient-address',
        );

        if (!recipientAddress) return;

        if (!value || value === 'send-ecard') {
          recipientAddress.classList.add('growfund-hidden');
          return;
        }

        if (value === 'send-post-mail' || value === 'send-ecard-and-post-mail') {
          recipientAddress.classList.remove('growfund-hidden');
          return;
        }
      });
    }
  });
})();

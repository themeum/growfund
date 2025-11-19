/**
 * Payment Method Selector JavaScript
 * Handles the selection behavior for payment method options
 */

document.addEventListener('DOMContentLoaded', function () {
  // Handle payment method selection
  const paymentMethodOptions = document.querySelectorAll('.growfund-payment-method-option');

  paymentMethodOptions.forEach((option) => {
    const radio = option.querySelector('.growfund-payment-method-radio');

    // Handle click on the label (option)
    option.addEventListener('click', function (e) {
      // Prevent double triggering when clicking directly on radio
      if (e.target.type === 'radio') return;

      // Check the radio button
      radio.checked = true;

      // Update visual state
      updatePaymentMethodSelection(radio);
    });

    // Handle direct radio button change
    radio.addEventListener('change', function () {
      updatePaymentMethodSelection(this);
    });
  });

  function updatePaymentMethodSelection(selectedRadio) {
    // Remove selected class from all options
    paymentMethodOptions.forEach((opt) => {
      opt.classList.remove('selected');
    });

    // Add selected class to the current option
    const selectedOption = selectedRadio.closest('.growfund-payment-method-option');
    if (selectedOption) {
      selectedOption.classList.add('selected');
    }
  }
});

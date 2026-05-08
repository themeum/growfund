document.addEventListener('DOMContentLoaded', function () {
  const isBillingAddressSame = document.getElementById('is_billing_address_same');
  const billingAddressSection = document.getElementById('billing_address_section');

  isBillingAddressSame.addEventListener('change', function () {
    if (isBillingAddressSame.checked) {
      billingAddressSection.style.display = 'none';
    } else {
      billingAddressSection.style.display = 'table';
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const bonusInput = document.querySelector('.growfund-input-bonus-support');
  const totalSpan = document.getElementById('growfund-order-total-amount');
  const totalInput = document.getElementById('total_amount_input');
  const bonusHiddenInput = document.getElementById('bonus_support_amount_input');

  if (!bonusInput || !totalSpan) return;

  // Get shipping and reward price from data attributes on the total span
  const shipping = parseFloat(totalSpan.getAttribute('data-shipping')) || 0;
  const rewardPrice = parseFloat(totalSpan.getAttribute('data-reward-price')) || 0;

  function updateTotal() {
    let bonus = parseFloat(bonusInput.value) || 0;
    let total = bonus + shipping + rewardPrice;
    totalSpan.textContent = total.toFixed(2);

    // Update the hidden input fields for form submission
    if (totalInput) {
      totalInput.value = total.toFixed(2);
    }
    if (bonusHiddenInput) {
      bonusHiddenInput.value = bonus.toFixed(2);
    }
  }

  function formatBonusInput() {
    const value = parseFloat(bonusInput.value) || 0;
    if (value >= 0) {
      bonusInput.value = value.toFixed(2);
    }
  }

  bonusInput.addEventListener('input', updateTotal);
  bonusInput.addEventListener('blur', () => {
    formatBonusInput();
    updateTotal();
  });
  bonusInput.addEventListener('change', () => {
    formatBonusInput();
    updateTotal();
  });

  // Initialize formatting
  formatBonusInput();
});

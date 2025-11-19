/**
 * Pledge Modal JavaScript
 * Handles pledge modal functionality including opening/closing, form handling, and navigation
 */

document.addEventListener('DOMContentLoaded', () => {
  const pledgeModal = document.getElementById('growfund-pledge-modal');
  const openPledgeButtons = document.querySelectorAll('[data-action="open-pledge-modal"]');
  const closePledgeButton = document.getElementById('growfund-pledge-modal-close');
  const overlay = pledgeModal ? pledgeModal.querySelector('.growfund-pledge-modal__overlay') : null;
  const pledgeAmountInput = document.getElementById('growfund-pledge-amount-input');
  const pledgeNoRewardBtn = document.getElementById('growfund-pledge-no-reward-btn');

  if (!pledgeModal) {
    return;
  }

  let lastFocusedElement = null;

  /**
   * Open the pledge modal
   */
  function openPledgeModal() {
    lastFocusedElement = document.activeElement;

    pledgeModal.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    const modalContent = pledgeModal.querySelector('.growfund-pledge-modal__content');
    if (modalContent) {
      modalContent.setAttribute('tabindex', '-1');
      modalContent.focus();
    }

    setTimeout(() => {
      if (pledgeAmountInput) {
        pledgeAmountInput.focus();
        pledgeAmountInput.select();
      }
    }, 100);
  }

  /**
   * Close the pledge modal
   */
  function closePledgeModal() {
    pledgeModal.classList.add('is-closing');

    setTimeout(() => {
      pledgeModal.classList.remove('is-open', 'is-closing');
      document.body.style.overflow = '';

      if (lastFocusedElement) {
        lastFocusedElement.focus();
        lastFocusedElement = null;
      }
    }, 300);
  }

  /**
   * Update pledge button text based on input value
   */
  function updatePledgeButtonText() {
    if (!pledgeAmountInput || !pledgeNoRewardBtn) return;

    const amount = parseFloat(pledgeAmountInput.value) || 0;
    const minAmount = parseFloat(pledgeAmountInput.dataset.minAmount) || 1;
    const maxAmount = parseFloat(pledgeAmountInput.dataset.maxAmount);
    const displayAmount = Math.max(amount, minAmount);

    const formattedAmount = displayAmount.toFixed(2);

    const currencySymbol = pledgeAmountInput.dataset.currencySymbol || '$';
    pledgeNoRewardBtn.textContent = `Pledge ${currencySymbol}${formattedAmount}`;

    if (amount >= minAmount && (isNaN(maxAmount) || amount <= maxAmount)) {
      pledgeNoRewardBtn.removeAttribute('disabled');
    } else {
      pledgeNoRewardBtn.setAttribute('disabled', 'true');
    }

    pledgeNoRewardBtn.dataset.amount = formattedAmount;
  }

  /**
   * Format input value to always show 2 decimal places
   */
  function formatInputValue() {
    if (!pledgeAmountInput) return;

    const value = parseFloat(pledgeAmountInput.value) || 0;
    if (value > 0) {
      pledgeAmountInput.value = value.toFixed(2);
    }
  }

  /**
   * Handle pledge without reward
   */
  function handlePledgeNoReward() {
    if (!pledgeNoRewardBtn || !pledgeAmountInput) return;

    const amount = parseFloat(pledgeAmountInput.value) || 0;
    const minAmount = parseFloat(pledgeAmountInput.dataset.minAmount) || 1;
    const campaignId = pledgeNoRewardBtn.dataset.campaignId;
    const checkoutBaseUrl = pledgeNoRewardBtn.dataset.checkoutUrl;

    if (amount <= 0) {
      const currencySymbol = pledgeAmountInput.dataset.currencySymbol || '$';
      alert(`Please enter an amount greater than ${currencySymbol}0`);
      pledgeAmountInput.focus();
      return;
    }

    if (amount < minAmount) {
      const currencySymbol = pledgeAmountInput.dataset.currencySymbol || '$';
      alert(`Minimum pledge amount is ${currencySymbol}${minAmount}`);
      pledgeAmountInput.focus();
      return;
    }

    if (checkoutBaseUrl && checkoutBaseUrl !== '#') {
      const url = new URL(checkoutBaseUrl, window.location.origin);
      url.searchParams.set('amount', amount);
      window.location.href = url.toString();
    }
  }

  /**
   * Handle reward selection (clicks on reward pledge buttons)
   */
  function handleRewardSelection(event) {
    const button = event.target.closest('.growfund-btn--reward');
    if (!button) return;

    const rewardCard = button.closest('.growfund-campaign-reward');
    if (!rewardCard) return;

    const checkoutUrl = button.getAttribute('href');

    if (checkoutUrl && checkoutUrl !== '#') {
      event.preventDefault();
      window.location.href = checkoutUrl;
    }
  }

  /**
   * Handle keyboard navigation for accessibility
   */
  function handleKeyDown(event) {
    if (!pledgeModal.classList.contains('is-open')) return;

    switch (event.key) {
      case 'Escape':
        event.preventDefault();
        closePledgeModal();
        break;
      case 'Tab':
        trapFocus(event);
        break;
    }
  }

  /**
   * Trap focus within the modal for accessibility
   */
  function trapFocus(event) {
    const focusableElements = pledgeModal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
    );
    const firstFocusableElement = focusableElements[0];
    const lastFocusableElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey) {
      if (document.activeElement === firstFocusableElement) {
        lastFocusableElement.focus();
        event.preventDefault();
      }
    } else {
      if (document.activeElement === lastFocusableElement) {
        firstFocusableElement.focus();
        event.preventDefault();
      }
    }
  }

  openPledgeButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();

      const pledgeAmount = button.getAttribute('data-pledge-amount');
      if (pledgeAmount && pledgeAmountInput) {
        pledgeAmountInput.value = parseFloat(pledgeAmount).toFixed(2);
        updatePledgeButtonText();
      }

      openPledgeModal();
    });
  });

  if (closePledgeButton) {
    closePledgeButton.addEventListener('click', closePledgeModal);
  }

  if (overlay) {
    overlay.addEventListener('click', closePledgeModal);
  }

  document.addEventListener('keydown', handleKeyDown);

  if (pledgeAmountInput) {
    pledgeAmountInput.addEventListener('input', updatePledgeButtonText);
    pledgeAmountInput.addEventListener('blur', () => {
      formatInputValue();
      updatePledgeButtonText();
    });
    pledgeAmountInput.addEventListener('change', () => {
      formatInputValue();
      updatePledgeButtonText();
    });

    formatInputValue();
    updatePledgeButtonText();
  }

  if (pledgeNoRewardBtn) {
    pledgeNoRewardBtn.addEventListener('click', (event) => {
      event.preventDefault();
      handlePledgeNoReward();
    });
  }

  if (pledgeModal) {
    pledgeModal.addEventListener('click', handleRewardSelection);
  }

  const modalContent = pledgeModal.querySelector('.growfund-pledge-modal__content');
  if (modalContent) {
    modalContent.addEventListener('click', (event) => {
      event.stopPropagation();
    });
  }

  window.growfund = window.growfund || {};
  window.growfund.openPledgeModal = openPledgeModal;
  window.growfund.closePledgeModal = closePledgeModal;
});

/**
 * Initialize pledge modal when new content is loaded dynamically
 * This function can be called when content is loaded via AJAX
 */
function initializePledgeModalTriggers() {
  const newOpenButtons = document.querySelectorAll(
    '[data-action="open-pledge-modal"]:not([data-pledge-modal-initialized])',
  );

  newOpenButtons.forEach((button) => {
    button.setAttribute('data-pledge-modal-initialized', 'true');
    button.addEventListener('click', (event) => {
      event.preventDefault();
      if (window.growfund && window.growfund.openPledgeModal) {
        window.growfund.openPledgeModal();
      }
    });
  });
}

window.initializePledgeModalTriggers = initializePledgeModalTriggers;

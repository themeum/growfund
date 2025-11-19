/**
 * Support Section JavaScript
 * Handles pledge input functionality and continue button visibility
 */

class SupportSection {
  constructor() {
    this.pledgeInput = null;
    this.continueButton = null;
    this.init();
  }

  init() {
    this.pledgeInput = document.getElementById('pledge-amount');
    this.continueButton = document.querySelector('.growfund-pledge-continue');

    if (!this.pledgeInput || !this.continueButton) {
      return;
    }

    this.setupEventListeners();
    this.initializeButtonState();
  }

  setupEventListeners() {
    this.pledgeInput.addEventListener('input', () => this.toggleContinueButton());
    this.pledgeInput.addEventListener('change', () => this.toggleContinueButton());
    this.pledgeInput.addEventListener('blur', () => this.toggleContinueButton());

    this.continueButton.addEventListener('click', (event) => this.handleDirectCheckout(event));
  }

  toggleContinueButton() {
    const value = parseFloat(this.pledgeInput.value) || 0;

    if (value > 0) {
      this.continueButton.style.display = 'block';
      const buttonElement = this.continueButton.querySelector('button, a');
      if (buttonElement) {
        buttonElement.setAttribute('data-pledge-amount', value.toFixed(2));
      }
    } else {
      this.continueButton.style.display = 'none';
    }
  }

  handleDirectCheckout(event) {
    const button = event.target.closest('button, a');
    if (!button) return;

    const action = button.getAttribute('data-action');
    if (action === 'direct-checkout') {
      event.preventDefault();

      const amount = parseFloat(button.getAttribute('data-pledge-amount')) || 0;
      if (amount <= 0) {
        alert('Please enter a valid amount');
        return;
      }

      const checkoutUrl = button.getAttribute('data-checkout-url');
      if (checkoutUrl && checkoutUrl !== '#') {
        const url = new URL(checkoutUrl, window.location.origin);
        url.searchParams.set('amount', amount.toFixed(2));
        window.location.href = url.toString();
      } else {
        console.error('Checkout URL not found');
      }
    }
  }

  initializeButtonState() {
    this.toggleContinueButton();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new SupportSection();
});

window.SupportSection = SupportSection;

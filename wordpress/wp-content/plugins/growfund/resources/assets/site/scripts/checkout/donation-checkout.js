/**
 * Donation Checkout JavaScript
 */

const CONFIG = {
  DEFAULT_AMOUNT: 30,
  MIN_AMOUNT: 1,
  MAX_AMOUNT: 10000000,
  DEBOUNCE_DELAY: 300,
};

const Utils = {
  sanitizeInput(input) {
    if (typeof input !== 'string') return '';
    return input.replace(/[<>]/g, '').trim();
  },

  validateAmount(amount) {
    const num = parseFloat(amount);
    const customAmountInput = document.getElementById('customAmount');

    let minAmount = CONFIG.MIN_AMOUNT;
    let maxAmount = CONFIG.MAX_AMOUNT;

    if (customAmountInput) {
      const dataMin = customAmountInput.getAttribute('data-min-amount');
      const dataMax = customAmountInput.getAttribute('data-max-amount');

      if (dataMin) minAmount = parseFloat(dataMin);
      if (dataMax) maxAmount = parseFloat(dataMax);
    }

    if (isNaN(num)) {
      return null;
    }

    if (num < minAmount) {
      return null;
    }

    if (num > maxAmount) {
      return null;
    }

    return num;
  },

  validateEmail(email) {
    const emailRegex =
      /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    return emailRegex.test(email);
  },

  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  safeQuerySelector(selector) {
    try {
      const element = document.querySelector(selector);
      if (!element) {
        return null;
      }
      return element;
    } catch (error) {
      console.error(`Error querying selector ${selector}:`, error);
      return null;
    }
  },

  safeQuerySelectorAll(selector) {
    try {
      const elements = document.querySelectorAll(selector);
      if (!elements || elements.length === 0) {
        return [];
      }
      return Array.from(elements);
    } catch (error) {
      console.error(`Error querying selector ${selector}:`, error);
      return [];
    }
  },

  safeGetElementById(id, suppressWarning = false) {
    try {
      const element = document.getElementById(id);
      if (!element) {
        return null;
      }
      return element;
    } catch (error) {
      console.error(`Error getting element by ID ${id}:`, error);
      return null;
    }
  },
};

class DonationCheckout {
  constructor() {
    this.elements = {};
    this.currentAmount = CONFIG.DEFAULT_AMOUNT;
    this.isCustomAmountVisible = false;
    this.selectedPaymentMethod = null;
    this.isTributeEnabled = false;
    this.tributeRequirement = null;
    this.tributeNotificationType = null;
    this.isFormSubmitted = false;
    this.resizeObserver = null;
    this.resizeTimeout = null;
    this.init();
  }

  init() {
    if (this.isInitialized) {
      return;
    }

    try {
      this.elements = this.initializeElements();

      this.handleAmountButtonClick = this.handleAmountButtonClick.bind(this);
      this.handleCustomAmountInput = this.handleCustomAmountInput.bind(this);
      this.handlePaymentOptionClick = this.handlePaymentOptionClick.bind(this);
      this.handleDedicateCheckboxChange = this.handleDedicateCheckboxChange.bind(this);
      this.handleFormSubmit = this.handleFormSubmit.bind(this);

      this.bindEvents();

      setTimeout(() => {
        this.initializeDefaultState();
        this.initializePaymentMethodContent();
        this.setupResizeObserver();
        this.initializeLocationDropdowns();
      }, 100);

      this.isInitialized = true;
    } catch (error) {
      console.error('Error initializing donation checkout:', error);
    }
  }

  initializeElements() {
    const elements = {
      amountButtons: document.querySelectorAll(
        '.growfund-donation-checkout__amount-buttons .growfund-btn[data-amount]',
      ),
      customAmountInput: Utils.safeGetElementById('customAmount'),
      selectedAmountDisplay: Utils.safeGetElementById('selectedAmount'),
      donationAmountInput: Utils.safeGetElementById('donationAmount'),
      summaryAmountDisplay: Utils.safeGetElementById('summaryAmount'),
      totalAmountDisplay: Utils.safeGetElementById('summaryTotal'),
      donateForm: Utils.safeGetElementById('donationForm'),
      dedicateCheckbox: Utils.safeGetElementById('dedicateDonation'),
      tributeSection: Utils.safeQuerySelector('.growfund-tribute-content'),
      paymentOptions: document.querySelectorAll('.growfund-payment-option'),
      paymentMethodRadios: document.querySelectorAll('.growfund-payment-method-radio'),
      paymentMethodContent: Utils.safeQuerySelector('.growfund-payment-method-content'),
    };

    return elements;
  }

  bindEvents() {
    document.addEventListener('click', (e) => {
      if (e.target.matches('.growfund-btn[data-amount]')) {
        this.handleAmountButtonClick(e.target);
      }
      if (e.target.closest('.growfund-payment-option')) {
        this.handlePaymentOptionClick(e.target.closest('.growfund-payment-option'));
      }
    });

    const amountInputContent = document.querySelector(
      '.growfund-donation-checkout__amount-input-content',
    );
    if (amountInputContent) {
      amountInputContent.addEventListener('click', this.handleAmountInputClick.bind(this));
    }

    if (this.elements.customAmountInput) {
      const debouncedHandler = Utils.debounce(
        this.handleCustomAmountInputEnhanced.bind(this),
        CONFIG.DEBOUNCE_DELAY,
      );
      this.elements.customAmountInput.addEventListener('input', debouncedHandler);

      this.elements.customAmountInput.addEventListener('focus', this.handleInputFocus.bind(this));
      this.elements.customAmountInput.addEventListener('blur', this.handleInputBlur.bind(this));

      this.elements.customAmountInput.addEventListener('change', (e) => {
        const amount = parseFloat(e.target.value) || 0;
        if (amount >= 1) {
          this.updateAmount(amount);
          this.currentAmount = amount;
        }
      });
    }

    if (this.elements.dedicateCheckbox) {
      this.elements.dedicateCheckbox.addEventListener(
        'change',
        this.handleDedicateCheckboxChange.bind(this),
      );
    }

    const tributeNotificationTypeSelect = Utils.safeGetElementById('tributeNotificationType', true);
    if (tributeNotificationTypeSelect) {
      tributeNotificationTypeSelect.addEventListener(
        'change',
        this.handleTributeNotificationTypeChange.bind(this),
      );

      tributeNotificationTypeSelect.addEventListener('change', () => {
        if (tributeNotificationTypeSelect.value) {
          tributeNotificationTypeSelect.style.borderColor = '';
          tributeNotificationTypeSelect.style.boxShadow = '';
        }
      });
    }

    if (this.elements.donateForm) {
      this.elements.donateForm.addEventListener('submit', this.handleFormSubmit.bind(this));
    }

    if (this.elements.paymentMethodRadios.length > 0) {
      this.elements.paymentMethodRadios.forEach((radio) => {
        radio.addEventListener('change', this.handlePaymentMethodChange.bind(this));
      });
    }
  }

  initializeDefaultState() {
    try {
      this.currentAmount = this.getDefaultAmountFromDOM();

      const amountInDollars = this.currentAmount;

      this.updateAmount(amountInDollars);

      const defaultButton = Utils.safeQuerySelector(`[data-amount="${this.currentAmount}"]`);
      if (defaultButton) {
        const allButtons = Utils.safeQuerySelectorAll(
          '.growfund-donation-checkout__amount-buttons .growfund-btn',
        );
        allButtons.forEach((btn) => btn.classList.remove('growfund-btn--primary'));

        defaultButton.classList.add('growfund-btn--primary');

        const formattedCurrency = defaultButton.dataset.formatted;
        if (formattedCurrency) {
          this.updateAmount(amountInDollars, formattedCurrency);
        }
      } else {
        const selectedButton = Utils.safeQuerySelector(
          '.growfund-donation-checkout__amount-buttons .growfund-btn--primary',
        );
        if (selectedButton) {
          const amount = parseFloat(selectedButton.dataset.amount);
          const formattedCurrency = selectedButton.dataset.formatted;
          if (!isNaN(amount)) {
            this.currentAmount = amount;
            this.updateAmount(amount, formattedCurrency);
          }
        }
      }

      this.initializeTributeSection();

      if (this.elements.dedicateCheckbox) {
        const isRequired =
          this.elements.dedicateCheckbox
            .closest('.growfund-dedicate-checkbox-wrapper')
            ?.getAttribute('data-requirement') === 'required';

        if (!isRequired) {
          this.elements.dedicateCheckbox.checked = false;
        }
      }

      this.initializePaymentMethodContent();

      setTimeout(() => {
        this.ensureDefaultNotificationType();
      }, 100);

      this.setupFormValidation();
    } catch (error) {
      console.error('Error initializing default state:', error);
    }
  }

  setupFormValidation() {
    const requiredFields = [{ id: 'fund', name: 'Library selection' }];

    // Check if fund element exists
    const fundElement = document.getElementById('fund');

    if (fundElement) {
      // Fund field exists (donor-decide mode), set up validation
      fundElement.setAttribute('required', 'required');
      fundElement.setAttribute('aria-required', 'true');

      requiredFields.forEach((field) => {
        const element = document.getElementById(field.id);
        if (element) {
          element.addEventListener('blur', () => {
            this.validateField(element, field.name);
          });

          element.addEventListener('input', () => {
            this.validateField(element, field.name);
          });
        }
      });
    }

    const amountFields = ['customAmount', 'donationAmount'];
    amountFields.forEach((fieldId) => {
      const element = document.getElementById(fieldId);
      if (element) {
        element.addEventListener('blur', () => {
          this.validateAmountField(element);
        });
      }
    });

    this.setupTributeFieldValidation();
  }

  setupTributeFieldValidation() {
    if (!this.elements.tributeSection) return;

    const tributeFields = this.elements.tributeSection.querySelectorAll(
      '[data-tribute-required="true"]',
    );

    tributeFields.forEach((field) => {
      field.addEventListener('input', () => {
        if (field.classList.contains('growfund-form-field--error')) {
          field.classList.remove('growfund-form-field--error');
          const errorMessage = field.parentNode.querySelector('.growfund-form-error-message');
          if (errorMessage) {
            errorMessage.remove();
          }
        }
      });

      field.addEventListener('focus', () => {
        if (field.classList.contains('growfund-form-field--error')) {
          field.classList.remove('growfund-form-field--error');
          const errorMessage = field.parentNode.querySelector('.growfund-form-error-message');
          if (errorMessage) {
            errorMessage.remove();
          }
        }
      });
    });
  }

  validateAmountField(element) {
    const amount = Utils.validateAmount(element.value);
    if (!amount) {
      element.style.borderColor = '#ef4444';
      element.setAttribute('aria-invalid', 'true');
    } else {
      element.style.borderColor = '';
      element.removeAttribute('aria-invalid');
    }
  }

  validateField(element, fieldName) {
    const value = element.value.trim();
    if (!value) {
      element.style.borderColor = '#ef4444';
      element.setAttribute('aria-invalid', 'true');
    } else if (fieldName === 'Email address' && !Utils.validateEmail(value)) {
      element.style.borderColor = '#ef4444';
      element.setAttribute('aria-invalid', 'true');
    } else {
      element.style.borderColor = '';
      element.removeAttribute('aria-invalid');
    }
  }

  initializeTributeSection() {
    if (!this.elements.tributeSection) return;

    const isRequired =
      this.elements.tributeSection
        .closest('.growfund-dedicate-checkbox-wrapper')
        ?.getAttribute('data-requirement') === 'required';

    if (isRequired) {
      this.elements.tributeSection.classList.remove('growfund-tribute-hidden');
      this.elements.tributeSection.style.height = 'auto';
      this.elements.tributeSection.style.overflow = 'visible';

      if (this.elements.dedicateCheckbox) {
        this.elements.dedicateCheckbox.checked = true;

        this.elements.dedicateCheckbox.setAttribute('data-required', 'true');
        this.elements.dedicateCheckbox.style.cursor = 'not-allowed';

        const checkboxWrapper = this.elements.dedicateCheckbox.closest(
          '.growfund-dedicate-checkbox-wrapper',
        );
        if (checkboxWrapper) {
          checkboxWrapper.setAttribute('title', 'Tribute is required for this donation');
        }
      }

      this.toggleTributeRequirements(true);
    } else {
      this.elements.tributeSection.classList.add('growfund-tribute-hidden');
      this.elements.tributeSection.style.height = '0px';
      this.elements.tributeSection.style.overflow = 'hidden';

      if (this.elements.dedicateCheckbox) {
        this.elements.dedicateCheckbox.checked = false;
      }

      this.toggleTributeRequirements(false);
    }

    this.initializeTributeNotificationFields();
  }

  initializeTributeNotificationFields() {
    const tributeNotificationTypeSelect = Utils.safeGetElementById('tributeNotificationType', true);

    if (tributeNotificationTypeSelect) {
      if (!tributeNotificationTypeSelect.value) {
        tributeNotificationTypeSelect.value = 'send-ecard';
      }

      this.handleTributeNotificationTypeChange({ target: tributeNotificationTypeSelect });
    } else {
      const ecardFields = document.querySelectorAll('.growfund-ecard-fields');
      const postmailFields = document.querySelectorAll('.growfund-postmail-fields');

      ecardFields.forEach((field) => {
        field.classList.add('show');
      });

      postmailFields.forEach((field) => {
        field.classList.add('show');
      });
    }

    setTimeout(() => {
      this.setupLocationDropdowns();
    }, 100);
  }

  handleTributeNotificationTypeChange(event) {
    const selectedValue = event.target.value;

    const ecardFields = document.querySelectorAll('.growfund-ecard-fields');
    const postmailFields = document.querySelectorAll('.growfund-postmail-fields');

    ecardFields.forEach((field) => {
      field.classList.remove('show');
      const inputs = field.querySelectorAll('input[required], textarea[required]');
      inputs.forEach((input) => {
        input.removeAttribute('required');
        input.classList.add('growfund-field-hidden');
        input.disabled = true;
      });
    });

    postmailFields.forEach((field) => {
      field.classList.remove('show');
      const inputs = field.querySelectorAll('input[required], textarea[required]');
      inputs.forEach((input) => {
        input.removeAttribute('required');
        input.classList.add('growfund-field-hidden');
        input.disabled = true;
      });
    });

    switch (selectedValue) {
      case 'send-ecard':
        ecardFields.forEach((field) => {
          setTimeout(() => {
            field.classList.add('show');
          }, 10);
          const inputs = field.querySelectorAll('input, textarea');
          inputs.forEach((input) => {
            if (input.classList.contains('growfund-field-hidden')) {
              input.setAttribute('required', 'required');
              input.classList.remove('growfund-field-hidden');
              input.disabled = false;
            }
          });
        });
        break;

      case 'send-post-mail':
        postmailFields.forEach((field) => {
          setTimeout(() => {
            field.classList.add('show');
          }, 10);
          const inputs = field.querySelectorAll('input, textarea');
          inputs.forEach((input) => {
            if (input.classList.contains('growfund-field-hidden')) {
              input.setAttribute('required', 'required');
              input.classList.remove('growfund-field-hidden');
              input.disabled = false;
            }
          });
        });

        setTimeout(() => {
          this.setupLocationDropdowns();
        }, 150);
        break;

      case 'send-ecard-and-post-mail':
        ecardFields.forEach((field) => {
          setTimeout(() => {
            field.classList.add('show');
          }, 10);
          const inputs = field.querySelectorAll('input, textarea');
          inputs.forEach((input) => {
            if (input.classList.contains('growfund-field-hidden')) {
              input.setAttribute('required', 'required');
              input.classList.remove('growfund-field-hidden');
              input.disabled = false;
            }
          });
        });
        postmailFields.forEach((field) => {
          setTimeout(() => {
            field.classList.add('show');
          }, 10);
          const inputs = field.querySelectorAll('input, textarea');
          inputs.forEach((input) => {
            if (input.classList.contains('growfund-field-hidden')) {
              input.setAttribute('required', 'required');
              input.classList.remove('growfund-field-hidden');
              input.disabled = false;
            }
          });
        });

        setTimeout(() => {
          this.setupLocationDropdowns();
        }, 200);
        break;

      default:
        break;
    }

    const delay = selectedValue === 'send-ecard-and-post-mail' ? 200 : 100;
    setTimeout(() => {
      this.recalculateTributeHeight();
    }, delay);
  }

  recalculateTributeHeight() {
    if (
      !this.elements.tributeSection ||
      this.elements.tributeSection.classList.contains('growfund-tribute-hidden')
    ) {
      return;
    }

    setTimeout(() => {
      const newHeight = this.getTributeContentHeight();
      const currentHeight = parseInt(this.elements.tributeSection.style.height) || 0;

      if (Math.abs(newHeight - currentHeight) > 5) {
        this.elements.tributeSection.style.height = newHeight + 'px';

        setTimeout(() => {
          if (
            this.elements.tributeSection &&
            !this.elements.tributeSection.classList.contains('growfund-tribute-hidden')
          ) {
            this.elements.tributeSection.style.height = 'auto';
            this.elements.tributeSection.style.overflow = 'visible';
          }
        }, 300);
      }
    }, 150);
  }

  initializePaymentMethodContent() {
    try {
      const paymentContentSections = document.querySelectorAll(
        '.growfund-stripe-content, .growfund-paypal-content, .growfund-paddle-content, .growfund-cash-content',
      );
      paymentContentSections.forEach((section) => {
        section.style.display = 'none';
      });

      const stripeContent = document.querySelector('.growfund-stripe-content');
      if (stripeContent) {
        stripeContent.style.display = 'block';
      }
    } catch (error) {
      console.error('Error initializing payment method content:', error);
    }
  }

  handleAmountButtonClick(button) {
    try {
      this.elements.amountButtons.forEach((btn) => btn.classList.remove('growfund-btn--primary'));

      button.classList.add('growfund-btn--primary');

      const amountInDollars = parseFloat(button.dataset.amount);
      const formattedCurrency = button.dataset.formatted;

      this.updateAmount(amountInDollars, formattedCurrency);
    } catch (error) {
      console.error('Error handling amount button click:', error);
    }
  }

  handleAmountInputClick() {
    const amountInputContent = document.querySelector(
      '.growfund-donation-checkout__amount-input-content',
    );
    const customAmountInput = this.elements.customAmountInput;

    if (amountInputContent && customAmountInput) {
      amountInputContent.style.display = 'none';
      customAmountInput.style.display = 'block';
      customAmountInput.focus();
      customAmountInput.value = this.currentAmount || 30;
    }
  }

  handleInputFocus() {
    if (this.elements.customAmountInput) {
      const numericValue = this.currentAmount || 30;
      this.elements.customAmountInput.value = numericValue;

      this.elements.customAmountInput.style.borderColor = '';
    }
  }

  handleInputBlur() {
    const amountInputContent = document.querySelector(
      '.growfund-donation-checkout__amount-input-content',
    );
    const customAmountInput = this.elements.customAmountInput;

    if (amountInputContent && customAmountInput) {
      const amount = parseFloat(customAmountInput.value) || 0;

      if (amount >= 1) {
        const minAmount = customAmountInput.getAttribute('data-min-amount') || CONFIG.MIN_AMOUNT;
        const maxAmount = customAmountInput.getAttribute('data-max-amount') || CONFIG.MAX_AMOUNT;

        if (amount < parseFloat(minAmount)) {
          this.showError(`Amount must be at least ${toCurrency(minAmount)}`);
          customAmountInput.style.borderColor = '#dc2626';
          return;
        }

        if (amount > parseFloat(maxAmount)) {
          this.showError(`Amount cannot exceed ${toCurrency(maxAmount)}`);
          customAmountInput.style.borderColor = '#dc2626';
          return;
        }

        customAmountInput.style.borderColor = '';

        this.updateAmount(amount);
      }

      customAmountInput.style.display = 'none';
      amountInputContent.style.display = 'flex';
    }
  }

  handleCustomAmountInput() {
    try {
      if (!this.elements.customAmountInput) {
        return;
      }

      let inputValue = this.elements.customAmountInput.value;

      inputValue = inputValue.replace(/[^\d.]/g, '');

      const amount = Utils.validateAmount(inputValue);

      if (amount) {
        this.elements.amountButtons.forEach((btn) => btn.classList.remove('growfund-btn--primary'));

        this.updateAmount(amount);
      } else {
        const minAmount =
          this.elements.customAmountInput.getAttribute('data-min-amount') || CONFIG.MIN_AMOUNT;
        const maxAmount =
          this.elements.customAmountInput.getAttribute('data-max-amount') || CONFIG.MAX_AMOUNT;

        if (parseFloat(inputValue) < parseFloat(minAmount)) {
          this.showError(`Amount must be at least ${toCurrency(minAmount)}`);
        } else if (parseFloat(inputValue) > parseFloat(maxAmount)) {
          this.showError(`Amount cannot exceed ${toCurrency(maxAmount)}`);
        } else {
          this.showError('Please enter a valid amount');
        }
      }
    } catch (error) {
      console.error('Error handling custom amount input:', error);
    }
  }

  handleCustomAmountInputEnhanced() {
    try {
      if (!this.elements.customAmountInput) {
        return;
      }

      const amount = parseFloat(this.elements.customAmountInput.value) || 0;

      if (amount >= 1) {
        const minAmount =
          this.elements.customAmountInput.getAttribute('data-min-amount') || CONFIG.MIN_AMOUNT;
        const maxAmount =
          this.elements.customAmountInput.getAttribute('data-max-amount') || CONFIG.MAX_AMOUNT;

        if (amount < parseFloat(minAmount)) {
          this.showError(`Amount must be at least ${toCurrency(minAmount)}`);
          return;
        }

        if (amount > parseFloat(maxAmount)) {
          this.showError(`Amount cannot exceed ${toCurrency(maxAmount)}`);
          return;
        }

        this.elements.amountButtons.forEach((btn) => {
          btn.classList.remove('growfund-btn--primary');
        });

        this.updateAmount(amount);
      }
    } catch (error) {
      console.error('Error handling enhanced custom amount input:', error);
    }
  }

  handlePaymentOptionClick(option) {
    try {
      this.elements.paymentOptions.forEach((opt) => opt.classList.remove('selected'));

      option.classList.add('selected');

      const radio = option.querySelector('input[type="radio"]');
      if (radio) {
        radio.checked = true;
      }
    } catch (error) {
      console.error('Error handling payment option click:', error);
    }
  }

  handlePaymentMethodChange(event) {
    try {
      const selectedMethod = event.target.value;

      const paymentContentSections = document.querySelectorAll(
        '.growfund-stripe-content, .growfund-paypal-content, .growfund-paddle-content, .growfund-cash-content',
      );
      paymentContentSections.forEach((section) => {
        section.style.display = 'none';
      });

      const selectedContent = document.querySelector(`.growfund-${selectedMethod}-content`);
      if (selectedContent) {
        selectedContent.style.display = 'block';
      }
    } catch (error) {
      console.error('Error handling payment method change:', error);
    }
  }

  handleDedicateCheckboxChange() {
    try {
      const isTributeRequired =
        this.elements.dedicateCheckbox
          ?.closest('.growfund-dedicate-checkbox-wrapper')
          ?.getAttribute('data-requirement') === 'required';

      if (isTributeRequired && !this.elements.dedicateCheckbox.checked) {
        this.elements.dedicateCheckbox.checked = true;

        this.showError(
          'A tribute is required for this donation. You cannot uncheck the tribute option.',
        );

        return;
      }

      if (this.elements.dedicateCheckbox.checked) {
        this.showTributeContent();
        this.toggleTributeRequirements(true);
      } else {
        this.hideTributeContent();
        this.toggleTributeRequirements(false);
      }
    } catch (error) {
      console.error('Error handling dedicate checkbox change:', error);
    }
  }

  toggleTributeRequirements(isRequired) {
    if (!this.elements.tributeSection) return;

    const tributeFields = this.elements.tributeSection.querySelectorAll(
      '[data-tribute-required="true"]',
    );

    tributeFields.forEach((field) => {
      if (isRequired) {
        field.setAttribute('required', 'required');
        field.classList.add('growfund-tribute-field-required');
      } else {
        field.removeAttribute('required');
        field.classList.remove('growfund-tribute-field-required');
      }
    });
  }

  showTributeContent() {
    if (!this.elements.tributeSection) {
      console.error('tributeSection not found');
      return;
    }

    this.elements.tributeSection.classList.remove('growfund-tribute-hidden');

    const contentHeight = this.getTributeContentHeight();

    this.elements.tributeSection.style.height = '0px';
    this.elements.tributeSection.style.overflow = 'hidden';

    requestAnimationFrame(() => {
      this.elements.tributeSection.style.height = contentHeight + 'px';
    });

    this.toggleTributeRequirements(true);

    this.initializeTributeNotificationFields();

    setTimeout(() => {
      this.setupLocationDropdowns();
    }, 100);

    setTimeout(() => {
      if (this.elements.tributeSection) {
        this.elements.tributeSection.style.height = 'auto';
        this.elements.tributeSection.style.overflow = 'visible';
        this.elements.tributeSection.style.minHeight = 'auto';

        this.ensureDefaultNotificationType();
      }
    }, 300);
  }

  hideTributeContent() {
    if (!this.elements.tributeSection) {
      console.error('tributeSection not found');
      return;
    }

    const currentHeight = this.elements.tributeSection.scrollHeight;

    this.elements.tributeSection.style.height = currentHeight + 'px';
    this.elements.tributeSection.style.overflow = 'hidden';

    this.elements.tributeSection.offsetHeight;

    requestAnimationFrame(() => {
      if (this.elements.tributeSection) {
        this.elements.tributeSection.style.height = '0px';
      }
    });

    setTimeout(() => {
      if (this.elements.tributeSection) {
        this.elements.tributeSection.classList.add('growfund-tribute-hidden');
        this.elements.tributeSection.style.height = '';
        this.elements.tributeSection.style.overflow = '';
        this.elements.tributeSection.style.minHeight = '';
      }
    }, 300);

    this.toggleTributeRequirements(false);
    this.clearTributeFields();

    this.clearLocationDropdowns();

    const ecardFields = document.querySelectorAll('.growfund-ecard-fields');
    const postmailFields = document.querySelectorAll('.growfund-postmail-fields');

    ecardFields.forEach((field) => {
      field.classList.remove('show');
    });

    postmailFields.forEach((field) => {
      field.classList.remove('show');
    });
  }
  getTributeContentHeight() {
    if (!this.elements.tributeSection) {
      console.error('tributeSection not found in getTributeContentHeight');
      return 0;
    }

    const originalDisplay = this.elements.tributeSection.style.display;
    const originalPosition = this.elements.tributeSection.style.position;
    const originalVisibility = this.elements.tributeSection.style.visibility;
    const originalHeight = this.elements.tributeSection.style.height;
    const originalOverflow = this.elements.tributeSection.style.overflow;
    const originalMaxHeight = this.elements.tributeSection.style.maxHeight;
    const originalMinHeight = this.elements.tributeSection.style.minHeight;

    this.elements.tributeSection.style.display = 'block';
    this.elements.tributeSection.style.position = 'absolute';
    this.elements.tributeSection.style.visibility = 'hidden';
    this.elements.tributeSection.style.height = 'auto';
    this.elements.tributeSection.style.overflow = 'visible';
    this.elements.tributeSection.style.maxHeight = 'none';
    this.elements.tributeSection.style.minHeight = 'auto';

    const ecardFields = this.elements.tributeSection.querySelectorAll('.growfund-ecard-fields');
    const postmailFields = this.elements.tributeSection.querySelectorAll('.growfund-postmail-fields');

    const originalEcardStates = Array.from(ecardFields).map((field) => ({
      display: field.style.display,
      opacity: field.style.opacity,
      maxHeight: field.style.maxHeight,
      classList: field.classList.contains('show'),
    }));

    const originalPostmailStates = Array.from(postmailFields).map((field) => ({
      display: field.style.display,
      opacity: field.style.opacity,
      maxHeight: field.style.maxHeight,
      classList: field.classList.contains('show'),
    }));

    ecardFields.forEach((field, index) => {
      if (originalEcardStates[index].classList) {
        field.style.display = 'block';
        field.style.opacity = '1';
        field.style.maxHeight = 'none';
        field.style.overflow = 'visible';
      } else {
        field.style.display = 'none';
        field.style.opacity = '0';
        field.style.maxHeight = '0';
        field.style.overflow = 'hidden';
      }
    });

    postmailFields.forEach((field, index) => {
      if (originalPostmailStates[index].classList) {
        field.style.display = 'block';
        field.style.opacity = '1';
        field.style.maxHeight = 'none';
        field.style.overflow = 'visible';
      } else {
        field.style.display = 'none';
        field.style.opacity = '0';
        field.style.maxHeight = '0';
        field.style.overflow = 'hidden';
      }
    });

    this.elements.tributeSection.offsetHeight;

    const height = this.elements.tributeSection.scrollHeight;

    this.elements.tributeSection.style.display = originalDisplay;
    this.elements.tributeSection.style.position = originalPosition;
    this.elements.tributeSection.style.visibility = originalVisibility;
    this.elements.tributeSection.style.height = originalHeight;
    this.elements.tributeSection.style.overflow = originalOverflow;
    this.elements.tributeSection.style.maxHeight = originalMaxHeight;
    this.elements.tributeSection.style.minHeight = originalMinHeight;

    ecardFields.forEach((field, index) => {
      field.style.display = originalEcardStates[index].display;
      field.style.opacity = originalEcardStates[index].opacity;
      field.style.maxHeight = originalEcardStates[index].maxHeight;
      field.style.overflow = originalEcardStates[index].classList ? 'visible' : 'hidden';
    });

    postmailFields.forEach((field, index) => {
      field.style.display = originalPostmailStates[index].display;
      field.style.opacity = originalPostmailStates[index].opacity;
      field.style.maxHeight = originalPostmailStates[index].maxHeight;
      field.style.overflow = originalPostmailStates[index].classList ? 'visible' : 'hidden';
    });

    return height;
  }

  setupResizeObserver() {
    if (window.ResizeObserver && this.elements.tributeSection) {
      this.resizeObserver = new ResizeObserver((entries) => {
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
          entries.forEach((entry) => {
            if (
              entry.target === this.elements.tributeSection &&
              !this.elements.tributeSection.classList.contains('growfund-tribute-hidden') &&
              this.elements.tributeSection.style.height !== '0px'
            ) {
              const newHeight = this.getTributeContentHeight();
              const currentHeight = parseInt(this.elements.tributeSection.style.height) || 0;
              if (Math.abs(newHeight - currentHeight) > 2) {
                this.elements.tributeSection.style.height = newHeight + 'px';
              }
            }
          });
        }, 100);
      });

      this.resizeObserver.observe(this.elements.tributeSection);
    }

    window.addEventListener('resize', this.handleWindowResize.bind(this));
  }

  handleWindowResize() {
    clearTimeout(this.resizeTimeout);
    this.resizeTimeout = setTimeout(() => {
      this.recalculateTributeHeight();
    }, 100);
  }

  destroy() {
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
      this.resizeObserver = null;
    }

    if (this.resizeTimeout) {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = null;
    }

    window.removeEventListener('resize', this.handleWindowResize.bind(this));

    this.elements = {};
    this.isInitialized = false;
  }

  clearTributeFields() {
    try {
      if (!this.elements.tributeSection) return;

      const tributeFields = this.elements.tributeSection.querySelectorAll(
        'input[type="text"], input[type="email"], input[type="tel"], textarea',
      );

      tributeFields.forEach((field) => {
        field.value = '';
      });

      const tributeNotificationTypeSelect = Utils.safeGetElementById(
        'tributeNotificationType',
        true,
      );
      if (tributeNotificationTypeSelect) {
        this.handleTributeNotificationTypeChange({ target: tributeNotificationTypeSelect });
      }

      this.clearLocationDropdowns();
    } catch (error) {
      console.error('Error clearing tribute fields:', error);
    }
  }

  updateAmount(amount, formattedCurrency = null) {
    try {
      const validatedAmount = Utils.validateAmount(amount);
      if (!validatedAmount) {
        return;
      }

      this.currentAmount = validatedAmount;

      if (this.elements.selectedAmountDisplay) {
        const formattedValue = toCurrency(validatedAmount);
        this.elements.selectedAmountDisplay.textContent = formattedValue;
      }

      if (this.elements.donationAmountInput) {
        this.elements.donationAmountInput.value = validatedAmount;
      }

      if (this.elements.summaryAmountDisplay) {
        this.elements.summaryAmountDisplay.textContent = formattedCurrency || validatedAmount;
      }

      if (this.elements.totalAmountDisplay) {
        this.elements.totalAmountDisplay.textContent = formattedCurrency || validatedAmount;
      }
    } catch (error) {
      console.error('Error updating amount:', error);
    }
  }

  handleFormSubmit(e) {
    try {
      const fundId = Utils.sanitizeInput(
        this.elements.donateForm.querySelector('#fund')?.value || '',
      );
      const amount = Utils.validateAmount(this.elements.donationAmountInput?.value);

      const missingFields = [];

      // Only validate fund selection if the field exists (donor-decide mode)
      const fundElement = document.getElementById('fund');
      if (fundElement && !fundId) {
        missingFields.push('Fund selection');
      }
      // If fund field doesn't exist (fixed mode), fund validation is handled server-side

      if (!amount) {
        const customAmountInput = document.getElementById('customAmount');
        let minAmount = CONFIG.MIN_AMOUNT;
        let maxAmount = CONFIG.MAX_AMOUNT;

        if (customAmountInput) {
          const dataMin = customAmountInput.getAttribute('data-min-amount');
          const dataMax = customAmountInput.getAttribute('data-max-amount');

          if (dataMin) minAmount = parseFloat(dataMin);
          if (dataMax) maxAmount = parseFloat(dataMax);
        }

        const currentAmount =
          this.elements.donationAmountInput?.value ||
          (this.elements.customAmountInput ? this.elements.customAmountInput.value : null);
        if (currentAmount) {
          const num = parseFloat(currentAmount);
          if (isNaN(num)) {
            missingFields.push('Donation amount (must be a valid number)');
          } else if (num < minAmount) {
            missingFields.push(`Donation amount (minimum is ${toCurrency(minAmount)})`);
          } else if (num > maxAmount) {
            missingFields.push(`Donation amount (maximum is ${toCurrency(maxAmount)})`);
          } else {
            missingFields.push('Donation amount (invalid amount)');
          }
        } else {
          missingFields.push(
            `Donation amount (must be between ${toCurrency(minAmount)} and ${toCurrency(
              maxAmount,
            )})`,
          );
        }
      }

      if (missingFields.length > 0) {
        e.preventDefault();
        this.showError(`Please fill in all required fields: ${missingFields.join(', ')}`);

        this.highlightMissingFields(missingFields);
        return;
      }

      const isTributeRequired =
        this.elements.dedicateCheckbox
          ?.closest('.growfund-dedicate-checkbox-wrapper')
          ?.getAttribute('data-requirement') === 'required';

      if (isTributeRequired && !this.elements.dedicateCheckbox?.checked) {
        e.preventDefault();
        this.showError(
          'A tribute is required for this donation. Please check the tribute checkbox.',
        );
        return;
      }

      if (this.elements.dedicateCheckbox?.checked || isTributeRequired) {
        const tributeFirstName = Utils.sanitizeInput(
          this.elements.donateForm.querySelector('#tributeFirstName')?.value || '',
        );
        const tributeLastName = Utils.sanitizeInput(
          this.elements.donateForm.querySelector('#tributeLastName')?.value || '',
        );
        const tributeEmail = Utils.sanitizeInput(
          this.elements.donateForm.querySelector('#tributeNotificationRecipientEmail')?.value || '',
        );
        const tributePhone = Utils.sanitizeInput(
          this.elements.donateForm.querySelector('#tributeNotificationRecipientPhone')?.value || '',
        );
        const tributeMessage = Utils.sanitizeInput(
          this.elements.donateForm.querySelector('#tributeMessage')?.value || '',
        );
        const tributeRecipientName = Utils.sanitizeInput(
          this.elements.donateForm.querySelector('#tributeNotificationRecipientName')?.value || '',
        );
        const tributeNotificationTypeSelect = this.elements.donateForm.querySelector(
          '#tributeNotificationType',
        );
        let tributeNotificationType = tributeNotificationTypeSelect
          ? Utils.sanitizeInput(tributeNotificationTypeSelect.value || '')
          : '';

        const missingTributeFields = [];

        const tributeType = this.elements.donateForm.querySelector(
          'input[name="tribute_type"]:checked',
        )?.value;
        if (!tributeType) missingTributeFields.push('Tribute type');

        if (!tributeFirstName) missingTributeFields.push('Tribute first name');
        if (!tributeLastName) missingTributeFields.push('Tribute last name');
        if (!tributeRecipientName) missingTributeFields.push('Recipient name');

        if (tributeNotificationTypeSelect && !tributeNotificationType) {
          missingTributeFields.push('Notification type');
        }

        if (
          tributeNotificationType === 'send-ecard' ||
          tributeNotificationType === 'send-ecard-and-post-mail'
        ) {
          if (!tributeEmail) missingTributeFields.push('Tribute email');
          if (!tributePhone) missingTributeFields.push('Tribute phone');
        }

        if (!tributeNotificationTypeSelect && !tributeNotificationType) {
          const hiddenNotificationType = this.elements.donateForm.querySelector(
            'input[name="tribute_notification_type"][type="hidden"]',
          )?.value;
          if (hiddenNotificationType) {
            tributeNotificationType = hiddenNotificationType;
          }
        }

        if (
          tributeNotificationType === 'send-ecard' ||
          tributeNotificationType === 'send-ecard-and-post-mail'
        ) {
          if (!tributeEmail) missingTributeFields.push('Tribute email');
          if (!tributePhone) missingTributeFields.push('Tribute phone');
        }

        if (
          tributeNotificationType === 'send-post-mail' ||
          tributeNotificationType === 'send-ecard-and-post-mail'
        ) {
          const tributeAddress = Utils.sanitizeInput(
            this.elements.donateForm.querySelector('#tributeNotificationRecipientAddress')?.value ||
              '',
          );
          const tributeCity = Utils.sanitizeInput(
            this.elements.donateForm.querySelector('#tributeNotificationRecipientCity')?.value ||
              '',
          );
          const tributeZipCode = Utils.sanitizeInput(
            this.elements.donateForm.querySelector('#tributeNotificationRecipientZipCode')?.value ||
              '',
          );
          const tributeCountry = Utils.sanitizeInput(
            this.elements.donateForm.querySelector('#tributeNotificationRecipientCountry')?.value ||
              '',
          );
          const tributeState = Utils.sanitizeInput(
            this.elements.donateForm.querySelector('#tributeNotificationRecipientState')?.value ||
              '',
          );

          if (!tributeAddress) missingTributeFields.push('Tribute address');
          if (!tributeCity) missingTributeFields.push('Tribute city');
          if (!tributeZipCode) missingTributeFields.push('Tribute ZIP code');
          if (!tributeCountry) missingTributeFields.push('Tribute country');
          if (!tributeState) missingTributeFields.push('Tribute state');
        }

        if (!tributeMessage) missingTributeFields.push('Tribute message');

        if (missingTributeFields.length > 0) {
          e.preventDefault();
          e.stopPropagation();
          this.showError(
            `Please fill in all required tribute fields: ${missingTributeFields.join(', ')}`,
          );

          this.highlightMissingFields(missingTributeFields);
          return false;
        }

        if (
          (tributeNotificationType === 'send-ecard' ||
            tributeNotificationType === 'send-ecard-and-post-mail') &&
          tributeEmail &&
          !Utils.validateEmail(tributeEmail)
        ) {
          e.preventDefault();
          this.showError('Please enter a valid email address for the tribute recipient.');
          return;
        }
      }
    } catch (error) {
      console.error('Error handling form submission:', error);
      e.preventDefault();
      this.showError('An error occurred. Please try again.');
    }
  }

  showError(message) {
    try {
      const errorContainer = document.querySelector('.growfund-error-message-container');
      if (errorContainer) {
        errorContainer.querySelector('.growfund-error-message-element').textContent = message;
        errorContainer.style.display = 'flex';

        setTimeout(() => {
          errorContainer.style.display = 'none';
        }, 5000);
      }
    } catch (error) {
      console.error('Error showing error message:', error);
    }
  }

  showDonationRange() {
    try {
      if (!this.elements.customAmountInput) {
        return;
      }

      const minAmount =
        this.elements.customAmountInput.getAttribute('data-min-amount') || CONFIG.MIN_AMOUNT;
      const maxAmount =
        this.elements.customAmountInput.getAttribute('data-max-amount') || CONFIG.MAX_AMOUNT;

      const message = `Please enter an amount between ${toCurrency(minAmount)} and ${toCurrency(
        maxAmount,
      )}`;
      this.showError(message);
    } catch (error) {
      console.error('Error showing donation range:', error);
    }
  }

  highlightMissingFields(missingFields) {
    this.clearFieldHighlights();

    const fieldSelectors = {
      'First name': '#firstName',
      'Last name': '#lastName',
      'Email address': '#email',
      'Phone number': '#phone',
      'Fund selection': '#fund',
      'Donation amount': '#customAmount, #donationAmount',
      'Tribute first name': '#tributeFirstName',
      'Tribute last name': '#tributeLastName',
      'Recipient name': '#tributeNotificationRecipientName',
      'Notification type': '#tributeNotificationType',
      'Tribute email': '#tributeNotificationRecipientEmail',
      'Tribute phone': '#tributeNotificationRecipientPhone',
      'Tribute address': '#tributeNotificationRecipientAddress',
      'Tribute city': '#tributeNotificationRecipientCity',
      'Tribute ZIP code': '#tributeNotificationRecipientZipCode',
      'Tribute country': '#tributeNotificationRecipientCountry',
      'Tribute state': '#tributeNotificationRecipientState',
      'Tribute message': '#tributeMessage',
    };

    if (!this.elements.donateForm.querySelector('#tributeNotificationType')) {
      delete fieldSelectors['Notification type'];
    }

    missingFields.forEach((fieldName) => {
      const selector = fieldSelectors[fieldName];
      if (selector) {
        const elements = this.elements.donateForm.querySelectorAll(selector);
        elements.forEach((element) => {
          let targetElement = element;
          if (element.classList.contains('growfund-dropdown')) {
            targetElement = element;
          } else {
            const dropdownWrapper = element.closest('.growfund-dropdown');
            if (dropdownWrapper) {
              targetElement = dropdownWrapper;
            }
          }

          targetElement.classList.add('growfund-form-field--error');

          if (targetElement.classList.contains('growfund-dropdown')) {
            targetElement.classList.add('growfund-dropdown--error');
          }

          const clearHighlight = () => {
            targetElement.classList.remove('growfund-form-field--error');
            targetElement.classList.remove('growfund-dropdown--error');
            element.removeEventListener('input', clearHighlight);
            element.removeEventListener('change', clearHighlight);
          };

          element.addEventListener('input', clearHighlight, { once: true });
          element.addEventListener('change', clearHighlight, { once: true });
        });
      }
    });
  }

  clearFieldHighlights() {
    const allInputs = this.elements.donateForm.querySelectorAll('input, select, textarea');
    allInputs.forEach((input) => {
      input.classList.remove('growfund-form-field--error');
    });

    const allDropdowns = this.elements.donateForm.querySelectorAll('.growfund-dropdown');
    allDropdowns.forEach((dropdown) => {
      dropdown.classList.remove('growfund-form-field--error');
      dropdown.classList.remove('growfund-dropdown--error');
    });

    this.clearTributeFieldErrors();
  }

  clearTributeFieldErrors() {
    if (!this.elements.tributeSection) return;

    const tributeFields = this.elements.tributeSection.querySelectorAll(
      '[data-tribute-required="true"]',
    );
    tributeFields.forEach((field) => {
      field.classList.remove('growfund-form-field--error');
      const errorMessage = field.parentNode.querySelector('.growfund-form-error-message');
      if (errorMessage) {
        errorMessage.remove();
      }
    });
  }

  ensureDefaultNotificationType() {
    if (this.elements.donateForm) {
      const tributeNotificationTypeSelect = this.elements.donateForm.querySelector(
        '#tributeNotificationType',
      );
      if (tributeNotificationTypeSelect && !tributeNotificationTypeSelect.value) {
        tributeNotificationTypeSelect.value = 'send-ecard';

        this.handleTributeNotificationTypeChange({ target: tributeNotificationTypeSelect });
      }
    }
  }

  getDefaultAmountFromDOM() {
    const selectedButton = Utils.safeQuerySelector(
      '.growfund-donation-checkout__amount-buttons .growfund-btn--primary',
    );
    if (selectedButton) {
      const amount = parseFloat(selectedButton.dataset.amount);
      if (!isNaN(amount)) {
        return amount;
      }
    }

    const firstButton = Utils.safeQuerySelector('.growfund-donation-checkout__amount-buttons .growfund-btn');
    if (firstButton) {
      const amount = parseFloat(firstButton.dataset.amount);
      if (!isNaN(amount)) {
        return amount;
      }
    }

    return CONFIG.DEFAULT_AMOUNT;
  }

  initializeLocationDropdowns() {
    const initializeWhenReady = () => {
      const dropdowns = document.querySelectorAll('[data-dropdown]');
      if (dropdowns.length > 0) {
        this.setupLocationDropdowns();
      } else {
        setTimeout(initializeWhenReady, 100);
      }
    };

    initializeWhenReady();
  }

  setupLocationDropdowns() {
    const countrySelect = document.getElementById('tributeNotificationRecipientCountry');
    const stateSelect = document.getElementById('tributeNotificationRecipientState');

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
      console.error('Error parsing location data:', error);
      return;
    }

    const populateStates = (countryCode, isInitialLoad = false) => {
      if (!isInitialLoad) {
        while (stateSelect.options.length > 1) {
          stateSelect.remove(1);
        }

        stateSelect.value = '';

        const changeEvent = new Event('change', { bubbles: true });
        stateSelect.dispatchEvent(changeEvent);
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
    };

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
  }

  clearLocationDropdowns() {
    const countrySelect = document.getElementById('tributeNotificationRecipientCountry');
    const stateSelect = document.getElementById('tributeNotificationRecipientState');

    if (countrySelect) {
      countrySelect.value = '';
      const dropdownWrapper = countrySelect.closest('[data-dropdown]');
      if (dropdownWrapper) {
        const dropdownInstance = window.dropdownManager?.dropdowns.find(
          (d) => d.element === dropdownWrapper,
        );
        if (dropdownInstance && dropdownInstance.resetSelection) {
          dropdownInstance.resetSelection();
        }
      }
    }

    if (stateSelect) {
      stateSelect.value = '';
      const dropdownWrapper = stateSelect.closest('[data-dropdown]');
      if (dropdownWrapper) {
        const dropdownInstance = window.dropdownManager?.dropdowns.find(
          (d) => d.element === dropdownWrapper,
        );
        if (dropdownInstance && dropdownInstance.resetSelection) {
          dropdownInstance.resetSelection();
        }
      }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  try {
    new DonationCheckout();
  } catch (error) {
    console.error('Failed to initialize donation checkout:', error);
  }
});

if (typeof module !== 'undefined' && module.exports) {
  module.exports = DonationCheckout;
}

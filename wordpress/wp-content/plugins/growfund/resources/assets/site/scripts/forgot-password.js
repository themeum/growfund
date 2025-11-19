document.addEventListener('DOMContentLoaded', function () {
  const forgotPasswordForm = document.querySelector('.growfund-forgot-password-form');
  if (!forgotPasswordForm) return;

  // Security: Add CSRF token to form if not present
  if (!forgotPasswordForm.querySelector('input[name="_wpnonce"]')) {
    const nonceInput = document.createElement('input');
    nonceInput.type = 'hidden';
    nonceInput.name = '_wpnonce';
    nonceInput.value = growfund.ajax_nonce;
    forgotPasswordForm.appendChild(nonceInput);
  } else {
    forgotPasswordForm.querySelector('input[name="_wpnonce"]').value = growfund.ajax_nonce;
  }

  // Security: Add timestamp for additional CSRF protection
  const timestampInput = document.createElement('input');
  timestampInput.type = 'hidden';
  timestampInput.name = '_timestamp';
  timestampInput.value = Date.now();
  forgotPasswordForm.appendChild(timestampInput);

  // Security: Basic XSS prevention only - no validation
  function sanitizeInput(input) {
    return input.trim().replace(/[<>]/g, '');
  }

  // Clear previous messages
  function clearMessages() {
    const existingAlerts = forgotPasswordForm.parentNode.querySelectorAll('.growfund-alert');
    existingAlerts.forEach((alert) => alert.remove());
  }

  // Show error message
  function showErrorMessage(message) {
    clearMessages();

    const errorAlert = document.createElement('div');
    errorAlert.className = 'growfund-alert growfund-alert--error';
    errorAlert.innerHTML = `<p>${message}</p>`;

    forgotPasswordForm.parentNode.insertBefore(errorAlert, forgotPasswordForm);

    // Scroll to top to show message
    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Show validation errors
  function showValidationErrors(errors) {
    clearMessages();

    // Show first error message
    const firstError = Object.values(errors)[0];
    if (firstError) {
      showErrorMessage(firstError);
    }
  }

  forgotPasswordForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const submitBtn = forgotPasswordForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Security: Clear previous errors and success messages
    clearMessages();

    // Prepare form data
    const formData = new FormData(forgotPasswordForm);

    // Security: Sanitize inputs to prevent XSS
    formData.set('email', sanitizeInput(formData.get('email')));

    // Set the action for forgot password
    formData.append('action', 'growfund_forgot_password');

    // Security: Update timestamp for CSRF protection
    formData.set('_timestamp', Date.now());

    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    // Security: Add security headers to request
    const requestOptions = {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-Forwarded-For': 'same-origin',
      },
      credentials: 'same-origin', // Security: Include cookies for session management
    };

    // Submit via AJAX
    // @todo: Need to use a utility function to handle the fetch request
    fetch(growfund.ajax_url, requestOptions)
      .then(async (response) => {
        // Security: Check response status and headers
        if (!response.ok) {
          // Handle 422 validation errors specifically
          if (response.status === 422) {
            try {
              const errorData = await response.json();
              // Return error data to be handled in the next .then()
              return { isError: true, status: 422, data: errorData };
            } catch (e) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
          }
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Security: Verify content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseData = await response.json();
        return responseData;
      })
      .then((data) => {
        // Handle validation errors from 422 response (ValidationException)
        if (data && data.isError && data.status === 422) {
          if (data.data && data.data.errors) {
            showValidationErrors(data.data.errors);
          } else {
            showErrorMessage('Validation failed. Please check your input.');
          }
          return;
        }

        // Handle successful response
        if (data && data.success && data.data && data.data.message) {
          // Redirect to the forgot password page with email parameter to show custom message
          const email = data.data.email || formData.get('email');
          const redirectUrl = `${growfund.forget_password_url}?email=${encodeURIComponent(email)}`;
          window.location.href = redirectUrl;
        } else if (data && data.message) {
          // Fallback for direct message structure
          const email = data.email || formData.get('email');
          const redirectUrl = `${growfund.forget_password_url}?email=${encodeURIComponent(email)}`;
          window.location.href = redirectUrl;
        } else {
          showErrorMessage('An unexpected error occurred. Please try again.');
        }
      })
      .catch((error) => {
        // Security: Show generic error message to prevent information leakage
        showErrorMessage('An error occurred while processing your request. Please try again.');
      })
      .finally(() => {
        // Re-enable submit button and restore original text
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
  });

  // Security: Rate limiting - prevent rapid form submissions
  let lastSubmissionTime = 0;
  const minSubmissionInterval = 2000; // 2 seconds

  forgotPasswordForm.addEventListener('submit', function (event) {
    const now = Date.now();
    if (now - lastSubmissionTime < minSubmissionInterval) {
      event.preventDefault();
      showErrorMessage('Please wait a moment before submitting again.');
      return;
    }
    lastSubmissionTime = now;
  });

  // Security: Input validation on blur
  const emailInput = forgotPasswordForm.querySelector('input[name="email"]');
  if (emailInput) {
    emailInput.addEventListener('blur', function () {
      const email = this.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (email && !emailRegex.test(email)) {
        this.classList.add('growfund-input--error');
        // Show inline error
        let errorElement = this.parentNode.querySelector('.growfund-input-error');
        if (!errorElement) {
          errorElement = document.createElement('div');
          errorElement.className = 'growfund-input-error';
          errorElement.style.color = '#dc2626';
          errorElement.style.fontSize = '0.75rem';
          errorElement.style.marginTop = '0.25rem';
          this.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = 'Please enter a valid email address.';
      } else {
        this.classList.remove('growfund-input--error');
        const errorElement = this.parentNode.querySelector('.growfund-input-error');
        if (errorElement) {
          errorElement.remove();
        }
      }
    });

    // Remove error state on input
    emailInput.addEventListener('input', function () {
      this.classList.remove('growfund-input--error');
      const errorElement = this.parentNode.querySelector('.growfund-input-error');
      if (errorElement) {
        errorElement.remove();
      }
    });
  }

  // Security: Prevent form submission if email is empty
  forgotPasswordForm.addEventListener('submit', function (event) {
    const email = emailInput.value.trim();
    if (!email) {
      event.preventDefault();
      showErrorMessage('Please enter your email address.');
      emailInput.focus();
      return;
    }
  });

  // Accessibility: Add ARIA labels and roles
  if (emailInput) {
    emailInput.setAttribute('aria-describedby', 'email-help');
    emailInput.setAttribute('aria-required', 'true');

    // Add help text for screen readers
    const helpText = document.createElement('div');
    helpText.id = 'email-help';
    helpText.className = 'growfund-sr-only';
    helpText.textContent =
      'Enter the email address associated with your account to receive a password reset link.';
    helpText.style.position = 'absolute';
    helpText.style.width = '1px';
    helpText.style.height = '1px';
    helpText.style.padding = '0';
    helpText.style.margin = '-1px';
    helpText.style.overflow = 'hidden';
    helpText.style.clip = 'rect(0, 0, 0, 0)';
    helpText.style.whiteSpace = 'nowrap';
    helpText.style.border = '0';

    emailInput.parentNode.appendChild(helpText);
  }

  // Security: Add honeypot field to prevent bot submissions
  const honeypotInput = document.createElement('input');
  honeypotInput.type = 'text';
  honeypotInput.name = 'website';
  honeypotInput.style.position = 'absolute';
  honeypotInput.style.left = '-9999px';
  honeypotInput.style.opacity = '0';
  honeypotInput.setAttribute('tabindex', '-1');
  honeypotInput.setAttribute('aria-hidden', 'true');

  forgotPasswordForm.appendChild(honeypotInput);

  // Check honeypot on form submission
  forgotPasswordForm.addEventListener('submit', function (event) {
    if (honeypotInput.value) {
      event.preventDefault();
      // Silently reject - don't show error message to prevent bot learning
      return;
    }
  });
});

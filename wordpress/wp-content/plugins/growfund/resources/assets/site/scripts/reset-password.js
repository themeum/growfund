document.addEventListener('DOMContentLoaded', function () {
  const resetPasswordForm = document.querySelector('.growfund-reset-password-form');
  if (!resetPasswordForm) return;

  // Security: Add CSRF token to form if not present
  if (!resetPasswordForm.querySelector('input[name="_wpnonce"]')) {
    const nonceInput = document.createElement('input');
    nonceInput.type = 'hidden';
    nonceInput.name = '_wpnonce';
    nonceInput.value = growfund.ajax_nonce;
    resetPasswordForm.appendChild(nonceInput);
  } else {
    resetPasswordForm.querySelector('input[name="_wpnonce"]').value = growfund.ajax_nonce;
  }

  // Security: Add timestamp for additional CSRF protection
  const timestampInput = document.createElement('input');
  timestampInput.type = 'hidden';
  timestampInput.name = '_timestamp';
  timestampInput.value = Date.now();
  resetPasswordForm.appendChild(timestampInput);

  // Security: Basic XSS prevention only - no validation
  function sanitizeInput(input) {
    return input.trim().replace(/[<>]/g, '');
  }

  // Clear previous messages
  function clearMessages() {
    const existingAlerts = resetPasswordForm.parentNode.querySelectorAll('.growfund-alert');
    existingAlerts.forEach((alert) => alert.remove());
  }

  // Show success message
  function showSuccessMessage(message) {
    clearMessages();

    const successAlert = document.createElement('div');
    successAlert.className = 'growfund-alert growfund-alert--success';
    successAlert.innerHTML = `<p>${message}</p>`;

    resetPasswordForm.parentNode.insertBefore(successAlert, resetPasswordForm);

    // Scroll to top to show message
    successAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Show error message
  function showErrorMessage(message) {
    clearMessages();

    const errorAlert = document.createElement('div');
    errorAlert.className = 'growfund-alert growfund-alert--error';
    errorAlert.innerHTML = `<p>${message}</p>`;

    resetPasswordForm.parentNode.insertBefore(errorAlert, resetPasswordForm);

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

  resetPasswordForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const submitBtn = resetPasswordForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Security: Clear previous errors and success messages
    clearMessages();

    // Get form data
    const formData = new FormData(resetPasswordForm);
    const password = formData.get('password');
    const passwordConfirmation = formData.get('password_confirmation');
    const key = formData.get('key');
    const login = formData.get('login');

    // Client-side validation
    if (!password || !passwordConfirmation) {
      showErrorMessage('Please fill in all fields.');
      return;
    }

    if (password !== passwordConfirmation) {
      showErrorMessage('Passwords do not match. Please try again.');
      return;
    }

    // Frontend validation: Check password length
    if (password.length < 8) {
      showErrorMessage('Password must be at least 8 characters long.');
      return;
    }

    // Security: Sanitize inputs to prevent XSS
    formData.set('password', sanitizeInput(password));
    formData.set('password_confirmation', sanitizeInput(passwordConfirmation));

    // Set the action for reset password
    formData.append('action', 'growfund_reset_password');

    // Security: Update timestamp for CSRF protection
    formData.set('_timestamp', Date.now());

    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Resetting...';

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
        // Debug: Log the response data to see structure
        console.log('Reset password response data:', data);

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
          showSuccessMessage(data.data.message);

          // Clear the form on success
          resetPasswordForm.reset();

          // Redirect to login page after a delay
          setTimeout(() => {
            if (data.data.redirect_url) {
              window.location.href = data.data.redirect_url;
            } else {
              window.location.href = growfund.login_url;
            }
          }, 3000);
        } else if (data && data.message) {
          // Fallback for direct message structure
          showSuccessMessage(data.message);

          // Clear the form on success
          resetPasswordForm.reset();

          // Redirect to login page after a delay
          setTimeout(() => {
            if (data.redirect_url) {
              window.location.href = data.redirect_url;
            } else {
              window.location.href = growfund.login_url;
            }
          }, 3000);
        } else {
          showErrorMessage('An unexpected error occurred. Please try again.');
        }
      })
      .catch((error) => {
        console.error('Reset password error:', error);

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

  resetPasswordForm.addEventListener('submit', function (event) {
    const now = Date.now();
    if (now - lastSubmissionTime < minSubmissionInterval) {
      event.preventDefault();
      showErrorMessage('Please wait a moment before submitting again.');
      return;
    }
    lastSubmissionTime = now;
  });

  // Real-time password confirmation validation
  const passwordInput = resetPasswordForm.querySelector('input[name="password"]');
  const passwordConfirmationInput = resetPasswordForm.querySelector(
    'input[name="password_confirmation"]',
  );

  if (passwordInput && passwordConfirmationInput) {
    function validatePasswordMatch() {
      const password = passwordInput.value;
      const confirmation = passwordConfirmationInput.value;

      if (confirmation && password !== confirmation) {
        passwordConfirmationInput.classList.add('growfund-input--error');
        // Show inline error
        let errorElement = passwordConfirmationInput.parentNode.querySelector('.growfund-input-error');
        if (!errorElement) {
          errorElement = document.createElement('div');
          errorElement.className = 'growfund-input-error';
          errorElement.style.color = '#dc2626';
          errorElement.style.fontSize = '0.75rem';
          errorElement.style.marginTop = '0.25rem';
          passwordConfirmationInput.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = 'Passwords do not match.';
      } else {
        passwordConfirmationInput.classList.remove('growfund-input--error');
        const errorElement = passwordConfirmationInput.parentNode.querySelector('.growfund-input-error');
        if (errorElement) {
          errorElement.remove();
        }
      }
    }

    passwordInput.addEventListener('input', validatePasswordMatch);
    passwordConfirmationInput.addEventListener('input', validatePasswordMatch);
    passwordConfirmationInput.addEventListener('blur', validatePasswordMatch);
  }

  // Accessibility: Add ARIA labels and roles
  if (passwordInput) {
    passwordInput.setAttribute('aria-describedby', 'password-help');
    passwordInput.setAttribute('aria-required', 'true');

    // Add help text for screen readers
    const helpText = document.createElement('div');
    helpText.id = 'password-help';
    helpText.className = 'growfund-sr-only';
    helpText.textContent = 'Enter your new password. It must be at least 8 characters long.';
    helpText.style.position = 'absolute';
    helpText.style.width = '1px';
    helpText.style.height = '1px';
    helpText.style.padding = '0';
    helpText.style.margin = '-1px';
    helpText.style.overflow = 'hidden';
    helpText.style.clip = 'rect(0, 0, 0, 0)';
    helpText.style.whiteSpace = 'nowrap';
    helpText.style.border = '0';

    passwordInput.parentNode.appendChild(helpText);
  }

  if (passwordConfirmationInput) {
    passwordConfirmationInput.setAttribute('aria-describedby', 'password-confirmation-help');
    passwordConfirmationInput.setAttribute('aria-required', 'true');

    // Add help text for screen readers
    const helpText = document.createElement('div');
    helpText.id = 'password-confirmation-help';
    helpText.className = 'growfund-sr-only';
    helpText.textContent = 'Confirm your new password by typing it again.';
    helpText.style.position = 'absolute';
    helpText.style.width = '1px';
    helpText.style.height = '1px';
    helpText.style.padding = '0';
    helpText.style.margin = '-1px';
    helpText.style.overflow = 'hidden';
    helpText.style.clip = 'rect(0, 0, 0, 0)';
    helpText.style.whiteSpace = 'nowrap';
    helpText.style.border = '0';

    passwordConfirmationInput.parentNode.appendChild(helpText);
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

  resetPasswordForm.appendChild(honeypotInput);

  // Check honeypot on form submission
  resetPasswordForm.addEventListener('submit', function (event) {
    if (honeypotInput.value) {
      event.preventDefault();
      // Silently reject - don't show error message to prevent bot learning
      return;
    }
  });
});

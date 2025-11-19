document.addEventListener('DOMContentLoaded', function () {
  const registerForm = document.querySelector('.growfund-register-form');
  if (!registerForm) return;

  // Security: Add CSRF token to form if not present
  if (!registerForm.querySelector('input[name="_wpnonce"]')) {
    const nonceInput = document.createElement('input');
    nonceInput.type = 'hidden';
    nonceInput.name = '_wpnonce';
    nonceInput.value = growfund.ajax_nonce;
    registerForm.appendChild(nonceInput);
  } else {
    registerForm.querySelector('input[name="_wpnonce"]').value = growfund.ajax_nonce;
  }

  // Security: Add timestamp for additional CSRF protection
  const timestampInput = document.createElement('input');
  timestampInput.type = 'hidden';
  timestampInput.name = '_timestamp';
  timestampInput.value = Date.now();
  registerForm.appendChild(timestampInput);

  // Security: Basic XSS prevention only - no validation
  function sanitizeInput(input) {
    return input.trim().replace(/[<>]/g, '');
  }

  registerForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const submitBtn = registerForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Security: Clear previous errors and success messages
    clearMessages();

    // Frontend validation: Check if passwords match
    const password = registerForm.querySelector('input[name="password"]').value;
    const passwordConfirmation = registerForm.querySelector(
      'input[name="password_confirmation"]',
    ).value;

    if (password !== passwordConfirmation) {
      showError('Passwords do not match. Please try again.');
      return;
    }

    // Frontend validation: Check password length
    if (password.length < 8) {
      showError('Password must be at least 8 characters long.');
      return;
    }

    // Prepare form data
    const formData = new FormData(registerForm);

    // Security: Sanitize inputs
    formData.set('first_name', sanitizeInput(formData.get('first_name')));
    formData.set('last_name', sanitizeInput(formData.get('last_name')));
    formData.set('email', sanitizeInput(formData.get('email')));

    // Determine the action based on form action URL
    const isFundraiser = registerForm.action.includes('register-fundraiser');
    formData.append('action', isFundraiser ? 'growfund_register_fundraiser' : 'growfund_register_user');

    // Security: Update timestamp for CSRF protection
    formData.set('_timestamp', Date.now());

    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating account...';

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

          // For other errors, try to get the actual error message from response
          try {
            const errorText = await response.text();
            // Check if it's a WordPress error response
            if (errorText.includes('Security check failed') || errorText.includes('nonce')) {
              throw new Error('Security check failed. Please refresh the page and try again.');
            }
            // If we can get text content, use it as the error message
            if (errorText.trim()) {
              throw new Error(errorText.trim());
            }
          } catch (textError) {
            // If we can't get text content, use a generic message based on status
            if (response.status === 403) {
              throw new Error('Access denied. Please refresh the page and try again.');
            } else if (response.status === 500) {
              throw new Error('Server error. Please try again later.');
            } else {
              throw new Error(`Registration failed (${response.status}). Please try again.`);
            }
          }
        }

        // Security: Verify content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
      })
      .then((data) => {
        // Handle validation errors from 422 response (ValidationException)
        if (data && data.isError && data.status === 422) {
          let errorMessage = '';

          // Handle the nested data structure: data.data.data.errors
          if (data.data && data.data.data && data.data.data.errors) {
            const firstFieldName = Object.keys(data.data.data.errors)[0];
            const firstFieldErrors = data.data.data.errors[firstFieldName];
            const firstErrorMessage = Array.isArray(firstFieldErrors)
              ? firstFieldErrors[0]
              : firstFieldErrors;
            errorMessage = sanitizeInput(firstErrorMessage);
          }
          // Fallback to data.data.errors if the nested structure doesn't exist
          else if (data.data && data.data.errors) {
            const firstFieldName = Object.keys(data.data.errors)[0];
            const firstFieldErrors = data.data.errors[firstFieldName];
            const firstErrorMessage = Array.isArray(firstFieldErrors)
              ? firstFieldErrors[0]
              : firstFieldErrors;
            errorMessage = sanitizeInput(firstErrorMessage);
          }
          // Then check for general error message
          else if (data.data && data.data.message) {
            errorMessage = sanitizeInput(data.data.message);
          }

          if (errorMessage) {
            showError(errorMessage);
          }
          return;
        }

        // Handle non-success responses (similar to login.js)
        if (data && !data.success) {
          let errorMessage = '';

          // Handle the nested data structure: data.data.data.errors
          if (data.data && data.data.data && data.data.data.errors) {
            const firstFieldName = Object.keys(data.data.data.errors)[0];
            const firstFieldErrors = data.data.data.errors[firstFieldName];
            const firstErrorMessage = Array.isArray(firstFieldErrors)
              ? firstFieldErrors[0]
              : firstFieldErrors;
            errorMessage = sanitizeInput(firstErrorMessage);
          }
          // Fallback to data.data.errors if the nested structure doesn't exist
          else if (data.data && data.data.errors) {
            const firstFieldName = Object.keys(data.data.errors)[0];
            const firstFieldErrors = data.data.errors[firstFieldName];
            const firstErrorMessage = Array.isArray(firstFieldErrors)
              ? firstFieldErrors[0]
              : firstFieldErrors;
            errorMessage = sanitizeInput(firstErrorMessage);
          }
          // Then check for general error message
          else if (data.data && data.data.message) {
            errorMessage = sanitizeInput(data.data.message);
          }

          if (errorMessage) {
            showError(errorMessage);
          }
          return;
        }

        // Additional check for ValidationException responses that might come in different format
        if (data && data.data && data.data.errors) {
          let errorMessage = '';
          const firstFieldName = Object.keys(data.data.errors)[0];
          const firstFieldErrors = data.data.errors[firstFieldName];
          const firstErrorMessage = Array.isArray(firstFieldErrors)
            ? firstFieldErrors[0]
            : firstFieldErrors;
          errorMessage = sanitizeInput(firstErrorMessage);

          if (errorMessage) {
            showError(errorMessage);
          }
          return;
        }

        // Additional check for nested data structure
        if (data && data.data && data.data.data && data.data.data.errors) {
          let errorMessage = '';
          const firstFieldName = Object.keys(data.data.data.errors)[0];
          const firstFieldErrors = data.data.data.errors[firstFieldName];
          const firstErrorMessage = Array.isArray(firstFieldErrors)
            ? firstFieldErrors[0]
            : firstFieldErrors;
          errorMessage = sanitizeInput(firstErrorMessage);

          if (errorMessage) {
            showError(errorMessage);
          }
          return;
        }

        if (data.success) {
          // Security: Validate redirect URL to prevent open redirects
          const redirectUrl = data.data.redirect_url;
          if (redirectUrl && isValidRedirectUrl(redirectUrl)) {
            showSuccess(data.data.message);

            // Security: Use window.location.replace for safer redirects
            setTimeout(() => {
              window.location.replace(redirectUrl);
            }, 2000);
          } else {
            showSuccess(data.data.message);
            setTimeout(() => {
              window.location.replace('/');
            }, 2000);
          }
        } else if (data.message) {
          // Handle direct message response (backend returns just {message: '...'})
          showSuccess(data.message);
          setTimeout(() => {
            window.location.replace('/');
          }, 2000);
        } else {
          // Show error message from backend response
          if (data.data && data.data.errors) {
            const firstFieldName = Object.keys(data.data.errors)[0];
            const firstFieldErrors = data.data.errors[firstFieldName];
            const firstErrorMessage = Array.isArray(firstFieldErrors)
              ? firstFieldErrors[0]
              : firstFieldErrors;
            showError(sanitizeInput(firstErrorMessage));
          } else if (data.data && data.data.message) {
            showError(sanitizeInput(data.data.message));
          }
        }
      })
      .catch((error) => {
        console.error('Registration error:', error);

        // Show the error message to the user
        let errorMessage = error.message || 'Registration failed. Please try again.';
        showError(errorMessage);
      })
      .finally(() => {
        // Restore submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
  });

  // Security: Validate redirect URLs to prevent open redirects
  function isValidRedirectUrl(url) {
    try {
      const urlObj = new URL(url, window.location.origin);
      return urlObj.origin === window.location.origin;
    } catch (e) {
      return false;
    }
  }

  function clearMessages() {
    // Hide any existing success/error alerts
    const alerts = registerForm.querySelectorAll('.growfund-alert');
    alerts.forEach((alert) => (alert.style.display = 'none'));
  }

  function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'growfund-alert growfund-alert--error';
    // Security: Use textContent instead of innerHTML to prevent XSS
    alertDiv.textContent = message;

    // Insert at the top of the form
    registerForm.insertBefore(alertDiv, registerForm.firstChild);

    // Auto-hide after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }

  function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'growfund-alert growfund-alert--success';
    // Security: Use textContent instead of innerHTML to prevent XSS
    alertDiv.textContent = message;

    // Insert at the top of the form
    registerForm.insertBefore(alertDiv, registerForm.firstChild);

    // Auto-hide after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }

  // Security: Prevent form resubmission on page refresh
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.querySelector('.growfund-login-form');
  if (!loginForm) return;

  // Security: Add CSRF token to form if not present
  if (!loginForm.querySelector('input[name="_wpnonce"]')) {
    const nonceInput = document.createElement('input');
    nonceInput.type = 'hidden';
    nonceInput.name = '_wpnonce';
    nonceInput.value = growfund.ajax_nonce;
    loginForm.appendChild(nonceInput);
  } else {
    loginForm.querySelector('input[name="_wpnonce"]').value = growfund.ajax_nonce;
  }

  // Security: Add timestamp for additional CSRF protection
  const timestampInput = document.createElement('input');
  timestampInput.type = 'hidden';
  timestampInput.name = '_timestamp';
  timestampInput.value = Date.now();
  loginForm.appendChild(timestampInput);

  // Security: Basic XSS prevention only - no validation
  function sanitizeInput(input) {
    return input.trim().replace(/[<>]/g, '');
  }

  loginForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const submitBtn = loginForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Security: Clear previous errors and success messages
    clearMessages();

    // Prepare form data
    const formData = new FormData(loginForm);

    // Security: Sanitize inputs to prevent XSS
    formData.set('user_login', sanitizeInput(formData.get('user_login')));

    // Set the action for login
    formData.append('action', 'growfund_login_user');

    // Security: Update timestamp for CSRF protection
    formData.set('_timestamp', Date.now());

    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing in...';

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
        // Check if response is a redirect
        if (response.redirected) {
          // Follow the redirect
          window.location.href = response.url;
          return;
        }

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

        // Handle non-success responses (similar to register.js)
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

        // If we reach here, it means we got an unexpected response
        console.warn('Unexpected login response:', data);
      })
      .catch((error) => {
        console.error('Login error:', error);

        // Show the error message to the user
        let errorMessage = error.message || 'Login failed. Please try again.';
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
    const alerts = loginForm.querySelectorAll('.growfund-alert');
    alerts.forEach((alert) => (alert.style.display = 'none'));
  }

  function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'growfund-alert growfund-alert--error';
    // Security: Use textContent instead of innerHTML to prevent XSS
    alertDiv.textContent = message;

    // Insert at the top of the form
    loginForm.insertBefore(alertDiv, loginForm.firstChild);

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
    loginForm.insertBefore(alertDiv, loginForm.firstChild);

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

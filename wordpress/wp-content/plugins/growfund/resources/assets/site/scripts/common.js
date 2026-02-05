(function () {
  const growfundAjaxClient = () => {
    const baseUrl = growfund.ajax_url ?? '';

    const _nonce = growfund.ajax_nonce ?? '';

    const normalizeRequestData = (data) => {
      if (data instanceof URLSearchParams) {
        return data;
      }

      if (typeof data === 'object' && data !== null) {
        return new URLSearchParams(
          Object.entries(data).filter(([, value]) => value !== null && value !== undefined),
        );
      }

      return new URLSearchParams();
    };

    return {
      get: async (action, params = {}) => {
        try {
          params = normalizeRequestData(params);
          const currentParams = new URLSearchParams(window.location.search);

          // Merge new params → override existing ones
          for (const [key, value] of params.entries()) {
            if (value === null || value === '') {
              currentParams.delete(key);
            } else {
              currentParams.set(key, value);
            }
          }

          currentParams.set('action', action);
          currentParams.set('_wpnonce', _nonce);

          const url = `${baseUrl}?${currentParams.toString()}`;

          const response = await fetch(url);

          if (!response.ok) {
            const error = await response.error();
            return {
              success: false,
              message: error.message,
            };
          }

          const result = await response.json();

          return result;
        } catch (error) {
          return {
            success: false,
            message: error.message,
          };
        }
      },
      post: async (action, data = {}, contentType = 'application/x-www-form-urlencoded') => {
        try {
          data = normalizeRequestData(data);

          const requestBody = new URLSearchParams({
            action: action,
            _wpnonce: _nonce,
          });

          for (const [key, value] of data.entries()) {
            if (value === null || value === '') {
              requestBody.delete(key);
            } else {
              requestBody.set(key, value);
            }
          }

          const response = await fetch(baseUrl, {
            method: 'POST',
            headers: {
              'Content-Type': contentType,
            },
            body: requestBody,
          });

          if (!response.ok) {
            const error = await response.error();
            return {
              success: false,
              message: error.message,
            };
          }

          const result = await response.json();

          return result;
        } catch (error) {
          return {
            success: false,
            message: error.message,
          };
        }
      },
    };
  };

  window.growfundAjaxClient = growfundAjaxClient();

  const growfundToCurrency = (amount) => {
    const currencySettings = window.growfund.currency_info;
    const formattedAmount = amount
      .toLocaleString(undefined, {
        minimumFractionDigits: currencySettings.decimal_places,
        maximumFractionDigits: currencySettings.decimal_places,
      })
      .replace('.', currencySettings.decimal_separator)
      .replace(/,/g, currencySettings.thousand_separator);

    const currencyParts = (currencySettings.currency || '').split(':');
    const currencySymbol = currencyParts[0] || '$';

    if (currencySettings.currency_position === 'after') {
      return `${formattedAmount}${currencySymbol}`;
    }

    return `${currencySymbol}${formattedAmount}`;
  };

  window.growfundToCurrency = growfundToCurrency;

  function growfundDebounce(func, delay = 300) {
    let timeoutId;

    return function (...args) {
      clearTimeout(timeoutId);

      timeoutId = setTimeout(() => {
        func.apply(this, args);
      }, delay);
    };
  }

  window.growfundDebounce = growfundDebounce;

  function growfundUpdateQueryParams(key, value) {
    const params = new URLSearchParams(window.location.search);

    if (key) {
      if (value !== null && value !== undefined && value !== '') {
        params.set(key, value);
      } else {
        params.delete(key);
      }
    }

    const queryString = params.toString();
    const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');

    window.history.pushState({ path: newUrl }, '', newUrl);

    return Object.fromEntries(params.entries());
  }

  window.growfundUpdateQueryParams = growfundUpdateQueryParams;

  function growfundGetDateTime(datetime, showTime = true, options = {}) {
    if (!datetime) return;
    // Normalize plain YYYY-MM-DD into full UTC date string
    let hasTime = showTime && datetime.includes('T');
    if (/^\d{4}-\d{2}-\d{2}$/.test(datetime)) {
      datetime += 'T00:00:00Z';
    }

    // If no timezone info, assume UTC (Z)
    else if (!/[zZ]$/.test(datetime) && !/[\+\-]\d{2}:?\d{2}$/.test(datetime)) {
      datetime += 'Z';
    }

    let date = new Date(datetime);

    if (isNaN(date)) {
      return;
    }

    if (Object.entries(options).length < 1) {
      options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      };
    }

    // Add time only if the original string had time info
    if (hasTime) {
      options.hour = '2-digit';
      options.minute = '2-digit';
    }

    return date.toLocaleString(undefined, options);
  }

  window.growfundGetDateTime = growfundGetDateTime;

  function updateAllGrowfundDateTimes() {
    document.querySelectorAll('[data-growfund-datetime]').forEach(function (el) {
      let raw = el.getAttribute('data-growfund-datetime').trim();

      if (!raw) {
        return;
      }

      el.textContent = growfundGetDateTime(raw);
    });
  }

  window.growfundEvents = {};
  window.updateAllGrowfundDateTimes = updateAllGrowfundDateTimes;

  document.addEventListener('DOMContentLoaded', function () {
    updateAllGrowfundDateTimes();
  });
})();

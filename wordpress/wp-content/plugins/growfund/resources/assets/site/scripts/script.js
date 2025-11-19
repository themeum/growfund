document.addEventListener('DOMContentLoaded', function () {
  window.toCurrency = function (amount) {
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

  document.querySelectorAll('[data-growfund-datetime]').forEach(function (el) {
    let raw = el.getAttribute('data-growfund-datetime').trim();

    if (!raw) {
      return;
    }

    // Normalize plain YYYY-MM-DD into full UTC date string
    let hasTime = raw.includes('T');
    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
      raw += 'T00:00:00Z';
    }

    // If no timezone info, assume UTC (Z)
    else if (!/[zZ]$/.test(raw) && !/[\+\-]\d{2}:?\d{2}$/.test(raw)) {
      raw += 'Z';
    }

    let date = new Date(raw);

    if (isNaN(date)) {
      return;
    }

    let options = {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    };

    // Add time only if the original string had time info
    if (hasTime) {
      options.hour = '2-digit';
      options.minute = '2-digit';
    }

    el.textContent = date.toLocaleString(undefined, options);
  });
});

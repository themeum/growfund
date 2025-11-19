jQuery(function ($) {
  function debounce(func, wait) {
    let timeout;
    return function () {
      const context = this,
        args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }

  // Bonus/Pledge
  $('#growfund_bonus_support_amount').on(
    'input',
    debounce(function () {
      var bonusAmount = $(this).val();

      $.post(growfund.ajax_url, {
        action: 'growfund_update_wc_session',
        _wpnonce: growfund.ajax_nonce,
        bonus_support_amount: bonusAmount,
      }).done(function () {
        $('body').trigger('update_checkout');
      });
    }, 500),
  );

  // Donation
  $('#growfund_donation_amount').on(
    'input',
    debounce(function () {
      var donationAmount = $(this).val();

      $.post(growfund.ajax_url, {
        action: 'growfund_update_wc_session',
        _wpnonce: growfund.ajax_nonce,
        contribution_amount: donationAmount,
      }).done(function () {
        $('body').trigger('update_checkout');
      });
    }, 500),
  );
});

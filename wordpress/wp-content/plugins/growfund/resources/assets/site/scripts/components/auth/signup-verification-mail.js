document.addEventListener('DOMContentLoaded', function () {
  const signupButton = document.getElementById('growfund_signup_submit_button');
  const verificationModal = document.getElementById('growfund-signup-verification-modal');

  if (signupButton && verificationModal) {
    signupButton.addEventListener('click', function (e) {
      growfundOpenModal(verificationModal);
    });
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('.growfund-signup-verification-mail-button')) {
      const modal = document.getElementById('growfund-signup-verification-modal');

      if (!modal) return;
      growfundCloseModal(modal);
    }
  });
});

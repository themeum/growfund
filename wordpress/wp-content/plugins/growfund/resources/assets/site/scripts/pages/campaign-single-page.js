(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const backButtons = document.querySelectorAll('.growfund-campaign-contribution-button');

    backButtons.forEach(function (button) {
      button.addEventListener('click', function (e) {
        const modal = document.getElementById('growfund_pledge_modal');

        if (!modal) return;

        growfundOpenModal(modal);
      });
    });

    document.addEventListener('click', function (e) {
      if (
        e.target.closest('.growfund-modal-close-button-icon') ||
        e.target.closest('.growfund-modal-cancel-button')
      ) {
        const modal = document.getElementById('growfund_pledge_modal');

        if (!modal) return;

        growfundCloseModal(modal);
      }
    });

    const showAllBtn = document.querySelector(
      '.growfund-campaign-single-page-right-content-creator-list-show-all-toggle-btn',
    );

    const showLessBtn = document.querySelector(
      '.growfund-campaign-single-page-right-content-creator-list-show-less-toggle-btn',
    );

    if (showAllBtn) {
      showAllBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const container = document.querySelector(
          '.growfund-campaign-single-page-right-content-creator-list-container',
        );
        if (!container) return;
        container.classList.add('is-expanded');
      });
    }

    if (showLessBtn) {
      showLessBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const container = document.querySelector(
          '.growfund-campaign-single-page-right-content-creator-list-container',
        );
        if (!container) return;
        container.classList.remove('is-expanded');
      });
    }
  });
})();

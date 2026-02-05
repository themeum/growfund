(function () {
  document.addEventListener('DOMContentLoaded', () => {
    const bookmarkButtons = document.querySelectorAll('.growfund-campaign-bookmark-button');

    bookmarkButtons.forEach((btn) => {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const redirectUrl = btn.getAttribute('data-redirect-url');

        if (redirectUrl) {
          window.location.href = redirectUrl;
          return;
        }

        const bookmarkedClass = 'growfund-campaign-is-bookmarked';
        const btnLabel = btn.querySelector('.growfund-campaign-bookmark-button-label');

        let bookmarkedLabel;
        let bookmarkLabel;

        if (btnLabel) {
          bookmarkedLabel = btnLabel.getAttribute('data-bookmarked-label');
          bookmarkLabel = btnLabel.getAttribute('data-bookmark-label');
        }

        const campaignId = btn.getAttribute('data-campaign-id');

        growfundAjaxClient
          .post('growfund_ajax_bookmark_campaign', { campaign_id: campaignId })
          .then((response) => {
            if (response.success && response.data && response.data.data) {
              if (response.data.data.is_bookmarked) {
                btn.classList.add(bookmarkedClass);
                btn.setAttribute('data-campaign-is-bookmarked', 'true');

                if (btnLabel) btnLabel.textContent = bookmarkedLabel;

                return;
              }

              btn.classList.remove(bookmarkedClass);
              btn.setAttribute('data-campaign-is-bookmarked', 'false');

              if (btnLabel) btnLabel.textContent = bookmarkLabel;
            }
          });
      });
    });
  });
})();

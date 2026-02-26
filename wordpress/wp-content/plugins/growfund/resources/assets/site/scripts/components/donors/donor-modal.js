document.addEventListener('DOMContentLoaded', () => {
  const donorContainer = document.querySelector('.growfund-donor-modal-collection');
  if (!donorContainer) return;
  const limit = donorContainer.dataset.limit;
  const campaignId = donorContainer.dataset.campaignId;

  const donorList = donorContainer.querySelector('.growfund-modal-donor-list');
  const newestBtn = document.querySelector('.growfund-donor-modal-newest-button');
  const topBtn = document.querySelector('.growfund-donor-modal-top-button');

  let currentSortKey = 'recent-only';
  let currentPage = 1;
  let hasMore = true;
  let loading = false;

  [newestBtn, topBtn].forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();

      if (event.target.classList.contains('growfund-donor-modal-newest-button')) {
        currentSortKey = 'recent-only';
        newestBtn?.classList.add('selected');
        topBtn?.classList.remove('selected');
      }

      if (event.target.classList.contains('growfund-donor-modal-top-button')) {
        currentSortKey = 'top-only';
        topBtn?.classList.add('selected');
        newestBtn?.classList.remove('selected');
      }

      fetchDonorList(donorList, campaignId, currentSortKey);
      window.growfundObserveInfiniteScroll('#growfund-donor-modal_infinite_scroll');
      currentPage = 1;
      hasMore = true;
      loading = false;
    });
  });

  if (limit === 'all' && window.growfundObserveInfiniteScroll) {
    document.addEventListener('growfund:infinite-scroll', async function (event) {
      const { isIntersecting } = event.detail;

      if (!hasMore || loading) {
        return;
      }

      if (isIntersecting) {
        loading = true;
        response = await fetchDonorList(
          donorList,
          campaignId,
          currentSortKey,
          currentPage + 1,
          0, // limit
          false, // reset
        );
        hasMore = response.data.data.has_more;
        currentPage = response.data.data.current_page;
        loading = false;
      }
    });

    window.growfundObserveInfiniteScroll('#growfund-donor-modal_infinite_scroll');
  }
});

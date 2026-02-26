async function fetchDonorList(
  donorList,
  campaignId,
  sortKey = null,
  pageNumber = 1,
  limit = 0,
  reset = true,
) {
  response = await growfundAjaxClient.get('growfund_ajax_donor_list', {
    campaign_id: campaignId,
    page: pageNumber,
    limit: limit,
    sort_key: sortKey,
  });

  if (response.success && response.data?.html) {
    if (reset) {
      donorList.innerHTML = response.data.html;
    } else {
      donorList.insertAdjacentHTML('beforeend', response.data.html);
    }
  }

  return response;
}

window.fetchDonorList = fetchDonorList;

document.addEventListener('DOMContentLoaded', function () {
  const container = document.querySelector(
    '.growfund-campaign-single-page-right-content-donor-list-container',
  );

  if (container) {
    const donorList = document.querySelector('#growfund_donor_list');
    const campaignId = container.dataset.campaignId;
    const limit = 5;
    const page = 1;
    fetchDonorList(donorList, campaignId, null, page, limit);
  }

  const button = document.querySelector('.growfund-donor-list-button');

  if (!button) return;

  button.addEventListener('click', function (e) {
    e.preventDefault();

    const modal = document.getElementById('growfund_donor_list_modal');
    if (!modal) return;

    growfundOpenModal(modal);

    const donorContainer = modal.querySelector('.growfund-donor-modal-collection');
    if (!donorContainer) return;

    const campaignId = donorContainer.dataset.campaignId;
    const donorList = donorContainer.querySelector('.growfund-modal-donor-list');
    fetchDonorList(donorList, campaignId, 'recent-only');
  });
});

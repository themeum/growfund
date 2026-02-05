(function () {
  const filterBtn = document.querySelector('.growfund-campaign-mobile-filter-button');
  const modal = document.querySelector('.growfund-filter-modal');
  const applyBtn = document.querySelector('.growfund-campaign-filter-modal-apply-button');
  const closeBtn = document.querySelector('.growfund-campaign-filter-modal-close');
  const clearBtn = document.querySelector('.growfund-campaign-filter-modal-clear-button');
  const overlay = modal.querySelector('.growfund-filter-modal-overlay');
  const mobileFilterList = document.querySelectorAll('.growfund-mobile-filter-group-content-list');

  function dispatchFilterCampaign(params) {
    document.dispatchEvent(
      new CustomEvent('growfund:filter-campaigns', {
        detail: {
          params,
        },
      }),
    );
  }

  window.growfundEvents.dispatchFilterCampaign = dispatchFilterCampaign;

  const closeModal = () => {
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
  };

  function filterModal() {
    filterBtn.addEventListener('click', function (e) {
      e.preventDefault();
      modal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', closeModal);

    const accordionToggles = document.querySelectorAll('.growfund-mobile-filter-item-toggle');

    accordionToggles.forEach((toggle) => {
      toggle.addEventListener('click', function (e) {
        const wrapper = this.closest('.growfund-filter-modal-group-wrapper');

        const content = wrapper.querySelector('.growfund-mobile-filter-group-content');
        if (content && content.querySelectorAll('li').length > 0) {
          wrapper.classList.toggle('is-active');
        }
      });
    });

    mobileFilterList.forEach((filter) => {
      const listItem = filter.getAttribute('data-name');
      filter.addEventListener('change', function (e) {
        if (e.target.matches(`input[type="radio"][name="${listItem}"]`)) {
          filter.setAttribute('data-selected-value', e.target.value);
        }
      });
    });
  }

  function applyFilters() {
    document
      .querySelector('#growfund_campaign_filter_category_slug')
      .addEventListener('growfund:select-change', function (e) {
        const { name, value } = e.detail;
        const params = growfundUpdateQueryParams(name, value);
        dispatchFilterCampaign({ ...params });
      });

    document
      .querySelector('#growfund_campaign_filter_sortby')
      .addEventListener('growfund:select-change', function (e) {
        const { name, value } = e.detail;
        const parse = value.split(':');
        const orderby = parse[0] ?? '';
        const order = parse[1] ?? '';
        growfundUpdateQueryParams('order', order);
        const params = growfundUpdateQueryParams(name, orderby);
        dispatchFilterCampaign({ ...params });
      });

    document
      .querySelector('#growfund_campaign_filter_search_input')
      .addEventListener('growfund:text-field', function (e) {
        const { name, value } = e.detail;
        const params = growfundUpdateQueryParams(name, value);
        dispatchFilterCampaign({ ...params });
      });

    applyBtn?.addEventListener('click', () => {
      let params = {};

      document
        .querySelectorAll('.growfund-filter-modal-body input[type="radio"]:checked')
        .forEach((input) => {
          const name = input.name;
          const value = input.value;

          if (name === 'orderby') {
            const parse = value.split(':');
            const orderby = parse[0] ?? '';
            const order = parse[1] ?? '';
            growfundUpdateQueryParams('order', order);
            params = growfundUpdateQueryParams('orderby', orderby);
            return;
          }

          params = growfundUpdateQueryParams(name, value);
        });

      dispatchFilterCampaign({ ...params });
      closeModal();
    });

    clearBtn?.addEventListener('click', function () {
      mobileFilterList.forEach((filter) => {
        filter.setAttribute('data-selected-value', '');
        filter.querySelectorAll('input[type="radio"]').forEach((radio) => {
          radio.checked = false;
        });
      });

      growfundUpdateQueryParams('category_slug', '');
      growfundUpdateQueryParams('orderby', '');
      growfundUpdateQueryParams('order', '');
      dispatchFilterCampaign({ category_slug: '', orderby: '', order: '' });
      closeModal();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    filterModal();
    applyFilters();
  });
})();

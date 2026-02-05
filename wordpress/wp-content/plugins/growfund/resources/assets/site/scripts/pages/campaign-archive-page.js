(function () {
  function getPaginatedCampaigns() {
    let page = 1;
    let hasMore = true;
    let isLoading = false;

    function fetchCampaigns(params, reset = false) {
      if ((!reset && !hasMore) || isLoading) return;

      isLoading = true;

      const debounced = growfundDebounce(function () {
        growfundAjaxClient
          .get('growfund_ajax_get_paginated_campaigns', params)
          .then((response) => {
            if (response.success && response.data && response.data.html) {
              const currentList = document.querySelector('#growfund_archive_page_campaigns_list');
              if (!currentList) return;

              page = response.data.data.current_page;
              hasMore = response.data.data.has_more;

              const temp = document.createElement('div');
              temp.innerHTML = response.data.html;

              const newList = temp.querySelector('.growfund-ajax-campaign-list');
              if (!newList) return;

              if (reset) {
                currentList.innerHTML = newList.innerHTML;
              } else {
                currentList.insertAdjacentHTML('beforeend', newList.innerHTML);
              }
            }
          })
          .finally(() => {
            isLoading = false;
          });
      }, 500);

      debounced();
    }

    document.addEventListener('growfund:filter-campaigns', function (event) {
      const { params } = event.detail;

      const featuredCampaignSection = document.querySelector('#growfund_featured_campaigns_slider');

      if (!params?.search && !params?.category_slug && !params?.orderby) {
        featuredCampaignSection?.classList.remove('growfund-hidden');

        if (growfundEvents.recalculateCampaignSlider) {
          growfundEvents.recalculateCampaignSlider();
        }
      } else {
        featuredCampaignSection?.classList.add('growfund-hidden');
      }

      fetchCampaigns(params, true);
    });

    document.addEventListener('growfund:infinite-scroll', function (event) {
      const { loader, isIntersecting } = event.detail;

      if (!hasMore) {
        loader?.classList.remove('growfund-show');
        return;
      }

      if (isIntersecting) {
        loader?.classList.add('growfund-show');
      } else {
        loader?.classList.remove('growfund-show');
      }

      const query = new URLSearchParams(window.location.search);
      query.set('page', page + 1);
      const params = Object.fromEntries(query.entries());
      fetchCampaigns(params);
    });
  }

  function getPaginatedFeaturedCampaigns() {
    let page = 1;
    let hasMore = true;
    let isLoading = false;
    function fetchFeaturedCampaigns(pageNo) {
      if (!hasMore || isLoading) return;

      isLoading = true;

      const debounced = growfundDebounce(function () {
        growfundAjaxClient
          .get('growfund_ajax_get_featured_campaigns', { page: pageNo })
          .then((response) => {
            if (response.success && response.data && response.data.html) {
              const currentList = document.querySelector(
                '#growfund_featured_campaigns_slider_campaign_list',
              );
              if (!currentList) return;

              const temp = document.createElement('div');
              temp.innerHTML = response.data.html;

              const newList = temp.querySelector('.growfund-ajax-campaign-list');
              if (!newList) return;

              page = response.data.data.current_page;
              hasMore = response.data.data.has_more;
              currentList.insertAdjacentHTML('beforeend', newList.innerHTML);

              if (growfundEvents.recalculateCampaignSlider) {
                growfundEvents.recalculateCampaignSlider();
              }
            }
          })
          .finally(() => {
            isLoading = false;
          });
      }, 500);

      debounced();
    }

    document.addEventListener('growfund:campaign-slider-ending', function (event) {
      fetchFeaturedCampaigns(page + 1);
    });
  }

  getPaginatedCampaigns();
  getPaginatedFeaturedCampaigns();

  document.addEventListener('DOMContentLoaded', () => {
    growfundObserveInfiniteScroll('#growfund_archive_page_campaigns_infinite_scroll');
  });
})();

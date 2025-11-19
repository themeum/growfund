document.addEventListener('DOMContentLoaded', function () {
  // Check if search.js is active by looking for search form elements
  const searchForm = document.getElementById('growfund-filters-search-form');
  const sortDropdown = document.querySelector('[data-dropdown-key="sort"]');
  const categoryDropdown = document.querySelector('[data-dropdown-key="category"]');
  const locationDropdown = document.querySelector('[data-dropdown-key="location"]');
  const searchInput = document.getElementById('search');

  // If any search elements exist, skip archive script entirely
  if (searchForm || sortDropdown || categoryDropdown || locationDropdown || searchInput) {
    return;
  }

  // Add a small delay to ensure search.js has a chance to run first
  setTimeout(() => {
    // Clean up any existing infinite scroll instances to prevent conflicts
    if (
      window.growfundSearchInfiniteScroll &&
      typeof window.growfundSearchInfiniteScroll.dispose === 'function'
    ) {
      window.growfundSearchInfiniteScroll.dispose();
      window.growfundSearchInfiniteScroll = null;
    }
    if (
      window.growfundArchiveInfiniteScroll &&
      typeof window.growfundArchiveInfiniteScroll.dispose === 'function'
    ) {
      window.growfundArchiveInfiniteScroll.dispose();
      window.growfundArchiveInfiniteScroll = null;
    }

    // Reset the global initialization flag to ensure proper state management
    window.growfundInfiniteScrollInitialized = false;

    // Check if there are any URL parameters that would trigger search.js
    const urlParams = new URLSearchParams(window.location.search);
    const hasSearchFilters = Array.from(urlParams.keys()).some(
      (key) => key !== 'page' && key !== 'limit' && key !== 'action' && key !== 'is_ajax_load',
    );

    // If there are search filters, don't initialize infinite scroll here
    // search.js will handle it after the AJAX search completes
    if (hasSearchFilters) {
      return;
    }

    const campaignListContainer = document.getElementById('growfund-campaign-list-container');
    if (!campaignListContainer) {
      return;
    }

    // Only initialize infinite scroll if it hasn't been initialized yet
    if (!window.growfundInfiniteScrollInitialized) {
      window.growfundInfiniteScrollInitialized = true;

      const infiniteScrollInstance = initializeInfiniteScroll({
        campaignListContainerId: 'growfund-campaign-list-container',
        initialPage: 2, // Always start from page 2 since page 1 is already loaded
        itemsPerPage: 12,
        initialHasMore: growfundArchiveData?.initialHasMore || false,
        loadCampaignsFunction: (currentPage, itemsPerPage) => {
          // Create a clean params object without page/limit to avoid conflicts
          const currentParams = new URLSearchParams(window.location.search);
          const params = new URLSearchParams();

          // Copy all current params except page and limit
          for (const [key, value] of currentParams.entries()) {
            if (key !== 'page' && key !== 'limit' && key !== 'action' && key !== 'is_ajax_load') {
              params.set(key, value);
            }
          }

          // Set the correct page and limit for infinite scroll
          params.set('page', currentPage.toString());
          params.set('limit', itemsPerPage.toString());
          params.set('action', 'growfund_ajax_get_campaigns_archive');
          params.set('is_ajax_load', 'true');
          params.set('_wpnonce', growfund.ajax_nonce);

          const url = growfund.ajax_url + '?' + params.toString();

          return fetch(url)
            .then((response) => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then((data) => {
              return data;
            });
        },
      });

      // Store the infinite scroll instance globally for proper cleanup
      window.growfundArchiveInfiniteScroll = infiniteScrollInstance;
    } else {
      if (window.growfundInfiniteScrollInitialized) {
      }
    }
  }, 100); // Small delay to ensure search.js runs first
});

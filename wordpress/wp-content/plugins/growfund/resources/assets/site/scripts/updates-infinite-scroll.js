/**
 * Updates Infinite Scroll Functionality
 * Handles infinite scrolling for campaign updates
 */

document.addEventListener('DOMContentLoaded', function () {
  // Add a small delay to ensure AJAX actions are registered
  setTimeout(function () {
    const updatesListContainer = document.querySelector('.growfund-updates-list');
    if (!updatesListContainer) {
      return;
    }

    // Get campaign ID from the page
    const campaignId = getCampaignId();

    if (!campaignId) {
      return;
    }

    // Only initialize infinite scroll if it hasn't already been done
    if (!window.growfundUpdatesInfiniteScrollInitialized) {
      window.growfundUpdatesInfiniteScrollInitialized = true;

      // Check if there are already updates loaded
      const existingUpdates = updatesListContainer.querySelectorAll('.growfund-update-item');
      const hasInitialUpdates = existingUpdates.length > 0;

      const infiniteScrollInstance = initializeInfiniteScroll({
        campaignListContainerId: 'growfund-updates-list',
        initialPage: 2, // Start from page 2 since page 1 is already loaded
        itemsPerPage: 12, // Increased from 4 to 12 for better performance
        initialHasMore: true, // We'll determine this from the AJAX response
        onContentLoaded: () => {
          // Reinitialize timeline when new updates are loaded with a small delay
          setTimeout(() => {
            // Reinitialize timeline updates
            if (typeof reinitializeTimelineUpdates === 'function') {
              reinitializeTimelineUpdates();
            }

            // Reattach event listeners to newly loaded content
            if (typeof window.reattachUpdatesEventListeners === 'function') {
              window.reattachUpdatesEventListeners();
            }

            // Force a scroll event to update the timeline position
            window.dispatchEvent(new Event('scroll'));
          }, 200); // Increased delay to ensure DOM is ready
        },
        loadCampaignsFunction: (currentPage, itemsPerPage) => {
          const params = new URLSearchParams();
          params.set('action', 'growfund_get_campaign_updates');
          params.set('campaign_id', parseInt(campaignId)); // Ensure it's an integer
          params.set('page', currentPage);
          params.set('limit', itemsPerPage);
          params.set('_wpnonce', growfund.ajax_nonce);

          return fetch(growfund.ajax_url, {
            method: 'POST',
            body: params,
          })
            .then((response) => {
              if (!response.ok) {
                return response.text().then((text) => {
                  throw new Error(`HTTP error! status: ${response.status} - ${text}`);
                });
              }
              return response.json();
            })
            .then((data) => {
              return data;
            })
            .catch((error) => {
              throw error;
            });
        },
      });
    }
  }, 100); // 100ms delay to ensure AJAX actions are registered
});

/**
 * Get campaign ID from the page
 * @returns {string|null} The campaign ID or null if not found
 */
function getCampaignId() {
  // Try to get from the tab content element first (where it's actually set)
  const tabContentElement = document.querySelector('.growfund-tab-content--updates');
  if (tabContentElement && tabContentElement.dataset.campaignId) {
    return tabContentElement.dataset.campaignId;
  }

  // Try to get from any element with data-campaign-id
  const campaignElement = document.querySelector('[data-campaign-id]');
  if (campaignElement) {
    return campaignElement.dataset.campaignId;
  }

  // Try to get from URL
  const urlParams = new URLSearchParams(window.location.search);
  const campaignId = urlParams.get('campaign_id');
  if (campaignId) {
    return campaignId;
  }

  // Try to extract from current URL path
  const pathSegments = window.location.pathname.split('/');
  const campaignIndex = pathSegments.findIndex((segment) => segment === 'campaigns');
  if (campaignIndex !== -1 && pathSegments[campaignIndex + 1]) {
    return pathSegments[campaignIndex + 1];
  }

  return null;
}

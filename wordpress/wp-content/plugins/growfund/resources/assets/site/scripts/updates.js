/**
 * Updates Tab Content Functionality
 * Handles navigation between updates list view and detail view
 */

// Global function to properly reinitialize timeline when returning to list view
window.reinitializeTimelineFromDetailView = function () {
  setTimeout(() => {
    // Completely destroy and recreate the timeline instance
    if (timelineUpdatesInstance) {
      // Remove the old scroll listener
      if (timelineUpdatesInstance.scrollHandler) {
        document.removeEventListener('scroll', timelineUpdatesInstance.scrollHandler);
      }

      // Destroy the old instance
      timelineUpdatesInstance = null;
    }

    // Create a new timeline instance
    timelineUpdatesInstance = new TimelineUpdates();

    // Ensure the list view is properly positioned
    const listView = document.getElementById('updates-list-view');
    if (listView) {
      listView.style.display = 'block';
      // Force a layout recalculation
      listView.offsetHeight;
    }
  }, 100);
};

// Global function to reattach event listeners to newly loaded content
window.reattachUpdatesEventListeners = function () {
  const listView = document.getElementById('updates-list-view');
  const detailViews = document.querySelectorAll('.growfund-update-detail-view');
  const backToListBtns = document.querySelectorAll('.back-to-updates');
  const readMoreBtns = document.querySelectorAll('button[data-update-id]');

  if (listView && detailViews.length > 0) {
    /**
     * Show the detail view
     */
    function showDetailView(updateId) {
      window.lastViewedUpdateId = updateId; // Store last viewed update ID
      listView.style.display = 'none';
      // Hide all detail views first
      detailViews.forEach((view) => {
        view.style.display = 'none';
        // Dispatch event for hidden detail view
        view.dispatchEvent(
          new CustomEvent('updateDetailViewHidden', { detail: { updateId: view.id } }),
        );
      });

      // Show the requested detail view
      const targetView = document.getElementById('update-detail-view-' + updateId);
      if (targetView) {
        targetView.style.display = 'block';
        // Dispatch event for shown detail view
        targetView.dispatchEvent(
          new CustomEvent('updateDetailViewShown', { detail: { updateId: updateId } }),
        );
      } else {
        // If detail view doesn't exist, load it via AJAX
        loadUpdateDetailView(updateId);
        return; // Exit early, the function will handle showing the view
      }

      // Optional: ensure the tab content is in view without jumping to the very top
      const tabWrapper = document.querySelector('.growfund-tab-content--updates');
      if (tabWrapper) {
        tabWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      // ---------------------------------------------
      // Push the update slug to the URL as a query param
      // This allows sharing / bookmarking a specific update
      // ---------------------------------------------
      try {
        const url = new URL(window.location);
        url.searchParams.set('update', updateId);
        window.history.pushState({ updateId }, '', url);
      } catch (e) {
        // Silently handle URL update errors
      }
    }

    /**
     * Load update detail view via AJAX
     */
    function loadUpdateDetailView(updateId) {
      // Get campaign ID from the page
      const campaignId = getCampaignId();
      if (!campaignId) {
        console.error('No campaign ID found');
        return;
      }

      // Show loading state
      const tabContent = document.querySelector('.growfund-tab-content--updates');
      if (tabContent) {
        tabContent.innerHTML +=
          '<div id="loading-detail-view" style="text-align: center; padding: 40px;">Loading update...</div>';
      }

      const params = new URLSearchParams();
      params.set('action', 'growfund_get_update_detail');
      params.set('campaign_id', parseInt(campaignId));
      params.set('campaign_update_id', updateId);
      params.set('_wpnonce', growfund.ajax_nonce);

      fetch(growfund.ajax_url, {
        method: 'POST',
        body: params,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Remove loading indicator
            const loadingIndicator = document.getElementById('loading-detail-view');
            if (loadingIndicator) {
              loadingIndicator.remove();
            }

            // Insert the detail view HTML into the tab content
            if (tabContent) {
              tabContent.insertAdjacentHTML('beforeend', data.data.html);
            }

            // Now show the detail view
            showDetailView(updateId);

            // Reattach event listeners to include the new detail view
            window.reattachUpdatesEventListeners();

            // Reinitialize comment form visibility for the new detail view
            if (typeof window.initializeUpdateCommentFormVisibility === 'function') {
              window.initializeUpdateCommentFormVisibility();
            }

            // Reinitialize comment system for the new detail view
            if (typeof initializeCommentSystem === 'function') {
              // Find the newly added update detail view
              const newUpdateDetailView = tabContent.querySelector(
                '.growfund-update-detail-view:last-child',
              );
              if (
                newUpdateDetailView &&
                typeof window.initializeCommentFormsForUpdateView === 'function'
              ) {
                window.initializeCommentFormsForUpdateView(newUpdateDetailView);
              } else {
                initializeCommentSystem();
              }
            }

            // Also try to reinitialize all comment forms as a fallback
            if (typeof window.reinitializeCommentForms === 'function') {
              setTimeout(() => {
                window.reinitializeCommentForms();
              }, 100);
            }

            // Reinitialize social sharing for the new content
            if (typeof window.initializeSocialSharing === 'function') {
              window.initializeSocialSharing();
            }

            // Clean up old update detail views to prevent conflicts
            cleanupOldUpdateDetailViews();
          } else {
            // Remove loading indicator and show error
            const loadingIndicator = document.getElementById('loading-detail-view');
            if (loadingIndicator) {
              loadingIndicator.innerHTML = 'Error loading update. Please try again.';
            }
          }
        })
        .catch((error) => {
          console.error('AJAX request error', error);
          // Remove loading indicator and show error
          const loadingIndicator = document.getElementById('loading-detail-view');
          if (loadingIndicator) {
            loadingIndicator.innerHTML = 'Error loading update. Please try again.';
          }
        });
    }

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

    /**
     * Show the list view
     */
    function showListView() {
      detailViews.forEach((view) => {
        view.style.display = 'none';
        // Dispatch event for hidden detail view
        view.dispatchEvent(
          new CustomEvent('updateDetailViewHidden', { detail: { updateId: view.id } }),
        );
      });
      listView.style.display = 'block';

      // Optional: ensure the tab content is in view without jumping to the very top
      const tabWrapper = document.querySelector('.growfund-tab-content--updates');
      if (tabWrapper) {
        tabWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      // ---------------------------------------------
      // Remove the update slug from the URL when returning to list view
      // ---------------------------------------------
      try {
        const url = new URL(window.location);
        url.searchParams.delete('update');
        window.history.pushState({}, '', url.pathname + url.search + url.hash);
      } catch (e) {
        // Silently handle URL reset errors
      }

      // Scroll to the last viewed update if available
      if (window.lastViewedUpdateId) {
        setTimeout(() => {
          const updateElem = document.querySelector(
            `.growfund-update-item[data-update-id='${window.lastViewedUpdateId}']`,
          );
          if (updateElem) {
            updateElem.scrollIntoView({ behavior: 'auto', block: 'center' });
            // Force a scroll event so the timeline updates
            window.dispatchEvent(new Event('scroll'));
          }
          window.lastViewedUpdateId = null; // Clear after use
        }, 150);
      }

      // Reinitialize timeline when returning to list view
      if (typeof window.reinitializeTimelineFromDetailView === 'function') {
        window.reinitializeTimelineFromDetailView();
      }
    }

    /**
     * Handle read more button clicks
     */
    function handleReadMoreClick(event) {
      event.preventDefault();
      const button = event.currentTarget;
      const updateId = button.getAttribute('data-update-id');
      if (updateId) {
        showDetailView(updateId);
      }
    }

    /**
     * Handle back to list button click
     */
    function handleBackToListClick(event) {
      event.preventDefault();
      showListView();
    }

    /**
     * Clean up old update detail views to prevent conflicts
     */
    function cleanupOldUpdateDetailViews() {
      // Remove all update detail views except the most recent one
      const detailViews = document.querySelectorAll('.growfund-update-detail-view');
      if (detailViews.length > 1) {
        // Keep only the last one (most recent)
        for (let i = 0; i < detailViews.length - 1; i++) {
          detailViews[i].remove();
        }
      }
    }

    // Remove existing event listeners to prevent duplicates
    backToListBtns.forEach((btn) => {
      btn.removeEventListener('click', handleBackToListClick);
      btn.addEventListener('click', handleBackToListClick);
    });

    readMoreBtns.forEach((btn) => {
      btn.removeEventListener('click', handleReadMoreClick);
      btn.addEventListener('click', handleReadMoreClick);
    });
  }
};

// Updates tab functionality
document.addEventListener('DOMContentLoaded', function () {
  const listView = document.getElementById('updates-list-view');
  const detailViews = document.querySelectorAll('.growfund-update-detail-view');
  const backToListBtns = document.querySelectorAll('.back-to-updates');
  const readMoreBtns = document.querySelectorAll('button[data-update-id]');

  if (listView && detailViews.length > 0) {
    /**
     * Show the detail view
     */
    function showDetailView(updateId) {
      window.lastViewedUpdateId = updateId; // Store last viewed update ID
      listView.style.display = 'none';
      // Hide all detail views first
      detailViews.forEach((view) => {
        view.style.display = 'none';
      });

      // Show the requested detail view
      const targetView = document.getElementById('update-detail-view-' + updateId);
      if (targetView) {
        targetView.style.display = 'block';
      } else {
        loadUpdateDetailView(updateId);
        return;
      }

      // ---------------------------------------------
      // Push the update slug to the URL as a query param
      // This allows sharing / bookmarking a specific update
      // ---------------------------------------------
      try {
        const url = new URL(window.location);
        url.searchParams.set('update', updateId);
        window.history.pushState({ updateId }, '', url);
      } catch (e) {
        // Silently handle URL update errors
      }
    }

    /**
     * Load update detail view via AJAX
     */
    function loadUpdateDetailView(updateId) {
      // Get campaign ID from the page
      const campaignId = getCampaignId();
      if (!campaignId) {
        console.error('No campaign ID found');
        return;
      }

      // Show loading state
      const tabContent = document.querySelector('.growfund-tab-content--updates');
      if (tabContent) {
        // Create loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'loading-detail-view';
        loadingDiv.style.textAlign = 'center';
        loadingDiv.style.padding = '40px';
        loadingDiv.textContent = 'Loading update...';

        tabContent.appendChild(loadingDiv);
      }

      const params = new URLSearchParams();
      params.set('action', 'growfund_get_update_detail');
      params.set('campaign_id', parseInt(campaignId));
      params.set('campaign_update_id', updateId);
      params.set('_wpnonce', growfund.ajax_nonce);

      fetch(growfund.ajax_url, {
        method: 'POST',
        body: params,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Remove loading indicator
            const loadingIndicator = document.getElementById('loading-detail-view');
            if (loadingIndicator) {
              loadingIndicator.remove();
            }

            // Insert the detail view HTML into the tab content (assuming server-side sanitization)
            if (tabContent) {
              tabContent.insertAdjacentHTML('beforeend', data.data.html);
            }

            // Now show the detail view
            showDetailView(updateId);

            // Reattach event listeners to include the new detail view
            window.reattachUpdatesEventListeners();

            // Reinitialize comment form visibility for the new detail view
            if (typeof window.initializeUpdateCommentFormVisibility === 'function') {
              window.initializeUpdateCommentFormVisibility();
            }

            // Reinitialize comment system for the new detail view
            if (typeof initializeCommentSystem === 'function') {
              // Find the newly added update detail view
              const newUpdateDetailView = tabContent.querySelector(
                '.growfund-update-detail-view:last-child',
              );
              if (
                newUpdateDetailView &&
                typeof window.initializeCommentFormsForUpdateView === 'function'
              ) {
                window.initializeCommentFormsForUpdateView(newUpdateDetailView);
              } else {
                initializeCommentSystem();
              }
            }

            // Also try to reinitialize all comment forms as a fallback
            if (typeof window.reinitializeCommentForms === 'function') {
              setTimeout(() => {
                window.reinitializeCommentForms();
              }, 100);
            }

            // Reinitialize social sharing for the new content
            if (typeof window.initializeSocialSharing === 'function') {
              window.initializeSocialSharing();
            }

            // Clean up old update detail views to prevent conflicts
            cleanupOldUpdateDetailViews();
          } else {
            // Remove loading indicator and show error
            const loadingIndicator = document.getElementById('loading-detail-view');
            if (loadingIndicator) {
              loadingIndicator.textContent = 'Error loading update. Please try again.';
            }
          }
        })
        .catch((error) => {
          console.error('AJAX request error', error);
          // Remove loading indicator and show error
          const loadingIndicator = document.getElementById('loading-detail-view');
          if (loadingIndicator) {
            loadingIndicator.textContent = 'Error loading update. Please try again.';
          }
        });
    }

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

    /**
     * Show the list view
     */
    function showListView() {
      detailViews.forEach((view) => {
        view.style.display = 'none';
      });
      listView.style.display = 'block';

      // Optional: ensure the tab content is in view without jumping to the very top
      const tabWrapper = document.querySelector('.growfund-tab-content--updates');
      if (tabWrapper) {
        tabWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      // ---------------------------------------------
      // Remove the update slug from the URL when returning to list view
      // ---------------------------------------------
      try {
        const url = new URL(window.location);
        url.searchParams.delete('update');
        window.history.pushState({}, '', url.pathname + url.search + url.hash);
      } catch (e) {
        // Silently handle URL reset errors
      }

      // Scroll to the last viewed update if available
      if (window.lastViewedUpdateId) {
        setTimeout(() => {
          const updateElem = document.querySelector(
            `.growfund-update-item[data-update-id='${window.lastViewedUpdateId}']`,
          );
          if (updateElem) {
            updateElem.scrollIntoView({ behavior: 'auto', block: 'center' });
            // Force a scroll event so the timeline updates
            window.dispatchEvent(new Event('scroll'));
          }
          window.lastViewedUpdateId = null; // Clear after use
        }, 150);
      }

      // Reinitialize timeline when returning to list view
      if (typeof window.reinitializeTimelineFromDetailView === 'function') {
        window.reinitializeTimelineFromDetailView();
      }
    }

    /**
     * Handle read more button clicks
     */
    function handleReadMoreClick(event) {
      event.preventDefault();
      const button = event.currentTarget;
      const updateId = button.getAttribute('data-update-id');
      if (updateId) {
        showDetailView(updateId);
      }
    }

    /**
     * Handle back to list button click
     */
    function handleBackToListClick(event) {
      event.preventDefault();
      showListView();
    }

    /**
     * Clean up old update detail views to prevent conflicts
     */
    function cleanupOldUpdateDetailViews() {
      // Remove all update detail views except the most recent one
      const detailViews = document.querySelectorAll('.growfund-update-detail-view');
      if (detailViews.length > 1) {
        // Keep only the last one (most recent)
        for (let i = 0; i < detailViews.length - 1; i++) {
          detailViews[i].remove();
        }
      }
    }

    // Event listeners
    backToListBtns.forEach((btn) => {
      btn.addEventListener('click', handleBackToListClick);
    });

    readMoreBtns.forEach((btn) => {
      btn.addEventListener('click', handleReadMoreClick);
    });

    // ---------------------------------------------
    // On initial load, check if a specific update is
    // requested via the `?update=` query parameter.
    // If present, directly open that detail view;
    // otherwise fall back to list view.
    // ---------------------------------------------
    try {
      const initialUpdateId = new URL(window.location).searchParams.get('update');
      if (initialUpdateId) {
        showDetailView(initialUpdateId);
      } else {
        showListView();
      }
    } catch (e) {
      // Fallback in environments without URL support
      showListView();
    }
  }
});

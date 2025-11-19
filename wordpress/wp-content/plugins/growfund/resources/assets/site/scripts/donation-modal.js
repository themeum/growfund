/**
 * Donation Modal JavaScript
 * Handles modal functionality, sorting, and infinite scroll for donations
 */

(function () {
  'use strict';

  // Global variables
  let currentModal = null;
  let currentSort = 'newest';
  let currentPage = 1;
  let hasMore = true;
  let isLoading = false;

  /**
   * Initialize donation modal functionality
   */
  function initDonationModal() {
    // Add event listeners for modal triggers
    document.addEventListener('click', handleModalTrigger);

    // Add event listeners for modal close
    document.addEventListener('click', handleModalClose);

    // Add event listeners for sorting
    document.addEventListener('click', handleSortChange);

    // Add event listeners for donate button
    document.addEventListener('click', handleDonateButton);

    // Add event listeners for overlay click
    document.addEventListener('click', handleOverlayClick);

    // Add event listeners for escape key
    document.addEventListener('keydown', handleEscapeKey);
  }

  /**
   * Handle modal trigger clicks
   */
  function handleModalTrigger(event) {
    const trigger = event.target.closest('[data-modal-trigger]');
    if (!trigger) return;

    event.preventDefault();

    const modalId = trigger.getAttribute('data-modal-trigger');
    const sort = trigger.getAttribute('data-sort') || 'newest';

    openModal(modalId, sort);
  }

  /**
   * Handle modal close clicks
   */
  function handleModalClose(event) {
    const closeBtn = event.target.closest('.growfund-donation-modal__close');
    if (!closeBtn) return;

    event.preventDefault();
    closeModal();
  }

  /**
   * Handle sort button clicks
   */
  function handleSortChange(event) {
    const sortBtn = event.target.closest('.growfund-donation-modal__sort-btn');
    if (!sortBtn || !currentModal) return;

    event.preventDefault();

    const sort = sortBtn.getAttribute('data-sort');
    if (sort === currentSort) return;

    // Update active state
    document.querySelectorAll('.growfund-donation-modal__sort-btn').forEach((btn) => {
      btn.classList.remove('growfund-donation-modal__sort-btn--active');
    });
    sortBtn.classList.add('growfund-donation-modal__sort-btn--active');

    // Reset pagination and load new data
    currentSort = sort;
    currentPage = 1;
    hasMore = true;

    // Clear existing content
    const listContainer = currentModal.querySelector('.growfund-donation-modal__list');
    listContainer.innerHTML = '';

    // Load first page
    loadDonations(currentPage, true);
  }

  /**
   * Handle donate button clicks
   */
  function handleDonateButton(event) {
    const donateBtn = event.target.closest('.growfund-donation-modal__donate-btn');
    if (!donateBtn) return;

    event.preventDefault();

    // Get campaign ID from modal
    const campaignId = getCampaignIdFromModal();
    if (!campaignId) {
      console.error('Campaign ID not found for donation redirect');
      return;
    }

    // Get checkout URL before closing the modal
    const checkoutUrl = currentModal.getAttribute('data-checkout-url');
    if (!checkoutUrl) {
      console.error('Checkout URL not available for campaign:', campaignId);
      return;
    }

    // Close modal
    closeModal();

    // Redirect to checkout page using server-generated URL
    window.location.href = checkoutUrl;
  }

  /**
   * Handle overlay clicks
   */
  function handleOverlayClick(event) {
    if (event.target.classList.contains('growfund-donation-modal__overlay')) {
      closeModal();
    }
  }

  /**
   * Handle escape key
   */
  function handleEscapeKey(event) {
    if (event.key === 'Escape' && currentModal) {
      closeModal();
    }
  }

  /**
   * Open donation modal
   */
  function openModal(modalId, sort) {
    currentModal = document.getElementById(modalId);
    if (!currentModal) {
      console.error('Modal not found:', modalId);
      console.log('Available modals:', document.querySelectorAll('[id^="growfund-donation-modal-"]'));
      return;
    }

    // Set initial sort
    currentSort = sort;
    currentPage = 1;
    hasMore = true;
    isLoading = false;

    // Show modal
    currentModal.classList.add('growfund-donation-modal--open');
    document.body.style.overflow = 'hidden';

    // Set active sort button
    const activeSortBtn = currentModal.querySelector(`[data-sort="${sort}"]`);
    if (activeSortBtn) {
      document.querySelectorAll('.growfund-donation-modal__sort-btn').forEach((btn) => {
        btn.classList.remove('growfund-donation-modal__sort-btn--active');
      });
      activeSortBtn.classList.add('growfund-donation-modal__sort-btn--active');
    }

    // Load initial donations
    loadDonations(currentPage, true);
  }

  /**
   * Close donation modal
   */
  function closeModal() {
    if (!currentModal) return;

    // Remove scroll listener
    const scrollableContainer = currentModal.querySelector('.growfund-donation-modal__list-container');
    if (scrollableContainer && currentModal._scrollListener) {
      scrollableContainer.removeEventListener('scroll', currentModal._scrollListener);
      currentModal._scrollListener = null;
    }

    // Hide modal
    currentModal.classList.remove('growfund-donation-modal--open');
    document.body.style.overflow = '';

    // Reset variables
    currentModal = null;
    currentSort = 'newest';
    currentPage = 1;
    hasMore = true;
    isLoading = false;
  }

  /**
   * Load donations via AJAX
   */
  function loadDonations(page, reset = false) {
    if (isLoading || (!hasMore && !reset)) return;

    isLoading = true;
    showLoading(true);

    const campaignId = getCampaignIdFromModal();
    if (!campaignId) {
      console.error('Campaign ID not found');
      showLoading(false);
      return;
    }

    // Prepare AJAX data
    const data = new FormData();
    data.append('action', 'growfund_get_campaign_donations_modal');
    data.append('campaign_id', campaignId);
    data.append('page', page);
    data.append('limit', 10);
    data.append('sort', currentSort);
    data.append('_wpnonce', growfund.ajax_nonce);

    // Make AJAX request
    fetch(growfund.ajax_url, {
      method: 'POST',
      body: data,
    })
      .then((response) => {
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          handleDonationsResponse(data.data, reset);
        } else {
          showError('Failed to load donations');
        }
      })
      .catch((error) => {
        showError('Failed to load donations');
      })
      .finally(() => {
        isLoading = false;
        showLoading(false);
      });
  }

  /**
   * Handle donations response
   */
  function handleDonationsResponse(response, reset) {
    const listContainer = currentModal.querySelector('.growfund-donation-modal__list');

    if (reset) {
      listContainer.innerHTML = '';
    }

    if (response.html) {
      listContainer.insertAdjacentHTML('beforeend', response.html);
    }

    // Update pagination state
    hasMore = response.has_more || false;
    currentPage = response.current_page || 1;

    // Show empty state if no donations
    if (reset && (!response.html || response.html.trim() === '')) {
      listContainer.innerHTML =
        '<div class="growfund-donation-modal__empty"><p>No donations found</p></div>';
    }

    // Initialize infinite scroll if there are more donations
    if (hasMore && response.html) {
      initializeInfiniteScroll();
    }
  }

  /**
   * Get campaign ID from modal
   */
  function getCampaignIdFromModal() {
    if (!currentModal) return null;

    const modalId = currentModal.id;
    const match = modalId.match(/growfund-donation-modal-(\d+)/);
    return match ? match[1] : null;
  }

  /**
   * Show/hide loading indicator
   */
  function showLoading(show) {
    if (!currentModal) return;

    const loadingEl = currentModal.querySelector('.growfund-donation-modal__loading');
    if (loadingEl) {
      loadingEl.style.display = show ? 'flex' : 'none';
    }
  }

  /**
   * Initialize infinite scroll
   */
  function initializeInfiniteScroll() {
    if (!currentModal) return;

    // Get the scrollable container (the list container's parent)
    const scrollableContainer = currentModal.querySelector('.growfund-donation-modal__list-container');
    const listContainer = currentModal.querySelector('.growfund-donation-modal__list');

    if (!scrollableContainer || !listContainer) {
      return;
    }

    // Remove existing scroll listener
    if (currentModal._scrollListener) {
      scrollableContainer.removeEventListener('scroll', currentModal._scrollListener);
    }

    // Create new scroll listener
    currentModal._scrollListener = function () {
      if (isLoading || !hasMore) return;

      const scrollTop = scrollableContainer.scrollTop;
      const scrollHeight = scrollableContainer.scrollHeight;
      const clientHeight = scrollableContainer.clientHeight;

      // Load more when user scrolls to bottom (with 100px threshold)
      if (scrollTop + clientHeight >= scrollHeight - 100) {
        loadDonations(currentPage + 1, false);
      }
    };

    // Add scroll listener to the scrollable container
    scrollableContainer.addEventListener('scroll', currentModal._scrollListener);
  }

  /**
   * Show error message
   */
  function showError(message) {
    if (!currentModal) return;

    const listContainer = currentModal.querySelector('.growfund-donation-modal__list');
    if (listContainer) {
      listContainer.innerHTML = `<div class="growfund-donation-modal__empty"><p>${message}</p></div>`;
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDonationModal);
  } else {
    initDonationModal();
  }

  // Export functions for external use
  window.CFDonationModal = {
    open: openModal,
    close: closeModal,
    loadDonations: loadDonations,
  };
})();

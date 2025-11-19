document.addEventListener('DOMContentLoaded', () => {
  // Skip if smooth scrolling is already initialized
  if (window.growfundSmoothSliderInitialized) {
    return;
  }

  const sliders = document.querySelectorAll('.growfund-campaign-slider');

  sliders.forEach((slider, index) => {
    const track = slider.querySelector('.growfund-campaign-slider__track');
    const prevBtn = slider.querySelector('.growfund-campaign-slider__button--prev');
    const nextBtn = slider.querySelector('.growfund-campaign-slider__button--next');

    if (!track) {
      return;
    }

    // If no navigation buttons, this might be a static display (fewer items than per_page)
    if (!prevBtn || !nextBtn) {
      return;
    }

    let currentPage = parseInt(slider.dataset.currentPage || '1', 10);
    const perPage = parseInt(slider.dataset.perPage || '4', 10);
    let hasMore = slider.dataset.hasMore === '1';
    const variant = slider.dataset.variant || 'list';
    let totalLoaded = parseInt(slider.dataset.totalLoaded || '0', 10);
    const limit = parseInt(slider.dataset.limit || '12', 10);

    // Get all campaign items for client-side sliding
    const campaignItems = track.querySelectorAll('.growfund-campaign-slider__item');
    const totalItems = campaignItems.length;

    if (totalItems === 0) {
      return;
    }

    let currentIndex = 0;
    let maxIndex = Math.max(0, totalItems - perPage);

    // Check if we should use client-side sliding (when we have items loaded and can slide)
    // For featured sliders, use client-side sliding if we have enough items, otherwise use AJAX
    let useClientSideSliding = totalItems > perPage;

    if (typeof growfund === 'undefined' || !growfund.ajax_url) {
      return;
    }

    function updateButtonStates() {
      if (useClientSideSliding) {
        prevBtn.disabled = currentIndex <= 0;
        // Allow next button to be clicked if there are more items to load
        nextBtn.disabled = currentIndex >= maxIndex && !hasMore;
      } else {
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = !hasMore;
      }
    }

    function showItems(startIndex, endIndex) {
      // Calculate the transform offset for smooth sliding
      const firstItem = campaignItems[0];
      if (!firstItem) return;

      // Get the actual item width including margin/padding
      const itemRect = firstItem.getBoundingClientRect();
      const itemWidth = itemRect.width;

      // Get the gap from CSS (fallback to 16px if not available)
      const computedStyle = window.getComputedStyle(track);
      const gap = parseInt(computedStyle.gap) || 16;

      const slideOffset = -(startIndex * (itemWidth + gap));

      // Apply smooth transform to the track
      track.style.transform = `translateX(${slideOffset}px)`;

      // Show all items but let CSS handle the sliding effect
      campaignItems.forEach((item, index) => {
        item.classList.remove('growfund-campaign-slider__item--hidden');
        item.style.visibility = 'visible';
      });
    }

    function slideToIndex(newIndex) {
      if (newIndex < 0 || newIndex > maxIndex) {
        return;
      }

      currentIndex = newIndex;

      // Show the appropriate items (CSS transition is handled by the track's transition property)
      showItems(currentIndex, currentIndex + perPage);

      // Update button states
      updateButtonStates();
    }

    function slideNext() {
      if (currentIndex < maxIndex) {
        slideToIndex(currentIndex + 1);
      } else if (hasMore) {
        // Load more items via AJAX when we reach the end
        fetchCampaigns(currentPage + 1);
      }
    }

    function slidePrev() {
      if (currentIndex > 0) {
        slideToIndex(currentIndex - 1);
      }
    }

    async function fetchCampaigns(page, customLimit = null) {
      currentPage = page;
      track.style.opacity = '0.5';
      prevBtn.disabled = true;
      nextBtn.disabled = true;

      const queryParams = new URLSearchParams({
        page: currentPage,
        limit: customLimit !== null ? customLimit : limit, // Use custom limit if provided
        variant: variant,
        action: 'growfund_get_campaign_slider_items',
        _wpnonce: growfund.ajax_nonce,
      });

      try {
        const response = await fetch(growfund.ajax_url, {
          method: 'POST',
          body: queryParams,
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
          // Only append unique items by campaign ID
          const existingIds = new Set(
            [...track.querySelectorAll('.growfund-campaign-slider__item')].map(
              (item) => item.dataset.campaignId,
            ),
          );
          const tempDiv = document.createElement('div');
          // Insert server-provided HTML (assuming server-side sanitization)
          tempDiv.insertAdjacentHTML('beforeend', result.data.html);
          let addedCount = 0;
          [...tempDiv.children].forEach((item) => {
            if (item.dataset.campaignId && !existingIds.has(item.dataset.campaignId)) {
              track.appendChild(item);
              addedCount++;
            }
          });

          // Update total loaded count
          const newItems = track.querySelectorAll('.growfund-campaign-slider__item');
          totalLoaded = newItems.length;
          slider.dataset.totalLoaded = totalLoaded.toString();

          // Recalculate max index for client-side sliding based on actual DOM items
          const actualItems = track.querySelectorAll('.growfund-campaign-slider__item');
          const actualTotal = actualItems.length;
          maxIndex = Math.max(0, actualTotal - perPage);
          // For featured sliders, switch to client-side sliding when we have enough items
          useClientSideSliding = totalLoaded > perPage;

          hasMore = result.data.has_more;
          slider.dataset.currentPage = currentPage;
          slider.dataset.hasMore = hasMore.toString();

          // If we now have enough items for client-side sliding, show the next set
          if (useClientSideSliding && currentIndex < maxIndex) {
            slideToIndex(currentIndex + 1);
          }
        } else {
          // Create error message
          const errorP = document.createElement('p');
          errorP.textContent = 'Error loading campaigns.';

          // Clear track and add error message
          while (track.firstChild) {
            track.removeChild(track.firstChild);
          }
          track.appendChild(errorP);
        }
      } catch (error) {
        // Create error message
        const errorP = document.createElement('p');
        errorP.textContent = 'Error loading campaigns.';

        // Clear track and add error message
        while (track.firstChild) {
          track.removeChild(track.firstChild);
        }
        track.appendChild(errorP);
      } finally {
        track.style.opacity = '1';
        updateButtonStates();
      }
    }

    // Initialize slider
    function initSlider() {
      // Set up track for smooth scrolling
      track.style.display = 'flex';

      // Ensure all items are visible initially
      campaignItems.forEach((item) => {
        item.classList.remove('growfund-campaign-slider__item--hidden');
        item.style.visibility = 'visible';
      });

      if (useClientSideSliding) {
        // Show first items initially for client-side sliding
        showItems(0, perPage);
      } else {
        // For AJAX-based sliders (like featured), ensure items are positioned correctly
        // Show only the first perPage items initially, even for AJAX sliders
        showItems(0, perPage);
      }

      // Update button states
      updateButtonStates();
    }

    // Event listeners
    prevBtn.addEventListener('click', (e) => {
      e.preventDefault();

      if (useClientSideSliding) {
        slidePrev();
      } else if (currentPage > 1) {
        fetchCampaigns(currentPage - 1);
      }
    });

    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();

      if (useClientSideSliding) {
        slideNext();
      } else if (hasMore) {
        // Load more items via AJAX
        fetchCampaigns(currentPage + 1);
      }
    });

    // Keyboard navigation
    slider.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        if (useClientSideSliding) {
          slidePrev();
        } else if (currentPage > 1) {
          fetchCampaigns(currentPage - 1);
        }
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        if (useClientSideSliding) {
          slideNext();
        } else if (hasMore) {
          fetchCampaigns(currentPage + 1);
        }
      }
    });

    // Touch/swipe support for mobile
    let startX = 0;
    let currentX = 0;
    let isDragging = false;

    track.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
      isDragging = true;
    });

    track.addEventListener('touchmove', (e) => {
      if (!isDragging) return;

      currentX = e.touches[0].clientX;
      const diff = startX - currentX;

      // Prevent default only if there's significant movement
      if (Math.abs(diff) > 10) {
        e.preventDefault();
      }
    });

    track.addEventListener('touchend', (e) => {
      if (!isDragging) return;

      const diff = startX - currentX;
      const threshold = 50; // Minimum swipe distance

      if (Math.abs(diff) > threshold) {
        if (diff > 0) {
          // Swiped left - go to next
          if (useClientSideSliding) {
            slideNext();
          } else if (hasMore) {
            fetchCampaigns(currentPage + 1);
          }
        } else {
          // Swiped right - go to previous
          if (useClientSideSliding) {
            slidePrev();
          } else if (currentPage > 1) {
            fetchCampaigns(currentPage - 1);
          }
        }
      }

      isDragging = false;
    });

    // Initialize the slider
    initSlider();

    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(() => {
        if (useClientSideSliding) {
          // Recalculate positions after resize
          slideToIndex(currentIndex);
        }
      }, 250);
    });
  });

  // Mark as initialized
  window.growfundSmoothSliderInitialized = true;
});
// Cache busting: 1752746158

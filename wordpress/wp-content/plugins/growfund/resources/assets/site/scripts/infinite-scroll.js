function initializeInfiniteScroll(config) {
  const {
    campaignListContainerId,
    initialPage,
    itemsPerPage,
    initialHasMore,
    loadCampaignsFunction,
    onContentLoaded,
  } = config;
  const campaignListContainer = document.getElementById(campaignListContainerId);
  let currentPage = initialPage || 1;
  let isLoading = false;
  let hasMore = initialHasMore;
  let observer = null;
  let listObserver = null;
  let loadingIndicator = null;

  if (!campaignListContainer) {
    return;
  }

  // Create a loading indicator element
  loadingIndicator = document.createElement('div');
  loadingIndicator.textContent = 'Loading...';
  loadingIndicator.style.textAlign = 'center';
  loadingIndicator.style.padding = '20px';
  loadingIndicator.style.display = 'none'; // Initially hidden
  loadingIndicator.style.position = 'absolute'; // Position absolutely to prevent layout shifts
  loadingIndicator.style.left = '0';
  loadingIndicator.style.right = '0';
  loadingIndicator.style.zIndex = '10';
  loadingIndicator.className = 'growfund-loading-indicator';

  // Insert the loading indicator at the end of the container to prevent layout shifts
  campaignListContainer.appendChild(loadingIndicator);

  function showLoadingIndicator() {
    loadingIndicator.style.display = 'block';
  }

  function hideLoadingIndicator() {
    loadingIndicator.style.display = 'none';
  }

  function loadMoreContent() {
    if (isLoading || !hasMore) {
      return;
    }

    isLoading = true;
    showLoadingIndicator();

    loadCampaignsFunction(currentPage, itemsPerPage)
      .then((response) => {
        // Handle WordPress AJAX response structure
        // WordPress wp_send_json_success() wraps data in { success: true, data: {...} }
        const data = response.success ? response.data : response;

        if (data && data.html && data.html.trim() !== '') {
          // Check if the data.html is an array or string
          if (Array.isArray(data.html)) {
            data.html.forEach((htmlString) => {
              campaignListContainer.insertAdjacentHTML('beforeend', htmlString);
            });
          } else {
            campaignListContainer.insertAdjacentHTML('beforeend', data.html);
          }
          currentPage++;
          hasMore = data.has_more || false;

          // Call the onContentLoaded callback if provided
          if (onContentLoaded && typeof onContentLoaded === 'function') {
            onContentLoaded();
          }
        } else {
          // No HTML content received, stop infinite scroll
          hasMore = false;
        }
      })
      .catch((error) => {
        console.error('Infinite scroll error:', error);
        hasMore = false; // Stop trying on error
      })
      .finally(() => {
        isLoading = false;
        hideLoadingIndicator();
      });
  }

  // Intersection Observer for infinite scrolling
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && hasMore && !isLoading) {
          loadMoreContent();
        }
      });
    },
    {
      root: null, // relative to the viewport
      rootMargin: '100px', // Reduced from 200px to 100px for better performance
      threshold: [0, 0.5], // Simplified thresholds for better performance
    },
  );

  // Observe only the last element to reduce overhead
  const observeLastElement = () => {
    const children = Array.from(campaignListContainer.children);
    const lastElement = children[children.length - 1]; // Observe only the last element

    if (lastElement) {
      observer.observe(lastElement);
    } else {
      // Fallback to container if no children
      observer.observe(campaignListContainer);
    }
  };

  // Initial observation
  observeLastElement();

  // Re-observe the new last element whenever new content is added
  const observerConfig = { childList: true };
  listObserver = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        // Disconnect all current observations
        observer.disconnect();

        // Re-observe the new last element
        observeLastElement();
        break; // Only need to process one mutation
      }
    }
  });

  listObserver.observe(campaignListContainer, observerConfig);

  // Add scroll-based fallback for fast scrolling
  let scrollTimeout;
  const handleScroll = () => {
    if (isLoading || !hasMore) return;

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
    const documentHeight = document.documentElement.scrollHeight;

    // If user is within 200px of the bottom, trigger loading (reduced from 300px)
    if (scrollTop + windowHeight >= documentHeight - 200) {
      loadMoreContent();
    }
  };

  // Improved throttled scroll listener with better performance
  let scrollTicking = false;
  const throttledScrollHandler = () => {
    if (!scrollTicking) {
      requestAnimationFrame(() => {
        handleScroll();
        scrollTicking = false;
      });
      scrollTicking = true;
    }
  };

  // Add scroll event listener with passive option for better performance
  window.addEventListener('scroll', throttledScrollHandler, { passive: true });

  // Return an object with dispose method for cleanup
  return {
    dispose: function () {
      if (observer) {
        observer.disconnect();
      }
      if (listObserver) {
        listObserver.disconnect();
      }
      if (loadingIndicator) {
        loadingIndicator.remove();
      }
      // Remove scroll listener
      window.removeEventListener('scroll', throttledScrollHandler);
    },
    loadMoreContent: loadMoreContent, // Expose for testing
  };
}

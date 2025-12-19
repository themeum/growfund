document.addEventListener('DOMContentLoaded', function () {
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

  const filtersForm = document.querySelector('.growfund-filters-form');
  const mainArea = document.querySelector('.growfund-main-area');
  const searchInput = document.getElementById('search');
  const campaignListContainer = document.getElementById(
    'growfund-campaign-list-container'
  );

  if (!filtersForm || !mainArea || !campaignListContainer) {
    // If campaign list container is missing, try to wait for it
    if (!campaignListContainer) {
      setTimeout(() => {
        const retryContainer = document.getElementById(
          'growfund-campaign-list-container'
        );
        if (retryContainer) {
          // Continue with the script logic
          continueWithScript();
        }
      }, 500);
    }
    return;
  }

  const baseUrl = window.location.href.split('?')[0];

  // Make infinite scroll instance globally accessible for proper cleanup
  if (!window.growfundSearchInfiniteScroll) {
    window.growfundSearchInfiniteScroll = null;
  }
  let currentInfiniteScroll = null;
  let itemsPerPage = 12;
  let isAjaxInProgress = false; // Add flag to track AJAX calls

  // Function to clean up existing infinite scroll observers
  function cleanupInfiniteScroll() {
    // Clean up global infinite scroll instance
    if (
      window.growfundSearchInfiniteScroll &&
      typeof window.growfundSearchInfiniteScroll.dispose === 'function'
    ) {
      window.growfundSearchInfiniteScroll.dispose();
      window.growfundSearchInfiniteScroll = null;
    }
    // Also clean up local reference
    if (
      currentInfiniteScroll &&
      typeof currentInfiniteScroll.dispose === 'function'
    ) {
      currentInfiniteScroll.dispose();
      currentInfiniteScroll = null;
    }
    // Also remove any existing loading indicators
    const existingLoadingIndicator = document.querySelector(
      '.growfund-loading-indicator'
    );
    if (existingLoadingIndicator) {
      existingLoadingIndicator.remove();
    }
  }

  // Function to continue with the script logic after container is found
  function continueWithScript() {
    // Re-check required elements
    const filtersForm = document.querySelector('.growfund-filters-form');
    const mainArea = document.querySelector('.growfund-main-area');
    const searchInput = document.getElementById('search');
    const campaignListContainer = document.getElementById(
      'growfund-campaign-list-container'
    );

    if (!filtersForm || !mainArea || !campaignListContainer) {
      return;
    }

    // Continue with the rest of the script logic
    setupEventListeners();
    checkInitialFilters();
  }

  // Debounce function
  function debounce(func, delay) {
    let timeout;
    return function (...args) {
      const context = this;
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(context, args), delay);
    };
  }

  const performAjaxSearch = (page = 1) => {
    // Prevent multiple simultaneous AJAX calls
    if (isAjaxInProgress) {
      return;
    }

    // Ensure campaign list container exists
    const campaignListContainer = document.getElementById(
      'growfund-campaign-list-container'
    );
    if (!campaignListContainer) {
      return;
    }

    isAjaxInProgress = true; // Set flag to prevent other calls

    const featuredProjects = document.querySelector(
      '.growfund-featured-projects'
    );
    if (featuredProjects) {
      featuredProjects.style.display = 'none';
    }

    const formData = new FormData(filtersForm);

    // Process mobile filters and map them to the correct field names
    const mobileCategory = formData.get('mobile_category_temp');
    const mobileLocation = formData.get('mobile_location');
    const mobileSort = formData.get('mobile_sort_temp');

    // Filter out mobile-specific fields to avoid them appearing in desktop search URLs
    const mobileFields = [
      'mobile_location',
      'mobile_sort_temp',
      'mobile_category_temp',
    ];
    for (const field of mobileFields) {
      formData.delete(field);
    }

    formData.append('action', 'growfund_search_campaigns');
    formData.append('_wpnonce', growfund.ajax_nonce);
    formData.append('page', page);
    formData.append('limit', itemsPerPage);

    // Add category filter - prioritize mobile category if available, otherwise use desktop dropdown
    if (mobileCategory) {
      formData.set('category', mobileCategory);
    } else {
      const categoryDropdown = filtersForm.querySelector(
        '[data-dropdown-key="category"]'
      );

      const selectedOption = categoryDropdown.querySelector(
        'li[aria-selected="true"]'
      );
      let categoryValue = '';

      if (selectedOption && selectedOption.dataset.value) {
        categoryValue = selectedOption.dataset.value;
      }
      formData.set('category', categoryValue);
    }

    // Add location filter - prioritize mobile location if available
    if (mobileLocation) {
      formData.set('location', mobileLocation);
    }

    // Add sort filter - prioritize mobile sort if available, otherwise use desktop dropdown
    if (mobileSort) {
      formData.set('sort', mobileSort);
    } else {
      const sortDropdown = filtersForm.querySelector(
        '[data-dropdown-key="sort"]'
      );
      if (sortDropdown && sortDropdown.dataset.value) {
        formData.set('sort', sortDropdown.dataset.value);
      }
    }

    // Manually build query string for URL bar
    const paramsForUrl = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
      paramsForUrl.append(key, value);
    }
    const newUrl = `${baseUrl}?${paramsForUrl.toString()}`;

    history.pushState(null, '', newUrl);

    mainArea.style.opacity = '0.5';

    fetch(growfund.ajax_url, {
      method: 'POST',
      body: new URLSearchParams(formData),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((errorData) => {
            throw new Error(JSON.stringify(errorData, null, 2));
          });
        }
        return response.json();
      })
      .then((result) => {
        // Double-check that the campaign list container still exists
        const currentContainer = document.getElementById(
          'growfund-campaign-list-container'
        );

        if (!currentContainer) {
          return;
        }

        if (result.success && result.data.html) {
          // Clear existing campaigns if it's a new search (page 1)
          if (page === 1) {
            // Clear previous results safely
            while (currentContainer.firstChild) {
              currentContainer.removeChild(currentContainer.firstChild);
            }

            // Update the total campaigns count for the header
            if (typeof growfundArchiveData !== 'undefined') {
              growfundArchiveData.initialTotalCampaigns = result.data.total;
              // Don't modify other properties to avoid conflicts
            }
            // Remove the existing infinite scroll instance if it exists
            cleanupInfiniteScroll();

            // Reset the global flag to ensure clean state
            window.growfundInfiniteScrollInitialized = false;
          }
          // Insert the server-provided HTML (assuming it's sanitized on server-side)
          currentContainer.insertAdjacentHTML('beforeend', result.data.html);

          // Only initialize infinite scroll if there are more campaigns to load and it hasn't been initialized yet
          if (
            result.data.has_more &&
            page === 1 &&
            !window.growfundInfiniteScrollInitialized
          ) {
            // Initialize immediately for smoother experience
            window.growfundInfiniteScrollInitialized = true;
            currentInfiniteScroll = initializeInfiniteScroll({
              campaignListContainerId: 'growfund-campaign-list-container',
              initialPage: page + 1, // Next page to load
              itemsPerPage: itemsPerPage,
              initialHasMore: result.data.has_more,
              loadCampaignsFunction: (currentPage, itemsPerPage) => {
                // This function will be called by the infinite scroll module
                // It will re-run the search with the next page number
                const newFormData = new FormData(filtersForm);

                // Process mobile filters and map them to the correct field names
                const mobileCategory = newFormData.get('mobile_category_temp');
                const mobileLocation = newFormData.get('mobile_location');
                const mobileSort = newFormData.get('mobile_sort_temp');

                // Filter out mobile-specific fields
                const mobileFields = [
                  'mobile_location',
                  'mobile_sort_temp',
                  'mobile_category_temp',
                ];
                for (const field of mobileFields) {
                  newFormData.delete(field);
                }

                newFormData.append('action', 'growfund_search_campaigns');
                newFormData.append('_wpnonce', growfund.ajax_nonce);
                newFormData.append('page', currentPage);
                newFormData.append('limit', itemsPerPage);

                // Add category filter - prioritize mobile category if available, otherwise use desktop dropdown
                if (mobileCategory) {
                  newFormData.set('category', mobileCategory);
                } else {
                  const categoryDropdown = filtersForm.querySelector(
                    '[data-dropdown-key="category"]'
                  );
                  const selectedOption = categoryDropdown.querySelector(
                    'li[aria-selected="true"]'
                  );
                  if (selectedOption && selectedOption.dataset.value) {
                    newFormData.set('category', selectedOption.dataset.value);
                  }
                }

                // Add location filter - prioritize mobile location if available
                if (mobileLocation) {
                  newFormData.set('location', mobileLocation);
                }

                // Add sort filter - prioritize mobile sort if available, otherwise use desktop dropdown
                if (mobileSort) {
                  newFormData.set('sort', mobileSort);
                } else {
                  const sortDropdown = filtersForm.querySelector(
                    '[data-dropdown-key="sort"]'
                  );
                  if (sortDropdown && sortDropdown.dataset.value) {
                    newFormData.set('sort', sortDropdown.dataset.value);
                  }
                }

                newFormData.append('is_ajax_load', 'true');

                return fetch(growfund.ajax_url, {
                  method: 'POST',
                  body: new URLSearchParams(newFormData),
                }).then((response) => {
                  if (!response.ok) {
                    return response.json().then((errorData) => {
                      throw new Error(JSON.stringify(errorData, null, 2));
                    });
                  }
                  return response.json();
                });
              },
            });
            // Store the infinite scroll instance globally for proper cleanup
            window.growfundSearchInfiniteScroll = currentInfiniteScroll;
          }
        } else {
          // Only show "no campaigns found" if we don't already have content
          if (currentContainer.children.length === 0) {
            // Create no campaigns message
            const noCampaignsDiv = document.createElement('div');
            noCampaignsDiv.className = 'growfund-no-campaigns';

            const contentDiv = document.createElement('div');
            contentDiv.className = 'growfund-no-campaigns__content';

            const heading = document.createElement('h2');
            heading.textContent = 'No campaigns found.';

            const paragraph = document.createElement('p');
            paragraph.textContent =
              'Try adjusting your search or filter criteria.';

            contentDiv.appendChild(heading);
            contentDiv.appendChild(paragraph);
            noCampaignsDiv.appendChild(contentDiv);

            // Clear container and add no campaigns message
            while (currentContainer.firstChild) {
              currentContainer.removeChild(currentContainer.firstChild);
            }
            currentContainer.appendChild(noCampaignsDiv);
          }
        }
      })
      .catch((error) => {
        // Create error message
        const errorP = document.createElement('p');
        errorP.textContent = 'An error occurred. Please try again.';

        // Clear main area and add error message
        while (mainArea.firstChild) {
          mainArea.removeChild(mainArea.firstChild);
        }
        mainArea.appendChild(errorP);
      })
      .finally(() => {
        mainArea.style.opacity = '1';
        isAjaxInProgress = false; // Reset flag when AJAX call completes
      });
  };

  const debouncedSearch = debounce(() => performAjaxSearch(1), 500);

  // Make performAjaxSearch available globally for other scripts
  window.performAjaxSearch = performAjaxSearch;

  // Function to setup event listeners
  function setupEventListeners() {
    if (searchInput) {
      searchInput.addEventListener('keyup', (e) => {
        // ignore keys that don't change the value
        if (e.key.length > 1 && e.key !== 'Backspace' && e.key !== 'Delete') {
          return;
        }
        debouncedSearch();
      });
    }

    // Listen for dropdown selection events
    document.addEventListener('dropdown:select', (e) => {
      if (e.detail && e.detail.value) {
        setTimeout(() => performAjaxSearch(1), 200); // Increased timeout to prevent rapid clicks
      }
    });

    // Listen for dropdown clear events
    document.addEventListener('dropdown:clear', (e) => {
      setTimeout(() => performAjaxSearch(1), 100); // Trigger search when dropdown is cleared
    });

    // We prevent form submission to handle everything with AJAX
    filtersForm.addEventListener('submit', (e) => {
      e.preventDefault();
      performAjaxSearch(1);
    });
  }

  // Function to check for initial filters and perform search if needed
  function checkInitialFilters() {
    // Initial load for existing filters in URL on page load
    // This ensures that if the page is loaded with filters in the URL, the search is performed immediately
    // and infinite scroll is initialized correctly.
    const urlParams = new URLSearchParams(window.location.search);
    const hasInitialFilters = Array.from(urlParams.keys()).some(
      (key) =>
        key !== 'page' &&
        key !== 'limit' &&
        key !== 'action' &&
        key !== 'is_ajax_load'
    );

    if (hasInitialFilters) {
      // Add a small delay to ensure dropdowns are fully initialized and container is available
      setTimeout(() => {
        // Double-check that the campaign list container exists before performing search
        const container = document.getElementById(
          'growfund-campaign-list-container'
        );
        if (!container) {
          // Wait a bit more and try again
          setTimeout(() => {
            performAjaxSearch(1);
          }, 500);
        } else {
          performAjaxSearch(1);
        }
      }, 200);
    } else {
      // If no search filters are applied initially, we need to initialize infinite scroll for the default case
      // since campaigns-archive.js is disabled when search elements are present
      setTimeout(() => {
        const container = document.getElementById(
          'growfund-campaign-list-container'
        );
        if (!container || window.growfundInfiniteScrollInitialized) {
          return;
        }

        // Check if there are campaigns already loaded and if we have more to load
        const hasCampaigns = container.children.length > 0;
        const hasMore =
          typeof growfundArchiveData !== 'undefined' &&
          growfundArchiveData.initialHasMore;

        if (
          hasCampaigns &&
          hasMore &&
          typeof initializeInfiniteScroll === 'function'
        ) {
          window.growfundInfiniteScrollInitialized = true;

          const infiniteScrollInstance = initializeInfiniteScroll({
            campaignListContainerId: 'growfund-campaign-list-container',
            initialPage: 2, // Always start from page 2 since page 1 is already loaded
            itemsPerPage: 12,
            initialHasMore: hasMore,
            loadCampaignsFunction: (currentPage, itemsPerPage) => {
              // Create a clean params object without page/limit to avoid conflicts
              const currentParams = new URLSearchParams(window.location.search);
              const params = new URLSearchParams();

              // Copy all current params except page and limit
              for (const [key, value] of currentParams.entries()) {
                if (
                  key !== 'page' &&
                  key !== 'limit' &&
                  key !== 'action' &&
                  key !== 'is_ajax_load'
                ) {
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
          window.growfundSearchInfiniteScroll = infiniteScrollInstance;
        }
      }, 100); // Match the delay used in campaigns-archive.js for consistency
    }
  }

  function restoreDropdownsFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);

    restoreDropdown('category', urlParams.get('category'));
    restoreDropdown('sort', urlParams.get('sort'));
  }
  function restoreDropdown(key, value) {
    if (!value) return;

    const dropdown = document.querySelector(`[data-dropdown-key="${key}"]`);
    if (!dropdown) return;

    const option = dropdown.querySelector(`li[data-value="${value}"]`);
    if (!option) return;

    dropdown
      .querySelectorAll('li[aria-selected="true"]')
      .forEach((li) => li.setAttribute('aria-selected', 'false'));

    option.setAttribute('aria-selected', 'true');
    dropdown.dataset.value = value;

    const valueEl = dropdown.querySelector('.growfund-dropdown__value');
    if (valueEl) {
      valueEl.textContent = option.textContent.trim();
    }
  }

  // Start the script logic
  restoreDropdownsFromUrl();
  setupEventListeners();
  checkInitialFilters();
});

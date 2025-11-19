/**
 * Bookmark functionality for campaigns
 * Handles bookmark button interactions and state management
 */

/**
 * Handle bookmark button click
 * @param {Event} event - Click event
 */
function handleBookmarkClick(event) {
  event.preventDefault();

  // Find the bookmark button element
  const button = event.target.closest('[data-action="growfund_bookmark_campaign"]');
  if (!button) {
    return;
  }

  const campaignId = button.getAttribute('data-campaign-id');
  const isCurrentlyBookmarked = button.getAttribute('data-is-bookmarked') === 'true';

  // Make AJAX request to toggle bookmark status
  fetch(growfund.ajax_url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      action: 'growfund_bookmark_campaign',
      campaign_id: campaignId,
      _wpnonce: growfund.ajax_nonce,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update button appearance based on server response
        if (
          data.data &&
          data.data.additional_data &&
          typeof data.data.additional_data.is_bookmarked !== 'undefined'
        ) {
          const newBookmarkState = data.data.additional_data.is_bookmarked;
          updateBookmarkButton(button, newBookmarkState);

          // Update data attribute
          button.setAttribute('data-is-bookmarked', newBookmarkState.toString());
        } else {
          // Fallback: toggle the current state
          const newBookmarkState = !isCurrentlyBookmarked;
          updateBookmarkButton(button, newBookmarkState);
          button.setAttribute('data-is-bookmarked', newBookmarkState.toString());
        }
      } else {
        // Handle error (could show a toast notification)
      }
    })
    .catch((error) => {
      // Handle error (could show a toast notification)
    });
}

/**
 * Update bookmark button appearance based on bookmark status
 * @param {HTMLElement} button - The bookmark button element
 * @param {boolean} isBookmarked - Whether the campaign is bookmarked
 */
function updateBookmarkButton(button, isBookmarked) {
  if (isBookmarked) {
    // Update to bookmarked state
    button.classList.remove('growfund-btn--gray');
    button.classList.add('growfund-btn--bookmark', 'bookmarked');
    button.setAttribute('data-is-bookmarked', 'true');

    // Swap icon to check-circled
    if (window.cfIcon) {
      window.cfIcon.swapIconInContainer(button, 'check-circled', 'bookmark');
    }

    // Update text
    const textNode = button.lastChild;
    if (textNode && textNode.nodeType === Node.TEXT_NODE) {
      textNode.textContent = 'Saved';
    } else {
      button.appendChild(document.createTextNode('Saved'));
    }
  } else {
    // Update to unbookmarked state
    button.classList.remove('growfund-btn--bookmark', 'bookmarked');
    button.classList.add('growfund-btn--gray');
    button.setAttribute('data-is-bookmarked', 'false');

    // Swap icon back to bookmark
    if (window.cfIcon) {
      window.cfIcon.swapIconInContainer(button, 'bookmark', 'check-circled');
    }

    // Update text
    const textNode = button.lastChild;
    if (textNode && textNode.nodeType === Node.TEXT_NODE) {
      textNode.textContent = 'Save for later';
    } else {
      button.appendChild(document.createTextNode('Save for later'));
    }
  }
}

/**
 * Initialize bookmark buttons with their current state
 */
function initializeBookmarkButtons() {
  const bookmarkButtons = document.querySelectorAll('[data-action="growfund_bookmark_campaign"]');

  bookmarkButtons.forEach((button) => {
    const isBookmarked = button.getAttribute('data-is-bookmarked') === 'true';
    if (isBookmarked) {
      // Set initial bookmarked state
      button.classList.remove('growfund-btn--gray');
      button.classList.add('growfund-btn--bookmark', 'bookmarked');

      // Swap icon to check-circled
      if (window.cfIcon) {
        window.cfIcon.swapIconInContainer(button, 'check-circled', 'bookmark');
      }

      // Update text
      const textNode = button.childNodes[button.childNodes.length - 1];
      if (textNode && textNode.nodeType === Node.TEXT_NODE) {
        textNode.textContent = 'Saved';
      }
    }
  });
}

// Initialize bookmark buttons when DOM is ready
document.addEventListener('DOMContentLoaded', initializeBookmarkButtons);

// Add click event listeners to bookmark buttons
document.addEventListener('click', function (event) {
  if (event.target.closest('[data-action="growfund_bookmark_campaign"]')) {
    handleBookmarkClick(event);
  }
});

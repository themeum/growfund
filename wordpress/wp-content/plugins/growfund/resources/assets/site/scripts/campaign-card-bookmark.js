/**
 * Campaign Card Bookmark functionality
 * Handles bookmark button interactions for campaign cards (icon-only buttons)
 */

/**
 * Handle campaign card bookmark button click
 * @param {Event} event - Click event
 */
function handleCampaignCardBookmarkClick(event) {
  event.preventDefault();
  event.stopPropagation();
  event.stopImmediatePropagation();

  const button = event.target.closest('[data-action="bookmark_campaign_card"]');
  if (!button) {
    return;
  }

  const campaignId = button.getAttribute('data-campaign-id');
  const isCurrentlyBookmarked = button.getAttribute('data-is-bookmarked') === 'true';

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
        if (
          data.data &&
          data.data.additional_data &&
          typeof data.data.additional_data.is_bookmarked !== 'undefined'
        ) {
          const newBookmarkState = data.data.additional_data.is_bookmarked;
          updateCampaignCardBookmarkButton(button, newBookmarkState);
          button.setAttribute('data-is-bookmarked', newBookmarkState.toString());
        } else {
          const newBookmarkState = !isCurrentlyBookmarked;
          updateCampaignCardBookmarkButton(button, newBookmarkState);
          button.setAttribute('data-is-bookmarked', newBookmarkState.toString());
        }
      } else {
        console.error('Bookmark toggle failed:', data);
      }
    })
    .catch((error) => {
      console.error('Bookmark toggle error:', error);
    });
}

/**
 * Update campaign card bookmark button appearance based on bookmark status
 * @param {HTMLElement} button - The bookmark button element
 * @param {boolean} isBookmarked - Whether the campaign is bookmarked
 */
function updateCampaignCardBookmarkButton(button, isBookmarked) {
  if (isBookmarked) {
    button.classList.add('bookmarked');
    button.setAttribute('data-is-bookmarked', 'true');
    button.setAttribute('aria-label', 'Remove bookmark');

    if (window.cfIcon) {
      window.cfIcon.swapIconInContainer(button, 'check-circled', 'bookmark');
    }
  } else {
    button.classList.remove('bookmarked');
    button.setAttribute('data-is-bookmarked', 'false');
    button.setAttribute('aria-label', 'Bookmark campaign');

    if (window.cfIcon) {
      window.cfIcon.swapIconInContainer(button, 'bookmark', 'check-circled');
    }
  }
}

/**
 * Initialize campaign card bookmark buttons with their current state
 */
function initializeCampaignCardBookmarkButtons() {
  const bookmarkButtons = document.querySelectorAll('[data-action="bookmark_campaign_card"]');

  bookmarkButtons.forEach((button) => {
    const isBookmarked = button.getAttribute('data-is-bookmarked') === 'true';

    if (isBookmarked) {
      button.classList.add('bookmarked');
      button.setAttribute('aria-label', 'Remove bookmark');

      if (window.cfIcon) {
        window.cfIcon.swapIconInContainer(button, 'check-circled', 'bookmark');
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  initializeCampaignCardBookmarkButtons();
});

document.addEventListener(
  'click',
  function (event) {
    const bookmarkButton = event.target.closest('[data-action="bookmark_campaign_card"]');
    if (bookmarkButton) {
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();

      const campaignLink = bookmarkButton.closest('a[href]');
      if (campaignLink) {
        campaignLink.style.pointerEvents = 'none';
        setTimeout(() => {
          campaignLink.style.pointerEvents = 'auto';
        }, 100);
      }

      handleCampaignCardBookmarkClick(event);
      return false;
    }
  },
  true,
);

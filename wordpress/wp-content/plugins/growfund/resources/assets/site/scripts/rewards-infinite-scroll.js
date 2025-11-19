/**
 * Rewards Infinite Scroll Functionality
 * Handles infinite scrolling for campaign rewards
 */

// Run immediately to fix URLs before other scripts can interfere
(function () {
  function fixRewardUrls(reward, campaignId) {
    const rewardCampaignId = reward.dataset.campaignId || campaignId;
    const rewardId = reward.dataset.rewardId;

    if (!rewardCampaignId) {
      return;
    }

    // Only select buttons that are actually pledge/checkout buttons
    const pledgeButtons = reward.querySelectorAll(
      '.growfund-reward-pledge-action a, .growfund-reward-button-container a, .growfund-btn--reward, .growfund-btn--primary',
    );

    pledgeButtons.forEach((button) => {
      if (button.href === '#' || button.href.includes('#')) {
        try {
          const url = new URL(growfund?.checkout_url);

          url.searchParams.set('campaign_id', rewardCampaignId);
          if (rewardId) {
            url.searchParams.set('reward_id', rewardId);
          }

          button.href = url.toString();
        } catch (e) {
          console.error('Invalid checkout URL:', growfund?.checkout_url, e);
        }
      }
    });
  }

  function initializeExistingRewards(container, campaignId) {
    const existingRewards = container.querySelectorAll('.growfund-campaign-reward');
    existingRewards.forEach((reward) => {
      fixRewardUrls(reward, campaignId);
    });
  }

  function getCampaignId() {
    // Try to get from the tab content element first (where it's actually set)
    const tabContentElement = document.querySelector('.growfund-tab-content--rewards');
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

  // Initialize immediately if DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeRewards);
  } else {
    initializeRewards();
  }

  function initializeRewards() {
    const rewardsGridContainer = document.querySelector('.growfund-rewards-grid');
    if (!rewardsGridContainer) {
      return;
    }

    // Get campaign ID from the page
    const campaignId = getCampaignId();
    if (!campaignId) {
      return;
    }

    // Initialize existing rewards to ensure they have proper checkout URLs
    initializeExistingRewards(rewardsGridContainer, campaignId);

    // Only initialize infinite scroll if it hasn't already been done
    if (!window.growfundRewardsInfiniteScrollInitialized) {
      window.growfundRewardsInfiniteScrollInitialized = true;

      const infiniteScrollInstance = initializeInfiniteScroll({
        campaignListContainerId: 'growfund-rewards-grid',
        initialPage: 2,
        itemsPerPage: 6,
        initialHasMore: true,
        onContentLoaded: () => {
          // Fix URLs for newly loaded rewards
          const newRewards = rewardsGridContainer.querySelectorAll(
            '.growfund-campaign-reward:not([data-url-fixed])',
          );
          newRewards.forEach((reward) => {
            reward.setAttribute('data-url-fixed', 'true');
            fixRewardUrls(reward, campaignId);
          });
        },
        loadCampaignsFunction: (currentPage, itemsPerPage) => {
          const params = new URLSearchParams();
          params.set('action', 'growfund_get_campaign_rewards');
          params.set('campaign_id', parseInt(campaignId));
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
  }
})();

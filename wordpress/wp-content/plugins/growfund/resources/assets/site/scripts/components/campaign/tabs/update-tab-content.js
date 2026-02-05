(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const updatesWrapper = document.querySelector('.growfund-update-tab-content-card-wrapper');
    const timelineProgress = document.querySelector(
      '.growfund-update-tab-content-timeline-line-progress',
    );
    const timelineDot = document.querySelector('.growfund-update-tab-content-timeline-dot-current');
    const progressTextWrapper = document.querySelector(
      '.growfund-update-tab-content-timeline-progress-text',
    );

    const timelineCount = document.querySelector(
      '.growfund-update-tab-content-timeline-progress-count',
    );
    const timelineCurrentDate = document.querySelector(
      '.growfund-update-tab-content-timeline-current-date',
    );
    const timelineStartEl = document.querySelector(
      '.growfund-update-tab-content-timeline-start .growfund-update-tab-content-timeline-date',
    );
    const timelineEndEl = document.querySelector(
      '.growfund-update-tab-content-timeline-end .growfund-update-tab-content-timeline-date',
    );
    function generateTimelinePoints() {
      const cards = updatesWrapper.querySelectorAll('.growfund-update-tab-content-card');
      const lineContainer = document.querySelector(
        '.growfund-update-tab-content-timeline-line-container',
      );
      if (!cards || !cards.length || !lineContainer) return;

      const total = cards.length;

      lineContainer
        .querySelectorAll('.growfund-update-tab-content-timeline-point')
        .forEach((p) => p.remove());

      cards.forEach((_, index) => {
        const point = document.createElement('div');
        point.classList.add('growfund-update-tab-content-timeline-point');

        const positionPercent = total > 1 ? (index / (total - 1)) * 100 : 0;
        point.style.top = `${positionPercent}%`;
        lineContainer.appendChild(point);
      });
    }

    function updateTimelineProgress(scrollIndex) {
      const wrapper = document.querySelector('.growfund-update-tab-content-card-wrapper');
      if (!wrapper) {
        return;
      }
      const cards = wrapper.querySelectorAll('.growfund-update-tab-content-card');
      const total = cards.length;

      if (total === 0) {
        return;
      }

      const currentUpdateDate = cards[scrollIndex].getAttribute('data-campaign-update-date');
      const firstUpdateDate = cards[0].getAttribute('data-campaign-update-date');
      const lastUpdateDate = cards[total - 1].getAttribute('data-campaign-update-date');

      const dateOptions = { month: 'short', day: 'numeric' };

      const formattedCurrent = growfundGetDateTime(currentUpdateDate, false, dateOptions);
      const formattedFirst = growfundGetDateTime(firstUpdateDate, false, dateOptions);
      const formattedLast = growfundGetDateTime(lastUpdateDate, false, dateOptions);

      if (timelineCount) timelineCount.textContent = `${scrollIndex + 1}/${total}`;
      if (timelineStartEl) timelineStartEl.textContent = formattedFirst;
      if (timelineEndEl) timelineEndEl.textContent = formattedLast;
      if (timelineCurrentDate) timelineCurrentDate.textContent = formattedCurrent;
      const background = document.querySelector(
        '.growfund-update-tab-content-timeline-line-background',
      );

      if (!background) return;
      const rect = updatesWrapper.getBoundingClientRect();
      const viewportHeight = window.innerHeight;
      const bgHeight = background.offsetHeight;

      const startOffset = viewportHeight / 2;
      const totalTravel = rect.height;
      const currentPos = startOffset - rect.top;

      let smoothProgress = currentPos / totalTravel;
      smoothProgress = Math.max(0, Math.min(1, smoothProgress));

      const dotTop = smoothProgress * bgHeight;
      if (timelineDot) timelineDot.style.top = `${dotTop}px`;
      if (timelineProgress) timelineProgress.style.height = `${dotTop}px`;

      if (progressTextWrapper) {
        progressTextWrapper.style.top = `${dotTop}px`;
        progressTextWrapper.style.transform = `translateY(-50%)`;
      }
    }

    function getMostVisibleCardIndex() {
      const cards = Array.from(
        updatesWrapper?.querySelectorAll('.growfund-update-tab-content-card') || [],
      );

      if (!cards.length) return 0;

      let maxVisible = 0;
      let visibleIndex = 0;
      const viewportHeight = window.innerHeight;

      cards.forEach((card, index) => {
        const rect = card.getBoundingClientRect();
        const visibleHeight = Math.min(rect.bottom, viewportHeight) - Math.max(rect.top, 0);

        if (visibleHeight > 0) {
          const visibilityRatio = visibleHeight / rect.height;
          if (visibilityRatio > maxVisible) {
            maxVisible = visibilityRatio;
            visibleIndex = index;
          }
        }
      });

      return visibleIndex;
    }

    let isLoading = false;

    function fetchUpdates(list) {
      const cardWrapper = list.classList.contains('growfund-update-tab-content-card-wrapper')
        ? list
        : list.querySelector('.growfund-update-tab-content-card-wrapper');

      const timelineContainer = list.querySelector(
        '.growfund-update-tab-content-card-timeline-container',
      );
      const timeline = timelineContainer.querySelector('.growfund-update-tab-content-timeline');

      if (!cardWrapper) {
        return;
      }
      const container = list.closest('.growfund-update-tab-content-card-container');
      const currentPage = parseInt(cardWrapper.dataset.currentPage || 0);
      const campaignId = parseInt(cardWrapper.dataset.campaignId || 0);
      const emptyState = container.querySelector('.growfund-update-empty-state');

      if (isLoading) return;
      isLoading = true;

      growfundAjaxClient
        .get('growfund_ajax_get_paginated_campaign_post_updates', {
          campaign_id: campaignId,
          page: currentPage + 1,
        })
        .then((response) => {
          if (response.success && response.data?.data?.results) {
            const hasData = response.success && response.data?.data?.results?.length > 0;
            if (hasData) {
              if (cardWrapper) cardWrapper.classList.remove('growfund-hidden');
              if (timeline) timeline.classList.remove('growfund-hidden');
              if (emptyState) emptyState.classList.add('growfund-hidden');

              cardWrapper.setAttribute('data-has-more', response.data.data.has_more);
              cardWrapper.setAttribute('data-current-page', response.data.data.current_page);
              window.requestAnimationFrame(() => {
                updateTimelineProgress(0);
              });
              if (list) {
                const container = list.closest('.growfund-update-tab-content-card-container');
                const loadMoreBtn = container
                  ? container.querySelector('.growfund-update-load-more')
                  : null;

                if (loadMoreBtn) {
                  if (response.data.data.has_more) {
                    loadMoreBtn.classList.remove('growfund-hidden');
                  } else {
                    loadMoreBtn.classList.add('growfund-hidden');
                  }
                }
              }
              if (response.success && response.data?.html) {
                cardWrapper.insertAdjacentHTML('beforeend', response.data.html);
                generateTimelinePoints();
                cardWrapper.dataset.currentPage = response.data.data.current_page;
              }
            } else {
              if (cardWrapper) cardWrapper.classList.add('growfund-hidden');
              if (timelineContainer) {
                timelineContainer.classList.add('growfund-hidden');
              }
              if (emptyState) emptyState.classList.remove('growfund-hidden');
            }
          }
        })
        .finally(() => {
          isLoading = false;
        });
    }

    document.addEventListener('growfund:tab-trigger', function (e) {
      if (e.detail && e.detail.key === 'updates') {
        const list = document.querySelector('.growfund-update-tab-content-card-container');
        if (list) {
          fetchUpdates(list);
        }
      }
    });
    let ticking = false;

    const onScroll = () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          const visibleIndex = getMostVisibleCardIndex();
          updateTimelineProgress(visibleIndex);
          ticking = false;
        });
        ticking = true;
      }
    };
    if (document.querySelector('.growfund-update-tab-content-card-wrapper')) {
      updateTimelineProgress(0);
    }

    document.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('growfund:load-more-update', function (e) {
      const { updateList } = e.detail;
      fetchUpdates(updateList);
    });
  });
})();

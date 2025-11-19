class TimelineUpdates {
  constructor() {
    this.timeline = document.querySelector('.growfund-updates-timeline');
    this.updatesList = document.querySelector('.growfund-updates-list');
    this.updates = Array.from(this.updatesList?.querySelectorAll('.growfund-update-item') || []);
    this.timelineProgress = document.querySelector('.growfund-timeline-line-progress');
    this.timelineDot = document.querySelector('.growfund-timeline-dot-current');
    this.timelineCount = document.querySelector('.growfund-timeline-count');
    this.timelineCurrentDate = document.querySelector('.growfund-timeline-current-date');
    this.timelineProgressText = document.querySelector('.growfund-timeline-progress-text');
    this.timelineContainer = document.querySelector('.growfund-timeline-line-container');
    this.timelineHeight = this.timelineContainer?.offsetHeight || 0;
    this.currentIndex = 0;
    this.initialized = false;

    // Get total updates count from the tab content data attribute
    this.totalUpdatesCount = 0;
    const tabContent = document.querySelector('.growfund-tab-content--updates');
    if (tabContent && tabContent.dataset.totalUpdatesCount) {
      this.totalUpdatesCount = parseInt(tabContent.dataset.totalUpdatesCount);
    }

    // Fallback: try to get from the timeline count text
    if (this.totalUpdatesCount === 0 && this.timelineCount) {
      const countText = this.timelineCount.textContent;
      const match = countText.match(/\/\d+/);
      if (match) {
        this.totalUpdatesCount = parseInt(match[0].substring(1));
      }
    }

    // Sort updates by date in descending order (newest first)
    this.updates.sort((a, b) => {
      const dateA = new Date(a.dataset.date);
      const dateB = new Date(b.dataset.date);
      return dateB - dateA;
    });

    this.init();
  }

  createTimelinePoints() {
    // Only create points if they don't already exist or if the count has changed
    const existingPoints = this.timelineContainer.querySelectorAll('.growfund-timeline-point');
    const totalPoints = this.totalUpdatesCount > 0 ? this.totalUpdatesCount : this.updates.length;

    // If points already exist and count matches, don't recreate
    if (existingPoints.length === totalPoints && totalPoints > 0) {
      return;
    }

    // Remove existing points if any
    existingPoints.forEach((point) => point.remove());

    // Create points for total number of updates, not just loaded ones
    for (let i = 0; i < totalPoints; i++) {
      const point = document.createElement('div');
      point.className = 'growfund-timeline-point';

      // Position points evenly along the timeline
      if (totalPoints === 1) {
        point.style.top = '50%'; // Center if only one point
      } else {
        point.style.top = `${(i / (totalPoints - 1)) * 100}%`;
      }

      this.timelineContainer.appendChild(point);
    }
  }

  init() {
    if (!this.timeline || !this.updatesList || this.updates.length === 0) return;

    // Create timeline points
    this.createTimelinePoints();

    // Set timeline dates
    const newestDate = this.updates[0].dataset.date;
    const oldestDate = this.updates[this.updates.length - 1].dataset.date;

    // Set newest date at top
    document.querySelector('.growfund-timeline-start .growfund-timeline-date-label').textContent = new Date(
      newestDate,
    ).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    // Set oldest date at bottom
    document.querySelector('.growfund-timeline-end .growfund-timeline-date-label').textContent = new Date(
      oldestDate,
    ).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

    // Set initial count from PHP (don't override on first load)
    if (!this.initialized) {
      const totalCount = this.totalUpdatesCount > 0 ? this.totalUpdatesCount : this.updates.length;
      this.timelineCount.textContent = `1/${totalCount}`;
      this.initialized = true;
    }

    // Initial update
    this.handleScroll();

    // Add scroll event listener with throttling
    let ticking = false;
    this.scrollHandler = () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          this.handleScroll();
          ticking = false;
        });
        ticking = true;
      }
    };

    document.addEventListener('scroll', this.scrollHandler, { passive: true });

    // Handle resize events
    let resizeTimer;
    window.addEventListener(
      'resize',
      () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          this.timelineHeight = this.timelineContainer?.offsetHeight || 0;
          this.createTimelinePoints(); // Recreate points on resize
          this.handleScroll();
        }, 250);
      },
      { passive: true },
    );
  }

  handleScroll() {
    // Find the most visible update
    let mostVisibleUpdate = null;
    let maxVisibility = 0;

    this.updates.forEach((update) => {
      const rect = update.getBoundingClientRect();
      const windowHeight = window.innerHeight || document.documentElement.clientHeight;

      // Calculate how much of the update is visible
      const visibleHeight = Math.min(rect.bottom, windowHeight) - Math.max(rect.top, 0);
      const visibility = visibleHeight / rect.height;

      if (visibility > maxVisibility && visibleHeight > 0) {
        maxVisibility = visibility;
        mostVisibleUpdate = update;
      }
    });

    if (!mostVisibleUpdate) return;

    // Calculate progress based on scroll position relative to total scrollable content
    const updatesList = document.querySelector('.growfund-updates-list');
    if (!updatesList) return;

    const listRect = updatesList.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;

    // Calculate scroll progress based on the updates list position
    let scrollProgress = 0;

    // If the list is fully visible, we're at the top
    if (listRect.top >= 0 && listRect.bottom <= windowHeight) {
      scrollProgress = 0;
    }
    // If the list is partially or fully scrolled past
    else if (listRect.top < 0) {
      // Calculate how much of the list has been scrolled past
      const listTop = listRect.top;
      const listHeight = listRect.height;
      const scrolledPast = Math.abs(listTop);
      const scrollableRange = listHeight - windowHeight;

      if (scrollableRange > 0) {
        scrollProgress = Math.min(1, scrolledPast / scrollableRange);
      } else {
        scrollProgress = 1; // List is shorter than window, so we're at the bottom
      }
    }
    // If the list is at the bottom
    else if (listRect.bottom <= windowHeight) {
      scrollProgress = 1;
    }

    // Convert to percentage (0-100)
    const progress = scrollProgress * 100;

    // Update timeline position
    this.timelineProgress.style.height = `${progress}%`;
    this.timelineDot.style.top = `${progress}%`;

    // Calculate adjusted progress for text position
    // Start 10% from top and end 10% before bottom
    const minTextPosition = 10;
    const maxTextPosition = 90;
    const adjustedProgress =
      minTextPosition + (progress * (maxTextPosition - minTextPosition)) / 100;

    // Move progress text with dot
    this.timelineProgressText.style.top = `${adjustedProgress}%`;
    this.timelineProgressText.style.transform = `translateY(-50%)`;

    // Calculate the current update index based on scroll progress and total updates
    const totalCount = this.totalUpdatesCount > 0 ? this.totalUpdatesCount : this.updates.length;

    // Calculate current index based on scroll progress
    // This ensures the count is properly synced with timeline dots
    let currentIndex = 0;
    if (totalCount > 1) {
      // Map scroll progress (0-1) to update index (0 to totalCount-1)
      currentIndex = Math.floor(scrollProgress * (totalCount - 1));
      // Ensure we don't go beyond the last update
      currentIndex = Math.min(currentIndex, totalCount - 1);
    } else if (totalCount === 1) {
      // If there's only one update, always show 1/1
      currentIndex = 0;
    }

    // Update the current index for timeline point highlighting
    this.currentIndex = currentIndex;

    // Update count display (add 1 since we're showing 1-based indexing)
    const displayCount = currentIndex + 1;
    this.timelineCount.textContent = `${displayCount}/${totalCount}`;

    // Update current date based on the most visible update
    const currentDate = new Date(mostVisibleUpdate.dataset.date);
    this.timelineCurrentDate.textContent = currentDate.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
    });
  }
}

// Initialize timeline updates when DOM is loaded
let timelineUpdatesInstance = null;

// Make TimelineUpdates available globally
window.TimelineUpdates = TimelineUpdates;

document.addEventListener('DOMContentLoaded', () => {
  timelineUpdatesInstance = new TimelineUpdates();
});

// Function to reinitialize timeline when new updates are loaded
function reinitializeTimelineUpdates() {
  if (timelineUpdatesInstance) {
    // Update the updates array with newly loaded updates
    const updatesList = document.querySelector('.growfund-updates-list');
    if (updatesList) {
      timelineUpdatesInstance.updates = Array.from(
        updatesList.querySelectorAll('.growfund-update-item') || [],
      );

      // Sort updates by date in descending order (newest first) to maintain consistency
      timelineUpdatesInstance.updates.sort((a, b) => {
        const dateA = new Date(a.dataset.date);
        const dateB = new Date(b.dataset.date);
        return dateB - dateA;
      });
    }

    // Recreate timeline points to ensure they're correct
    timelineUpdatesInstance.createTimelinePoints();

    // Force a scroll event to recalculate the timeline position
    // This ensures the timeline is positioned correctly based on current scroll
    setTimeout(() => {
      timelineUpdatesInstance.handleScroll();

      // Trigger a scroll event to ensure the timeline updates properly
      window.dispatchEvent(new Event('scroll'));

      // Reattach scroll event listener to ensure it continues working
      if (timelineUpdatesInstance && timelineUpdatesInstance.scrollHandler) {
        // Remove existing scroll listener to prevent duplicates
        document.removeEventListener('scroll', timelineUpdatesInstance.scrollHandler);

        // Recreate the scroll handler
        let ticking = false;
        timelineUpdatesInstance.scrollHandler = () => {
          if (!ticking) {
            window.requestAnimationFrame(() => {
              timelineUpdatesInstance.handleScroll();
              ticking = false;
            });
            ticking = true;
          }
        };

        // Reattach scroll event listener
        document.addEventListener('scroll', timelineUpdatesInstance.scrollHandler, {
          passive: true,
        });
      }
    }, 50);
  }
}

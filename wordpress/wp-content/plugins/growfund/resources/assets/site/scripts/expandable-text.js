document.addEventListener('DOMContentLoaded', function () {
  // Initialize expandable text functionality
  initExpandableTextComponent();
});

function initExpandableTextComponent() {
  const expandableTexts = document.querySelectorAll('.growfund-expandable-text');

  expandableTexts.forEach((container, index) => {
    const contentElement = container.querySelector('.growfund-expandable-text__content');
    const readMoreBtn = container.querySelector('.growfund-expandable-text__read-more');
    const readLessBtn = container.querySelector('.growfund-expandable-text__read-less');

    if (!contentElement || !readMoreBtn || !readLessBtn) {
      return;
    }

    // Get max lines from data attribute or default to 2
    const maxLines = parseInt(contentElement.dataset.maxLines) || 2;

    // First, reset the element to its natural state to measure properly
    contentElement.style.maxHeight = 'none';
    contentElement.style.overflow = 'visible';

    // Check if text is longer than max lines
    const lineHeight = parseInt(window.getComputedStyle(contentElement).lineHeight);
    const maxHeight = lineHeight * maxLines;

    // Check if content is actually longer than max lines
    const isLongerThanMaxLines = contentElement.scrollHeight > maxHeight;

    if (isLongerThanMaxLines) {
      // Apply truncation
      contentElement.style.maxHeight = maxHeight + 'px';
      contentElement.style.overflow = 'hidden';
      contentElement.classList.add('growfund-expandable-text__content--truncated');

      // Show read more button
      readMoreBtn.style.display = 'inline';

      // Add click handlers
      readMoreBtn.addEventListener('click', function (e) {
        e.preventDefault();
        contentElement.style.maxHeight = 'none';
        contentElement.classList.remove('growfund-expandable-text__content--truncated');
        readMoreBtn.style.display = 'none';
        readLessBtn.style.display = 'inline';
      });

      readLessBtn.addEventListener('click', function (e) {
        e.preventDefault();
        contentElement.style.maxHeight = maxHeight + 'px';
        contentElement.classList.add('growfund-expandable-text__content--truncated');
        readLessBtn.style.display = 'none';
        readMoreBtn.style.display = 'inline';
      });
    } else {
      // Text is short, ensure no truncation and hide buttons
      contentElement.style.maxHeight = 'none';
      contentElement.style.overflow = 'visible';
      contentElement.classList.remove('growfund-expandable-text__content--truncated');
      readMoreBtn.style.display = 'none';
      readLessBtn.style.display = 'none';
    }
  });
}

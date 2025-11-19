/**
 * Social Sharing Functionality
 * Handles social media sharing buttons and interactions
 */

// Social sharing functionality
function initializeSocialSharing() {
  const shareButtons = document.querySelectorAll('.growfund-share-btn');

  shareButtons.forEach((button) => {
    button.addEventListener('click', function (e) {
      const platform = this.getAttribute('data-platform');
      const href = this.getAttribute('href');

      // Handle platforms with valid URLs
      if (href && href !== '#') {
        e.preventDefault();
        openShareWindow(href, platform);
      }
    });
  });
}

// Open share window
function openShareWindow(url, platform) {
  const width = 600;
  const height = 400;
  const left = (window.screen.width - width) / 2;
  const top = (window.screen.height - height) / 2;

  const shareWindow = window.open(
    url,
    'share',
    `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`,
  );

  // Focus the new window
  if (shareWindow) {
    shareWindow.focus();
  }
}

// Initialize social sharing when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  initializeSocialSharing();
});

// Make functions globally available for reinitialization
window.initializeSocialSharing = initializeSocialSharing;
window.openShareWindow = openShareWindow;

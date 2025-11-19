document.addEventListener('DOMContentLoaded', function () {
  // Initialize video functionality
  initVideoComponent();
});

function initVideoComponent() {
  // Find all video containers
  const videoContainers = document.querySelectorAll('[data-video-container]');

  videoContainers.forEach((container) => {
    const videoId = container.getAttribute('data-video-container');
    const video = document.getElementById(videoId);
    const playOverlay = document.querySelector(`[data-play-overlay="${videoId}"]`);
    const thumb = container.querySelector('.growfund-video-thumb');
    const nextBtn = document.querySelector('.growfund-slider-btn.next');
    const prevBtn = document.querySelector('.growfund-slider-btn.prev');

    if (!video || !container) return;

    // Handle play button click
    if (playOverlay) {
      playOverlay.addEventListener('click', function () {
        video.play();
        thumb.classList.add('hidden');
        video.classList.remove('growfund-video-hidden');

        prevBtn?.classList.add('hidden');
        nextBtn?.classList.add('hidden');
      });
    }

    // Handle video events
    video.addEventListener('play', function () {
      if (playOverlay) {
        playOverlay.classList.add('hidden');
      }
      setTimeout(() => {
        video.setAttribute('controls', 'controls');
      }, 300);
    });

    video.addEventListener('pause', function () {
      playOverlay.classList.remove('hidden');
      if (playOverlay && video.currentTime === 0) {
        thumb.classList.remove('hidden');
        prevBtn?.classList.remove('hidden');
        nextBtn?.classList.remove('hidden');
      }
    });

    video.addEventListener('ended', function () {
      if (playOverlay) {
        playOverlay.classList.remove('hidden');
        thumb.classList.remove('hidden');
        video.classList.add('growfund-video-hidden');
        prevBtn?.classList.remove('hidden');
        nextBtn?.classList.remove('hidden');
      }
    });

    // Handle video click to toggle play/pause
    video.addEventListener('click', function (event) {
      video.removeAttribute('controls', 'controls');
      if (video.paused) {
        video.play();
        prevBtn?.classList.add('hidden');
        nextBtn?.classList.add('hidden');
      } else {
        video.pause();
        prevBtn?.classList.remove('hidden');
        nextBtn?.classList.remove('hidden');
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.growfund-media-slider-container');
  const slides = document.querySelectorAll('.growfund-media-slider-item');
  const nextBtn = document.querySelector(
    '.growfund-media-slider-btn.growfund-media-slider-btn-next',
  );
  const prevBtn = document.querySelector(
    '.growfund-media-slider-btn.growfund-media-slider-btn-prev',
  );
  const thumbs = document.querySelectorAll('.growfund-media-slider-thumb');

  if (!container || slides.length === 0) return;

  let index = 0;

  function updateSlide() {
    container.style.transform = `translateX(-${index * 100}%)`;

    prevBtn?.classList.remove('hidden');
    nextBtn?.classList.remove('hidden');

    slides.forEach((slide, i, slides) => {
      slide.classList.toggle('growfund-media-slider-item-active-item', i === index);

      if (index === 0) {
        prevBtn?.setAttribute('disabled', 'disabled');
        prevBtn?.classList.add('hidden');
      } else {
        prevBtn?.removeAttribute('disabled');
        prevBtn?.classList.remove('hidden');
      }

      if (index === slides.length - 1) {
        nextBtn?.setAttribute('disabled', 'disabled');
        nextBtn?.classList.add('hidden');
      } else {
        nextBtn?.removeAttribute('disabled');
        nextBtn?.classList.remove('hidden');
      }
    });

    thumbs.forEach((thumb, i) => {
      thumb.classList.toggle('active', i === index);
    });

    const videoContainers = document.querySelectorAll('[data-video-container]');
    videoContainers.forEach((videoContainer) => {
      const videoId = videoContainer.getAttribute('data-video-container');
      if (videoId) {
        const video = document.getElementById(videoId);
        if (video && !video.paused) {
          video.pause();
        }
      }
    });
  }

  nextBtn?.addEventListener('click', () => {
    index = (index + 1) % slides.length;
    updateSlide();
  });

  prevBtn?.addEventListener('click', () => {
    index = (index - 1 + slides.length) % slides.length;
    updateSlide();
  });

  thumbs.forEach((thumb, i) => {
    thumb.addEventListener('click', () => {
      index = i;
      updateSlide();
    });
  });

  // Initialize first state
  updateSlide();
});

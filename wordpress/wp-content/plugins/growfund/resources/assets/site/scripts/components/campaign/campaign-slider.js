(function () {
  window.growfundEvents.recalculateCampaignSlider = (recalculate = true) => {
    document.dispatchEvent(
      new CustomEvent('growfund:recalculate-campaign-slider', {
        detail: recalculate,
      }),
    );
  };

  document.addEventListener('DOMContentLoaded', () => {
    const prevButton = document.querySelector('.growfund-campaign-slider-arrow-left');
    const nextButton = document.querySelector('.growfund-campaign-slider-arrow-right');
    const sliderTrack = document.querySelector('.growfund-campaign-slider-track');
    const sliderWindow = document.querySelector('.growfund-campaign-slider-window');

    if (!sliderTrack || !sliderWindow) return;

    let stepSize,
      maxTranslate,
      currentTranslate = 0,
      campaignCards,
      secondLastCardIndex = 0;

    function isSecondLastCardVisible() {
      const secondLastCard = campaignCards[secondLastCardIndex];
      if (!secondLastCard) return false;

      const windowLeft = currentTranslate;
      const windowRight = currentTranslate + sliderWindow.offsetWidth;

      const cardLeft = secondLastCard.offsetLeft;
      const cardRight = cardLeft + secondLastCard.offsetWidth;

      return cardRight > windowLeft && cardLeft < windowRight;
    }

    function calculateMetrics() {
      campaignCards = sliderTrack.children;
      if (campaignCards.length === 0) return;
      secondLastCardIndex = campaignCards.length - 2;

      const windowWidth = sliderWindow.offsetWidth;
      const trackStyle = window.getComputedStyle(sliderTrack);

      const cards = Array.from(campaignCards);
      const gapValue = parseFloat(trackStyle.gap);

      cards.forEach((card) => {
        if (windowWidth > 640) {
          const desktopCardWidth = (windowWidth - gapValue) / 2;
          card.style.flex = `0 0 ${desktopCardWidth}px`;
          card.style.width = `${desktopCardWidth}px`;
        } else {
          card.style.flex = `0 0 ${windowWidth}px`;
          card.style.width = `${windowWidth}px`;
        }
      });
      const cardWidth = campaignCards[0]?.getBoundingClientRect().width;
      stepSize = cardWidth + gapValue;
      maxTranslate = sliderTrack.scrollWidth - sliderWindow.offsetWidth;

      if (currentTranslate > maxTranslate) {
        currentTranslate = maxTranslate;
        updateSlider();
      }
    }

    function dispatchSliderEndingEvent() {
      document.dispatchEvent(
        new CustomEvent('growfund:campaign-slider-ending', {
          detail: {
            state: 'ending',
            index: secondLastCardIndex,
          },
        }),
      );
    }

    function updateSlider() {
      if (currentTranslate < 0) currentTranslate = 0;
      if (currentTranslate > maxTranslate) currentTranslate = maxTranslate;

      sliderTrack.style.transform = `translateX(-${currentTranslate}px)`;
      prevButton.style.opacity = currentTranslate <= 0 ? '0.3' : '1';
      prevButton.style.pointerEvents = currentTranslate <= 0 ? 'none' : 'auto';
      nextButton.style.opacity = currentTranslate >= maxTranslate - 5 ? '0.3' : '1';
      nextButton.style.pointerEvents = currentTranslate >= maxTranslate - 5 ? 'none' : 'auto';

      if (isSecondLastCardVisible()) {
        dispatchSliderEndingEvent();
      }
    }

    nextButton.addEventListener('click', () => {
      currentTranslate += stepSize;
      updateSlider();
    });

    prevButton.addEventListener('click', () => {
      currentTranslate -= stepSize;
      updateSlider();
    });

    window.addEventListener('resize', calculateMetrics);

    document.addEventListener('growfund:recalculate-campaign-slider', () => {
      calculateMetrics();
      updateSlider();
    });

    // Initial load
    calculateMetrics();
    updateSlider();
  });
})();

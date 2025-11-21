/**
 * Countdown Timer JavaScript
 * Provides live countdown functionality for campaign end dates
 */

(function () {
  'use strict';

  // Store active countdown timers
  const activeTimers = new Map();

  /**
   * Initialize countdown for a specific element
   * @param {HTMLElement} element - The countdown container element
   */
  function initializeCountdown(element) {
    if (!element || activeTimers.has(element)) {
      return;
    }

    const endDate = element.getAttribute('data-end-date');
    if (!endDate) {
      console.warn('Countdown element missing end date:', element);
      return;
    }

    // Parse end date
    const targetDate = new Date(endDate);
    if (isNaN(targetDate.getTime())) {
      console.error('Invalid end date format:', endDate);
      return;
    }

    // Add loading state
    element.classList.add('loading');

    // Start the countdown
    const timer = startCountdown(element, targetDate);
    activeTimers.set(element, timer);

    // Remove loading state
    element.classList.remove('loading');
  }

  /**
   * Start countdown timer
   * @param {HTMLElement} element - The countdown container element
   * @param {Date} targetDate - The target end date
   * @returns {number} - Timer interval ID
   */
  function startCountdown(element, targetDate) {
    return setInterval(() => {
      updateCountdown(element, targetDate);
    }, 1000);
  }

  /**
   * Update countdown display
   * @param {HTMLElement} element - The countdown container element
   * @param {Date} targetDate - The target end date
   */
  function updateCountdown(element, targetDate) {
    const now = new Date();
    const timeLeft = targetDate.getTime() - now.getTime();

    if (timeLeft <= 0) {
      // Campaign has ended
      displayExpired(element);
      stopCountdown(element);
      return;
    }

    // Calculate time units
    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

    // Update display with animation
    updateTimeUnit(element, 'days', days);
    updateTimeUnit(element, 'hours', hours);
    updateTimeUnit(element, 'minutes', minutes);
    updateTimeUnit(element, 'seconds', seconds);
  }

  /**
   * Update a specific time unit with animation
   * @param {HTMLElement} element - The countdown container element
   * @param {string} unit - The time unit (days, hours, minutes, seconds)
   * @param {number} value - The new value to display
   */
  function updateTimeUnit(element, unit, value) {
    const numberElement = element.querySelector(`[data-unit="${unit}"]`);
    if (!numberElement) return;

    const currentValue = parseInt(numberElement.textContent) || 0;
    if (currentValue !== value) {
      // Add animation class
      numberElement.classList.add('updating');

      // Update the value
      numberElement.textContent = value.toString().padStart(2, '0');

      // Remove animation class after animation completes
      setTimeout(() => {
        numberElement.classList.remove('updating');
      }, 300);
    }
  }

  /**
   * Display expired state
   * @param {HTMLElement} element - The countdown container element
   */
  function displayExpired(element) {
    element.classList.add('expired');

    // Update all time units to show expired state
    const numberElements = element.querySelectorAll('.growfund-countdown__number');
    numberElements.forEach((el) => {
      el.textContent = '00';
    });

    // Add expired message
    const expiredMessage = document.createElement('div');
    expiredMessage.className = 'growfund-countdown__expired';
    expiredMessage.textContent = 'Campaign has ended';
    expiredMessage.style.cssText = `
            text-align: center;
            font-weight: 600;
            color: #dc2626;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            grid-column: 1 / span 4;
        `;

    element.appendChild(expiredMessage);
  }

  /**
   * Stop countdown for a specific element
   * @param {HTMLElement} element - The countdown container element
   */
  function stopCountdown(element) {
    const timer = activeTimers.get(element);
    if (timer) {
      clearInterval(timer);
      activeTimers.delete(element);
    }
  }

  /**
   * Stop all active countdowns
   */
  function stopAllCountdowns() {
    activeTimers.forEach((timer, element) => {
      clearInterval(timer);
    });
    activeTimers.clear();
  }

  /**
   * Initialize all countdown elements on the page
   */
  function initializeAllCountdowns() {
    const countdownElements = document.querySelectorAll('.growfund-countdown[data-end-date]');
    countdownElements.forEach((element) => {
      initializeCountdown(element);
    });
  }

  /**
   * Cleanup function for page unload
   */
  function cleanup() {
    stopAllCountdowns();
  }

  // Public API - expose functions globally
  window.initializeCountdown = initializeCountdown;
  window.stopCountdown = stopCountdown;
  window.stopAllCountdowns = stopAllCountdowns;

  // Auto-initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAllCountdowns);
  } else {
    initializeAllCountdowns();
  }

  // Cleanup on page unload
  window.addEventListener('beforeunload', cleanup);
  window.addEventListener('pagehide', cleanup);

  // Handle dynamic content (for AJAX-loaded content)
  if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === Node.ELEMENT_NODE) {
            const countdownElements = node.querySelectorAll('.growfund-countdown[data-end-date]');
            countdownElements.forEach((element) => {
              initializeCountdown(element);
            });
          }
        });
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }
})();

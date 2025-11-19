/**
 * FAQ Accordion Component
 * Handles smooth accordion animations without layout shifts
 */

class FAQAccordion {
  constructor(container = document) {
    this.container = container;
    this.faqItems = this.container.querySelectorAll('[data-faq-item]');
    this.faqTriggers = this.container.querySelectorAll('[data-faq-trigger]');
    this.faqPanels = this.container.querySelectorAll('[data-faq-panel]');

    // Animation settings
    this.animationDuration = 300;
    this.easingFunction = 'cubic-bezier(0.25, 0.46, 0.45, 0.94)';

    this.init();
  }

  init() {
    if (this.faqTriggers.length === 0) {
      return;
    }

    this.bindEvents();
    this.setInitialState();
    this.setupResizeObserver();
  }

  bindEvents() {
    this.faqTriggers.forEach((trigger) => {
      trigger.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggleItem(trigger);
      });

      // Enhanced keyboard support
      trigger.addEventListener('keydown', (e) => {
        switch (e.key) {
          case 'Enter':
          case ' ':
            e.preventDefault();
            this.toggleItem(trigger);
            break;
          case 'ArrowDown':
            e.preventDefault();
            this.focusNextItem(trigger);
            break;
          case 'ArrowUp':
            e.preventDefault();
            this.focusPreviousItem(trigger);
            break;
          case 'Home':
            e.preventDefault();
            this.focusFirstItem();
            break;
          case 'End':
            e.preventDefault();
            this.focusLastItem();
            break;
        }
      });
    });
  }

  toggleItem(trigger) {
    const faqItem = trigger.closest('[data-faq-item]');
    const panel = faqItem.querySelector('[data-faq-panel]');
    const isCurrentlyActive = faqItem.classList.contains('active');

    // Close all other items first (accordion behavior)
    this.closeAllItems();

    // Toggle current item
    if (!isCurrentlyActive) {
      this.openItem(faqItem, trigger, panel);
    }
  }

  openItem(faqItem, trigger, panel) {
    // Calculate the content height first
    const content = panel.querySelector('.growfund-faq-answer-content');
    const contentHeight = this.getContentHeight(content);

    // Set explicit height for smooth animation
    panel.style.height = '0px';

    // Add active class and update ARIA
    faqItem.classList.add('active');
    trigger.setAttribute('aria-expanded', 'true');

    // Use requestAnimationFrame for smoother animation
    requestAnimationFrame(() => {
      panel.style.height = contentHeight + 'px';
    });

    // Trigger custom event
    this.triggerEvent('faqItemOpened', {
      item: faqItem,
      trigger: trigger,
      panel: panel,
    });
  }

  closeItem(faqItem, trigger, panel) {
    if (!faqItem.classList.contains('active')) {
      return;
    }

    // Get current height for smooth closing animation
    const content = panel.querySelector('.growfund-faq-answer-content');
    const currentHeight = this.getContentHeight(content);

    // Set current height explicitly
    panel.style.height = currentHeight + 'px';

    // Force reflow
    panel.offsetHeight;

    // Animate to zero height
    panel.style.height = '0px';

    // Remove active class and update ARIA after animation starts
    setTimeout(() => {
      faqItem.classList.remove('active');
      trigger.setAttribute('aria-expanded', 'false');
    }, 50);

    // Trigger custom event
    this.triggerEvent('faqItemClosed', {
      item: faqItem,
      trigger: trigger,
      panel: panel,
    });
  }

  closeAllItems() {
    this.faqItems.forEach((item) => {
      const trigger = item.querySelector('[data-faq-trigger]');
      const panel = item.querySelector('[data-faq-panel]');
      if (item.classList.contains('active')) {
        this.closeItem(item, trigger, panel);
      }
    });
  }

  openAllItems() {
    this.faqItems.forEach((item) => {
      const trigger = item.querySelector('[data-faq-trigger]');
      const panel = item.querySelector('[data-faq-panel]');
      if (!item.classList.contains('active')) {
        this.openItem(item, trigger, panel);
      }
    });
  }

  getContentHeight(content) {
    // Store original styles
    const originalDisplay = content.style.display;
    const originalPosition = content.style.position;
    const originalVisibility = content.style.visibility;
    const originalHeight = content.style.height;

    // Temporarily make content visible and measurable
    content.style.display = 'block';
    content.style.position = 'absolute';
    content.style.visibility = 'hidden';
    content.style.height = 'auto';

    // Get the scroll height which includes padding
    const height = content.scrollHeight;

    // Restore original styles
    content.style.display = originalDisplay;
    content.style.position = originalPosition;
    content.style.visibility = originalVisibility;
    content.style.height = originalHeight;

    return height;
  }

  setInitialState() {
    // Set up proper ARIA attributes and initial heights
    this.faqItems.forEach((item) => {
      const trigger = item.querySelector('[data-faq-trigger]');
      const panel = item.querySelector('[data-faq-panel]');
      const isActive = item.classList.contains('active');

      trigger.setAttribute('aria-expanded', isActive.toString());

      // Set initial panel height
      if (isActive) {
        const content = panel.querySelector('.growfund-faq-answer-content');
        const height = this.getContentHeight(content);
        panel.style.height = height + 'px';
      } else {
        panel.style.height = '0px';
      }

      // Ensure proper button attributes
      if (!trigger.hasAttribute('type')) {
        trigger.setAttribute('type', 'button');
      }
    });
  }

  setupResizeObserver() {
    // Recalculate heights on resize to prevent layout issues
    if (window.ResizeObserver) {
      this.resizeObserver = new ResizeObserver((entries) => {
        // Debounce resize updates to prevent excessive calculations
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
          entries.forEach((entry) => {
            const faqItem = entry.target.closest('[data-faq-item]');
            if (faqItem && faqItem.classList.contains('active')) {
              const panel = faqItem.querySelector('[data-faq-panel]');
              const content = panel.querySelector('.growfund-faq-answer-content');
              const newHeight = this.getContentHeight(content);
              // Update height only if it's significantly different to avoid micro-shifts
              const currentHeight = parseInt(panel.style.height) || 0;
              if (Math.abs(newHeight - currentHeight) > 2) {
                panel.style.height = newHeight + 'px';
              }
            }
          });
        }, 100);
      });

      // Observe all content elements
      this.faqPanels.forEach((panel) => {
        const content = panel.querySelector('.growfund-faq-answer-content');
        if (content) {
          this.resizeObserver.observe(content);
        }
      });
    }
  }

  // Enhanced keyboard navigation
  focusNextItem(currentTrigger) {
    const currentIndex = Array.from(this.faqTriggers).indexOf(currentTrigger);
    const nextIndex = (currentIndex + 1) % this.faqTriggers.length;
    this.faqTriggers[nextIndex].focus();
  }

  focusPreviousItem(currentTrigger) {
    const currentIndex = Array.from(this.faqTriggers).indexOf(currentTrigger);
    const prevIndex = currentIndex === 0 ? this.faqTriggers.length - 1 : currentIndex - 1;
    this.faqTriggers[prevIndex].focus();
  }

  focusFirstItem() {
    this.faqTriggers[0].focus();
  }

  focusLastItem() {
    this.faqTriggers[this.faqTriggers.length - 1].focus();
  }

  triggerEvent(eventName, detail) {
    const event = new CustomEvent(eventName, {
      detail: detail,
      bubbles: true,
      cancelable: true,
    });

    this.container.dispatchEvent(event);
  }

  // Public API methods
  getActiveItem() {
    return this.container.querySelector('[data-faq-item].active');
  }

  getActiveItems() {
    return this.container.querySelectorAll('[data-faq-item].active');
  }

  openItemByIndex(index) {
    const item = this.faqItems[index];
    if (item) {
      const trigger = item.querySelector('[data-faq-trigger]');
      const panel = item.querySelector('[data-faq-panel]');
      this.closeAllItems();
      this.openItem(item, trigger, panel);
    }
  }

  destroy() {
    // Remove all event listeners
    this.faqTriggers.forEach((trigger) => {
      trigger.removeEventListener('click', this.toggleItem);
      trigger.removeEventListener('keydown', this.toggleItem);
    });

    // Disconnect resize observer
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }

    // Reset inline styles
    this.faqPanels.forEach((panel) => {
      panel.style.height = '';
    });
  }
}

// Auto-initialize FAQ accordions when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  // Initialize FAQ components with the new selector
  const faqContainers = document.querySelectorAll('[data-component="faq-accordion"]');

  faqContainers.forEach((container) => {
    new FAQAccordion(container);
  });

  // Fallback: Initialize globally if no specific containers found
  if (faqContainers.length === 0) {
    // Look for legacy containers
    const legacyContainers = document.querySelectorAll('.growfund-faq-layout, .growfund-faq-content');
    if (legacyContainers.length > 0) {
      legacyContainers.forEach((container) => {
        new FAQAccordion(container);
      });
    } else {
      new FAQAccordion();
    }
  }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FAQAccordion;
}

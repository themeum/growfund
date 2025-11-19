/**
 * Tabs Component JavaScript
 * Handles tab switching functionality with URL hash support
 */

class TabSwitcher {
  constructor() {
    this.tabButtons = document.querySelectorAll('.growfund-tabs__btn');
    this.tabContents = document.querySelectorAll('.growfund-tab-content');

    this.activeTabKey = this.determineDefaultTab();

    this.tabsNav = document.getElementById('growfund-tabs-nav');
    this.scrollLeftBtn = document.querySelector('.growfund-tabs__scroll-btn--left');
    this.scrollRightBtn = document.querySelector('.growfund-tabs__scroll-btn--right');
    this.scrollLeftWrapper = document.querySelector('.growfund-tabs__scroll-btn-wrapper--left');
    this.scrollRightWrapper = document.querySelector('.growfund-tabs__scroll-btn-wrapper--right');

    this.init();
  }

  determineDefaultTab() {
    const isDonationMode = document.querySelector('[data-mode="donation"]') !== null;
    return isDonationMode ? 'info' : 'campaign';
  }

  init() {
    if (this.tabButtons.length === 0 || this.tabContents.length === 0) {
      return;
    }

    this.tabButtons.forEach((button, index) => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const tabName = this.getTabNameFromButton(button, index);
        this.switchTab(tabName);
      });
    });

    this.initHorizontalScroll();
    this.initializeFromHash();

    window.addEventListener('hashchange', () => {
      this.initializeFromHash();
    });

    this.initFAQPromptHandlers();
  }

  initializeFromHash() {
    const hash = window.location.hash.slice(1);

    if (hash && this.tabExists(hash)) {
      this.switchTab(hash, false, false);
    } else {
      this.setActiveTab(this.activeTabKey);
    }
  }

  initHorizontalScroll() {
    if (!this.tabsNav || !this.scrollLeftBtn || !this.scrollRightBtn) {
      return;
    }

    const scrollAmount = 200;

    this.scrollLeftBtn.addEventListener('click', () => {
      this.tabsNav.scrollBy({
        left: -scrollAmount,
        behavior: 'auto',
      });
    });

    this.scrollRightBtn.addEventListener('click', () => {
      this.tabsNav.scrollBy({
        left: scrollAmount,
        behavior: 'auto',
      });
    });

    this.updateScrollButtons();

    this.tabsNav.addEventListener('scroll', () => {
      this.updateScrollButtons();
    });

    window.addEventListener('resize', () => {
      this.updateScrollButtons();
    });

    setTimeout(() => {
      this.updateScrollButtons();
    }, 100);
  }

  initFAQPromptHandlers() {
    document.addEventListener('click', (e) => {
      const faqPrompt = e.target.closest('.growfund-faq-prompt');
      if (faqPrompt) {
        e.preventDefault();
        this.switchTab('faq');
      }
    });
  }

  updateScrollButtons() {
    if (!this.tabsNav || !this.scrollLeftBtn || !this.scrollRightBtn) {
      return;
    }

    const { scrollLeft, scrollWidth, clientWidth } = this.tabsNav;
    const maxScrollLeft = scrollWidth - clientWidth;

    if (scrollLeft <= 0) {
      this.scrollLeftBtn.disabled = true;
    } else {
      this.scrollLeftBtn.disabled = false;
    }

    if (scrollLeft >= maxScrollLeft - 1) {
      this.scrollRightBtn.disabled = true;
    } else {
      this.scrollRightBtn.disabled = false;
    }

    if (scrollWidth <= clientWidth) {
      if (this.scrollLeftWrapper) {
        this.scrollLeftWrapper.style.display = 'none';
      }
      if (this.scrollRightWrapper) {
        this.scrollRightWrapper.style.display = 'none';
      }
    } else {
      if (this.scrollLeftWrapper) {
        this.scrollLeftWrapper.style.display = 'flex';
      }
      if (this.scrollRightWrapper) {
        this.scrollRightWrapper.style.display = 'flex';
      }
    }
  }

  getTabNameFromButton(button, index) {
    const dataTab = button.getAttribute('data-tab');
    if (dataTab) {
      return dataTab;
    }

    const buttonText = button.textContent.trim().toLowerCase();
    const cleanText = buttonText.replace(/\s*\d+\s*$/, '').trim();

    const tabMap = {
      campaign: 'campaign',
      info: 'info',
      rewards: 'rewards',
      faq: 'faq',
      updates: 'updates',
      comments: 'comments',
    };

    return tabMap[cleanText] || Object.keys(tabMap)[index] || this.activeTabKey;
  }

  switchTab(tabName, updateHash = true, scrollToTab = false) {
    this.activeTabKey = tabName;

    if (updateHash) {
      this.updateURLHash(tabName);
    }

    this.updateTabButtons(tabName);
    this.updateTabContent(tabName);
    this.triggerTabChangeEvent(tabName);
  }

  updateURLHash(tabName) {
    const newURL = window.location.pathname + window.location.search + '#' + tabName;

    if (window.location.hash.slice(1) !== tabName) {
      history.pushState(null, null, newURL);
    }
  }

  scrollToActiveTab(tabName) {
    const tabsContainer = this.tabsNav?.closest('.growfund-tabs') || document.querySelector('.growfund-tabs');

    if (tabsContainer) {
      setTimeout(() => {
        tabsContainer.scrollIntoView({
          behavior: 'auto',
          block: 'start',
          inline: 'nearest',
        });
      }, 100);
    } else {
      const activeTabContent = Array.from(this.tabContents).find(
        (content) => content.getAttribute('data-tab') === tabName,
      );

      if (activeTabContent) {
        setTimeout(() => {
          activeTabContent.scrollIntoView({
            behavior: 'auto',
            block: 'start',
            inline: 'nearest',
          });
        }, 100);
      }
    }
  }

  updateTabButtons(activeTabName) {
    this.tabButtons.forEach((button, index) => {
      const tabName = this.getTabNameFromButton(button, index);
      const badge = button.querySelector('.growfund-badge');

      if (tabName === activeTabName) {
        button.classList.add('growfund-tabs__btn--active');
        button.setAttribute('aria-selected', 'true');

        if (badge) {
          badge.classList.add('growfund-badge--active');
        }
      } else {
        button.classList.remove('growfund-tabs__btn--active');
        button.setAttribute('aria-selected', 'false');

        if (badge) {
          badge.classList.remove('growfund-badge--active');
        }
      }
    });
  }

  updateTabContent(activeTabName) {
    this.tabContents.forEach((content) => {
      const contentTabName = content.getAttribute('data-tab');

      if (contentTabName === activeTabName) {
        content.classList.add('growfund-tab-content--active');
        content.setAttribute('aria-hidden', 'false');

        this.loadTabContent(content, activeTabName);
      } else {
        content.classList.remove('growfund-tab-content--active');
        content.setAttribute('aria-hidden', 'true');
      }
    });
  }

  loadTabContent(contentElement, tabName) {
    if (contentElement.getAttribute('data-loaded') === 'true') {
      return;
    }

    contentElement.setAttribute('data-loaded', 'true');
    this.initializeTabSpecificFeatures(tabName);
  }

  initializeTabSpecificFeatures(tabName) {
    switch (tabName) {
      case 'rewards':
        this.initializeRewardTiers();
        break;
      case 'comments':
        this.initializeComments();
        break;
      default:
        break;
    }
  }

  initializeRewardTiers() {
    const rewardTiers = document.querySelectorAll('.growfund-campaign-reward');

    rewardTiers.forEach((tier) => {
      tier.addEventListener('click', () => {
        rewardTiers.forEach((t) => t.classList.remove('growfund-campaign-reward--selected'));
        tier.classList.add('growfund-campaign-reward--selected');
      });
    });
  }

  initializeComments() {
    // Comment functionality handled by existing code
  }

  setActiveTab(tabName) {
    this.switchTab(tabName, false, false);
  }

  tabExists(tabName) {
    return Array.from(this.tabContents).some(
      (content) => content.getAttribute('data-tab') === tabName,
    );
  }

  triggerTabChangeEvent(tabName) {
    const event = new CustomEvent('tabChanged', {
      detail: {
        activeTab: tabName,
        tabSwitcher: this,
      },
      bubbles: true,
    });

    document.dispatchEvent(event);
  }

  getActiveTab() {
    return this.activeTabKey;
  }

  activateTab(tabName) {
    if (this.tabExists(tabName)) {
      this.switchTab(tabName);
      return true;
    }
    return false;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.growfundTabs = new TabSwitcher();

  const chooseRewardLinks = document.querySelectorAll('.growfund-empty-action[href="#choose-reward"]');
  chooseRewardLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();

      if (window.growfundTabs) {
        window.growfundTabs.activateTab('rewards');
      }
    });
  });
});

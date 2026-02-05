(function () {
  function dispatchTabTrigger(key) {
    document.dispatchEvent(
      new CustomEvent('growfund:tab-trigger', {
        detail: {
          key,
        },
      }),
    );
  }

  document.addEventListener('DOMContentLoaded', function () {
    const tabWrappers = document.querySelectorAll('[data-growfund-tab-contents]');

    tabWrappers.forEach((wrapper) => {
      const tabButtons = wrapper.querySelectorAll('[data-growfund-tab-contents-trigger]');
      const tabPanels = wrapper.querySelectorAll('[data-growfund-tab-contents-panel]');

      function activateTab(key) {
        dispatchTabTrigger(key);
        tabButtons.forEach((btn) => {
          if (btn.dataset.growfundTabContentsTrigger === key) {
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
          } else {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
          }
        });

        tabPanels.forEach((panel) => {
          if (panel.dataset.growfundTabContentsPanel === key) {
            panel.style.display = 'block';
            panel.setAttribute('aria-hidden', 'false');
          } else {
            panel.style.display = 'none';
            panel.setAttribute('aria-hidden', 'true');
          }
        });

        // Update URL hash
        if (window.location.hash.slice(1) !== key) {
          window.history.pushState(null, null, `#${key}`);
        }
      }

      // Add click events to tabs
      tabButtons.forEach((button) => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          const key = button.dataset.growfundTabContentsTrigger;
          activateTab(key);
        });
      });

      // Activate initial tab based on hash or first tab
      const initialHash = window.location.hash.slice(1);
      if (
        initialHash &&
        wrapper.querySelector(`[data-growfund-tab-contents-trigger="${initialHash}"]`)
      ) {
        activateTab(initialHash);
      } else if (tabButtons.length > 0) {
        activateTab(tabButtons[0].dataset.growfundTabContentsTrigger);
      }

      // Handle back/forward browser buttons
      window.addEventListener('popstate', () => {
        const currentHash = window.location.hash.slice(1);
        if (
          currentHash &&
          wrapper.querySelector(`[data-growfund-tab-contents-trigger="${currentHash}"]`)
        ) {
          activateTab(currentHash);
        }
      });
    });
  });
})();

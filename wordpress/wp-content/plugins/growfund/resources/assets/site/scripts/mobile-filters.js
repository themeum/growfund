document.addEventListener('DOMContentLoaded', () => {
  const filterModal = document.getElementById('growfund-filter-modal');
  const openFilterButton = document.getElementById('growfund-mobile-filter-trigger');
  const closeFilterButton = document.getElementById('growfund-filter-modal-close');
  const overlay = filterModal ? filterModal.querySelector('.growfund-filter-modal__overlay') : null;

  if (openFilterButton) {
    openFilterButton.addEventListener('click', () => {
      if (filterModal) {
        filterModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        document
          .querySelectorAll('#growfund-filter-modal .growfund-mobile-filter-category--is-expanded')
          .forEach((item) => {
            const childrenContainer =
              item.querySelector('.growfund-filter-group__content.growfund-mobile-filter-category__children') ||
              item.querySelector('.growfund-mobile-filter-category__children');
            if (childrenContainer) {
              updateCollapsibleHeight(childrenContainer);
            }
          });
      }
    });
  }

  const closeModal = () => {
    if (filterModal) {
      filterModal.classList.remove('is-open');
      document.body.style.overflow = '';
    }
  };

  if (closeFilterButton) {
    closeFilterButton.addEventListener('click', closeModal);
  }

  if (overlay) {
    overlay.addEventListener('click', closeModal);
  }

  const arrowDownSvg =
    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>';
  const arrowUpSvg =
    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 10L8 6L4 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>';

  const updateCollapsibleHeight = (element) => {
    const parentItem = element.parentElement.closest(
      '.growfund-mobile-filter-category-item, .growfund-filter-group',
    );

    const isParentItemExpanded =
      parentItem && parentItem.classList.contains('growfund-mobile-filter-category--is-expanded');

    if (isParentItemExpanded) {
      element.style.maxHeight = 'auto';
      getComputedStyle(element).height;
      element.style.maxHeight = element.scrollHeight + 'px';
    } else {
      element.style.maxHeight = '0';
    }
  };

  const collapsibleItems = document.querySelectorAll('.growfund-mobile-filter-category--has-children');

  collapsibleItems.forEach((item) => {
    const childrenContainer =
      item.querySelector('.growfund-filter-group__content.growfund-mobile-filter-category__children') ||
      item.querySelector('.growfund-mobile-filter-category__children');

    const arrow = item.querySelector('.growfund-mobile-filter-category__arrow');

    if (childrenContainer) {
      if (item.classList.contains('growfund-mobile-filter-category--is-expanded')) {
        updateCollapsibleHeight(childrenContainer);
        if (arrow) {
          arrow.innerHTML = arrowUpSvg;
        }
      } else {
        updateCollapsibleHeight(childrenContainer);
      }

      childrenContainer.addEventListener('transitionend', (event) => {
        if (event.propertyName === 'max-height') {
          const immediateParentContent = childrenContainer.parentElement.closest(
            '.growfund-filter-group__content, .growfund-mobile-filter-category__children',
          );
          if (immediateParentContent) {
            updateCollapsibleHeight(immediateParentContent);
          }
        }
      });
    }
  });

  if (filterModal) {
    filterModal.addEventListener('click', (e) => {
      const toggleButton = e.target.closest('.growfund-mobile-filter-category__toggle');
      if (toggleButton) {
        e.preventDefault();

        const item = toggleButton.closest('.growfund-mobile-filter-category-item, .growfund-filter-group');
        if (!item) return;

        const childrenContainer =
          item.querySelector('.growfund-filter-group__content.growfund-mobile-filter-category__children') ||
          item.querySelector('.growfund-mobile-filter-category__children');

        if (!childrenContainer) return;

        const isCurrentlyExpanded = item.classList.contains(
          'growfund-mobile-filter-category--is-expanded',
        );
        const isTopLevelGroup = item.classList.contains('growfund-filter-group');

        if (!isCurrentlyExpanded) {
          if (isTopLevelGroup) {
            document.querySelectorAll('.growfund-filter-group').forEach((otherGroup) => {
              if (
                otherGroup !== item &&
                otherGroup.classList.contains('growfund-mobile-filter-category--is-expanded')
              ) {
                otherGroup.classList.remove('growfund-mobile-filter-category--is-expanded');
                otherGroup.querySelector('.growfund-mobile-filter-category__arrow').innerHTML =
                  arrowDownSvg;
                const otherChildrenContainer = otherGroup.querySelector(
                  '.growfund-filter-group__content.growfund-mobile-filter-category__children',
                );
                if (otherChildrenContainer) {
                  updateCollapsibleHeight(otherChildrenContainer);
                }
              }
            });
          } else {
            const parentUl = item.parentElement;
            if (parentUl) {
              Array.from(parentUl.children).forEach((siblingItem) => {
                if (
                  siblingItem !== item &&
                  siblingItem.classList.contains('growfund-mobile-filter-category-item') &&
                  siblingItem.classList.contains('growfund-mobile-filter-category--is-expanded')
                ) {
                  siblingItem.classList.remove('growfund-mobile-filter-category--is-expanded');
                  siblingItem.querySelector('.growfund-mobile-filter-category__arrow').innerHTML =
                    arrowDownSvg;
                  const siblingChildrenContainer = siblingItem.querySelector(
                    '.growfund-mobile-filter-category__children',
                  );
                  if (siblingChildrenContainer) {
                    updateCollapsibleHeight(siblingChildrenContainer);
                  }
                }
              });
            }
          }
        }

        if (isCurrentlyExpanded) {
          item.classList.remove('growfund-mobile-filter-category--is-expanded');
          toggleButton.querySelector('.growfund-mobile-filter-category__arrow').innerHTML = arrowDownSvg;
        } else {
          item.classList.add('growfund-mobile-filter-category--is-expanded');
          toggleButton.querySelector('.growfund-mobile-filter-category__arrow').innerHTML = arrowUpSvg;
        }

        updateCollapsibleHeight(childrenContainer);

        const immediateParentContent = item.parentElement.closest(
          '.growfund-filter-group__content, .growfund-mobile-filter-category__children',
        );
        if (immediateParentContent) {
          updateCollapsibleHeight(immediateParentContent);
        }
      }

      const sortItem = e.target.closest(
        '[data-filter-group="sort"] .growfund-mobile-filter-category-item',
      );
      if (sortItem) {
        const sortValue = sortItem.dataset.value;
        const hiddenInput = filterModal.querySelector('input[name="mobile_sort_temp"]');
        if (hiddenInput) {
          hiddenInput.value = sortValue;
        }

        const allSortItems = filterModal.querySelectorAll(
          '[data-filter-group="sort"] .growfund-mobile-filter-category-item',
        );
        allSortItems.forEach((item) =>
          item.classList.remove('growfund-mobile-filter-category--is-active'),
        );
        sortItem.classList.add('growfund-mobile-filter-category--is-active');
      }
    });

    filterModal.addEventListener('change', (e) => {
      if (e.target.matches('input[name="mobile_category_temp"]')) {
        filterModal.querySelectorAll('.growfund-mobile-filter-category-item').forEach((item) => {
          item.classList.remove(
            'growfund-mobile-filter-category--is-active',
            'growfund-mobile-filter-category--is-active-parent',
          );
        });

        const parentLi = e.target.closest('.growfund-mobile-filter-category-item');
        if (parentLi) {
          parentLi.classList.add('growfund-mobile-filter-category--is-active');

          let current = parentLi;
          while (current.parentElement) {
            const ancestor = current.parentElement.closest('.growfund-mobile-filter-category-item');
            if (ancestor) {
              ancestor.classList.add('growfund-mobile-filter-category--is-active-parent');
              current = ancestor;
            } else {
              break;
            }
          }
        }
      }
      if (e.target.matches('input[name="mobile_sort_temp"]')) {
        filterModal.querySelectorAll('input[name="mobile_sort_temp"]').forEach((radio) => {
          const item = radio.closest('.growfund-mobile-filter-category-item');
          if (item) {
            item.classList.remove('growfund-mobile-filter-category--is-active');
          }
        });

        const parentLi = e.target.closest('.growfund-mobile-filter-category-item');
        if (parentLi) {
          parentLi.classList.add('growfund-mobile-filter-category--is-active');
        }
      }
    });
  }

  collapsibleItems.forEach((item) => {
    const toggleButton =
      item.querySelector('.growfund-filter-group__title .growfund-mobile-filter-category__toggle') ||
      item.querySelector('.growfund-mobile-filter-category__toggle');
    if (toggleButton) {
    }
  });

  document
    .querySelectorAll(
      '.growfund-mobile-filter-options-list-label input[type="radio"], .growfund-mobile-filter-sort-list-label input[type="radio"]',
    )
    .forEach((radio) => {
      radio.addEventListener('change', () => {
        const closestChildrenContainer =
          radio.closest('.growfund-mobile-filter-category__children') ||
          radio.closest('.growfund-filter-group__content');
        if (closestChildrenContainer) {
          requestAnimationFrame(() => {
            setTimeout(() => {
              updateCollapsibleHeight(closestChildrenContainer);
            }, 100);
          });
        }
      });
    });

  const clearButton = document.getElementById('growfund-filter-clear');
  if (clearButton) {
    clearButton.addEventListener('click', function () {
      document.querySelectorAll('input[name="mobile_category_temp"]:checked').forEach((radio) => {
        radio.checked = false;
      });

      const locationInput = document.querySelector('input[name="mobile_location"]');
      if (locationInput) {
        locationInput.value = '';
      }

      const sortInput = document.querySelector('#growfund-filter-modal input[name="mobile_sort_temp"]');
      if (sortInput) {
        sortInput.value = '';
      }
      document
        .querySelectorAll('[data-filter-group="sort"] .growfund-mobile-filter-category-item')
        .forEach((item) => item.classList.remove('growfund-mobile-filter-category--is-active'));

      const mainSearchInput = document.querySelector('input[name="search"]');
      if (mainSearchInput) {
        mainSearchInput.value = '';
      }

      const baseUrl = window.location.href.split('?')[0];
      window.location.href = baseUrl;
    });
  }

  const applyButton = document.getElementById('growfund-filter-apply');
  if (applyButton) {
    applyButton.addEventListener('click', function () {
      const url = new URL(window.location.href);
      const params = new URLSearchParams(url.search);

      const selectedCategory = document.querySelector(
        '#growfund-filter-modal input[name="mobile_category_temp"]:checked',
      );
      if (selectedCategory && selectedCategory.value) {
        params.set('category', selectedCategory.value);
      } else {
        params.delete('category');
      }

      const mobileLocationInput = document.querySelector(
        '#growfund-filter-modal input[name="mobile_location"]',
      );
      if (mobileLocationInput && mobileLocationInput.value) {
        params.set('location', mobileLocationInput.value);
      } else {
        params.delete('location');
      }

      const selectedSort = document.querySelector(
        '#growfund-filter-modal input[name="mobile_sort_temp"]:checked',
      );
      if (selectedSort && selectedSort.value) {
        params.set('sort', selectedSort.value);
      } else {
        params.delete('sort');
      }

      const mainSearchInput = document.querySelector('input[name="search"]');
      if (mainSearchInput && mainSearchInput.value) {
        params.set('search', mainSearchInput.value);
      } else {
        params.delete('search');
      }

      const mainForm = document.querySelector('.growfund-filters-form');
      if (mainForm) {
        const categoryDropdown = mainForm.querySelector('[data-dropdown-key="category"]');
        if (categoryDropdown && selectedCategory && selectedCategory.value) {
          categoryDropdown.dataset.value = selectedCategory.value;
        }

        const sortDropdown = mainForm.querySelector('[data-dropdown-key="sort"]');
        if (sortDropdown && selectedSort && selectedSort.value) {
          sortDropdown.dataset.value = selectedSort.value;
        }
      }

      if (filterModal) {
        filterModal.classList.remove('is-open');
        document.body.style.overflow = '';
      }

      if (window.performAjaxSearch) {
        window.performAjaxSearch(1);
      }
    });
  }
});

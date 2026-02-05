(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const mainUpdateContainer = document.querySelector(
      '.growfund-update-tab-content-card-container',
    );

    const detailWrapper = document.querySelector('#growfund_update_tab_content_detail');

    let currentNav = { next: 0, prev: 0 };

    if (detailWrapper) {
      detailWrapper.addEventListener('click', function (e) {
        const backBtn = e.target.closest('#growfund-update-detail-button');
        if (backBtn) {
          detailWrapper.classList.remove('is-open');
          if (mainUpdateContainer) mainUpdateContainer.classList.remove('is-hidden');
          return;
        }

        const nextBtn = e.target.closest('.growfund-update-detail-next-button');
        if (nextBtn && currentNav.next) {
          e.preventDefault();
          showUpdateDetail(currentNav.next);
          return;
        }

        const prevBtn = e.target.closest('.growfund-update-detail-prev-button');
        if (prevBtn && currentNav.prev) {
          e.preventDefault();
          showUpdateDetail(currentNav.prev);
          return;
        }
      });
    }
    function showUpdateDetail(updateId) {
      growfundAjaxClient
        .get('growfund_ajax_get_campaign_post_update_detail', {
          id: updateId,
        })
        .then((response) => {
          if (response.success && response.data?.html) {
            const detailWrapper = document.querySelector('#growfund_update_tab_content_detail');
            detailWrapper.classList.add('is-open');

            const listContainer = document.querySelector(
              '.growfund-update-tab-content-card-container',
            );
            if (listContainer) {
              listContainer.classList.add('is-hidden');
            }
            detailWrapper.innerHTML = response.data.html;
            detailWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });

            currentNav.next = response.data.data.next_id;
            currentNav.prev = response.data.data.prev_id;

            const nextBtn = detailWrapper.querySelector('.growfund-update-detail-next-button');
            const prevBtn = detailWrapper.querySelector('.growfund-update-detail-prev-button');

            if (nextBtn) {
              nextBtn.disabled = !currentNav.next;
              nextBtn.classList.toggle('is-disabled', !currentNav.next);
            }

            if (prevBtn) {
              prevBtn.disabled = !currentNav.prev;
              prevBtn.classList.toggle('is-disabled', !currentNav.prev);
            }

            const commentContainer = document.querySelector(
              '#growfund_update_detail_comment_container',
            );
            if (commentContainer) {
              const list = commentContainer.querySelector('.growfund-comment-content-list');
              if (list) {
                growfundFetchComments(list);
              }
            }
          } else {
            console.error('Server returned success but no HTML content.');
          }
        })
        .catch((error) => {
          console.error('AJAX Error:', error);
        });
    }

    if (mainUpdateContainer) {
      mainUpdateContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.growfund-button.growfund-update-card-read-more');

        if (btn) {
          e.preventDefault();
          const updateId = btn.id;
          showUpdateDetail(updateId);
        }
      });
    }

    function dispatchLoadMoreUpdate(updateList) {
      document.dispatchEvent(
        new CustomEvent('growfund:load-more-update', {
          detail: {
            updateList,
          },
        }),
      );
    }

    function handleUpdateLoadMoreBtnClick(event) {
      const btn = event.target;
      const updateContainer = btn.closest('.growfund-update-tab-content-card-container');
      if (updateContainer) {
        const list = updateContainer.querySelector('.growfund-update-tab-content-card-wrapper');
        if (list) {
          dispatchLoadMoreUpdate(list);
        }
      }
    }

    const loadMoreBtns = mainUpdateContainer.querySelectorAll('.growfund-update-load-more');
    loadMoreBtns.forEach((loadMoreBtn) => {
      loadMoreBtn.removeEventListener('click', handleUpdateLoadMoreBtnClick);
      loadMoreBtn.addEventListener('click', handleUpdateLoadMoreBtnClick);
    });
  });
})();

(function () {
  let isLoading = false;
  let isReplyLoading = false;

  function fetchComments(list) {
    const currentPage = parseInt(list.getAttribute('data-current-page') ?? 0);
    const hasMore = list.getAttribute('data-has-more') === 'true' ? true : false;

    const postId = parseInt(list.getAttribute('data-post-id') ?? 0);
    const commentType = list.getAttribute('data-comment-type') ?? 'comment';

    if (isLoading || !hasMore) return;

    if (growfundAjaxClient) {
      isLoading = true;

      growfundAjaxClient
        .get('growfund_ajax_get_comments', {
          post_id: postId,
          comment_type: commentType,
          parent_id: 0,
          page: currentPage + 1,
        })
        .then((response) => {
          if (response.success && response.data && response.data.html && response.data.data) {
            list.insertAdjacentHTML('beforeend', response.data.html);
            list.setAttribute('data-current-page', response.data.data.current_page);
            list.setAttribute('data-has-more', response.data.data.has_more);

            const listWrapper = list.closest('.growfund-comment-content-list-wrapper');

            if (listWrapper) {
              const loadMoreBtn = listWrapper.querySelector('.growfund-comment-load-more');

              if (loadMoreBtn) {
                if (response.data.data.has_more) {
                  loadMoreBtn.classList.remove('growfund-hidden');
                } else {
                  loadMoreBtn.classList.add('growfund-hidden');
                }
              }
            }

            reinitializeComments();
          }
        })
        .finally(() => {
          isLoading = false;
        });
    }
  }

  window.growfundFetchComments = fetchComments;

  function fetchReplies(replyTread) {
    const currentPage = parseInt(replyTread.getAttribute('data-current-page') ?? 0);
    const hasMore = replyTread.getAttribute('data-has-more') === 'true' ? true : false;

    const postId = parseInt(replyTread.getAttribute('data-post-id') ?? 0);
    const commentType = replyTread.getAttribute('data-comment-type') ?? 'comment';
    const parentId = parseInt(replyTread.getAttribute('data-parent') ?? 0);

    if (isReplyLoading || !hasMore || !parentId) return;

    if (growfundAjaxClient) {
      isReplyLoading = true;

      growfundAjaxClient
        .get('growfund_ajax_get_comments', {
          post_id: postId,
          comment_type: commentType,
          parent_id: parentId,
          page: currentPage + 1,
        })
        .then((response) => {
          if (response.success && response.data && response.data.html && response.data.data) {
            replyTread.insertAdjacentHTML('beforeend', response.data.html);
            replyTread.setAttribute('data-current-page', response.data.data.current_page);
            replyTread.setAttribute('data-has-more', response.data.data.has_more);

            const replyTreadWrapper = replyTread.closest(
              '.growfund-comment-replies-thread-wrapper',
            );
            const loadMoreBtn = replyTreadWrapper.querySelector(
              '.growfund-comment-reply-load-more',
            );

            if (loadMoreBtn) {
              if (response.data.data.has_more) {
                loadMoreBtn.classList.remove('growfund-hidden');
              } else {
                loadMoreBtn.classList.add('growfund-hidden');
              }
            }

            reinitializeComments();
          }
        })
        .finally(() => {
          isReplyLoading = false;
        });
    }
  }

  function fetchCommentById(commentId, postBtn) {
    if (growfundAjaxClient) {
      growfundAjaxClient
        .get('growfund_ajax_get_comment_by_id', { comment_id: commentId })
        .then((response) => {
          if (response.success && response.data && response.data.html) {
            const parentId = parseInt(response.data.data.comment_parent);

            if (!parentId) {
              const commentContainer = postBtn.closest('.growfund-comment-container');
              const list = commentContainer.querySelector('.growfund-comment-content-list');
              list.insertAdjacentHTML('afterbegin', response.data.html);
            } else {
              const list = postBtn.closest('.growfund-comment-content-list');
              const repliesThread = list.querySelector(
                `.growfund-comment-replies-thread[data-parent="${parentId}"]`,
              );
              repliesThread.insertAdjacentHTML('afterbegin', response.data.html);
            }
            reinitializeComments();
          }
        });
    }
  }

  function handleCommentLoadMoreBtnClick(event) {
    const btn = event.target;
    const commentContainer = btn.closest('.growfund-comment-container');
    if (commentContainer) {
      const list = commentContainer.querySelector('.growfund-comment-content-list');
      if (list) {
        fetchComments(list);
      }
    }
  }

  function calculateVLineHeight(vLine, commentItem, replyTreadWrapper) {
    let suggestedHeight = 0;
    let totalMarginHeight = 64;

    const loadMoreBtn = replyTreadWrapper.querySelector('.growfund-comment-reply-load-more');
    if (loadMoreBtn && !loadMoreBtn.classList.contains('growfund-hidden')) {
      totalMarginHeight = 86;
    }

    const replies = replyTreadWrapper.querySelectorAll('.growfund-is-reply');
    const lastReply = replies.length ? replies[replies.length - 1] : null;

    if (lastReply && lastReply.clientHeight) {
      suggestedHeight = commentItem.clientHeight - lastReply.clientHeight - totalMarginHeight;
    }

    if (suggestedHeight === 0 && replyTreadWrapper.clientHeight) {
      suggestedHeight =
        commentItem.clientHeight - replyTreadWrapper.clientHeight - totalMarginHeight;
    }

    vLine.style.height = `${suggestedHeight}px`;
  }

  function initializeVLineHeight() {
    const vLines = document.querySelectorAll('.growfund-v-line');
    vLines.forEach((vLine) => {
      const commentItem = vLine.closest('.growfund-comment-item');
      if (commentItem) {
        const replyTreadWrapper = commentItem.querySelector(
          '.growfund-comment-replies-thread-wrapper',
        );

        if (replyTreadWrapper) {
          calculateVLineHeight(vLine, commentItem, replyTreadWrapper);
        }
      }
    });
  }

  function handleReplyBtnClick(event) {
    const btn = event.target;
    const replyWrapper = btn.closest('.growfund-comment-reply-wrapper');

    if (replyWrapper) {
      const replyBox = replyWrapper.querySelector('.growfund-comment-reply-input-box');
      const replyTreadWrapper = replyWrapper.querySelector(
        '.growfund-comment-replies-thread-wrapper',
      );
      const commentItem = replyWrapper.closest('.growfund-comment-item');
      const vLine = commentItem.querySelector('.growfund-v-line');
      if (replyBox) {
        const isOpening = replyBox.classList.contains('growfund-hidden');
        replyBox.classList.toggle('growfund-hidden');
        if (vLine) {
          if (isOpening) {
            vLine.classList.remove('growfund-hidden');
          } else if (replyTreadWrapper && replyTreadWrapper.classList.contains('growfund-hidden')) {
            vLine.classList.add('growfund-hidden');
          }

          window.requestAnimationFrame(() => {
            calculateVLineHeight(vLine, commentItem, replyTreadWrapper);
          });
        }
      }

      if (replyTreadWrapper) {
        if (replyTreadWrapper.classList.contains('growfund-hidden')) {
          replyTreadWrapper.classList.remove('growfund-hidden');
        } else {
          replyTreadWrapper.classList.add('growfund-hidden');
        }
      }

      if (commentItem) {
        const vLine = commentItem.querySelector('.growfund-v-line');
        if (vLine) {
          calculateVLineHeight(vLine, commentItem, replyTreadWrapper);
        }
      }
    }
  }

  function handleCancelBtnClick(event) {
    const btn = event.currentTarget;
    const replyWrapper = btn.closest('.growfund-comment-reply-wrapper');
    const commentItem = btn.closest('.growfund-comment-item');

    if (replyWrapper) {
      const replyBox = replyWrapper.querySelector('.growfund-comment-reply-input-box');
      const replyThread = replyWrapper.querySelector('.growfund-comment-replies-thread-wrapper');

      if (replyBox) {
        replyBox.classList.add('growfund-hidden');
      }
      if (commentItem) {
        const vLine = commentItem.querySelector('.growfund-v-line');

        if (vLine) {
          const hasVisibleReplies =
            replyThread && !replyThread.classList.contains('growfund-hidden');

          if (!hasVisibleReplies) {
            vLine.classList.add('growfund-hidden');
          } else {
            calculateVLineHeight(vLine, commentItem, replyThread);
          }
        }
      }
    }
  }

  function handleReplyLoadMoreBtnClick(event) {
    const btn = event.target;
    const replyTreadWrapper = btn.closest('.growfund-comment-replies-thread-wrapper');
    if (replyTreadWrapper) {
      const replyTread = replyTreadWrapper.querySelector('.growfund-comment-replies-thread');
      if (replyTread) {
        fetchReplies(replyTread);
      }
    }
  }

  function growfundReinitializeCommentItem() {
    initializeVLineHeight();

    const replyBtns = document.querySelectorAll('.growfund-comment-reply-btn');
    const cancelBtns = document.querySelectorAll('.growfund-comment-cancel-btn');

    cancelBtns.forEach((cancelBtn) => {
      cancelBtn.removeEventListener('click', handleCancelBtnClick);
      cancelBtn.addEventListener('click', handleCancelBtnClick);
    });

    replyBtns.forEach((replyBtn) => {
      replyBtn.removeEventListener('click', handleReplyBtnClick);
      replyBtn.addEventListener('click', handleReplyBtnClick);
    });

    const replyLoadMoreBtns = document.querySelectorAll('.growfund-comment-reply-load-more');
    replyLoadMoreBtns.forEach((loadMoreBtn) => {
      loadMoreBtn.removeEventListener('click', handleReplyLoadMoreBtnClick);
      loadMoreBtn.addEventListener('click', handleReplyLoadMoreBtnClick);
    });
  }

  function reinitializeComments() {
    growfundReinitializeCommentItem();
    growfundInitializeCommentBoxes();
  }

  document.addEventListener('growfund:create-comment', function (e) {
    const { commentId, postBtn } = e.detail;

    if (postBtn && commentId) fetchCommentById(commentId, postBtn);
  });

  document.addEventListener('DOMContentLoaded', () => {
    growfundReinitializeCommentItem();

    const loadMoreBtns = document.querySelectorAll('.growfund-comment-load-more');
    loadMoreBtns.forEach((loadMoreBtn) => {
      loadMoreBtn.removeEventListener('click', handleCommentLoadMoreBtnClick);
      loadMoreBtn.addEventListener('click', handleCommentLoadMoreBtnClick);
    });
  });
})();

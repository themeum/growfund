(function () {
  function dispatchPostComment(data, commentId, postBtn) {
    document.dispatchEvent(
      new CustomEvent('growfund:create-comment', {
        detail: {
          ...data,
          commentId,
          postBtn,
        },
      }),
    );
  }

  function getCommentBoxValues(commentBox) {
    const data = {};

    const postId = commentBox.querySelector('input[name="post_id"]');
    const commentType = commentBox.querySelector('input[name="comment_type"]');
    const parentId = commentBox.querySelector('input[name="parent_id"]');
    const content = commentBox.querySelector('textarea[name="content"]');

    if (postId) data.post_id = postId.value;
    if (commentType) data.comment_type = commentType.value;
    if (parentId) data.parent_id = parentId.value;
    if (content) data.content = content.value.trim();

    return data;
  }
  function updateCharCount(textarea) {
    const commentBox = textarea.closest('.growfund-comment-input-box');
    if (!commentBox) return;
    const counter = commentBox.querySelector('.growfund-comment-input-char-count span');
    if (!counter) return;
    counter.textContent = textarea.value.length;
  }

  function clearTextAreaValue(commentBox) {
    const textarea = commentBox.querySelector('textarea[name="content"]');
    textarea.value = '';
    updateCharCount(textarea);
  }
  function handleTextareaInput(event) {
    updateCharCount(event.target);
  }

  function handleCancelBtnClickEventListener(event) {
    const cancelBtn = event.target;
    const commentBox = cancelBtn.closest('.growfund-comment-input-box');

    if (commentBox) {
      clearTextAreaValue(commentBox);
    }
  }

  function handlePostBtnClickEventListener(event) {
    const postButton = event.target;
    const commentBox = postButton.closest('.growfund-comment-input-box');
    const data = getCommentBoxValues(commentBox);
    if (data.content) {
      if (growfundAjaxClient) {
        growfundAjaxClient.post('growfund_ajax_create_comment', data).then((response) => {
          if (response.success && response.data && response.data.data) {
            const emptyDiv = document.querySelector('.growfund-empty-comment-div');
            if (emptyDiv) {
              emptyDiv.classList.add('growfund-hidden');
            }
            dispatchPostComment(data, response.data.data, event.target);
            clearTextAreaValue(commentBox);
          }
        });
      }

      return;
    }
  }

  function growfundInitializeCommentBoxes() {
    const commentBoxes = document.querySelectorAll('.growfund-comment-input-box');

    commentBoxes.forEach((commentBox) => {
      const textarea = commentBox.querySelector('.growfund-comment-textarea');
      if (textarea) {
        textarea.removeEventListener('input', handleTextareaInput);
        textarea.addEventListener('input', handleTextareaInput);
      }
      const cancelButton = commentBox.querySelector('.growfund-comment-cancel-btn');

      if (cancelButton) {
        cancelButton.removeEventListener('click', handleCancelBtnClickEventListener);
        cancelButton.addEventListener('click', handleCancelBtnClickEventListener);
      }

      const postButton = commentBox.querySelector('.growfund-comment-post-btn');

      if (postButton) {
        postButton.removeEventListener('click', handlePostBtnClickEventListener);
        postButton.addEventListener('click', handlePostBtnClickEventListener);
      }
    });
  }

  window.growfundInitializeCommentBoxes = growfundInitializeCommentBoxes;

  document.addEventListener('DOMContentLoaded', function () {
    growfundInitializeCommentBoxes();
  });
})();

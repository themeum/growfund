/**
 * Comments System JavaScript
 * Handles comment submission, replies, and AJAX functionality
 */

document.addEventListener('DOMContentLoaded', function () {
  // Initialize comment system
  initializeCommentSystem();
});

// Also initialize on window load as a fallback for dynamically loaded content
window.addEventListener('load', function () {
  if (typeof window.reinitializeCommentForms === 'function') {
    setTimeout(() => {
      window.reinitializeCommentForms();
    }, 200);
  }
});

function initializeCommentSystem() {
  // Clean up existing event listeners first
  cleanupCommentSystem();

  // Initialize main comment form
  initializeCommentForm();

  // Initialize reply functionality
  initializeReplySystem();

  // Initialize character count
  initializeCharacterCount();

  // Initialize comment form visibility for update comments
  initializeUpdateCommentFormVisibility();
}

function cleanupCommentSystem() {
  // Remove all existing event listeners by cloning and replacing elements
  const commentForms = document.querySelectorAll('.growfund-comment-form');
  commentForms.forEach((form, index) => {
    const textarea = form.querySelector('.growfund-comment-form__textarea');
    const submitBtn = form.querySelector('.growfund-comment-form__submit-btn');
    const cancelBtn = form.querySelector('.growfund-comment-form__cancel-btn');

    if (textarea) {
      const newTextarea = textarea.cloneNode(true);
      textarea.parentNode.replaceChild(newTextarea, textarea);
    }

    if (submitBtn) {
      const newSubmitBtn = submitBtn.cloneNode(true);
      submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
    }

    if (cancelBtn) {
      const newCancelBtn = cancelBtn.cloneNode(true);
      cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    }
  });
}

function initializeCommentForm() {
  const commentForms = document.querySelectorAll('.growfund-comment-form');

  // Initialize all comment forms, including those in hidden update detail views
  // This ensures they work when the update detail view becomes visible
  commentForms.forEach((form, index) => {
    initializeSingleCommentForm(form, index);
  });
}

function initializeSingleCommentForm(form, index = 0) {
  const textarea = form.querySelector('.growfund-comment-form__textarea');
  const submitBtn = form.querySelector('.growfund-comment-form__submit-btn');
  const cancelBtn = form.querySelector('.growfund-comment-form__cancel-btn');
  const errorDiv = form.querySelector('.growfund-comment-form__error');

  if (!textarea || !submitBtn) {
    // Don't return early - the form might be in a hidden container
    // We'll try to initialize it when it becomes visible
    return;
  }

  // Remove existing event listeners to prevent duplicates
  const newTextarea = textarea.cloneNode(true);
  const newSubmitBtn = submitBtn.cloneNode(true);
  const newCancelBtn = cancelBtn ? cancelBtn.cloneNode(true) : null;

  textarea.parentNode.replaceChild(newTextarea, textarea);
  submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
  if (cancelBtn) {
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
  }

  // Handle textarea input
  newTextarea.addEventListener('input', function () {
    const content = this.value.trim();
    newSubmitBtn.disabled = content.length === 0;
    updateCharacterCount(this);
  });

  // Handle submit button click
  newSubmitBtn.addEventListener('click', function () {
    submitComment(form);
  });

  // Handle cancel button click (for reply forms)
  if (newCancelBtn) {
    newCancelBtn.addEventListener('click', function () {
      removeReplyForm(form);
    });
  }

  // Handle Enter key (Ctrl+Enter to submit)
  newTextarea.addEventListener('keydown', function (e) {
    if (e.ctrlKey && e.key === 'Enter') {
      e.preventDefault();
      submitComment(form);
    }
  });

  // Mark form as initialized
  form.dataset.initialized = 'true';
}

function initializeReplySystem() {
  // Delegate event listener for reply buttons
  document.addEventListener('click', function (e) {
    if (e.target.closest('.growfund-comment__reply-btn')) {
      e.preventDefault();
      const replyBtn = e.target.closest('.growfund-comment__reply-btn');
      const commentId = replyBtn.dataset.commentId;
      const commentElement = replyBtn.closest('.growfund-comment');

      if (commentId && commentElement) {
        showReplyForm(commentElement, commentId);
      }
    }
  });
}

function initializeCharacterCount() {
  const textareas = document.querySelectorAll('.growfund-comment-form__textarea');

  textareas.forEach((textarea) => {
    updateCharacterCount(textarea);
  });
}

function updateCharacterCount(textarea) {
  // In the new structure, char count lives in the footer, sibling to actions
  const form = textarea.closest('.growfund-comment-form');
  let charCount = form ? form.querySelector('.growfund-comment-form__char-count') : null;
  // Fallback for legacy structure where it was inside the input wrapper
  if (!charCount) {
    charCount = textarea.parentElement.querySelector('.growfund-comment-form__char-count');
  }
  if (!charCount) return;

  const current = textarea.value.length;
  const max = textarea.maxLength || 1000;
  const currentSpan = charCount.querySelector('.growfund-comment-form__char-current');

  if (currentSpan) {
    currentSpan.textContent = current;

    // Add warning class if approaching limit
    if (current > max * 0.9) {
      currentSpan.style.color = 'var(--growfund-orange-6)';
    } else if (current > max * 0.8) {
      currentSpan.style.color = 'var(--growfund-yellow-6)';
    } else {
      currentSpan.style.color = 'var(--growfund-gray-13)';
    }
  }
}

function showReplyForm(commentElement, commentId) {
  // Check if this is a reply (nested comment)
  const isNestedReply = commentElement.classList.contains('growfund-comment--nested');

  // Don't allow replies to replies
  if (isNestedReply) {
    return;
  }

  // Remove any existing reply forms
  removeAllReplyForms();

  // Get comment author name
  const authorElement = commentElement.querySelector('.growfund-comment__author');
  const authorName = authorElement ? authorElement.textContent.trim() : '';

  // Create reply form
  const replyForm = createReplyForm(commentId, authorName);

  // Insert at the end of the thread (after any existing replies)
  const commentThread =
    commentElement.closest('.growfund-comment-thread') || commentElement.parentElement;
  if (commentThread) {
    const repliesContainer = commentThread.querySelector('.growfund-replies');
    if (repliesContainer) {
      repliesContainer.insertAdjacentElement('afterend', replyForm);
    } else {
      commentThread.appendChild(replyForm);
    }
  } else {
    // Fallback to previous behavior
    commentElement.appendChild(replyForm);
  }

  // Add smooth entrance animation
  replyForm.style.opacity = '0';
  replyForm.style.transform = 'translateY(-10px)';

  requestAnimationFrame(() => {
    replyForm.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    replyForm.style.opacity = '1';
    replyForm.style.transform = 'translateY(0)';
  });

  // Focus on the textarea
  const textarea = replyForm.querySelector('.growfund-comment-form__textarea');
  if (textarea) {
    textarea.focus();
  }
}

function createReplyForm(commentId, authorName) {
  const formDiv = document.createElement('div');
  formDiv.className = 'growfund-comment-form growfund-comment-form--reply';
  formDiv.dataset.parentId = commentId;

  // Get comment type from the parent comment's context
  const parentComment = document.querySelector(`[data-comment-id="${commentId}"]`);
  let commentType = 'comment'; // default

  if (parentComment) {
    // Find the comments list that contains this comment
    const commentsList = parentComment.closest('.growfund-comments__list');
    if (commentsList && commentsList.dataset.commentType) {
      commentType = commentsList.dataset.commentType;
    }
  }

  formDiv.dataset.commentType = commentType;

  // Get the correct post_id based on the comment type
  let postId = getCurrentPostId();

  // If this is an update comment, try to get the post_id from the update detail view
  if (commentType === 'update_comment') {
    const updateDetailView = parentComment?.closest('.growfund-update-detail-view');
    if (updateDetailView) {
      const updateForm = updateDetailView.querySelector('.growfund-comment-form');
      if (updateForm && updateForm.dataset.postId) {
        postId = updateForm.dataset.postId;
      }
    }
  }

  formDiv.dataset.postId = postId;

  // Show loading state
  const loadingDiv = document.createElement('div');
  loadingDiv.className = 'growfund-comment-form__loading';

  const spinnerDiv = document.createElement('div');
  spinnerDiv.className = 'growfund-spinner';

  const loadingSpan = document.createElement('span');
  loadingSpan.textContent = 'Loading reply form...';

  loadingDiv.appendChild(spinnerDiv);
  loadingDiv.appendChild(loadingSpan);

  // Clear existing content and add loading state
  formDiv.innerHTML = '';
  formDiv.appendChild(loadingDiv);

  // Fetch the reply form template from server
  const formData = new FormData();
  formData.append('action', 'growfund_get_reply_form_template');
  formData.append('comment_id', commentId);
  formData.append('post_id', getCurrentPostId());
  formData.append('author_name', authorName);
  formData.append('comment_type', commentType);
  formData.append('_wpnonce', growfund.ajax_nonce);

  fetch(growfund.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Replace loading state with actual form
        // Clear existing content
        formDiv.innerHTML = '';
        // Insert the server-provided HTML (assuming it's sanitized on server-side)
        formDiv.insertAdjacentHTML('beforeend', data.data.html);

        // Initialize the new form manually
        initializeSingleCommentForm(formDiv);

        // Focus on the textarea
        const textarea = formDiv.querySelector('.growfund-comment-form__textarea');
        if (textarea) {
          textarea.focus();
        }
      } else {
        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'growfund-comment-form__error';

        const errorP = document.createElement('p');
        errorP.className = 'growfund-comment-form__error-message';
        errorP.textContent = 'Failed to load reply form. Please try again.';

        errorDiv.appendChild(errorP);

        // Clear existing content and add error
        formDiv.innerHTML = '';
        formDiv.appendChild(errorDiv);
      }
    })
    .catch((error) => {
      // Create error message
      const errorDiv = document.createElement('div');
      errorDiv.className = 'growfund-comment-form__error';

      const errorP = document.createElement('p');
      errorP.className = 'growfund-comment-form__error-message';
      errorP.textContent = 'Failed to load reply form. Please try again.';

      errorDiv.appendChild(errorP);

      // Clear existing content and add error
      formDiv.innerHTML = '';
      formDiv.appendChild(errorDiv);
    });

  return formDiv;
}

function removeReplyForm(form) {
  if (form.classList.contains('growfund-comment-form--reply')) {
    // Add smooth transition before removing
    form.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
    form.style.opacity = '0';
    form.style.transform = 'translateY(-10px)';

    setTimeout(() => {
      form.remove();
    }, 200);
  }
}

function removeAllReplyForms() {
  const replyForms = document.querySelectorAll('.growfund-comment-form--reply');
  replyForms.forEach((form) => form.remove());
}

function submitComment(form) {
  const textarea = form.querySelector('.growfund-comment-form__textarea');
  const submitBtn = form.querySelector('.growfund-comment-form__submit-btn');
  const errorDiv = form.querySelector('.growfund-comment-form__error');

  if (!textarea || !submitBtn) {
    console.error('Comment form elements not found');
    return;
  }

  const content = textarea.value.trim();

  if (!content) {
    console.error('Comment content is empty');
    return;
  }

  // Get the correct post ID for this specific form
  const postId = form.dataset.postId;
  const commentType = form.dataset.commentType || 'comment';

  // Disable submit button and show loading state
  submitBtn.disabled = true;
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Posting...';

  // Hide any previous errors
  if (errorDiv) {
    errorDiv.style.display = 'none';
  }

  // Prepare form data
  const formData = new FormData();
  formData.append('action', 'growfund_create_comment');
  formData.append('post_id', postId);
  formData.append('comment_content', content);
  formData.append('parent_id', form.dataset.parentId || 0);
  formData.append('comment_type', commentType);
  formData.append('_wpnonce', growfund.ajax_nonce);

  // Submit via AJAX
  fetch(growfund.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        // Clear form
        textarea.value = '';
        updateCharacterCount(textarea);
        submitBtn.disabled = true;

        // Add new comment to the list
        addCommentToList(data.data);

        // Remove reply form if it's a reply
        if (form.classList.contains('growfund-comment-form--reply')) {
          removeReplyForm(form);
        }
      } else {
        let errorMsg = 'Failed to post comment';
        if (typeof data.data === 'string') {
          errorMsg = data.data;
        } else if (data.data && typeof data.data.message === 'string') {
          errorMsg = data.data.message;
        } else if (typeof data.message === 'string') {
          errorMsg = data.message;
        } else if (data.data) {
          try {
            errorMsg = JSON.stringify(data.data);
          } catch (e) {}
        }
        console.error('Comment submission failed:', errorMsg);
        throw new Error(errorMsg);
      }
    })
    .catch(async (error) => {
      console.error('Comment submission error:', error);
      let errorMessage = 'Failed to post comment';

      // If error is a Response object (from fetch), try to parse JSON
      if (error instanceof Response) {
        try {
          const errorData = await error.json();
          if (errorData && errorData.data && errorData.data.message) {
            errorMessage = errorData.data.message;
          } else if (errorData && errorData.message) {
            errorMessage = errorData.message;
          } else {
            errorMessage = JSON.stringify(errorData, null, 2);
          }
        } catch (e) {
          errorMessage = 'An unknown error occurred';
        }
      } else if (error && typeof error === 'object') {
        // Check for common nested error structures
        if (error.message) {
          errorMessage = error.message;
        } else if (error.data && error.data.message) {
          errorMessage = error.data.message;
        } else if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.response && error.response.data) {
          errorMessage = JSON.stringify(error.response.data, null, 2);
        } else if (error.response) {
          errorMessage = JSON.stringify(error.response, null, 2);
        } else {
          try {
            errorMessage = JSON.stringify(
              error,
              (key, value) => {
                if (typeof value === 'object' && value !== null) {
                  if (Array.isArray(value)) return value;
                  return Object.fromEntries(
                    Object.entries(value).filter(([k, v]) => typeof v !== 'object'),
                  );
                }
                return value;
              },
              2,
            );
          } catch (e) {
            errorMessage = String(error);
          }
        }
      } else if (typeof error === 'string') {
        errorMessage = error;
      }

      showErrorMessage(form, errorMessage);
    })
    .finally(() => {
      // Restore submit button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
}

function addCommentToList(commentData) {
  // Check if this is a reply
  if (commentData.parent_id && commentData.parent_id > 0) {
    addReplyToComment(commentData);
  } else {
    addTopLevelComment(commentData);
  }
}

function addTopLevelComment(commentData) {
  // Use the comment type from the response data
  const commentType = commentData.comment_type || 'comment';

  let commentsList = null;

  // Find the comments list that matches the current comment type
  const allCommentsLists = document.querySelectorAll(
    `.growfund-comments__list[data-comment-type="${commentType}"]`,
  );

  if (commentType === 'update_comment') {
    // For update comments, look for the comments list inside ANY visible update detail view
    const updateDetailViews = document.querySelectorAll('.growfund-update-detail-view');

    for (const updateDetailView of updateDetailViews) {
      const isVisible =
        updateDetailView.style.display !== 'none' && updateDetailView.offsetParent !== null;

      if (isVisible) {
        // Find the comments list inside this visible update detail view
        const commentsListInView = updateDetailView.querySelector(
          `.growfund-comments__list[data-comment-type="${commentType}"]`,
        );
        if (commentsListInView) {
          commentsList = commentsListInView;
          break;
        }
      }
    }

    // If no visible update detail view found, try to find any update comment list
    if (!commentsList) {
      for (const list of allCommentsLists) {
        const updateDetailView = list.closest('.growfund-update-detail-view');
        if (updateDetailView) {
          commentsList = list;
          break;
        }
      }
    }
  } else {
    // For campaign comments, look for comments list outside update detail views
    for (const list of allCommentsLists) {
      const updateDetailView = list.closest('.growfund-update-detail-view');
      if (!updateDetailView) {
        // This is a campaign comment list (not inside update detail view)
        commentsList = list;
        break;
      }
    }
  }

  if (!commentsList) {
    console.error('No comments list found for comment type:', commentType);
    return;
  }

  // Create comment HTML
  createCommentHTML(commentData)
    .then((commentHTML) => {
      // Insert at the top of the list
      commentsList.insertAdjacentHTML('afterbegin', commentHTML);

      // Hide the "No comments yet" message if it exists
      const noCommentsMessage = commentsList.querySelector('.growfund-no-comments');
      if (noCommentsMessage) {
        noCommentsMessage.style.display = 'none';
      }

      // Initialize the new comment
      const newComment = commentsList.querySelector('.growfund-comment-thread:first-child');

      if (newComment) {
        // Add animation class
        newComment.style.opacity = '0';
        newComment.style.transform = 'translateY(-10px)';

        setTimeout(() => {
          newComment.style.transition = 'all 0.3s ease';
          newComment.style.opacity = '1';
          newComment.style.transform = 'translateY(0)';
        }, 10);
      } else {
        console.error('New comment element not found after insertion');
      }
    })
    .catch((error) => {
      console.error('Error creating comment HTML:', error);
    });
}

function addReplyToComment(commentData) {
  // Find the parent comment
  const parentComment = document.querySelector(`[data-comment-id="${commentData.parent_id}"]`);
  if (!parentComment) {
    return;
  }

  // Check if the parent is a nested reply itself
  const isParentNested = parentComment.classList.contains('growfund-comment--nested');

  if (isParentNested) {
    // This is a reply to a reply - add it as a nested reply
    addNestedReply(commentData, parentComment);
  } else {
    // This is a reply to a top-level comment
    addTopLevelReply(commentData, parentComment);
  }
}

function addTopLevelReply(commentData, parentComment) {
  // Place replies inside the thread container, not inside the comment element
  const threadContainer =
    parentComment.closest('.growfund-comment-thread') || parentComment.parentElement;
  if (!threadContainer) return;

  // Find or create the replies container within the thread
  let repliesContainer = threadContainer.querySelector('.growfund-replies');
  if (!repliesContainer) {
    repliesContainer = document.createElement('div');
    repliesContainer.className = 'growfund-replies';
    threadContainer.appendChild(repliesContainer);
  }

  // Create reply HTML
  createCommentHTML(commentData, true)
    .then((replyHTML) => {
      // Add the reply to the replies container
      repliesContainer.insertAdjacentHTML('beforeend', replyHTML);

      // Add animation to the new reply
      const newReply = repliesContainer.querySelector('.growfund-comment:last-child');
      if (newReply) {
        newReply.style.opacity = '0';
        newReply.style.transform = 'translateX(-10px)';

        setTimeout(() => {
          newReply.style.transition = 'all 0.3s ease';
          newReply.style.opacity = '1';
          newReply.style.transform = 'translateX(0)';
        }, 10);
      }

      // Add has-replies class to the thread container if this is the first reply
      if (!threadContainer.classList.contains('has-replies')) {
        threadContainer.classList.add('has-replies');
      }

      // Ensure the parent comment's pseudo-element is visible after adding reply
      const parentCommentElement = threadContainer.querySelector('.growfund-comment');
      if (parentCommentElement) {
        requestAnimationFrame(() => {
          parentCommentElement.style.transition = 'z-index 0.3s ease';
          parentCommentElement.style.position = 'relative';
          parentCommentElement.style.zIndex = '10';
        });
      }

      // Ensure the last reply's pseudo-element is visible (for hiding the vertical line)
      const lastReply = repliesContainer.querySelector('.growfund-comment--nested:last-child');
      if (lastReply) {
        requestAnimationFrame(() => {
          lastReply.style.transition = 'z-index 0.3s ease';
          lastReply.style.position = 'relative';
          lastReply.style.zIndex = '15';
        });
      }
    })
    .catch((error) => {
      // Error handling
    });
}

function addNestedReply(commentData, parentReply) {
  // For nested replies, we need to create a nested structure
  // Find the replies container of the parent comment (grandparent)
  const grandparentComment = parentReply.closest('.growfund-comment-thread');
  if (!grandparentComment) {
    return;
  }

  // Find or create the replies container in the grandparent
  let repliesContainer = grandparentComment.querySelector('.growfund-replies');
  if (!repliesContainer) {
    repliesContainer = document.createElement('div');
    repliesContainer.className = 'growfund-replies';
    grandparentComment.appendChild(repliesContainer);
  }

  // Create a nested reply container for this level
  createCommentHTML(commentData, true)
    .then((nestedReplyHTML) => {
      // Add the nested reply after the parent reply
      const parentReplyElement = repliesContainer.querySelector(
        `[data-comment-id="${commentData.parent_id}"]`,
      );
      if (parentReplyElement) {
        parentReplyElement.insertAdjacentHTML('afterend', nestedReplyHTML);
      } else {
        // Fallback: add to the end of replies container
        repliesContainer.insertAdjacentHTML('beforeend', nestedReplyHTML);
      }

      // Add animation to the new nested reply
      const newNestedReply = repliesContainer.querySelector(
        `[data-comment-id="${commentData.id}"]`,
      );
      if (newNestedReply) {
        newNestedReply.style.opacity = '0';
        newNestedReply.style.transform = 'translateX(-10px)';

        setTimeout(() => {
          newNestedReply.style.transition = 'all 0.3s ease';
          newNestedReply.style.opacity = '1';
          newNestedReply.style.transform = 'translateX(0)';
        }, 10);
      }

      // Ensure the parent comment's pseudo-element is visible after adding nested reply
      const parentCommentElement = grandparentComment.querySelector('.growfund-comment');
      if (parentCommentElement) {
        requestAnimationFrame(() => {
          parentCommentElement.style.transition = 'z-index 0.3s ease';
          parentCommentElement.style.position = 'relative';
          parentCommentElement.style.zIndex = '10';
        });
      }

      // Ensure the last reply's pseudo-element is visible (for hiding the vertical line)
      const lastReply = repliesContainer.querySelector('.growfund-comment--nested:last-child');
      if (lastReply) {
        requestAnimationFrame(() => {
          lastReply.style.transition = 'z-index 0.3s ease';
          lastReply.style.position = 'relative';
          lastReply.style.zIndex = '15';
        });
      }
    })
    .catch((error) => {
      // Error handling
    });
}

function createCommentHTML(commentData, isReply = false) {
  // Fetch the comment template from server
  const formData = new FormData();
  formData.append('action', 'growfund_get_comment_template');

  const commentDataJson = JSON.stringify(commentData);
  formData.append('comment_data', commentDataJson);
  formData.append('is_reply', isReply ? '1' : '0');

  // Use the comment type from the comment data, not from the form
  const commentType = commentData.comment_type || 'comment';
  formData.append('comment_type', commentType);

  formData.append('_wpnonce', growfund.ajax_nonce);

  return fetch(growfund.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        return data.data.html;
      } else {
        console.error('Failed to get comment template:', data);
        throw new Error('Failed to get comment template');
      }
    })
    .catch((error) => {
      console.error('Error in createCommentHTML:', error);
      throw new Error('Failed to get comment template from server');
    });
}

function showErrorMessage(form, message) {
  const errorDiv = form.querySelector('.growfund-comment-form__error');
  const errorMessage = form.querySelector('.growfund-comment-form__error-message');

  if (errorDiv && errorMessage) {
    errorMessage.textContent = message;
    errorDiv.style.display = 'block';
  }
}

function getCurrentPostId() {
  // Try to get from the visible comment form
  const allCommentForms = document.querySelectorAll('.growfund-comment-form');
  let currentForm = null;

  // First, try to find a visible form in an update detail view (for update comments)
  for (const form of allCommentForms) {
    const updateDetailView = form.closest('.growfund-update-detail-view');
    const isVisible = form.offsetParent !== null;

    if (updateDetailView && isVisible) {
      // This is a visible form in an update detail view
      currentForm = form;
      break;
    }
  }

  // If no update form found, try to find any visible form
  if (!currentForm) {
    for (const form of allCommentForms) {
      if (form.offsetParent !== null) {
        currentForm = form;
        break;
      }
    }
  }

  // Fallback to first form if no visible form found
  if (!currentForm) {
    currentForm = document.querySelector('.growfund-comment-form');
  }

  if (currentForm && currentForm.dataset.postId) {
    const postId = currentForm.dataset.postId;
    return postId;
  }

  // Fallback to getting from URL or other methods
  const fallbackPostId = document.querySelector('[data-post-id]')?.dataset.postId || '';
  return fallbackPostId;
}

// Function to handle comment form visibility for update comments
window.initializeUpdateCommentFormVisibility = function () {
  // Function to check if update detail view is visible and show/hide comment form accordingly
  function updateCommentFormVisibility() {
    const updateDetailViews = document.querySelectorAll('.growfund-update-detail-view');
    const updateCommentForms = document.querySelectorAll('#comment-form-wrapper-update_comment');
    const allCommentForms = document.querySelectorAll('.growfund-comment-form');

    updateCommentForms.forEach((formWrapper) => {
      // Find the closest update detail view
      const updateDetailView = formWrapper.closest('.growfund-update-detail-view');

      if (updateDetailView) {
        const isVisible =
          updateDetailView.style.display !== 'none' && updateDetailView.offsetParent !== null;

        if (isVisible) {
          formWrapper.style.display = 'block';
        } else {
          formWrapper.style.display = 'none';
        }
      }
    });
  }

  // Function to initialize comment forms for a specific update detail view
  function initializeCommentFormsForUpdateView(updateDetailView) {
    const commentForms = updateDetailView.querySelectorAll('.growfund-comment-form');

    commentForms.forEach((form, index) => {
      // Check if form is already initialized by looking for event listeners
      const textarea = form.querySelector('.growfund-comment-form__textarea');
      const submitBtn = form.querySelector('.growfund-comment-form__submit-btn');

      if (textarea && submitBtn) {
        // Re-initialize the form to ensure it works properly
        initializeSingleCommentForm(form, index);
      }
    });
  }

  // Make the function available globally
  window.initializeCommentFormsForUpdateView = initializeCommentFormsForUpdateView;

  // Add a global function to reinitialize all comment forms
  window.reinitializeCommentForms = function () {
    const commentForms = document.querySelectorAll('.growfund-comment-form');
    commentForms.forEach((form, index) => {
      const textarea = form.querySelector('.growfund-comment-form__textarea');
      const submitBtn = form.querySelector('.growfund-comment-form__submit-btn');

      if (textarea && submitBtn) {
        // Check if form is already initialized by looking for data attribute
        if (!form.dataset.initialized) {
          initializeSingleCommentForm(form, index);
          form.dataset.initialized = 'true';
        }
      }
    });
  };

  // Initial check
  updateCommentFormVisibility();

  // Set up a mutation observer to watch for changes in display style
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
        updateCommentFormVisibility();

        // Check if any update detail views became visible
        const target = mutation.target;
        if (target.classList.contains('growfund-update-detail-view')) {
          const isVisible = target.style.display !== 'none' && target.offsetParent !== null;
          if (isVisible) {
            setTimeout(() => {
              if (typeof window.reinitializeCommentForms === 'function') {
                window.reinitializeCommentForms();
              }
            }, 100);
          }
        }
      }
    });
  });

  // Observe all update detail views
  const updateDetailViews = document.querySelectorAll('.growfund-update-detail-view');
  updateDetailViews.forEach((view) => {
    observer.observe(view, {
      attributes: true,
      attributeFilter: ['style'],
    });
  });

  // Also listen for custom events that might change visibility
  document.addEventListener('updateDetailViewShown', function (event) {
    updateCommentFormVisibility();

    // Reinitialize comment forms in the newly shown detail view
    const updateId = event.detail?.updateId;
    if (updateId) {
      const detailView = document.getElementById('update-detail-view-' + updateId);
      if (detailView && typeof window.initializeCommentFormsForUpdateView === 'function') {
        window.initializeCommentFormsForUpdateView(detailView);
      }
    }
  });
  document.addEventListener('updateDetailViewHidden', updateCommentFormVisibility);
};

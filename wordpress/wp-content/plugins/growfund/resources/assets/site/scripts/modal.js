/**
 * Receipt Modal JavaScript
 * Handles receipt modal functionality including opening/closing, form handling, and navigation
 */

document.addEventListener('DOMContentLoaded', () => {
  const modals = document.querySelectorAll('.growfund-modal');
  const closeButtons = document.querySelectorAll('.growfund-modal-close');
  const overlays = document.querySelectorAll('.growfund-modal .growfund-modal__overlay');

  if (!modals) {
    return;
  }

  /**
   * Open the modal
   */
  function openModal(e) {
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  /**
   * Close the modal
   */
  function closeModal(e) {
    e.target.closest('.growfund-modal').classList.add('is-closing');

    setTimeout(() => {
      e.target.closest('.growfund-modal').classList.remove('is-open', 'is-closing');
      document.body.style.overflow = '';
    }, 300);
  }

  if (closeButtons) {
    closeButtons.forEach((closeButton) => {
      closeButton.addEventListener('click', closeModal);
    });
  }

  if (overlays) {
    overlays.forEach((overlay) => {
      overlay.addEventListener('click', closeModal);
    });
  }
});

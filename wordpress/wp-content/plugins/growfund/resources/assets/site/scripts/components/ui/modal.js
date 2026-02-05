document.addEventListener('click', function (event) {
  const isCloseBtn = event.target.closest('.growfund-modal-close-button-icon');
  const isCancelBtn = event.target.closest('.growfund-modal-cancel-button');
  const isOverlay = event.target.classList.contains('growfund-modal-overlay');

  if (isCloseBtn || isCancelBtn || isOverlay) {
    const modal = event.target.closest('.growfund-modal');
    if (modal) {
      closeModal(modal);
    }
  }

  function openModal(modal) {
    modal.classList.add('is-active');
    document.body.classList.add('no-scroll');
  }

  function closeModal(modal) {
    modal.classList.remove('is-active');
    document.body.classList.remove('no-scroll');
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      const activeModal = document.querySelector('.growfund-modal.is-active');
      if (activeModal) {
        closeModal(activeModal);
      }
    }
  });
});

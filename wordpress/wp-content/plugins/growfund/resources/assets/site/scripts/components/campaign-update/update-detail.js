(function () {
  document.addEventListener('click', function (e) {
    const nextBtn = e.target.closest('.growfund-update-detail-next-button');

    if (nextBtn) {
      const currentItem = nextBtn.closest('.growfund-update-detail-item');
      const nextItem = currentItem ? currentItem.nextElementSibling : null;

      if (nextItem) {
        nextItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        console.warn('No next item found in the DOM.');
      }
      return;
    }

    const prevBtn = e.target.closest('.growfund-update-detail-prev-button');
    if (prevBtn) {
      const currentItem = prevBtn.closest('.growfund-update-detail-item');
      const prevItem = currentItem ? currentItem.previousElementSibling : null;

      if (prevItem) {
        prevItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        console.warn('No previous item found in the DOM.');
      }
      return;
    }
  });
})();

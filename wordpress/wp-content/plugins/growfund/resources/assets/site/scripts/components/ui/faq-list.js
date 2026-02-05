document.addEventListener('DOMContentLoaded', function () {
  const faqItems = document.querySelectorAll('.growfund-faq-list-item');

  faqItems.forEach((item) => {
    const quesBtns = item.querySelectorAll('.growfund-faq-list-question');
    quesBtns.forEach((btn) => {
      btn.addEventListener('click', function () {
        const isOpen = item.classList.contains('active');

        faqItems.forEach((otherItem) => {
          otherItem.classList.remove('active');
        });

        if (!isOpen) {
          item.classList.add('active');
        }
      });
    });
  });
});

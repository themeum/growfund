document.addEventListener('DOMContentLoaded', () => {
  document
    .querySelector('.growfund-admin-migration-banner__button--close')
    ?.addEventListener('click', () => {
      const banner = document.getElementById('growfund_admin_migration_banner');
      if (banner) {
        banner.style.transition = 'opacity 0.3s ease';
        banner.style.opacity = '0';
        setTimeout(() => (banner.style.display = 'none'), 300);
      }
    });
});

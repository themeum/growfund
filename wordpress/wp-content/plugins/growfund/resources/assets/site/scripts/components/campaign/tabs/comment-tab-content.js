(function () {
  document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('growfund:tab-trigger', function (e) {
      if (e.detail && e.detail.key === 'comments') {
        const commentContainer = document.querySelector('#growfund_campaign_comment_container');
        if (commentContainer) {
          const list = commentContainer.querySelector('.growfund-comment-content-list');
          if (list) {
            growfundFetchComments(list);
          }
        }
      }
    });
  });
})();

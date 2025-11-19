import { useCallback } from 'react';

const useManageWordpressLayout = () => {
  const getElements = () => {
    return {
      adminMenu: document.getElementById('adminmenumain'),
      adminBar: document.getElementById('wpadminbar'),
      content: document.getElementById('wpcontent'),
      toolbar: document.querySelector<HTMLElement>('.wp-toolbar'),
      fundraiserSidebar: document.getElementById('fundraiser-sidebar'),
      fundraiserContent: document.getElementById('fundraiser-content'),
    };
  };

  const showWordpressLayout = useCallback(() => {
    const { adminMenu, adminBar, content, toolbar, fundraiserSidebar, fundraiserContent } =
      getElements();

    if (adminMenu) adminMenu.style.display = 'block';
    if (adminBar) adminBar.style.display = 'block';
    if (content) content.style.marginLeft = '160px';
    if (toolbar) toolbar.style.paddingTop = '32px';
    if (fundraiserSidebar) fundraiserSidebar.style.display = 'block';
    if (fundraiserContent) fundraiserContent.style.marginLeft = '';
  }, []);

  const hideWordpressLayout = useCallback(() => {
    const { adminMenu, adminBar, content, toolbar, fundraiserSidebar, fundraiserContent } =
      getElements();

    if (adminMenu) adminMenu.style.display = 'none';
    if (adminBar) adminBar.style.display = 'none';
    if (content) content.style.marginLeft = '0';
    if (toolbar) toolbar.style.paddingTop = '0';
    if (fundraiserSidebar) fundraiserSidebar.style.display = 'none';
    if (fundraiserContent) fundraiserContent.style.marginLeft = '0';
  }, []);

  return { showWordpressLayout, hideWordpressLayout };
};

export { useManageWordpressLayout };

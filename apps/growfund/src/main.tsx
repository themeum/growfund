import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';

import { conditionalImport } from '@/lib/import.ts';
import { createQueryRegistry } from '@/lib/query-registry.ts';
import { createRegistry } from '@/lib/registry.ts';

import App from './App.tsx';

import './index.css';

createRegistry();
createQueryRegistry();

if (
  window.growfund.has_growfund_pro &&
  (process.env.VITE_APP_TYPE === 'pro' || process.env.NODE_ENV === 'development')
) {
  void (async () => {
    const { registerComponents, registerQuery, registerPages } = await conditionalImport<
      ['registerComponents', 'registerQuery', 'registerPages'],
      { registerComponents: () => void; registerQuery: () => void; registerPages: () => void }
    >('index', ['registerComponents', 'registerQuery', 'registerPages']);

    if (!registerComponents || !registerQuery || !registerPages) {
      return;
    }

    registerComponents();
    registerQuery();
    registerPages();
  })();
}

function initApp() {
  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  createRoot(document.getElementById('growfund-root')!).render(
    <StrictMode>
      <App />
    </StrictMode>,
  );
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initApp);
} else {
  initApp();
}

function resetActiveMenu(root: HTMLElement) {
  const activeMenus = root.querySelectorAll('& > ul > li.current');

  for (const menu of [...activeMenus]) {
    menu.classList.remove('current');
  }
}

function getLeadingRouteHash(hash: string) {
  const hashParts = hash.split('/');
  return hashParts.slice(0, 2).join('/');
}

function checkActiveSubmenu(root: HTMLElement) {
  const searchParams = new URLSearchParams(window.location.search);

  if (searchParams.has('page') && searchParams.get('page') === 'growfund') {
    const hash = getLeadingRouteHash(window.location.hash || '#');

    const currentUrl = `admin.php?page=growfund${hash}`;
    const menuItems = [...root.querySelectorAll('& > ul > li')];

    for (const menuItem of menuItems) {
      const link = menuItem.querySelector('& > a')?.getAttribute('href');
      if (link === currentUrl) {
        menuItem.classList.add('current');
      }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const growfundAdminMenu = document.getElementById('toplevel_page_growfund');
  if (!growfundAdminMenu) {
    return;
  }

  resetActiveMenu(growfundAdminMenu);
  checkActiveSubmenu(growfundAdminMenu);

  const menuItems = [
    ...growfundAdminMenu.querySelectorAll('& > ul > li:not(:has(.growfund-menu-separator))'),
  ];

  for (const menuItem of menuItems) {
    menuItem.addEventListener('click', (event) => {
      event.preventDefault();
      const url = (event.target as Element).closest('a')?.getAttribute('href');
      resetActiveMenu(growfundAdminMenu);
      menuItem.classList.add('current');
      if (url) {
        if ((event as MouseEvent).metaKey || (event as MouseEvent).ctrlKey) {
          window.open(url, '_blank');
        } else {
          window.location.href = url;
        }
      }
    });
  }
});

window.addEventListener('popstate', () => {
  const growfundAdminMenu = document.getElementById('toplevel_page_growfund');
  if (!growfundAdminMenu) {
    return;
  }

  resetActiveMenu(growfundAdminMenu);
  checkActiveSubmenu(growfundAdminMenu);
});

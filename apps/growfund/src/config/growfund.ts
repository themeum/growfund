import { Buffer } from 'buffer';

export const growfundConfig = window.growfund;

// Reset the post ID to 0 that is automatically set by wordpress.
// eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
if (window.wp.media?.view?.settings?.post) {
  window.wp.media.view.settings.post.id = 0;
}

window.Buffer = Buffer;

export const wordpress = window.wp;

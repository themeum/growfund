/**
 * Shim to use WordPress global wp.i18n in ES modules
 * This allows us to use import syntax while accessing the global
 */

const i18n =
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
  typeof window !== 'undefined' && window.wp?.i18n
    ? window.wp.i18n
    : import('../../../node_modules/@wordpress/i18n').then((mod) => mod);

export const { __, _x, _n, _nx, sprintf, setLocaleData, getLocaleData } = i18n as Required<
  NonNullable<typeof window.wp.i18n>
>;

export default i18n;

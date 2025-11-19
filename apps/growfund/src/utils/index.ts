import { sprintf } from '@wordpress/i18n';
import { toast } from 'sonner';

import { LIST_LIMIT } from '@/constants/list-limits';
import { type ErrorResponse } from '@/lib/api';
import { type Address } from '@/schemas/address';
import { getCountryByCode } from '@/utils/countries';

export function isDefined<T>(value: T | null | undefined): value is T {
  return value !== null && value !== undefined;
}

export function isObject(value: unknown): value is object {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

// eslint-disable-next-line @typescript-eslint/no-empty-function
export const noop = () => {};

export function getObjectKeys<T>(value: T) {
  if (!isDefined(value) || !isObject(value)) {
    return [] as (keyof T)[];
  }

  return Object.keys(value) as (keyof T)[];
}

export function getObjectValues<T extends object, K extends keyof T>(value: T) {
  return Object.values(value) as T[K][];
}

export function getObjectEntries<T extends object, K extends keyof T>(value: T) {
  return Object.entries(value) as [K, T[K]][];
}

// eslint-disable-next-line @typescript-eslint/no-unnecessary-type-parameters
export function parseJSON<T>(value: string): T {
  return JSON.parse(value) as T;
}

export function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
    const r = (Math.random() * 16) | 0,
      v = c == 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

export const range = (count: number) => Array.from(Array(count).keys());
export function trim(value: string, char?: string) {
  if (!isDefined(value)) return value;
  if (!isDefined(char)) return value.trim();
  return value.replace(new RegExp(`^${char}+|${char}+$`, 'g'), '');
}

export function generateRandomPassword() {
  return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
}

export function arrayUniqueBy<T extends object>(array: T[], key: keyof T): T[] {
  const unique = array.reduce<Record<string, T>>((result, current) => {
    const keyValue = String(current[key]);
    if (isDefined(result[keyValue])) {
      return result;
    }

    return { ...result, [keyValue]: current };
  }, {});

  return Object.values(unique);
}

export function isErrorResponse(error: unknown): error is ErrorResponse {
  return (
    isDefined(error) &&
    isObject(error) &&
    'success' in error &&
    'isAxiosError' in error &&
    error.isAxiosError === true
  );
}

export function assertIsDefined<T>(val: T, errorMsg: string): asserts val is NonNullable<T> {
  if (val === undefined || val === null) {
    throw new Error(errorMsg);
  }
}

export function toPaginatedQueryParams<T extends object>(params: T) {
  const defaults = {
    page: 1,
    per_page: LIST_LIMIT.DEFAULT,
  };

  return {
    ...defaults,
    ...params,
  } as T;
}

export function isNumeric(value: string): value is `${number}` {
  return !isNaN(Number(value));
}

export function createAcronym(user: { first_name?: string; last_name?: string }) {
  const firstName = (user.first_name ?? '').trim();
  const lastName = (user.last_name ?? '').trim();

  if (firstName && lastName) {
    return sprintf('%s%s', firstName.charAt(0), lastName.charAt(0)).toUpperCase();
  }

  if (firstName) {
    return firstName.slice(0, 2).toUpperCase();
  }

  return '';
}

export function emptyCell(count = 1) {
  return '—'.repeat(count);
}

export function displayAddress(address: Address | undefined | null) {
  if (!isDefined(address)) {
    return emptyCell(1);
  }

  return [
    address.address,
    address.address_2,
    address.state,
    address.city,
    getCountryByCode(address.country)?.name,
    address.zip_code,
  ]
    .filter(isDefined)
    .join(', ');
}

export async function copyToClipboard(content: string) {
  if (typeof navigator.clipboard !== 'undefined' && window.isSecureContext) {
    try {
      await navigator.clipboard.writeText(content);
    } catch (err) {
      toast.error(err instanceof Error ? err.message : 'Error copying into clipboard');
      return false;
    }
    return true;
  }

  const element = document.createElement('textarea');
  element.innerText = content;
  element.style.position = 'fixed';
  element.style.left = '-99999px';
  document.body.appendChild(element);

  element.focus();
  element.select();

  try {
    // eslint-disable-next-line @typescript-eslint/no-deprecated
    document.execCommand('copy');
  } catch (err) {
    toast.error(err instanceof Error ? err.message : 'Error coping into clipboard');
    return false;
  } finally {
    document.body.removeChild(element);
  }

  return true;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function debounce<T extends (...args: any[]) => any>(
  func: T,
  delay: number,
): (...args: Parameters<T>) => void {
  let timeoutId: NodeJS.Timeout | null = null;

  return (...args: Parameters<T>): void => {
    if (timeoutId !== null) {
      clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
      func(...args);
    }, delay);
  };
}

export function isDeepEqual(obj1: unknown, obj2: unknown): boolean {
  if (obj1 === obj2) {
    return true;
  }

  if (!isDefined(obj1) || !isDefined(obj2)) {
    return false;
  }

  if (typeof obj1 !== typeof obj2) {
    return false;
  }

  if (obj1 instanceof Date && obj2 instanceof Date) {
    return obj1.getTime() === obj2.getTime();
  }

  if (Array.isArray(obj1) && Array.isArray(obj2)) {
    if (obj1.length !== obj2.length) {
      return false;
    }
    for (let i = 0; i < obj1.length; i++) {
      if (!isDeepEqual(obj1[i], obj2[i])) {
        return false;
      }
    }
    return true;
  }

  if (typeof obj1 === 'object' && typeof obj2 === 'object') {
    const keys1 = Object.keys(obj1 as Record<string, unknown>);
    const keys2 = Object.keys(obj2 as Record<string, unknown>);

    if (keys1.length !== keys2.length) {
      return false;
    }

    for (const key of keys1) {
      if (!keys2.includes(key)) {
        return false;
      }
      if (
        !isDeepEqual((obj1 as Record<string, unknown>)[key], (obj2 as Record<string, unknown>)[key])
      ) {
        return false;
      }
    }
    return true;
  }

  return false;
}

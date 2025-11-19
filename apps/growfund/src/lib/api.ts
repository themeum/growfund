import { __ } from '@wordpress/i18n';
import axios, {
  type AxiosError,
  type AxiosRequestHeaders,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios';
import { format } from 'date-fns';

import { growfundConfig } from '@/config/growfund';
import { DATE_FORMATS } from '@/lib/date';
import { type Prettify } from '@/types';
import { getObjectKeys, isDefined, isObject } from '@/utils';
import { isMediaObject, isVideoObject } from '@/utils/media';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const apiClient = axios.create({
  baseURL: growfundConfig.rest_url_base,
});

function processPayload(data: unknown): unknown {
  if (!isDefined(data)) {
    return null;
  }

  if (data === null || ['boolean', 'number', 'string'].includes(typeof data)) {
    if (typeof data === 'string' && !data) {
      return null;
    }
    return data;
  }

  if (Array.isArray(data)) {
    return data.map(processPayload);
  }

  if (data instanceof Date) {
    return format(data, DATE_FORMATS.ATOM);
  }

  if (data instanceof File || data instanceof Blob || data instanceof FormData) {
    return data;
  }

  if (isMediaObject(data)) {
    if (isVideoObject(data)) {
      return {
        id: data.id,
        poster: isDefined(data.poster?.id) ? Number(data.poster.id) : null,
      };
    }
    return Number(data.id);
  }

  if (isObject(data)) {
    return getObjectKeys(data).reduce<Record<string, unknown>>((acc, key) => {
      acc[key] = processPayload(data[key]);
      return acc;
    }, {});
  }

  return data;
}

function prepare(config: InternalAxiosRequestConfig) {
  if (!isDefined(config.headers)) {
    config.headers = {} as AxiosRequestHeaders;
  }

  const { nonce } = growfundConfig;

  config.headers.Accept = 'application/json';
  config.headers['Content-Type'] = 'application/json';

  if (nonce) {
    config.headers['X-WP-Nonce'] = nonce;
  }

  if (config.data) {
    config.data = processPayload(config.data);
  }

  if (config.data instanceof FormData) {
    config.headers['Content-Type'] = 'multipart/form-data';
  }

  if (!isDefined(config.params)) {
    config.params = {} as Record<string, unknown>;
  }

  config.params = { ...config.params, _method: config.method } as Record<string, unknown>;

  return config;
}

apiClient.interceptors.request.use((config) => {
  return prepare(config);
});

export type ErrorResponse = Prettify<
  AxiosError & {
    success: false;
    message: string;
    errors: Record<string, string>;
  }
>;

type Response<T, D> = AxiosResponse<T, D> & {
  success: boolean;
  message: string;
  data: T;
};

apiClient.interceptors.response.use(
  (response) => {
    if (isObject(response) && 'data' in response) {
      return response.data as Response<Record<string, unknown> | boolean, unknown>;
    }
    return response as Response<Record<string, unknown> | boolean, unknown>;
  },
  (error: unknown) => {
    if (isDefined(error) && typeof error === 'object' && error !== null && 'response' in error) {
      const errorResponse = error.response as AxiosResponse<{
        success: boolean;
        message: string;
        errors: Record<string, string>;
      }>;
      const errorMessage = errorResponse.data.message || __('Something went wrong.', 'growfund');

      const errors = errorResponse.data.errors;
      const modifiedError = {
        ...error,
        success: false,
        message: errorMessage,
        errors: errors,
        isAxiosError: true,
      } as ErrorResponse;

      return Promise.reject(modifiedError as Error);
    }
  },
);

export { apiClient };

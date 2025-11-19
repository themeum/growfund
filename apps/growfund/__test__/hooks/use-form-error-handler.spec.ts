import { renderHook } from '@testing-library/react';
import { useForm } from 'react-hook-form';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { type ErrorResponse } from '@/lib/api';

// Mock WordPress i18n
vi.mock('@wordpress/i18n', () => ({
  __: (text: string) => text,
}));

// Mock utils
vi.mock('@/utils', () => ({
  getObjectEntries: (obj: Record<string, string>) => Object.entries(obj),
  isDefined: (value: unknown) => value !== undefined && value !== null,
  isErrorResponse: (error: unknown): error is ErrorResponse => {
    return typeof error === 'object' && error !== null && 'response' in error;
  },
  isObject: (value: unknown) => typeof value === 'object' && value !== null,
}));

describe('useFormErrorHandler', () => {
  const mockForm = {
    setError: vi.fn(),
    clearErrors: vi.fn(),
    formState: {
      errors: {
        email: { type: 'server', message: 'Invalid email' },
        password: { type: 'required', message: 'Password is required' },
      },
    },
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should return error handler functions', () => {
    const { result } = renderHook(() => useFormErrorHandler(mockForm as any));

    expect(result.current).toHaveProperty('mapServerErrors');
    expect(result.current).toHaveProperty('createErrorHandler');
    expect(result.current).toHaveProperty('clearServerErrors');
    expect(typeof result.current.mapServerErrors).toBe('function');
    expect(typeof result.current.createErrorHandler).toBe('function');
    expect(typeof result.current.clearServerErrors).toBe('function');
  });

  it('should handle null form gracefully', () => {
    const { result } = renderHook(() => useFormErrorHandler(null));

    expect(result.current).toHaveProperty('mapServerErrors');
    expect(result.current).toHaveProperty('createErrorHandler');
    expect(result.current).toHaveProperty('clearServerErrors');
  });

  describe('mapServerErrors', () => {
    it('should set form errors when errorResponse has errors', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));
      const errorResponse: ErrorResponse = {
        message: 'Validation failed',
        errors: {
          email: 'Invalid email format',
          password: 'Password too short',
        },
        response: { status: 422 } as any,
        isAxiosError: true,
        toJSON: () => ({}),
        name: 'AxiosError',
        success: false,
      };

      result.current.mapServerErrors(errorResponse);

      expect(mockForm.setError).toHaveBeenCalledWith('email', {
        type: 'server',
        message: 'Invalid email format',
      });
      expect(mockForm.setError).toHaveBeenCalledWith('password', {
        type: 'server',
        message: 'Password too short',
      });
    });

    it('should not set errors when errorResponse has no errors', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));
      const errorResponse: ErrorResponse = {
        message: 'Server error',
        response: { status: 500 } as any,
        isAxiosError: true,
        toJSON: () => ({}),
        name: 'AxiosError',
        success: false,
        errors: {},
      };

      result.current.mapServerErrors(errorResponse);

      expect(mockForm.setError).not.toHaveBeenCalled();
    });

    it('should not set errors when errorResponse.errors is not an object', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));
      const errorResponse: ErrorResponse = {
        message: 'Server error',
        errors: 'string error' as any,
        response: { status: 422 } as any,
        isAxiosError: true,
        toJSON: () => ({}),
        name: 'AxiosError',
        success: false,
      };

      result.current.mapServerErrors(errorResponse);

      expect(mockForm.setError).not.toHaveBeenCalled();
    });
  });

  describe('createErrorHandler', () => {
    it('should return error message for ErrorResponse with 400/422 status', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));
      const errorResponse: ErrorResponse = {
        message: 'Validation failed',
        errors: { email: 'Invalid email' },
        response: { status: 422 } as any,
        isAxiosError: true,
        toJSON: () => ({}),
        name: 'AxiosError',
        success: false,
      };

      const errorHandler = result.current.createErrorHandler();
      const message = errorHandler(errorResponse);

      expect(message).toBe('Validation failed');
      expect(mockForm.setError).toHaveBeenCalledWith('email', {
        type: 'server',
        message: 'Invalid email',
      });
    });

    it('should return error message for ErrorResponse without mapping errors for other status codes', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));
      const errorResponse: ErrorResponse = {
        message: 'Server error',
        errors: { email: 'Invalid email' },
        response: { status: 500 } as any,
        isAxiosError: true,
        toJSON: () => ({}),
        name: 'AxiosError',
        success: false,
      };

      const errorHandler = result.current.createErrorHandler();
      const message = errorHandler(errorResponse);

      expect(message).toBe('Server error');
      expect(mockForm.setError).not.toHaveBeenCalled();
    });

    it('should return default message for regular Error objects', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));
      const error = new Error('Network error');

      const errorHandler = result.current.createErrorHandler();
      const message = errorHandler(error);

      expect(message).toBe('An unknown error occurred');
      expect(mockForm.setError).not.toHaveBeenCalled();
    });
  });

  describe('clearServerErrors', () => {
    it('should clear only server errors from form', () => {
      const { result } = renderHook(() => useFormErrorHandler(mockForm as any));

      result.current.clearServerErrors();

      expect(mockForm.clearErrors).toHaveBeenCalledWith('email');
      expect(mockForm.clearErrors).not.toHaveBeenCalledWith('password');
    });

    it('should handle form with no server errors', () => {
      const formWithoutServerErrors = {
        ...mockForm,
        formState: {
          errors: {
            password: { type: 'required', message: 'Password is required' },
          },
        },
      };

      const { result } = renderHook(() => useFormErrorHandler(formWithoutServerErrors as any));

      result.current.clearServerErrors();

      expect(mockForm.clearErrors).not.toHaveBeenCalled();
    });

    it('should handle null form gracefully', () => {
      const { result } = renderHook(() => useFormErrorHandler(null));

      expect(() => result.current.clearServerErrors()).not.toThrow();
    });
  });

  describe('integration with react-hook-form', () => {
    it('should work with actual react-hook-form instance', () => {
      const TestComponent = () => {
        const form = useForm({
          defaultValues: { email: '', password: '' },
        });
        const { mapServerErrors, createErrorHandler, clearServerErrors } =
          useFormErrorHandler(form);

        return { form, mapServerErrors, createErrorHandler, clearServerErrors };
      };

      const { result } = renderHook(() => TestComponent());

      expect(result.current.form).toBeDefined();
      expect(result.current.mapServerErrors).toBeDefined();
      expect(result.current.createErrorHandler).toBeDefined();
      expect(result.current.clearServerErrors).toBeDefined();
    });
  });
});

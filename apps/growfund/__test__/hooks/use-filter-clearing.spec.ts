import { renderHook } from '@testing-library/react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useFilterClearing } from '@/hooks/use-filter-clearing';

// Mock utils
vi.mock('@/utils', () => ({
  getObjectKeys: (obj: Record<string, any>) => Object.keys(obj),
  isDefined: (value: unknown) => value !== undefined && value !== null,
}));

describe('useFilterClearing', () => {
  const createMockForm = <T extends FieldValues>(): UseFormReturn<T> =>
    ({
      reset: vi.fn(),
      formState: {
        errors: {} as any,
        isDirty: false,
        isLoading: false,
        isSubmitted: false,
        isSubmitting: false,
        isSubmitSuccessful: false,
        isValid: true,
        isValidating: false,
        touchedFields: {} as any,
        dirtyFields: {} as any,
        defaultValues: {} as any,
        submitCount: 0,
        disabled: false,
        validatingFields: {} as any,
        isReady: true,
      },
      control: {} as any,
      handleSubmit: vi.fn(),
      watch: vi.fn(),
      getValues: vi.fn(),
      setValue: vi.fn(),
      trigger: vi.fn(),
      clearErrors: vi.fn(),
      setError: vi.fn(),
      unregister: vi.fn(),
      register: vi.fn(),
      getFieldState: vi.fn(),
      setFocus: vi.fn(),
      resetField: vi.fn(),
      formStateRef: { current: {} },
      defaultValuesRef: { current: {} },
      getFieldArray: vi.fn(),
      append: vi.fn(),
      prepend: vi.fn(),
      insert: vi.fn(),
      swap: vi.fn(),
      move: vi.fn(),
      update: vi.fn(),
      replace: vi.fn(),
      remove: vi.fn(),
      subscribe: vi.fn(),
    } as UseFormReturn<T>);

  const mockSyncQueryParams = vi.fn();
  const mockTransformParamsToFormData = vi.fn((params) => params);

  const defaultParams = {
    search: 'test',
    category: 'tech',
    start_date: '2023-01-01',
    end_date: '2023-12-31',
    status: 'active',
  };

  beforeEach(() => {
    vi.clearAllMocks();
    mockTransformParamsToFormData.mockImplementation((params) => params);
  });

  it('should return filter clearing functions', () => {
    const form = createMockForm();
    const { result } = renderHook(() =>
      useFilterClearing({
        form,
        syncQueryParams: mockSyncQueryParams,
        params: defaultParams,
        transformParamsToFormData: mockTransformParamsToFormData,
      }),
    );

    expect(result.current).toHaveProperty('handleClearFilter');
    expect(result.current).toHaveProperty('handleClearAllFilters');
    expect(typeof result.current.handleClearFilter).toBe('function');
    expect(typeof result.current.handleClearAllFilters).toBe('function');
  });

  describe('handleClearFilter', () => {
    it('should clear single filter and update form', () => {
      const form = createMockForm();
      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: defaultParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearFilter('search');

      expect(mockSyncQueryParams).toHaveBeenCalledWith({ search: undefined });
      expect(mockTransformParamsToFormData).toHaveBeenCalledWith({
        ...defaultParams,
        search: undefined,
      });
      expect(form.reset).toHaveBeenCalledWith({
        search: '',
        category: 'tech',
        start_date: '2023-01-01',
        end_date: '2023-12-31',
        status: 'active',
      });
    });

    it('should clear date field and reset all date fields', () => {
      const form = createMockForm();
      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: defaultParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearFilter('start_date');

      expect(mockSyncQueryParams).toHaveBeenCalledWith({
        start_date: undefined,
        end_date: undefined,
      });
      expect(mockTransformParamsToFormData).toHaveBeenCalledWith({
        ...defaultParams,
        start_date: undefined,
        end_date: undefined,
      });
      expect(form.reset).toHaveBeenCalledWith({
        search: 'test',
        category: 'tech',
        start_date: '',
        end_date: '',
        status: 'active',
      });
    });

    it('should handle custom date fields', () => {
      const form = createMockForm();
      const customDateFields = ['created_at', 'updated_at'];
      const paramsWithCustomDates = {
        ...defaultParams,
        created_at: '2023-01-01',
        updated_at: '2023-12-31',
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: paramsWithCustomDates,
          dateFields: customDateFields,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearFilter('created_at');

      expect(mockSyncQueryParams).toHaveBeenCalledWith({
        created_at: undefined,
        updated_at: undefined,
      });
    });

    it('should handle undefined values in params', () => {
      const form = createMockForm();
      const paramsWithUndefined = {
        search: 'test',
        category: undefined,
        start_date: '2023-01-01',
        end_date: '2023-12-31',
        status: 'active',
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: paramsWithUndefined,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearFilter('search');

      expect(form.reset).toHaveBeenCalledWith({
        search: '',
        category: '',
        start_date: '2023-01-01',
        end_date: '2023-12-31',
        status: 'active',
      });
    });
  });

  describe('handleClearAllFilters', () => {
    it('should clear all filters and reset form', () => {
      const form = createMockForm();
      const clearedParams = {
        search: undefined,
        category: undefined,
        start_date: undefined,
        end_date: undefined,
        status: undefined,
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: defaultParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearAllFilters(clearedParams);

      expect(mockSyncQueryParams).toHaveBeenCalledWith(clearedParams);
      expect(mockTransformParamsToFormData).toHaveBeenCalledWith(clearedParams);
      expect(form.reset).toHaveBeenCalledWith({
        search: '',
        category: '',
        start_date: '',
        end_date: '',
        status: '',
      });
    });

    it('should handle partial clearing', () => {
      const form = createMockForm();
      const partialClearedParams = {
        search: undefined,
        category: 'tech', // Keep this one
        start_date: undefined,
        end_date: undefined,
        status: 'active', // Keep this one
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: defaultParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearAllFilters(partialClearedParams);

      expect(mockSyncQueryParams).toHaveBeenCalledWith(partialClearedParams);
      expect(form.reset).toHaveBeenCalledWith({
        search: '',
        category: 'tech',
        start_date: '',
        end_date: '',
        status: 'active',
      });
    });
  });

  describe('makeResetSafe function', () => {
    it('should convert undefined values to empty strings', () => {
      const form = createMockForm();
      const paramsWithUndefined = {
        search: undefined,
        category: 'tech',
        start_date: undefined,
        end_date: '2023-12-31',
        status: 'active',
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: paramsWithUndefined,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearAllFilters(paramsWithUndefined);

      expect(form.reset).toHaveBeenCalledWith({
        search: '',
        category: 'tech',
        start_date: '',
        end_date: '2023-12-31',
        status: 'active',
      });
    });

    it('should preserve defined values', () => {
      const form = createMockForm();
      const paramsWithValues = {
        search: 'test',
        category: 'tech',
        start_date: '2023-01-01',
        end_date: '2023-12-31',
        status: 'active',
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: paramsWithValues,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearAllFilters(paramsWithValues);

      expect(form.reset).toHaveBeenCalledWith({
        search: 'test',
        category: 'tech',
        start_date: '2023-01-01',
        end_date: '2023-12-31',
        status: 'active',
      });
    });
  });

  describe('integration with react-hook-form', () => {
    it('should work with mock form instance', () => {
      const form = createMockForm();

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: defaultParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      expect(result.current.handleClearFilter).toBeDefined();
      expect(result.current.handleClearAllFilters).toBeDefined();
    });
  });

  describe('edge cases', () => {
    it('should handle empty params object', () => {
      const form = createMockForm();
      const emptyParams = {};

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: emptyParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      expect(() => {
        result.current.handleClearFilter('search');
      }).not.toThrow();
    });

    it('should handle non-existent filter key', () => {
      const form = createMockForm();

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: defaultParams,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      expect(() => {
        result.current.handleClearFilter('non_existent' as any);
      }).not.toThrow();
    });

    it('should handle null values in params', () => {
      const form = createMockForm();
      const paramsWithNull = {
        search: null,
        category: 'tech',
        start_date: null,
        end_date: '2023-12-31',
        status: 'active',
      };

      const { result } = renderHook(() =>
        useFilterClearing({
          form,
          syncQueryParams: mockSyncQueryParams,
          params: paramsWithNull,
          transformParamsToFormData: mockTransformParamsToFormData,
        }),
      );

      result.current.handleClearAllFilters(paramsWithNull);

      expect(form.reset).toHaveBeenCalledWith({
        search: '',
        category: 'tech',
        start_date: '',
        end_date: '2023-12-31',
        status: 'active',
      });
    });
  });
});

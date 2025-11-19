import { act, renderHook } from '@testing-library/react';
import { parseAsBoolean, parseAsInteger, parseAsString, useQueryStates } from 'nuqs';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useQueryParamsStates } from '@/hooks/use-query-params-states';

// Mock nuqs
vi.mock('nuqs', () => ({
  useQueryStates: vi.fn(),
  parseAsString: vi.fn(),
  parseAsInteger: vi.fn(),
  parseAsBoolean: vi.fn(),
}));

// Mock utils
vi.mock('@/utils', () => ({
  getObjectEntries: (obj: Record<string, any>) => Object.entries(obj),
  isDefined: (value: unknown) => value !== undefined && value !== null,
}));

describe('useQueryParamsStates', () => {
  const mockKeyMap = {
    search: parseAsString,
    page: parseAsInteger,
    category: parseAsString,
    active: parseAsBoolean,
  };

  const mockParams = {
    search: 'test',
    page: 1,
    category: 'tech',
    active: true,
  };

  const mockSetParams = vi.fn();
  const mockUseQueryStates = vi.mocked(useQueryStates);

  beforeEach(() => {
    vi.clearAllMocks();
    mockUseQueryStates.mockReturnValue([mockParams, mockSetParams]);
  });

  it('should return query state management functions', () => {
    const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

    expect(result.current).toHaveProperty('params');
    expect(result.current).toHaveProperty('setParams');
    expect(result.current).toHaveProperty('readQueryParams');
    expect(result.current).toHaveProperty('syncQueryParams');
    expect(typeof result.current.readQueryParams).toBe('function');
    expect(typeof result.current.syncQueryParams).toBe('function');
  });

  it('should return params from useQueryStates', () => {
    const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

    expect(result.current.params).toEqual(mockParams);
    expect(mockUseQueryStates).toHaveBeenCalledWith(mockKeyMap);
  });

  it('should return setParams from useQueryStates', () => {
    const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

    expect(result.current.setParams).toBe(mockSetParams);
  });

  describe('readQueryParams', () => {
    it('should return params when no callback is provided', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

      const params = result.current.readQueryParams();

      expect(params).toEqual(mockParams);
    });

    it('should call callback with params and return result', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const mockCallback = vi.fn((params) => ({
        search: params.search?.toUpperCase(),
        page: params.page,
      }));

      const transformedParams = result.current.readQueryParams(mockCallback);

      expect(mockCallback).toHaveBeenCalledWith(mockParams);
      expect(transformedParams).toEqual({
        search: 'TEST',
        page: 1,
      });
    });

    it('should handle callback that returns different structure', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const mockCallback = vi.fn(() => ({
        filtered: true,
        count: 10,
      }));

      const result_params = result.current.readQueryParams(mockCallback);

      expect(result_params).toEqual({
        filtered: true,
        count: 10,
      });
    });
  });

  describe('syncQueryParams', () => {
    it('should sync query params with defined values', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const newValues = {
        search: 'new search',
        page: 2,
        category: 'science',
      };

      act(() => {
        result.current.syncQueryParams(newValues);
      });

      expect(mockSetParams).toHaveBeenCalledWith(newValues);
    });

    it('should convert falsy values to null', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const valuesWithFalsy = {
        search: '',
        page: 0,
        category: null,
        active: false,
      };

      act(() => {
        result.current.syncQueryParams(valuesWithFalsy);
      });

      expect(mockSetParams).toHaveBeenCalledWith({
        search: null,
        page: null,
        category: null,
        active: null,
      });
    });

    it('should handle undefined values', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const valuesWithUndefined = {
        search: 'test',
        page: undefined,
        category: 'tech',
      };

      act(() => {
        result.current.syncQueryParams(valuesWithUndefined);
      });

      expect(mockSetParams).toHaveBeenCalledWith({
        search: 'test',
        page: null,
        category: 'tech',
      });
    });

    it('should handle empty object', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

      act(() => {
        result.current.syncQueryParams({});
      });

      expect(mockSetParams).toHaveBeenCalledWith({});
    });

    it('should handle partial updates', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const partialUpdate = {
        search: 'updated search',
      };

      act(() => {
        result.current.syncQueryParams(partialUpdate);
      });

      expect(mockSetParams).toHaveBeenCalledWith({
        search: 'updated search',
      });
    });
  });

  describe('integration scenarios', () => {
    it('should handle complex key map', () => {
      const complexKeyMap = {
        search: parseAsString,
        filters: parseAsString,
        sort: parseAsString,
        page: parseAsInteger,
        limit: parseAsInteger,
        active: parseAsBoolean,
      };

      const complexParams = {
        search: 'test',
        filters: ['category:tech', 'status:active'],
        sort: { field: 'name', direction: 'asc' },
        page: 1,
        limit: 10,
        active: true,
      };

      mockUseQueryStates.mockReturnValue([complexParams, mockSetParams]);

      const { result } = renderHook(() => useQueryParamsStates(complexKeyMap));

      expect(result.current.params).toEqual(complexParams);

      act(() => {
        result.current.syncQueryParams({
          search: 'new search',
          page: 2,
        });
      });

      expect(mockSetParams).toHaveBeenCalledWith({
        search: 'new search',
        page: 2,
      });
    });

    it('should handle callback that filters params', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const filterCallback = vi.fn((params) => {
        const filtered: Record<string, any> = {};
        if (params.search) {
          filtered.search = params.search;
        }
        if (params.page && params.page > 1) {
          filtered.page = params.page;
        }
        return filtered;
      });

      const filteredParams = result.current.readQueryParams(filterCallback);

      expect(filteredParams).toEqual({
        search: 'test',
      });
    });
  });

  describe('edge cases', () => {
    it('should handle empty key map', () => {
      const emptyKeyMap = {};
      const emptyParams = {};

      mockUseQueryStates.mockReturnValue([emptyParams, mockSetParams]);

      const { result } = renderHook(() => useQueryParamsStates(emptyKeyMap));

      expect(result.current.params).toEqual({});
      expect(result.current.readQueryParams()).toEqual({});
    });

    it('should handle null/undefined params', () => {
      const nullParams = null as any;

      mockUseQueryStates.mockReturnValue([nullParams, mockSetParams]);

      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

      expect(result.current.params).toBeNull();
      expect(result.current.readQueryParams()).toBeNull();
    });

    it('should handle callback that throws error', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));
      const errorCallback = vi.fn(() => {
        throw new Error('Callback error');
      });

      expect(() => {
        result.current.readQueryParams(errorCallback);
      }).toThrow('Callback error');
    });

    it('should handle syncQueryParams with non-object values', () => {
      const { result } = renderHook(() => useQueryParamsStates(mockKeyMap));

      act(() => {
        result.current.syncQueryParams({
          search: 'test',
          page: 'invalid' as any,
        });
      });

      expect(mockSetParams).toHaveBeenCalledWith({
        search: 'test',
        page: 'invalid',
      });
    });
  });

  describe('memoization', () => {
    it('should memoize readQueryParams callback', () => {
      const { result, rerender } = renderHook(() => useQueryParamsStates(mockKeyMap));

      const callback1 = result.current.readQueryParams;
      rerender();
      const callback2 = result.current.readQueryParams;

      // Should be the same function reference due to useCallback
      expect(callback1).toBe(callback2);
    });

    it('should memoize syncQueryParams callback', () => {
      const { result, rerender } = renderHook(() => useQueryParamsStates(mockKeyMap));

      const callback1 = result.current.syncQueryParams;
      rerender();
      const callback2 = result.current.syncQueryParams;

      // Should be the same function reference due to useCallback
      expect(callback1).toBe(callback2);
    });
  });
});

import { act, renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { useDebounce } from '@/hooks/use-debounce';

describe('useDebounce', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('should return initial value immediately', () => {
    const { result } = renderHook(() => useDebounce('initial', 300));

    expect(result.current).toBe('initial');
  });

  it('should debounce value changes', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 300 },
    });

    expect(result.current).toBe('initial');

    // Change value
    rerender({ value: 'updated', delay: 300 });

    // Value should not change immediately
    expect(result.current).toBe('initial');

    // Fast-forward time by 299ms (just before delay)
    act(() => {
      vi.advanceTimersByTime(299);
    });

    expect(result.current).toBe('initial');

    // Fast-forward time by 1ms more (total 300ms)
    act(() => {
      vi.advanceTimersByTime(1);
    });

    expect(result.current).toBe('updated');
  });

  it('should use default delay of 300ms', () => {
    const { result, rerender } = renderHook(({ value }) => useDebounce(value), {
      initialProps: { value: 'initial' },
    });

    expect(result.current).toBe('initial');

    rerender({ value: 'updated' });

    act(() => {
      vi.advanceTimersByTime(299);
    });

    expect(result.current).toBe('initial');

    act(() => {
      vi.advanceTimersByTime(1);
    });

    expect(result.current).toBe('updated');
  });

  it('should handle custom delay', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 500 },
    });

    expect(result.current).toBe('initial');

    rerender({ value: 'updated', delay: 500 });

    act(() => {
      vi.advanceTimersByTime(499);
    });

    expect(result.current).toBe('initial');

    act(() => {
      vi.advanceTimersByTime(1);
    });

    expect(result.current).toBe('updated');
  });

  it('should cancel previous timer when value changes rapidly', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 300 },
    });

    expect(result.current).toBe('initial');

    // Rapid value changes
    rerender({ value: 'first', delay: 300 });
    rerender({ value: 'second', delay: 300 });
    rerender({ value: 'third', delay: 300 });

    // Value should still be initial
    expect(result.current).toBe('initial');

    // Fast-forward time by 300ms
    act(() => {
      vi.advanceTimersByTime(300);
    });

    // Should only have the last value
    expect(result.current).toBe('third');
  });

  it('should handle delay changes', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 300 },
    });

    expect(result.current).toBe('initial');

    // Change both value and delay
    rerender({ value: 'updated', delay: 500 });

    act(() => {
      vi.advanceTimersByTime(300);
    });

    // Should not update yet (new delay is 500ms)
    expect(result.current).toBe('initial');

    act(() => {
      vi.advanceTimersByTime(200);
    });

    // Should update now (total 500ms)
    expect(result.current).toBe('updated');
  });

  it('should handle zero delay', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 0 },
    });

    expect(result.current).toBe('initial');

    rerender({ value: 'updated', delay: 0 });

    // With zero delay, should update immediately
    act(() => {
      vi.advanceTimersByTime(0);
    });

    expect(result.current).toBe('updated');
  });

  it('should handle different data types', () => {
    // Test with number
    const { result: numberResult, rerender: numberRerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      {
        initialProps: { value: 0, delay: 100 },
      },
    );

    expect(numberResult.current).toBe(0);

    numberRerender({ value: 42, delay: 100 });

    act(() => {
      vi.advanceTimersByTime(100);
    });

    expect(numberResult.current).toBe(42);

    // Test with boolean
    const { result: booleanResult, rerender: booleanRerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      {
        initialProps: { value: false, delay: 100 },
      },
    );

    expect(booleanResult.current).toBe(false);

    booleanRerender({ value: true, delay: 100 });

    act(() => {
      vi.advanceTimersByTime(100);
    });

    expect(booleanResult.current).toBe(true);

    // Test with object
    const { result: objectResult, rerender: objectRerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      {
        initialProps: { value: { name: 'initial' }, delay: 100 },
      },
    );

    expect(objectResult.current).toEqual({ name: 'initial' });

    objectRerender({ value: { name: 'updated' }, delay: 100 });

    act(() => {
      vi.advanceTimersByTime(100);
    });

    expect(objectResult.current).toEqual({ name: 'updated' });
  });

  it('should handle array values', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: [1, 2, 3], delay: 100 },
    });

    expect(result.current).toEqual([1, 2, 3]);

    rerender({ value: [4, 5, 6], delay: 100 });

    act(() => {
      vi.advanceTimersByTime(100);
    });

    expect(result.current).toEqual([4, 5, 6]);
  });

  it('should handle null values', () => {
    const { result, rerender } = renderHook(
      ({ value, delay }: { value: null; delay: number }) => useDebounce(value, delay),
      {
        initialProps: { value: null, delay: 100 },
      },
    );

    expect(result.current).toBe(null);

    rerender({ value: null, delay: 100 });

    act(() => {
      vi.advanceTimersByTime(100);
    });

    expect(result.current).toBe(null);
  });

  it('should handle undefined values', () => {
    const { result, rerender } = renderHook(
      ({ value, delay }: { value: undefined; delay: number }) => useDebounce(value, delay),
      {
        initialProps: { value: undefined, delay: 100 },
      },
    );

    expect(result.current).toBe(undefined);

    rerender({ value: undefined, delay: 100 });

    act(() => {
      vi.advanceTimersByTime(100);
    });

    expect(result.current).toBe(undefined);
  });

  it('should clean up timer on unmount', () => {
    const clearTimeoutSpy = vi.spyOn(global, 'clearTimeout');

    const { unmount, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 300 },
    });

    rerender({ value: 'updated', delay: 300 });

    unmount();

    expect(clearTimeoutSpy).toHaveBeenCalled();
  });

  it('should handle multiple rapid changes correctly', () => {
    const { result, rerender } = renderHook(({ value, delay }) => useDebounce(value, delay), {
      initialProps: { value: 'initial', delay: 200 },
    });

    expect(result.current).toBe('initial');

    // Make multiple rapid changes
    const values = ['first', 'second', 'third', 'fourth', 'final'];

    values.forEach((value) => {
      rerender({ value, delay: 200 });
    });

    // Should still be initial
    expect(result.current).toBe('initial');

    // Fast-forward time
    act(() => {
      vi.advanceTimersByTime(200);
    });

    // Should only have the final value
    expect(result.current).toBe('final');
  });
});

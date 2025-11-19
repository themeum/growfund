import { act, renderHook } from '@testing-library/react';
import { useBlocker } from 'react-router';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';

// Mock WordPress i18n
vi.mock('@wordpress/i18n', () => ({
  __: (text: string) => text,
}));

// Mock react-router
vi.mock('react-router', () => ({
  useBlocker: vi.fn(),
}));

// Mock consent dialog context
const mockOpenDialog = vi.fn();
vi.mock('@/features/campaigns/contexts/consent-dialog-context', () => ({
  useConsentDialog: () => ({
    openDialog: mockOpenDialog,
  }),
}));

describe('useRouteBlockerGuard', () => {
  const mockBlocker = {
    state: 'idle' as const,
    proceed: vi.fn(),
    reset: vi.fn(),
    location: { pathname: '/current' },
  };

  const originalAddEventListener = window.addEventListener;
  const originalRemoveEventListener = window.removeEventListener;
  let eventListeners: { [key: string]: Function[] } = {};
  const mockUseBlocker = vi.mocked(useBlocker);

  beforeEach(() => {
    vi.clearAllMocks();
    eventListeners = {}; // Reset event listeners

    // Mock window event listeners
    window.addEventListener = vi.fn(
      (event: string, listener: EventListenerOrEventListenerObject) => {
        if (!eventListeners[event]) {
          eventListeners[event] = [];
        }
        eventListeners[event].push(listener as Function);
      },
    ) as any;

    window.removeEventListener = vi.fn(
      (event: string, listener: EventListenerOrEventListenerObject) => {
        if (eventListeners[event]) {
          const index = eventListeners[event].indexOf(listener as Function);
          if (index > -1) {
            eventListeners[event].splice(index, 1);
          }
        }
      },
    ) as any;

    mockUseBlocker.mockReturnValue(mockBlocker as any);
  });

  afterEach(() => {
    window.addEventListener = originalAddEventListener;
    window.removeEventListener = originalRemoveEventListener;
    eventListeners = {};
  });

  it('should not block navigation when form is not dirty', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: false }));

    expect(mockUseBlocker).toHaveBeenCalledWith(expect.any(Function));

    // Test the blocker function
    const blockerFunction = mockUseBlocker.mock.calls[0][0] as any;
    const shouldBlock = blockerFunction({
      currentLocation: { pathname: '/current' },
      nextLocation: { pathname: '/next' },
    });

    expect(shouldBlock).toBe(false);
  });

  it('should block navigation when form is dirty and pathname changes', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    const blockerFunction = mockUseBlocker.mock.calls[0][0] as any;
    const shouldBlock = blockerFunction({
      currentLocation: { pathname: '/current' },
      nextLocation: { pathname: '/next' },
    });

    expect(shouldBlock).toBe(true);
  });

  it('should not block navigation when form is dirty but pathname is the same', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    const blockerFunction = mockUseBlocker.mock.calls[0][0] as any;
    const shouldBlock = blockerFunction({
      currentLocation: { pathname: '/current' },
      nextLocation: { pathname: '/current' },
    });

    expect(shouldBlock).toBe(false);
  });

  it('should open dialog when blocker state is blocked', () => {
    const blockedBlocker = {
      ...mockBlocker,
      state: 'blocked' as const,
    };
    mockUseBlocker.mockReturnValue(blockedBlocker as any);

    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    expect(mockOpenDialog).toHaveBeenCalledWith({
      title: 'Unsaved Changes',
      content: 'You have unsaved changes. Are you sure you want to leave without saving?',
      confirmText: 'Leave anyway',
      confirmButtonVariant: 'destructive',
      declineText: 'Stay',
      onConfirm: expect.any(Function),
      onDecline: expect.any(Function),
    });
  });

  it('should not open dialog when blocker state is not blocked', () => {
    const idleBlocker = {
      ...mockBlocker,
      state: 'idle' as const,
    };
    mockUseBlocker.mockReturnValue(idleBlocker as any);

    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    expect(mockOpenDialog).not.toHaveBeenCalled();
  });

  it('should proceed and close dialog when user confirms', () => {
    const blockedBlocker = {
      ...mockBlocker,
      state: 'blocked' as const,
    };
    mockUseBlocker.mockReturnValue(blockedBlocker as any);

    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    const dialogConfig = mockOpenDialog.mock.calls[0][0];
    const mockCloseDialog = vi.fn();

    act(() => {
      dialogConfig.onConfirm(mockCloseDialog);
    });

    expect(blockedBlocker.proceed).toHaveBeenCalled();
    expect(mockCloseDialog).toHaveBeenCalled();
  });

  it('should reset blocker when user declines', () => {
    const blockedBlocker = {
      ...mockBlocker,
      state: 'blocked' as const,
    };
    mockUseBlocker.mockReturnValue(blockedBlocker as any);

    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    const dialogConfig = mockOpenDialog.mock.calls[0][0];

    act(() => {
      dialogConfig.onDecline();
    });

    expect(blockedBlocker.reset).toHaveBeenCalled();
  });

  it('should add beforeunload event listener when form is dirty', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    expect(window.addEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  it('should add beforeunload event listener even when form is not dirty', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: false }));

    expect(window.addEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  it('should prevent default and return message on beforeunload when form is dirty', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    const beforeunloadHandler = eventListeners['beforeunload'][0];
    const mockEvent = {
      preventDefault: vi.fn(),
    };

    const result = beforeunloadHandler(mockEvent);

    expect(mockEvent.preventDefault).toHaveBeenCalled();
    expect(result).toBe('You have unsaved changes. Are you sure you want to leave?');
  });

  it('should not prevent default on beforeunload when form is not dirty', () => {
    renderHook(() => useRouteBlockerGuard({ isDirty: false }));

    const beforeunloadHandler = eventListeners['beforeunload'][0];
    const mockEvent = {
      preventDefault: vi.fn(),
    };

    const result = beforeunloadHandler(mockEvent);

    expect(mockEvent.preventDefault).not.toHaveBeenCalled();
    expect(result).toBeUndefined();
  });

  it('should remove beforeunload event listener on cleanup', () => {
    const { unmount } = renderHook(() => useRouteBlockerGuard({ isDirty: true }));

    unmount();

    expect(window.removeEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  it('should handle isDirty state changes', () => {
    const { rerender } = renderHook(({ isDirty }) => useRouteBlockerGuard({ isDirty }), {
      initialProps: { isDirty: false },
    });

    // Initially should have beforeunload listener
    expect(window.addEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function));

    // Change to dirty
    rerender({ isDirty: true });

    // Should still have beforeunload listener
    expect(window.addEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function));

    // Change back to not dirty
    rerender({ isDirty: false });

    // Should still have beforeunload listener (it's always added)
    expect(window.addEventListener).toHaveBeenCalledWith('beforeunload', expect.any(Function));
  });

  it('should handle blocker state changes', () => {
    const { rerender } = renderHook(({ isDirty }) => useRouteBlockerGuard({ isDirty }), {
      initialProps: { isDirty: true },
    });

    // Initially blocked
    const blockedBlocker = {
      ...mockBlocker,
      state: 'blocked' as const,
    };
    mockUseBlocker.mockReturnValue(blockedBlocker as any);

    rerender({ isDirty: true });

    expect(mockOpenDialog).toHaveBeenCalled();

    // Change to idle
    const idleBlocker = {
      ...mockBlocker,
      state: 'idle' as const,
    };
    mockUseBlocker.mockReturnValue(idleBlocker as any);

    rerender({ isDirty: true });

    // Should not call openDialog again
    expect(mockOpenDialog).toHaveBeenCalledTimes(1);
  });

  describe('edge cases', () => {
    it('should handle missing blocker methods gracefully', () => {
      const incompleteBlocker = {
        state: 'blocked' as const,
        proceed: undefined,
        reset: undefined,
        location: { pathname: '/current' },
      };
      mockUseBlocker.mockReturnValue(incompleteBlocker as any);

      expect(() => {
        renderHook(() => useRouteBlockerGuard({ isDirty: true }));
      }).not.toThrow();
    });

    it('should handle beforeunload event without preventDefault method', () => {
      renderHook(() => useRouteBlockerGuard({ isDirty: true }));

      const beforeunloadHandler = eventListeners['beforeunload'][0];
      const mockEvent = {}; // No preventDefault method

      expect(() => {
        beforeunloadHandler(mockEvent);
      }).toThrow('event.preventDefault is not a function');
    });

    it('should handle rapid isDirty changes', () => {
      const { rerender } = renderHook(({ isDirty }) => useRouteBlockerGuard({ isDirty }), {
        initialProps: { isDirty: false },
      });

      // Rapid changes
      rerender({ isDirty: true });
      rerender({ isDirty: false });
      rerender({ isDirty: true });
      rerender({ isDirty: false });

      // Should not throw
      expect(() => {
        rerender({ isDirty: true });
      }).not.toThrow();
    });
  });
});

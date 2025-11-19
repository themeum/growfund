import { act, renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { useDialogCloseMiddleware, useWordpressMedia } from '@/hooks/use-wp-media';
import { type AcceptedMediaTypes } from '@/types/media';

// Mock WordPress i18n
vi.mock('@wordpress/i18n', () => ({
  __: (text: string) => text,
}));

// Mock growfund config
vi.mock('@/config/growfund', () => ({
  wordpress: {
    media: vi.fn(),
  },
}));

// Mock utils
vi.mock('@/utils', () => ({
  isDefined: (value: unknown) => value !== undefined && value !== null,
}));

describe('useWordpressMedia', () => {
  const mockMediaInstance = {
    on: vi.fn(),
    off: vi.fn(),
    open: vi.fn(),
    state: vi.fn(),
  };

  const originalDispatchEvent = window.dispatchEvent;
  const originalGetElementById = document.getElementById;

  beforeEach(async () => {
    vi.clearAllMocks();

    // Get the mocked wordpress.media function
    const { wordpress } = vi.mocked(await import('@/config/growfund'));
    (wordpress.media as any).mockReturnValue(mockMediaInstance);

    // Mock window.dispatchEvent
    window.dispatchEvent = vi.fn();

    // Mock document.getElementById
    document.getElementById = vi.fn();
  });

  afterEach(() => {
    window.dispatchEvent = originalDispatchEvent;
    document.getElementById = originalGetElementById;
  });

  it('should return media modal functions and state', () => {
    const { result } = renderHook(() => useWordpressMedia());

    expect(result.current).toHaveProperty('openMediaModal');
    expect(result.current).toHaveProperty('attachments');
    expect(result.current).toHaveProperty('isMediaOpen');
    expect(typeof result.current.openMediaModal).toBe('function');
    expect(Array.isArray(result.current.attachments)).toBe(true);
    expect(typeof result.current.isMediaOpen).toBe('boolean');
  });

  it('should initialize with empty attachments and closed state', () => {
    const { result } = renderHook(() => useWordpressMedia());

    expect(result.current.attachments).toEqual([]);
    expect(result.current.isMediaOpen).toBe(false);
  });

  it('should open media modal with default options', async () => {
    const { wordpress } = vi.mocked(await import('@/config/growfund'));
    const { result } = renderHook(() => useWordpressMedia());

    act(() => {
      result.current.openMediaModal();
    });

    expect(wordpress.media).toHaveBeenCalledWith({
      title: 'Select or Upload Image',
      button: {
        text: 'Upload',
      },
      multiple: false,
      library: {},
    });

    expect(mockMediaInstance.open).toHaveBeenCalled();
  });

  it('should open media modal with custom options', async () => {
    const { wordpress } = vi.mocked(await import('@/config/growfund'));
    const { result } = renderHook(() => useWordpressMedia());
    const customOptions = {
      multiple: true,
      title: 'Select Images',
      button_text: 'Choose Images',
      types: ['image' as AcceptedMediaTypes],
      onSelect: vi.fn(),
    };

    act(() => {
      result.current.openMediaModal(customOptions);
    });

    expect(wordpress.media).toHaveBeenCalledWith({
      title: 'Select Images',
      button: {
        text: 'Choose Images',
      },
      multiple: true,
      library: {
        type: ['image'],
      },
    });
  });

  it('should set up event listeners when opening modal', () => {
    const { result } = renderHook(() => useWordpressMedia());

    act(() => {
      result.current.openMediaModal();
    });

    expect(mockMediaInstance.on).toHaveBeenCalledWith('open', expect.any(Function));
    expect(mockMediaInstance.on).toHaveBeenCalledWith('select', expect.any(Function));
  });

  it('should dispatch wp-media-open event when modal opens', () => {
    const { result } = renderHook(() => useWordpressMedia());

    act(() => {
      result.current.openMediaModal();
    });

    // Get the open handler
    const openHandler = mockMediaInstance.on.mock.calls.find((call) => call[0] === 'open')?.[1];

    act(() => {
      openHandler();
    });

    expect(window.dispatchEvent).toHaveBeenCalledWith(
      new CustomEvent('wp-media-open', { detail: { isOpen: true } }),
    );
    expect(result.current.isMediaOpen).toBe(true);
  });

  it('should remove aria-hidden from wpwrap when modal opens', () => {
    const mockWpWrap = {
      hasAttribute: vi.fn().mockReturnValue(true),
      removeAttribute: vi.fn(),
    };
    document.getElementById = vi.fn().mockReturnValue(mockWpWrap);

    const { result } = renderHook(() => useWordpressMedia());

    act(() => {
      result.current.openMediaModal();
    });

    const openHandler = mockMediaInstance.on.mock.calls.find((call) => call[0] === 'open')?.[1];

    act(() => {
      openHandler();
    });

    expect(document.getElementById).toHaveBeenCalledWith('wpwrap');
    expect(mockWpWrap.removeAttribute).toHaveBeenCalledWith('aria-hidden');
  });

  it('should handle media selection for single file', () => {
    const { result } = renderHook(() => useWordpressMedia());
    const mockSelection = {
      first: vi.fn().mockReturnValue({
        toJSON: vi.fn().mockReturnValue({
          id: 123,
          url: 'http://example.com/image.jpg',
          filename: 'image.jpg',
          sizes: {},
          width: 800,
          height: 600,
          filesize: 1024,
          mime: 'image/jpeg',
          type: 'image',
          author: 1,
          authorName: 'Admin',
          date: '2023-01-01',
          thumb: 'http://example.com/thumb.jpg',
        }),
      }),
    };

    mockMediaInstance.state.mockReturnValue({
      get: vi.fn().mockReturnValue(mockSelection),
    });

    act(() => {
      result.current.openMediaModal();
    });

    const selectHandler = mockMediaInstance.on.mock.calls.find((call) => call[0] === 'select')?.[1];

    act(() => {
      selectHandler();
    });

    expect(result.current.attachments).toHaveLength(1);
    expect(result.current.attachments[0]).toEqual({
      id: '123',
      url: 'http://example.com/image.jpg',
      filename: 'image.jpg',
      sizes: {},
      width: 800,
      height: 600,
      filesize: 1024,
      mime: 'image/jpeg',
      type: 'image',
      author: '1',
      authorName: 'Admin',
      date: '2023-01-01',
      thumb: 'http://example.com/thumb.jpg',
    });
    expect(result.current.isMediaOpen).toBe(false);
  });

  it('should handle media selection for multiple files', () => {
    const { result } = renderHook(() => useWordpressMedia());
    const mockSelection = [
      {
        toJSON: vi.fn().mockReturnValue({
          id: 123,
          url: 'http://example.com/image1.jpg',
          filename: 'image1.jpg',
          sizes: {},
          width: 800,
          height: 600,
          filesize: 1024,
          mime: 'image/jpeg',
          type: 'image',
          author: 1,
          authorName: 'Admin',
          date: '2023-01-01',
          thumb: 'http://example.com/thumb1.jpg',
        }),
      },
      {
        toJSON: vi.fn().mockReturnValue({
          id: 124,
          url: 'http://example.com/image2.jpg',
          filename: 'image2.jpg',
          sizes: {},
          width: 1200,
          height: 800,
          filesize: 2048,
          mime: 'image/jpeg',
          type: 'image',
          author: 1,
          authorName: 'Admin',
          date: '2023-01-02',
          thumb: 'http://example.com/thumb2.jpg',
        }),
      },
    ];

    mockMediaInstance.state.mockReturnValue({
      get: vi.fn().mockReturnValue(mockSelection),
    });

    act(() => {
      result.current.openMediaModal({ multiple: true });
    });

    const selectHandler = mockMediaInstance.on.mock.calls.find((call) => call[0] === 'select')?.[1];

    act(() => {
      selectHandler();
    });

    expect(result.current.attachments).toHaveLength(2);
    expect(result.current.attachments[0].id).toBe('123');
    expect(result.current.attachments[1].id).toBe('124');
  });

  it('should call onSelect callback when provided', () => {
    const { result } = renderHook(() => useWordpressMedia());
    const mockOnSelect = vi.fn();
    const mockSelection = {
      first: vi.fn().mockReturnValue({
        toJSON: vi.fn().mockReturnValue({
          id: 123,
          url: 'http://example.com/image.jpg',
          filename: 'image.jpg',
          sizes: {},
          width: 800,
          height: 600,
          filesize: 1024,
          mime: 'image/jpeg',
          type: 'image',
          author: 1,
          authorName: 'Admin',
          date: '2023-01-01',
          thumb: 'http://example.com/thumb.jpg',
        }),
      }),
    };

    mockMediaInstance.state.mockReturnValue({
      get: vi.fn().mockReturnValue(mockSelection),
    });

    act(() => {
      result.current.openMediaModal({ onSelect: mockOnSelect });
    });

    const selectHandler = mockMediaInstance.on.mock.calls.find((call) => call[0] === 'select')?.[1];

    act(() => {
      selectHandler();
    });

    expect(mockOnSelect).toHaveBeenCalledWith(result.current.attachments);
  });

  it('should clean up event listeners on unmount', () => {
    const { result, unmount } = renderHook(() => useWordpressMedia());

    act(() => {
      result.current.openMediaModal();
    });

    unmount();

    // The cleanup should be called when the component unmounts
    expect(mockMediaInstance.off).toHaveBeenCalledWith('select');
    expect(mockMediaInstance.off).toHaveBeenCalledWith('open');
    expect(mockMediaInstance.off).toHaveBeenCalledWith('close');
  });

  it('should handle missing selection state gracefully', () => {
    const { result } = renderHook(() => useWordpressMedia());

    mockMediaInstance.state.mockReturnValue({
      get: vi.fn().mockReturnValue(null),
    });

    act(() => {
      result.current.openMediaModal();
    });

    const selectHandler = mockMediaInstance.on.mock.calls.find((call) => call[0] === 'select')?.[1];

    expect(() => {
      act(() => {
        selectHandler();
      });
    }).not.toThrow();

    expect(result.current.attachments).toEqual([]);
  });
});

describe('useDialogCloseMiddleware', () => {
  const originalAddEventListener = window.addEventListener;
  const originalRemoveEventListener = window.removeEventListener;
  let eventListeners: Record<string, Function[]> = {};

  beforeEach(() => {
    vi.clearAllMocks();

    window.addEventListener = vi.fn(
      (event: string, listener: EventListenerOrEventListenerObject) => {
        if (!eventListeners[event]) {
          eventListeners[event] = [];
        }
        eventListeners[event].push(listener as Function);
      },
    );

    window.removeEventListener = vi.fn(
      (event: string, listener: EventListenerOrEventListenerObject) => {
        if (eventListeners[event]) {
          const index = eventListeners[event].indexOf(listener as Function);
          if (index > -1) {
            eventListeners[event].splice(index, 1);
          }
        }
      },
    );
  });

  afterEach(() => {
    window.addEventListener = originalAddEventListener;
    window.removeEventListener = originalRemoveEventListener;
    eventListeners = {};
  });

  it('should return applyMiddleware function', () => {
    const { result } = renderHook(() => useDialogCloseMiddleware());

    expect(result.current).toHaveProperty('applyMiddleware');
    expect(typeof result.current.applyMiddleware).toBe('function');
  });

  it('should add wp-media-open event listener', () => {
    renderHook(() => useDialogCloseMiddleware());

    expect(window.addEventListener).toHaveBeenCalledWith('wp-media-open', expect.any(Function));
  });

  it('should remove event listener on unmount', () => {
    const { unmount } = renderHook(() => useDialogCloseMiddleware());

    unmount();

    expect(window.removeEventListener).toHaveBeenCalledWith('wp-media-open', expect.any(Function));
  });

  it('should apply middleware that blocks callback when media is open', () => {
    const { result } = renderHook(() => useDialogCloseMiddleware());
    const mockCallback = vi.fn();

    // Simulate media opening first
    const eventHandler = eventListeners['wp-media-open'][0];
    act(() => {
      eventHandler(new CustomEvent('wp-media-open', { detail: { isOpen: true } }));
    });

    // Now create the middleware after the state is updated
    const middleware = result.current.applyMiddleware(mockCallback);

    // Call middleware with false (should be blocked)
    act(() => {
      middleware(false);
    });

    expect(mockCallback).not.toHaveBeenCalled();
  });

  it('should apply middleware that allows callback when media is closed', () => {
    const { result } = renderHook(() => useDialogCloseMiddleware());
    const mockCallback = vi.fn();

    const middleware = result.current.applyMiddleware(mockCallback);

    // Simulate media opening then closing
    const eventHandler = eventListeners['wp-media-open'][0];
    act(() => {
      eventHandler(new CustomEvent('wp-media-open', { detail: { isOpen: true } }));
      eventHandler(new CustomEvent('wp-media-open', { detail: { isOpen: false } }));
    });

    // Call middleware with false (should be allowed)
    middleware(false);

    expect(mockCallback).toHaveBeenCalledWith(false);
  });

  it('should apply middleware that allows callback when media was never open', () => {
    const { result } = renderHook(() => useDialogCloseMiddleware());
    const mockCallback = vi.fn();

    const middleware = result.current.applyMiddleware(mockCallback);

    // Call middleware without opening media
    middleware(false);

    expect(mockCallback).toHaveBeenCalledWith(false);
  });

  it('should handle event without detail gracefully', () => {
    const { result } = renderHook(() => useDialogCloseMiddleware());
    const mockCallback = vi.fn();

    const middleware = result.current.applyMiddleware(mockCallback);

    // Simulate event with null detail
    const eventHandler = eventListeners['wp-media-open'][0];
    act(() => {
      eventHandler(new CustomEvent('wp-media-open', { detail: null }));
    });

    // Should not throw and callback should work
    act(() => {
      middleware(false);
    });

    expect(mockCallback).toHaveBeenCalledWith(false);
  });
});

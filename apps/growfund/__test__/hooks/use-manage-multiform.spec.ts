import { act, renderHook } from '@testing-library/react';
import { useForm } from 'react-hook-form';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useManageMultiform } from '@/hooks/use-manage-multiform';

// Mock utils
vi.mock('@/utils', () => ({
  noop: () => {},
}));

describe('useManageMultiform', () => {
  const createMockForm = (id: string) => ({
    getValues: vi.fn(() => ({ id })),
    setValue: vi.fn(),
    watch: vi.fn(),
    reset: vi.fn(),
    formState: { isDirty: false },
  });

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should initialize with empty state', () => {
    const { result } = renderHook(() => useManageMultiform<any, string>());

    expect(result.current.getCurrentForm()).toBeNull();
    expect(result.current.getCurrentKey()).toBeNull();
    expect(result.current.isCurrentFormDirty).toBe(false);
    expect(typeof result.current.registerForm).toBe('function');
    expect(typeof result.current.setIsCurrentFormDirty).toBe('function');
  });

  describe('registerForm', () => {
    it('should register a new form and set it as current', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');

      act(() => {
        const unregister = result.current.registerForm('form1', mockForm1 as any);
        expect(typeof unregister).toBe('function');
      });

      expect(result.current.getCurrentForm()).toBe(mockForm1);
      expect(result.current.getCurrentKey()).toBe('form1');
    });

    it('should return noop function when trying to register existing form', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');

      act(() => {
        result.current.registerForm('form1', mockForm1 as any);
      });

      act(() => {
        const unregister = result.current.registerForm('form1', mockForm1 as any);
        expect(typeof unregister).toBe('function'); // noop returns a function
      });

      expect(result.current.getCurrentForm()).toBe(mockForm1);
      expect(result.current.getCurrentKey()).toBe('form1');
    });

    it('should handle multiple forms and keep track of the last registered one', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');
      const mockForm2 = createMockForm('form2');

      act(() => {
        result.current.registerForm('form1', mockForm1 as any);
      });

      expect(result.current.getCurrentForm()).toBe(mockForm1);
      expect(result.current.getCurrentKey()).toBe('form1');

      act(() => {
        result.current.registerForm('form2', mockForm2 as any);
      });

      expect(result.current.getCurrentForm()).toBe(mockForm2);
      expect(result.current.getCurrentKey()).toBe('form2');
    });

    it('should return unregister function that removes form and resets current key', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');

      let unregister: (() => void) | undefined;

      act(() => {
        unregister = result.current.registerForm('form1', mockForm1 as any);
      });

      expect(result.current.getCurrentForm()).toBe(mockForm1);
      expect(result.current.getCurrentKey()).toBe('form1');

      act(() => {
        unregister?.();
      });

      expect(result.current.getCurrentForm()).toBeNull();
      expect(result.current.getCurrentKey()).toBeNull();
    });
  });

  describe('getCurrentForm', () => {
    it('should return the last registered form', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');
      const mockForm2 = createMockForm('form2');
      const mockForm3 = createMockForm('form3');

      act(() => {
        result.current.registerForm('form1', mockForm1 as any);
        result.current.registerForm('form2', mockForm2 as any);
        result.current.registerForm('form3', mockForm3 as any);
      });

      expect(result.current.getCurrentForm()).toBe(mockForm3);
    });

    it('should return null when no forms are registered', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());

      expect(result.current.getCurrentForm()).toBeNull();
    });

    it('should return null when all forms are unregistered', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');

      act(() => {
        const unregister = result.current.registerForm('form1', mockForm1 as any);
        unregister();
      });

      expect(result.current.getCurrentForm()).toBeNull();
    });
  });

  describe('getCurrentKey', () => {
    it('should return the current form key', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');

      act(() => {
        result.current.registerForm('form1', mockForm1 as any);
      });

      expect(result.current.getCurrentKey()).toBe('form1');
    });

    it('should return null when no forms are registered', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());

      expect(result.current.getCurrentKey()).toBeNull();
    });
  });

  describe('isCurrentFormDirty state', () => {
    it('should allow setting and getting dirty state', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());

      expect(result.current.isCurrentFormDirty).toBe(false);

      act(() => {
        result.current.setIsCurrentFormDirty(true);
      });

      expect(result.current.isCurrentFormDirty).toBe(true);

      act(() => {
        result.current.setIsCurrentFormDirty(false);
      });

      expect(result.current.isCurrentFormDirty).toBe(false);
    });
  });

  describe('unregisterForm', () => {
    it('should remove form from internal map', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const mockForm1 = createMockForm('form1');

      act(() => {
        result.current.registerForm('form1', mockForm1 as any);
      });

      expect(result.current.getCurrentForm()).toBe(mockForm1);

      act(() => {
        result.current.registerForm('form1', mockForm1 as any); // This should return noop since form exists
      });

      // Form should still be there
      expect(result.current.getCurrentForm()).toBe(mockForm1);
    });

    it('should handle unregistering non-existent form gracefully', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());

      expect(() => {
        act(() => {
          result.current.registerForm('non-existent', createMockForm('test') as any);
        });
      }).not.toThrow();
    });
  });

  describe('integration with react-hook-form', () => {
    it('should work with actual react-hook-form instances', () => {
      const TestComponent = () => {
        const form1 = useForm({ defaultValues: { name: '' } });
        const form2 = useForm({ defaultValues: { email: '' } });
        const multiform = useManageMultiform<any, string>();

        return { form1, form2, multiform };
      };

      const { result } = renderHook(() => TestComponent());

      act(() => {
        result.current.multiform.registerForm('user', result.current.form1);
      });

      expect(result.current.multiform.getCurrentForm()).toBe(result.current.form1);
      expect(result.current.multiform.getCurrentKey()).toBe('user');

      act(() => {
        result.current.multiform.registerForm('contact', result.current.form2);
      });

      expect(result.current.multiform.getCurrentForm()).toBe(result.current.form2);
      expect(result.current.multiform.getCurrentKey()).toBe('contact');
    });
  });

  describe('edge cases', () => {
    it('should handle rapid form registration and unregistration', () => {
      const { result } = renderHook(() => useManageMultiform<any, string>());
      const forms = Array.from({ length: 10 }, (_, i) => createMockForm(`form${i}`));

      act(() => {
        forms.forEach((form, i) => {
          const unregister = result.current.registerForm(`form${i}`, form as any);
          if (i % 2 === 0) {
            unregister();
          }
        });
      });

      // Should have the last registered form that wasn't immediately unregistered
      expect(result.current.getCurrentForm()).toBe(forms[9]);
      expect(result.current.getCurrentKey()).toBe('form9');
    });

    it('should handle different key types', () => {
      const { result } = renderHook(() => useManageMultiform<any, number>());
      const mockForm = createMockForm('form1');

      act(() => {
        result.current.registerForm(123, mockForm as any);
      });

      expect(result.current.getCurrentForm()).toBe(mockForm);
      expect(result.current.getCurrentKey()).toBe(123);
    });
  });
});

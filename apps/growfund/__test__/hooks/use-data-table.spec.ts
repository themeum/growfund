import { type ColumnDef } from '@tanstack/react-table';
import { act, renderHook } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useDataTable } from '@/hooks/use-data-table';

// Mock @tanstack/react-table
vi.mock('@tanstack/react-table', () => ({
  getCoreRowModel: vi.fn(() => 'coreRowModel'),
  getSortedRowModel: vi.fn(() => 'sortedRowModel'),
  useReactTable: vi.fn(),
}));

describe('useDataTable', () => {
  const mockData = [
    { id: 1, name: 'John', email: 'john@example.com' },
    { id: 2, name: 'Jane', email: 'jane@example.com' },
  ];

  const mockColumns: ColumnDef<(typeof mockData)[0]>[] = [
    {
      accessorKey: 'id',
      header: 'ID',
    },
    {
      accessorKey: 'name',
      header: 'Name',
    },
    {
      accessorKey: 'email',
      header: 'Email',
    },
  ];

  let mockUseReactTable: any;

  beforeEach(async () => {
    vi.clearAllMocks();

    // Get the mocked function and setup default implementation
    const { useReactTable } = await vi.importMock('@tanstack/react-table');
    mockUseReactTable = useReactTable as any;
    (useReactTable as any).mockImplementation((config: any) => ({
      getHeaderGroups: vi.fn(() => []),
      getRowModel: vi.fn(() => ({ rows: [] })),
      getCanPreviousPage: vi.fn(() => false),
      getCanNextPage: vi.fn(() => false),
      getPageCount: vi.fn(() => 1),
      getState: vi.fn(() => ({
        sorting: config.state?.sorting || [],
        columnVisibility: config.state?.columnVisibility || {},
      })),
      setSorting: config.onSortingChange,
      setColumnVisibility: config.onColumnVisibilityChange,
    }));
  });

  it('should initialize with default state', () => {
    const { result } = renderHook(() => useDataTable(mockData, mockColumns));

    expect(result.current.table).toBeDefined();
    expect(result.current.table.getState().sorting).toEqual([]);
    expect(result.current.table.getState().columnVisibility).toEqual({});
  });

  it('should pass data and columns to useReactTable', () => {
    renderHook(() => useDataTable(mockData, mockColumns));

    expect(mockUseReactTable).toHaveBeenCalledWith(
      expect.objectContaining({
        data: mockData,
        columns: mockColumns,
        getCoreRowModel: 'coreRowModel',
        getSortedRowModel: 'sortedRowModel',
        enableRowSelection: true,
        state: {
          sorting: [],
          columnVisibility: {},
        },
        defaultColumn: {
          size: 150,
          minSize: 50,
          maxSize: 500,
        },
      }),
    );
  });

  it('should handle custom options', () => {
    const customOptions = {
      enableSorting: false,
      enableColumnFilters: true,
    };

    renderHook(() => useDataTable(mockData, mockColumns, customOptions));

    expect(mockUseReactTable).toHaveBeenCalledWith(
      expect.objectContaining({
        ...customOptions,
        data: mockData,
        columns: mockColumns,
      }),
    );
  });

  it('should handle empty data array', () => {
    const { result } = renderHook(() => useDataTable([], mockColumns));

    expect(result.current.table).toBeDefined();
    expect(result.current.table.getState().sorting).toEqual([]);
  });

  it('should handle empty columns array', () => {
    const { result } = renderHook(() => useDataTable(mockData, []));

    expect(result.current.table).toBeDefined();
  });

  it('should update sorting state when onSortingChange is called', () => {
    const { result } = renderHook(() => useDataTable(mockData, mockColumns));
    const mockSorting = [{ id: 'name', desc: true }];

    act(() => {
      result.current.table.setSorting(mockSorting);
    });

    expect(result.current.table.getState().sorting).toEqual(mockSorting);
  });

  it('should update column visibility when onColumnVisibilityChange is called', () => {
    const { result } = renderHook(() => useDataTable(mockData, mockColumns));
    const mockVisibility = { name: false, email: true };

    act(() => {
      result.current.table.setColumnVisibility(mockVisibility);
    });

    expect(result.current.table.getState().columnVisibility).toEqual(mockVisibility);
  });

  it('should maintain default column configuration', () => {
    renderHook(() => useDataTable(mockData, mockColumns));

    expect(mockUseReactTable).toHaveBeenCalledWith(
      expect.objectContaining({
        defaultColumn: {
          size: 150,
          minSize: 50,
          maxSize: 500,
        },
      }),
    );
  });

  it('should handle complex data types', () => {
    const complexData = [
      { id: 1, user: { name: 'John', age: 30 }, tags: ['admin', 'user'] },
      { id: 2, user: { name: 'Jane', age: 25 }, tags: ['user'] },
    ];

    const complexColumns: ColumnDef<(typeof complexData)[0]>[] = [
      { accessorKey: 'id', header: 'ID' },
      { accessorKey: 'user.name', header: 'Name' },
      { accessorKey: 'user.age', header: 'Age' },
    ];

    const { result } = renderHook(() => useDataTable(complexData, complexColumns));

    expect(result.current.table).toBeDefined();
  });

  it('should handle undefined options', () => {
    const { result } = renderHook(() => useDataTable(mockData, mockColumns, undefined));

    expect(result.current.table).toBeDefined();
  });

  it('should handle null options', () => {
    const { result } = renderHook(() => useDataTable(mockData, mockColumns, null as any));

    expect(result.current.table).toBeDefined();
  });
});

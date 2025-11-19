import {
  type ColumnDef,
  getCoreRowModel,
  getSortedRowModel,
  type SortingState,
  useReactTable,
  type VisibilityState,
} from '@tanstack/react-table';
import { useState } from 'react';

import { type AnyObject } from '@/types';

const useDataTable = <TData extends object>(
  data: TData[],
  columns: ColumnDef<TData>[],
  options?: AnyObject,
) => {
  const [sorting, setSorting] = useState<SortingState>([]);
  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    enableRowSelection: true,
    state: {
      sorting,
      columnVisibility,
    },
    onSortingChange: setSorting,
    onColumnVisibilityChange: setColumnVisibility,
    defaultColumn: {
      size: 150,
      minSize: 50,
      maxSize: 500,
    },
    ...options,
  });

  return { table };
};

export { useDataTable };

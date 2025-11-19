/* eslint-disable react-refresh/only-export-components */
import {
    flexRender,
    getCoreRowModel,
    getSortedRowModel,
    useReactTable,
    type ColumnPinningState,
    type PaginationState,
    type RowSelectionState,
    type SortingState,
    type Table,
    type VisibilityState,
} from '@tanstack/react-table';
import { __, _n, sprintf } from '@wordpress/i18n';
import { parseAsInteger, useQueryState } from 'nuqs';
import React, { memo, use, useEffect, useMemo, useRef, useState } from 'react';
import { useForm } from 'react-hook-form';

import { EmptySearchIcon } from '@/app/icons';
import { SelectField } from '@/components/form/select-field';
import { Button } from '@/components/ui/button';
import { Card, CardHeader } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import Paginator from '@/components/ui/paginator';
import { Skeleton } from '@/components/ui/skeleton';
import {
    TableBody,
    TableCell,
    Table as TableComponent,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';
import { type AnyObject, type Option, type TableColumnDef } from '@/types';
import { isDefined } from '@/utils';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
interface DataTableContextType<T extends { id: string | number } = any> {
  table: Table<T>;
  selectedRows: RowSelectionState;
  setSelectedRows: (rows: RowSelectionState) => void;
  total: number;
  limit: number;
  loading?: boolean;
}

const DataTableContext = React.createContext<DataTableContextType | null>(null);

const useDataTableContext = () => {
  const context = use(DataTableContext);

  if (!context) {
    throw new Error('useTableContext must be used within a TableProvider');
  }

  return context;
};

interface DataTableProviderProps<T extends { id: string | number }> {
  columns: TableColumnDef<T>[];
  data: T[];
  children: React.ReactNode;
  columnPinning?: ColumnPinningState;
  total?: number;
  limit?: number;
  options?: AnyObject;
  loading?: boolean;
}

const DataTableProvider = <T extends { id: string | number }>({
  children,
  columns,
  data,
  columnPinning,
  total = 0,
  limit = 10,
  options,
  loading,
}: DataTableProviderProps<T>) => {
  const [rowSelection, setRowSelection] = useState<RowSelectionState>({});
  const [sorting, setSorting] = useState<SortingState>([]);
  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
  const [columnPinningState, setColumnPinningState] = useState<ColumnPinningState>(
    columnPinning ?? {},
  );

  const [page] = useQueryState('pg', parseAsInteger.withDefault(1));
  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: Math.max(page - 1, 0),
    pageSize: limit,
  });

  useEffect(() => {
    setColumnPinningState(columnPinning ?? {});
  }, [columnPinning]);

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    enableRowSelection: true,
    onRowSelectionChange: setRowSelection,
    getRowId: (row) => String(row.id),
    onColumnPinningChange: setColumnPinningState,
    state: {
      sorting,
      columnVisibility,
      rowSelection,
      columnPinning: columnPinningState,
      pagination,
    },
    onSortingChange: setSorting,
    onPaginationChange: setPagination,
    onColumnVisibilityChange: setColumnVisibility,
    defaultColumn: {
      size: 150,
      minSize: 20,
      maxSize: 500,
    },
    manualPagination: true,
    rowCount: total,
    ...options,
  });

  const contextValue = useMemo(() => {
    return {
      table,
      selectedRows: rowSelection,
      setSelectedRows: setRowSelection,
      total: total,
      limit: limit,
      loading,
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    rowSelection,
    setRowSelection,
    table,
    total,
    loading,
    columnVisibility,
    sorting,
    columnPinningState,
  ]);

  return <DataTableContext value={contextValue}>{children}</DataTableContext>;
};

interface DataTableProps<T extends { id: string | number }> {
  columns: TableColumnDef<T>[];
  data: T[];
  children: React.ReactNode;
  columnPinning?: ColumnPinningState;
  total?: number;
  limit?: number;
  options?: AnyObject;
  loading?: boolean;
}

const DataTable = <T extends { id: string }>({
  columns,
  data,
  children,
  columnPinning,
  total,
  limit,
  loading,
}: DataTableProps<T>) => {
  return (
    <DataTableProvider
      columns={columns}
      data={data}
      columnPinning={columnPinning}
      total={total}
      limit={limit}
      loading={loading}
    >
      {children}
    </DataTableProvider>
  );
};

type DataTableContentProps = React.HTMLAttributes<HTMLDivElement>;

const DataTableContent = ({ className, ...props }: DataTableContentProps) => {
  const { table, selectedRows, setSelectedRows, loading } = useDataTableContext();

  const lastSelectedIndex = useRef<number | null>(null);

  useEffect(() => {
    const handleCheckboxClick = (event: MouseEvent) => {
      const target = event.target as HTMLInputElement;
      const checkbox = target.closest('[data-row-selection-checkbox]');

      if (!checkbox) {
        return;
      }

      const currentIndex = Number(checkbox.getAttribute('data-row-index') ?? '-1');

      if (currentIndex === -1) {
        return;
      }

      const isShiftKey = event.shiftKey;
      const { rows } = table.getRowModel();
      const currentCheckboxStatus = selectedRows[rows[currentIndex].id];

      if (isShiftKey && lastSelectedIndex.current !== null) {
        event.preventDefault();
        const start = Math.min(lastSelectedIndex.current, currentIndex);
        const end = Math.max(lastSelectedIndex.current, currentIndex);

        const newSelectedRows = { ...selectedRows };

        for (let i = start; i <= end; i++) {
          const rowId = rows[i].id;
          newSelectedRows[rowId] = !currentCheckboxStatus;
        }

        setSelectedRows(newSelectedRows);
      } else {
        const newRowSelection = { ...selectedRows };
        const rowId = rows[currentIndex].id;
        newRowSelection[rowId] = !currentCheckboxStatus;
        setSelectedRows(newRowSelection);
      }
      lastSelectedIndex.current = currentIndex;
    };

    document.addEventListener('click', handleCheckboxClick);

    return () => {
      document.removeEventListener('click', handleCheckboxClick);
    };
  }, [selectedRows, setSelectedRows, table]);

  const renderTableRows = () => {
    if (loading) {
      return (
        <TableBody>
          {Array.from({ length: 10 }).map((_, index) => {
            return (
              <TableRow key={index}>
                {table.getAllColumns().map((column) => {
                  return (
                    <TableCell key={column.id} className="growfund-py-4">
                      <Skeleton className="growfund-h-4 growfund-w-full" animate />
                    </TableCell>
                  );
                })}
              </TableRow>
            );
          })}
        </TableBody>
      );
    }

    return table.getRowModel().rows.length > 0 ? (
      <TableBody>
        {table.getRowModel().rows.map((row) => {
          const isSelected = row.getIsSelected();
          return (
            <TableRow
              key={row.id}
              className={cn(
                'growfund-h-12 growfund-group/row',
                isSelected && 'growfund-bg-background-surface-secondary',
              )}
              dat-row="true"
            >
              {row.getVisibleCells().map((cell) => {
                const isPinnedLeft = cell.column.getIsPinned() === 'left';
                const isPinnedRight = cell.column.getIsPinned() === 'right';
                const isPinned = isPinnedLeft || isPinnedRight;

                const offset =
                  (cell.column.columnDef.meta as { offset: number } | undefined)?.offset ?? 0;

                return (
                  <TableCell
                    key={cell.id}
                    className={cn(
                      'growfund-typo-tiny group-hover/row:growfund-bg-background-surface-secondary',
                      isPinned && 'growfund-sticky growfund-z-positive growfund-bg-background-surface',
                      isSelected && 'growfund-bg-background-surface-secondary',
                    )}
                    style={{
                      width: `${cell.column.getSize()}px`,
                      ...(isPinnedRight && { right: offset }),
                      ...(isPinnedLeft && { left: offset }),
                    }}
                    data-cell="true"
                  >
                    <div>{flexRender(cell.column.columnDef.cell, cell.getContext())}</div>
                  </TableCell>
                );
              })}
            </TableRow>
          );
        })}
      </TableBody>
    ) : (
      <TableBody>
        <TableRow className="hover:growfund-bg-transparent growfund-h-20">
          <TableCell
            colSpan={table.getAllColumns().length}
            className="growfund-text-center growfund-pt-6 growfund-pb-12"
          >
            <div className="growfund-w-full growfund-flex growfund-flex-col growfund-items-center growfund-justify-center">
              <EmptySearchIcon />
              <span className="growfund-typo-small growfund-text-fg-secondary growfund-text-center">
                {__('No matching results found.', 'growfund')}
              </span>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    );
  };

  return (
    <TableComponent
      className={cn(
        'growfund-border-none growfund-w-full growfund-border-collapse growfund-border-spacing-2 growfund-table-fixed',
        className,
      )}
      {...props}
    >
      <TableHeader className={cn('growfund-h-12 growfund-bg-background-surface-secondary')}>
        {table.getHeaderGroups().map((headerGroup) => {
          return (
            <TableRow key={headerGroup.id}>
              {headerGroup.headers.map((header) => {
                const offset =
                  (header.column.columnDef.meta as { offset: number } | undefined)?.offset ?? 0;
                const isPinnedLeft = header.column.getIsPinned() === 'left';
                const isPinnedRight = header.column.getIsPinned() === 'right';
                const isPinned = isPinnedLeft || isPinnedRight;

                return (
                  <TableHead
                    key={header.id}
                    className={cn(
                      isPinned && 'growfund-sticky growfund-z-positive growfund-bg-background-surface-secondary',
                    )}
                    style={{
                      width: `${header.getSize()}px`,
                      ...(isPinnedRight && { right: offset }),
                      ...(isPinnedLeft && { left: offset }),
                    }}
                  >
                    <div
                      className={cn(
                        'growfund-flex growfund-items-center growfund-gap-2 hover:growfund-no-underline growfund-px-0 growfund-typo-tiny growfund-text-fg-secondary',
                      )}
                    >
                      {header.isPlaceholder
                        ? null
                        : flexRender(header.column.columnDef.header, header.getContext())}
                    </div>
                  </TableHead>
                );
              })}
            </TableRow>
          );
        })}
      </TableHeader>
      {renderTableRows()}
    </TableComponent>
  );
};

DataTableContent.displayName = 'DataTableContent';

interface DataTableWrapperProps extends React.HTMLAttributes<HTMLDivElement> {
  ref?: React.RefObject<HTMLDivElement>;
}

const DataTableWrapper = ({ className, children, ref, ...props }: DataTableWrapperProps) => {
  return (
    <Card {...props} className={cn('growfund-overflow-hidden growfund-pb-1', className)} ref={ref}>
      {children}
    </Card>
  );
};

DataTableWrapper.displayName = 'DataTableWrapper';

interface DataTableWrapperHeaderProps<A> extends React.HTMLAttributes<HTMLDivElement> {
  actions?: Option<A>[];
  onActionChange?: (action: A, selectedRows: string[]) => void;
  secondaryActions?: (selectedRows: string[]) => React.ReactNode;
}

const DataTableWrapperHeader = memo(
  <A extends string>({
    children,
    className,
    actions,
    onActionChange,
    secondaryActions,
    ...props
  }: DataTableWrapperHeaderProps<A>) => {
    const { table } = useDataTableContext();
    const selectedRows = Object.keys(table.getSelectedRowModel().rowsById);
    const totalSelectedRows = selectedRows.length;
    const isAllPageRowsSelected = table.getIsAllPageRowsSelected();
    const form = useForm<{ action: A }>();

    return (
      <CardHeader {...props} className={cn(className)}>
        <div className="growfund-min-h-9 growfund-flex growfund-items-center growfund-w-full">
          {totalSelectedRows > 0 ? (
            <Form {...form}>
              <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-justify-between growfund-w-full">
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  <div className="growfund-flex growfund-items-center">
                    <span className="growfund-text-fg-secondary">
                      {
                        /* translators: %d: number of selected items */
                        sprintf(
                          _n(
                            '%d item selected',
                            '%d items selected',
                            totalSelectedRows,
                            'growfund',
                          ),
                          totalSelectedRows,
                        )
                      }
                    </span>
                    <Button
                      variant="link"
                      className="growfund-text-fg-emphasis"
                      size="sm"
                      onClick={() => {
                        table.toggleAllPageRowsSelected(!isAllPageRowsSelected);
                      }}
                    >
                      {isAllPageRowsSelected
                        ? __('Clear selection', 'growfund')
                        : __('Select all', 'growfund')}
                    </Button>
                  </div>

                  {actions && actions.length > 0 && (
                    <div className="growfund-flex growfund-items-center growfund-gap-2">
                      <SelectField
                        control={form.control}
                        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-explicit-any
                        name={'action' as any}
                        placeholder={__('Action', 'growfund')}
                        options={actions}
                      />
                      <Button
                        variant="secondary"
                        onClick={form.handleSubmit(
                          (values) => {
                            onActionChange?.(values.action, selectedRows);
                            table.resetRowSelection();
                            // eslint-disable-next-line @typescript-eslint/no-explicit-any
                            form.setValue('action' as any, undefined);
                          },
                          (errors) => {
                            console.error(errors);
                          },
                        )}
                      >
                        {__('Apply', 'growfund')}
                      </Button>
                    </div>
                  )}
                </div>
                {isDefined(secondaryActions) && secondaryActions(selectedRows)}
              </div>
            </Form>
          ) : (
            children
          )}
        </div>
      </CardHeader>
    );
  },
);

DataTableWrapperHeader.displayName = 'DataTableWrapperHeader';

interface DataTablePaginationProps extends React.HTMLAttributes<HTMLDivElement> {
  ref?: React.RefObject<HTMLDivElement>;
}

const DataTablePagination = ({ className, ref, ...props }: DataTablePaginationProps) => {
  const { table, total, limit } = useDataTableContext();
  const pagination = table.getState().pagination;
  const currentPage = pagination.pageIndex + 1;
  const numberOfRecords = table.getRowCount();

  const [page, setPage] = useQueryState('pg', parseAsInteger.withDefault(1));

  useEffect(() => {
    table.setPageIndex.call(null, page - 1);
  }, [page, table.setPageIndex]);

  const totalPages = useMemo(() => {
    return Math.ceil(total / limit);
  }, [limit, total]);

  useEffect(() => {
    if (page > totalPages) {
      void setPage(Math.max(1, totalPages));
    }
  }, [page, totalPages, setPage]);

  if (numberOfRecords === 0) {
    return null;
  }

  return (
    <div className={cn('growfund-mt-4', className)} ref={ref} {...props}>
      <Paginator
        currentPage={currentPage}
        totalItems={total}
        itemsPerPage={limit}
        onPageChange={(index) => {
          void setPage(index);
        }}
      />
    </div>
  );
};

DataTablePagination.displayName = 'DataTablePagination';

export {
    DataTable,
    DataTableContent,
    DataTablePagination,
    DataTableProvider,
    DataTableWrapper,
    DataTableWrapperHeader,
    useDataTableContext
};


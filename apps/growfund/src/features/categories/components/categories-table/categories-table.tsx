import { zodResolver } from '@hookform/resolvers/zod';
import { DotsVerticalIcon, Pencil2Icon } from '@radix-ui/react-icons';
import { createColumnHelper } from '@tanstack/react-table';
import { __, sprintf } from '@wordpress/i18n';
import { Trash2 } from 'lucide-react';
import { parseAsString } from 'nuqs';
import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';

import { CategoryEmptyStateIcon } from '@/app/icons';
import ActiveFilters from '@/components/active-filters';
import BulkDeleteDialog from '@/components/dialogs/bulk-delete-dialog';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { TextField } from '@/components/form/text-field';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import {
    DataTable,
    DataTableContent,
    DataTableWrapper,
    DataTableWrapperHeader,
} from '@/components/table/data-table';
import ThreeDotsOptions from '@/components/three-dots-options';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Form } from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import { CategoryFormDialog } from '@/features/categories/components/dialogs/category-form-dialog';
import { useCategoryFilters } from '@/features/categories/hooks/use-category-filters';
import {
    type CategoryFilter,
    CategoryFilterSchema,
} from '@/features/categories/schemas/categories-filter';
import { type Category } from '@/features/categories/schemas/category';
import {
    useBulkCategoryActionsMutation,
    useGetCategoriesQuery,
} from '@/features/categories/services/category';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { type TableColumnDef } from '@/types';
import { emptyCell, isDefined, noop } from '@/utils';
import { matchQueryStatus } from '@/utils/match-query-status';

const columnHelper = createColumnHelper<Category>();

const CategoriesTable = () => {
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
  const [selectedCategoryIds, setSelectedCategoryIds] = useState<string[]>([]);

  const form = useForm<CategoryFilter>({
    resolver: zodResolver(CategoryFilterSchema),
  });

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      search: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      search: params.search || null,
    }),
    formToParams: (formData) => ({
      search: formData.search ?? null,
    }),
    watchFields: ['search'],
  });

  const categoriesQuery = useGetCategoriesQuery();
  const categoryBulkActionsMutation = useBulkCategoryActionsMutation();

  const { handleClearFilter, handleClearAllFilters, keyMap, valueMap } = useCategoryFilters({
    form,
    syncQueryParams,
    params,
  });

  const handleDelete = useCallback((selectedRows: string[]) => {
    setSelectedCategoryIds(selectedRows);
    setOpenDeleteDialog(true);
  }, []);

  const columns = useMemo(() => {
    return [
      columnHelper.display({
        id: 'select',
        header: ({ table }) => (
          <div className="growfund-ps-2">
            <Checkbox
              checked={
                table.getIsAllPageRowsSelected() ||
                (table.getIsSomePageRowsSelected() && 'indeterminate')
              }
              onCheckedChange={(value) => {
                table.toggleAllPageRowsSelected(!!value);
              }}
              aria-label={__('Select all', 'growfund')}
            />
          </div>
        ),
        cell: ({ row }) => (
          <div className="growfund-ps-2">
            <Checkbox
              checked={row.getIsSelected()}
              data-row-selection-checkbox
              data-row-index={row.index}
              onCheckedChange={() => {
                row.getToggleSelectedHandler();
              }}
              aria-label={__('Select row', 'growfund')}
            />
          </div>
        ),
        size: 40,
        enableSorting: false,
      }),
      columnHelper.accessor('name', {
        header: () => __('Name', 'growfund'),
        cell: (props) => (
          <div
            role="button"
            onKeyDown={noop}
            onClick={() => {
              setEditingCategory(props.row.original);
            }}
          >
            {props.row.original.level > 1 ? (
              <span
                title={props.getValue()}
                className="growfund-text-fg-primary group-hover/row:growfund-text-fg-emphasis hover:growfund-underline growfund-cursor-pointer growfund-break-all growfund-line-clamp-1"
              >
                — {props.getValue()}
              </span>
            ) : (
              <span
                title={props.getValue()}
                className="growfund-text-fg-special-2 group-hover/row:growfund-text-fg-emphasis hover:growfund-underline growfund-cursor-pointer growfund-break-all growfund-line-clamp-1"
              >
                {props.getValue()}
              </span>
            )}
          </div>
        ),
        size: 120,
      }),
      columnHelper.accessor('image', {
        header: () => __('Image', 'growfund'),
        cell: (props) => (
          <div>
            <Image
              src={props.row.original.image?.url ?? null}
              alt={props.row.original.name}
              className="growfund-w-12"
              aspectRatio="square"
            />
          </div>
        ),
        size: 120,
        enableSorting: false,
      }),
      columnHelper.accessor('description', {
        header: () => __('Description', 'growfund'),
        cell: (props) => (
          <div className="growfund-break-all growfund-line-clamp-2">{props.getValue() || emptyCell()}</div>
        ),
        size: 260,
      }),
      columnHelper.accessor('slug', {
        header: () => __('Slug', 'growfund'),
        cell: (props) => (
          <div className="growfund-break-all growfund-line-clamp-1">{props.getValue() || emptyCell()}</div>
        ),
        size: 250,
      }),
      columnHelper.accessor('count', {
        header: () => __('Count', 'growfund'),
        cell: (props) => <div className="growfund-px-2">{props.getValue() || 0}</div>,
        size: 60,
      }),
      columnHelper.display({
        id: 'actions',
        header: () => null,
        cell: (props) => (
          <ThreeDotsOptions
            options={[
              { label: __('Edit', 'growfund'), value: 'edit', icon: Pencil2Icon },
              {
                label: __('Delete', 'growfund'),
                value: 'delete',
                is_critical: true,
                icon: Trash2,
                hidden: props.row.original.is_default,
              },
            ]}
            onOptionSelect={(value) => {
              if (value === 'edit') {
                setEditingCategory(props.row.original);
                return;
              }
              if (value === 'delete') {
                handleDelete([props.row.original.id]);
              }
            }}
          >
            <Button
              variant="ghost"
              size="icon"
              className="growfund-size-6 growfund-opacity-0 group-hover/row:growfund-opacity-100"
            >
              <DotsVerticalIcon />
            </Button>
          </ThreeDotsOptions>
        ),
        size: 40,
      }),
    ] as TableColumnDef<Category>[];
  }, [handleDelete]);

  const categoriesToDelete = useMemo(() => {
    if (!categoriesQuery.data || selectedCategoryIds.length === 0) {
      return [];
    }

    return categoriesQuery.data
      .filter((category) => selectedCategoryIds.includes(category.id))
      .map((category) => ({
        image: category.image?.url ?? null,
        name: category.name,
      }));
  }, [categoriesQuery, selectedCategoryIds]);

  const bulkDeleteTitle = useMemo(() => {
    const count = categoriesToDelete.length;
    return count === 1
      ? __('Delete category', 'growfund')
      : /* translators: %d: number of categories */
        sprintf(__('Delete %d categories', 'growfund'), count);
  }, [categoriesToDelete]);

  return matchQueryStatus(categoriesQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>{__('Error loading categories', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <CategoryEmptyStateIcon />
        <EmptyStateDescription>
          {__('No categories created yet.', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      const filteredData = data.filter((item) =>
        item.name.toLowerCase().includes((params.search ?? '').toLowerCase()),
      );

      return (
        <DataTable
          columns={columns}
          data={filteredData}
          columnPinning={{ left: ['select'], right: ['actions'] }}
        >
          <DataTableWrapper>
            <DataTableWrapperHeader
              actions={[{ label: __('Delete', 'growfund'), value: 'delete' }]}
              onActionChange={(action, selectedRows) => {
                if (action === 'delete') {
                  handleDelete(selectedRows);
                }
              }}
            >
              <div className="growfund-w-full growfund-space-y-4">
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  <Form {...form}>
                    <TextField
                      control={form.control}
                      type="search"
                      name="search"
                      placeholder={__('Search...', 'growfund')}
                      className="growfund-w-40"
                    />
                  </Form>
                </div>
                <ActiveFilters
                  params={params}
                  onClear={handleClearFilter}
                  onClearAll={handleClearAllFilters}
                  keyMap={keyMap}
                  valueMap={valueMap}
                />
              </div>
            </DataTableWrapperHeader>
            <DataTableContent />
          </DataTableWrapper>
          <CategoryFormDialog
            open={isDefined(editingCategory)}
            onOpenChange={() => {
              setEditingCategory(null);
            }}
            defaultValues={editingCategory ?? undefined}
          />
          <BulkDeleteDialog
            type="category"
            title={bulkDeleteTitle}
            description={__(
              'Are you sure you want to delete the selected categories? Once deleted, the action cannot be undone.',
              'growfund',
            )}
            deleteButtonText={__('Delete', 'growfund')}
            open={openDeleteDialog}
            setOpen={setOpenDeleteDialog}
            data={categoriesToDelete}
            onDelete={(closeDialog) => {
              categoryBulkActionsMutation.mutate(
                {
                  ids: selectedCategoryIds,
                  action: 'delete',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Categories deleted successfully', 'growfund'));
                  },
                },
              );
            }}
            loading={categoryBulkActionsMutation.isPending}
          />
        </DataTable>
      );
    },
  });
};

export default CategoriesTable;

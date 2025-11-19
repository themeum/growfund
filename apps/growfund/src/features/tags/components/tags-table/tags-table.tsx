import { zodResolver } from '@hookform/resolvers/zod';
import { DotsVerticalIcon, Pencil2Icon } from '@radix-ui/react-icons';
import { createColumnHelper } from '@tanstack/react-table';
import { __, sprintf } from '@wordpress/i18n';
import { Trash2 } from 'lucide-react';
import { parseAsString } from 'nuqs';
import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';

import { TagEmptyStateIcon } from '@/app/icons';
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
import { TagFormDialog } from '@/features/tags/components/dialogs/tag-form-dialog';
import { useTagFilters } from '@/features/tags/hooks/use-tag-filters';
import { type Tag } from '@/features/tags/schemas/tag';
import { type TagFilter, TagFilterSchema } from '@/features/tags/schemas/tags-filter';
import { useGetTagsQuery, useTagsBulkActionsMutation } from '@/features/tags/services/tag';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { type TableColumnDef } from '@/types';
import { emptyCell, isDefined, noop } from '@/utils';
import { matchQueryStatus } from '@/utils/match-query-status';

const columnHelper = createColumnHelper<Tag>();

const TagsTable = () => {
  const [editingTag, setEditingTag] = useState<Tag | null>(null);
  const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
  const [selectedTagIds, setSelectedTagIds] = useState<string[]>([]);

  const form = useForm<TagFilter>({
    resolver: zodResolver(TagFilterSchema),
  });

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      search: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      search: params.search ?? undefined,
      action: undefined,
    }),
    formToParams: (formData) => ({
      search: formData.search ?? null,
    }),
    watchFields: ['search'],
  });

  const getTagsQuery = useGetTagsQuery();
  const tagBulkActionsMutation = useTagsBulkActionsMutation();

  const { handleClearFilter, handleClearAllFilters, keyMap, valueMap } = useTagFilters({
    form,
    syncQueryParams,
    params,
  });

  const handleDelete = useCallback((selectedRows: string[]) => {
    setSelectedTagIds(selectedRows);
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
          <span
            className="group-hover/row:growfund-text-fg-emphasis hover:growfund-underline growfund-cursor-pointer growfund-break-all growfund-line-clamp-1"
            role="button"
            onKeyDown={noop}
            onClick={() => {
              setEditingTag(props.row.original);
            }}
            title={props.getValue()}
          >
            {props.getValue()}
          </span>
        ),
        size: 240,
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
        cell: (props) => <div className="growfund-px-2">{props.getValue() ?? 0}</div>,
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
              },
            ]}
            onOptionSelect={(value) => {
              if (value === 'edit') {
                setEditingTag({
                  name: props.row.original.name,
                  description: props.row.original.description,
                  slug: props.row.original.slug,
                  id: props.row.original.id,
                });
                return;
              }
              if (value === 'delete') {
                handleDelete([props.row.original.id]);
                return;
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
    ] as TableColumnDef<Tag>[];
  }, [handleDelete]);

  const tagsToDelete = useMemo(() => {
    if (!getTagsQuery.data || selectedTagIds.length === 0) {
      return [];
    }

    return getTagsQuery.data
      .filter((tag) => selectedTagIds.includes(tag.id))
      .map((tag) => ({
        name: tag.name,
      }));
  }, [getTagsQuery, selectedTagIds]);

  const bulkDeleteTitle = useMemo(() => {
    const count = tagsToDelete.length;
    return count == 1
      ? __('Delete tag', 'growfund')
      : /* translators: %d: Number of tags */
        sprintf(__('Delete %d tags', 'growfund'), count);
  }, [tagsToDelete]);

  return matchQueryStatus(getTagsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>{__('Error fetching tags', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <TagEmptyStateIcon />
        <EmptyStateDescription>{__('No tags created yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    Success: (data) => {
      const filteredData = data.data.filter((tag) =>
        tag.name.toLowerCase().includes((params.search ?? '').toLowerCase()),
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
          <TagFormDialog
            open={isDefined(editingTag)}
            onOpenChange={() => {
              setEditingTag(null);
            }}
            defaultValues={editingTag ?? undefined}
          />
          <BulkDeleteDialog
            type="tag"
            title={bulkDeleteTitle}
            description={__(
              'Are you sure you want to delete the selected tags? Once deleted, the action cannot be undone.',
              'growfund',
            )}
            deleteButtonText={__('Delete', 'growfund')}
            open={openDeleteDialog}
            setOpen={setOpenDeleteDialog}
            data={tagsToDelete}
            onDelete={(closeDialog) => {
              tagBulkActionsMutation.mutate(
                {
                  ids: selectedTagIds,
                  action: 'delete',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Tags deleted successfully', 'growfund'));
                  },
                },
              );
            }}
            loading={tagBulkActionsMutation.isPending}
          />
        </DataTable>
      );
    },
  });
};

export default TagsTable;

import { zodResolver } from '@hookform/resolvers/zod';
import { DotsVerticalIcon, Pencil2Icon, ResetIcon } from '@radix-ui/react-icons';
import { createColumnHelper } from '@tanstack/react-table';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Trash, Trash2Icon } from 'lucide-react';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link } from 'react-router';
import { toast } from 'sonner';

import { BackerEmptyStateIcon, ErrorIcon } from '@/app/icons';
import ActiveFilters from '@/components/active-filters';
import BulkDeleteDialog from '@/components/dialogs/bulk-delete-dialog';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import CampaignField from '@/components/form/campaign-field/campaign-field';
import { DatePickerField } from '@/components/form/date-picker-field';
import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import {
  DataTable,
  DataTableContent,
  DataTablePagination,
  DataTableWrapper,
  DataTableWrapperHeader,
} from '@/components/table/data-table';
import ThreeDotsOptions from '@/components/three-dots-options';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Form } from '@/components/ui/form';
import { RouteConfig } from '@/config/route-config';
import { useBackerFilters } from '@/features/backers/hooks/use-backer-filters';
import { type Backer } from '@/features/backers/schemas/backer';
import {
  type BackerFilterForm,
  BackerFilterFormSchema,
} from '@/features/backers/schemas/backer-filter';
import {
  useBackerBulkActionsMutation,
  useBackersQuery,
  useEmptyBackersTrashMutation,
} from '@/features/backers/services/backer';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import ManageBackerDialog from '@/features/pledges/components/dialogs/manage-backer-dialog';
import { useCurrency } from '@/hooks/use-currency';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS, toQueryParamSafe } from '@/lib/date';
import { type TableColumnDef } from '@/types';
import { createAcronym, emptyCell, isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const columnsHelper = createColumnHelper<Backer>();

const BackersTable = () => {
  const [editingBacker, setEditingBacker] = useState<Backer | null>(null);
  const [isDeleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [selectedBackerIds, setSelectedBackerIds] = useState<string[]>([]);
  const { openDialog } = useConsentDialog();
  const { isCollaborator, currentUser } = useCurrentUser();
  const backerBulkActionsMutation = useBackerBulkActionsMutation();
  const emptyBackersTrashMutation = useEmptyBackersTrashMutation();

  const form = useForm<BackerFilterForm>({
    resolver: zodResolver(BackerFilterFormSchema),
  });

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      pg: parseAsInteger.withDefault(1),
      search: parseAsString,
      status: parseAsString,
      campaign_id: parseAsString,
      start_date: parseAsString,
      end_date: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      page: params.pg,
      search: params.search,
      status: params.status,
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search || null,
      campaign_id: formData.campaign_id || null,
      status: formData.status || null,
      start_date: formData.date_range?.from ? toQueryParamSafe(formData.date_range.from) : null,
      end_date: formData.date_range?.to ? toQueryParamSafe(formData.date_range.to) : null,
    }),
    watchFields: ['search', 'date_range', 'status', 'campaign_id'],
  });

  const { handleClearFilter, handleClearAllFilters, keyMap, valueMap } = useBackerFilters({
    form,
    syncQueryParams,
    params,
  });

  const { toCurrency } = useCurrency();

  const backersQuery = useBackersQuery({
    page: params.pg,
    search: params.search ?? undefined,
    status: params.status ?? undefined,
    campaign_id: params.campaign_id ?? undefined,
  });
  const isTrashBackers = params.status === 'trashed';

  const tableBulkActions = useMemo(() => {
    if (isTrashBackers) {
      return [
        { label: __('Delete', 'growfund'), value: 'delete' },
        { label: __('Restore', 'growfund'), value: 'restore' },
      ];
    }

    return [{ label: __('Move to trash', 'growfund'), value: 'trash' }];
  }, [isTrashBackers]);

  const handleMoveToTrash = useCallback((selectedRows: string[]) => {
    setSelectedBackerIds(selectedRows);
    setDeleteDialogOpen(true);
  }, []);

  const handleRestore = useCallback(
    (selectedRows: string[]) => {
      backerBulkActionsMutation.mutate(
        {
          ids: selectedRows,
          action: 'restore',
        },
        {
          onSuccess() {
            toast.success(__('Backers restored successfully.', 'growfund'));
          },
        },
      );
    },
    [backerBulkActionsMutation],
  );

  const handleDeletePermanently = useCallback(
    (selectedRows: string[]) => {
      openDialog({
        title: __('Delete backers permanently', 'growfund'),
        showCheckBox: true,
        checkBoxLabel: __('Delete all associated pledges and records', 'growfund'),
        content: __(
          'Are you sure you want to delete the selected backers permanently? This action cannot be undone.',
          'growfund',
        ),
        confirmText: __('Delete Permanently', 'growfund'),
        confirmButtonVariant: 'destructive',
        declineText: __('Keep', 'growfund'),
        onConfirm: async (closeDialog) => {
          await backerBulkActionsMutation.mutateAsync({
            is_permanent_delete: true,
            ids: selectedRows,
            action: 'delete',
          });
          closeDialog();
          toast.success(__('Backers deleted permanently.', 'growfund'));
        },
      });
    },
    [backerBulkActionsMutation, openDialog],
  );

  const handleEmptyTrash = useCallback(() => {
    openDialog({
      title: __('Empty Trashed Backers', 'growfund'),
      content: __('Are you sure you want to empty the trashed backers?', 'growfund'),
      confirmText: __('Empty Trash', 'growfund'),
      confirmButtonVariant: 'destructive',
      declineText: __('Keep', 'growfund'),
      checkBoxLabel: __('Delete all associated pledges and records', 'growfund'),
      showCheckBox: true,
      onConfirm: async (closeDialog, isChecked) => {
        await emptyBackersTrashMutation.mutateAsync({ delete_associated_pledges: isChecked });
        closeDialog();
      },
    });
  }, [emptyBackersTrashMutation, openDialog]);

  const getRowDropdownActions = useCallback(
    (backer: Backer) => {
      const hasEditPermission = isCollaborator ? backer.created_by === currentUser.id : true;

      if (isTrashBackers) {
        return [
          {
            label: __('Restore', 'growfund'),
            value: 'restore',
            icon: ResetIcon,
            disabled: !hasEditPermission,
          },
          {
            label: __('Delete', 'growfund'),
            value: 'delete',
            icon: Trash2Icon,
            is_critical: true,
            disabled: !hasEditPermission,
          },
          {
            label: __('Edit', 'growfund'),
            value: 'edit',
            icon: Pencil2Icon,
            disabled: !hasEditPermission,
          },
        ];
      }

      return [
        {
          label: __('Edit', 'growfund'),
          value: 'edit',
          icon: Pencil2Icon,
          disabled: !hasEditPermission,
        },
        {
          label: __('Trash', 'growfund'),
          value: 'trash',
          icon: Trash2Icon,
          is_critical: true,
          disabled: !hasEditPermission,
        },
      ];
    },
    [currentUser.id, isCollaborator, isTrashBackers],
  );

  const handleActionSelect = useCallback(
    (action: string, rowBacker: Backer) => {
      if (action === 'edit') {
        setEditingBacker(rowBacker);
        return;
      }

      if (action === 'trash') {
        handleMoveToTrash([rowBacker.id]);
      }

      if (action === 'delete') {
        handleDeletePermanently([rowBacker.id]);
      }

      if (action === 'restore') {
        handleRestore([rowBacker.id]);
      }
    },
    [handleDeletePermanently, handleMoveToTrash, handleRestore],
  );

  const columns = useMemo(() => {
    return [
      columnsHelper.display({
        id: 'selector',
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
        size: 44,
      }),
      columnsHelper.display({
        header: __('Backer Details', 'growfund'),
        cell: ({ row }) => {
          const acronym = createAcronym(row.original);

          return (
            <div className="growfund-flex growfund-items-center growfund-gap-3">
              <div>
                <Avatar>
                  <AvatarImage src={row.original.image?.url} className="growfund-object-cover" />
                  <AvatarFallback>{acronym}</AvatarFallback>
                </Avatar>
              </div>
              <div>
                <Link
                  title={sprintf('%s %s', row.original.first_name, row.original.last_name)}
                  to={RouteConfig.BackerDetails.buildLink({ id: row.original.id })}
                  className="growfund-text-fg-primary growfund-typo-tiny growfund-font-medium group-hover/row:growfund-text-fg-emphasis hover/row:growfund-underline growfund-break-all growfund-line-clamp-1"
                >
                  {sprintf('%s %s', row.original.first_name, row.original.last_name)}
                </Link>
                <p className="growfund-text-fg-secondary">{row.original.email}</p>
              </div>
            </div>
          );
        },
        size: 290,
      }),
      columnsHelper.accessor('number_of_contributions', {
        header: __('Pledges', 'growfund'),
        cell: ({ row }) => (
          <span className="growfund-text-right">{row.original.number_of_contributions}</span>
        ),
        size: 80,
      }),
      columnsHelper.accessor('total_contributions', {
        header: __('Total Given', 'growfund'),
        cell: (props) => (
          <span className="growfund-text-right">{toCurrency(props.getValue())}</span>
        ),
        size: 128,
      }),
      columnsHelper.display({
        id: 'latest_pledge',
        header: __('Latest Pledge', 'growfund'),
        cell: ({ row }) => (
          <span className="growfund-text-right">
            {row.original.latest_pledge_date
              ? format(row.original.latest_pledge_date, DATE_FORMATS.LOCALIZED_DATE_TIME)
              : emptyCell()}
          </span>
        ),
        size: 198,
      }),
      columnsHelper.accessor('joined_at', {
        header: __('Date Created', 'growfund'),
        cell: ({ row }) => (
          <span className="growfund-text-right">
            {row.original.joined_at
              ? format(row.original.joined_at, DATE_FORMATS.LOCALIZED_DATE)
              : emptyCell()}
          </span>
        ),
      }),
      columnsHelper.display({
        id: 'actions',
        header: () => null,
        cell: (props) => {
          const backer = props.row.original;
          return (
            <ThreeDotsOptions
              options={getRowDropdownActions(backer)}
              onOptionSelect={(action) => {
                handleActionSelect(action, backer);
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
          );
        },
        size: 40,
      }),
    ] as TableColumnDef<Backer>[];
  }, [toCurrency, getRowDropdownActions, handleActionSelect]);

  const backersToDelete = useMemo(() => {
    if (!backersQuery.data || selectedBackerIds.length === 0) {
      return [];
    }
    return backersQuery.data.results
      .filter((backer) => {
        return selectedBackerIds.includes(backer.id);
      })
      .map((backer) => ({
        id: backer.id,
        image: backer.image?.url ?? null,
        name: sprintf('%s %s', backer.first_name, backer.last_name),
        email: backer.email,
      }));
  }, [backersQuery.data, selectedBackerIds]);

  const bulkDeleteTitle = useMemo(() => {
    const count = backersToDelete.length;
    return count === 1
      ? __('Move backer to trash', 'growfund')
      : /* translators: %d: number of backers */
        sprintf(__('Move %d Backers to trash', 'growfund'), count);
  }, [backersToDelete]);

  return matchPaginatedQueryStatus(backersQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorIcon />
        <ErrorStateDescription>{__('Error fetching backers', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <BackerEmptyStateIcon />
        <EmptyStateDescription>{__('No backers created yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      const backers = data.results;
      return (
        <>
          <DataTable
            columns={columns}
            data={backers}
            columnPinning={{ left: ['select'], right: ['actions'] }}
            loading={backersQuery.isLoading}
            total={data.total}
            limit={data.per_page}
          >
            <DataTableWrapper className="growfund-mt-10">
              <DataTableWrapperHeader
                className="growfund-w-full"
                actions={tableBulkActions}
                onActionChange={(action, selectedRows) => {
                  if (action === 'trash') {
                    setDeleteDialogOpen(true);
                    setSelectedBackerIds(selectedRows);
                  }
                  if (action === 'restore') {
                    handleRestore(selectedRows);
                    return;
                  }

                  if (action === 'delete') {
                    handleDeletePermanently(selectedRows);
                  }
                }}
              >
                <Form {...form}>
                  <div className="growfund-w-full growfund-space-y-4">
                    <div className="growfund-grid growfund-grid-cols-[8rem_auto] growfund-items-center growfund-justify-between growfund-w-full">
                      <div className="growfund-flex growfund-items-center growfund-gap-2">
                        <CampaignField control={form.control} name="campaign_id" />
                        <SelectField
                          className="growfund-bg-background-white"
                          control={form.control}
                          name="status"
                          placeholder={__('All', 'growfund')}
                          options={[{ value: 'trashed', label: __('Trashed', 'growfund') }]}
                        />
                        {isTrashBackers && (
                          <Button variant="ghost" onClick={handleEmptyTrash}>
                            <Trash />
                            {__('Empty Trash', 'growfund')}
                          </Button>
                        )}
                      </div>

                      <div className="growfund-flex growfund-items-center growfund-gap-3 growfund-min-w-96 growfund-ms-auto">
                        <TextField
                          control={form.control}
                          name="search"
                          type="search"
                          placeholder={__('Search...', 'growfund')}
                          className="growfund-w-full"
                        />
                        <DatePickerField
                          control={form.control}
                          name="date_range"
                          type="range"
                          showRangePresets
                          clearable
                        />
                      </div>
                    </div>
                    <ActiveFilters
                      params={params}
                      onClear={handleClearFilter}
                      onClearAll={handleClearAllFilters}
                      keyMap={keyMap}
                      valueMap={valueMap}
                    />
                  </div>
                </Form>
              </DataTableWrapperHeader>
              <DataTableContent />
            </DataTableWrapper>
            <DataTablePagination />
          </DataTable>
          <ManageBackerDialog
            defaultValues={editingBacker ?? undefined}
            open={isDefined(editingBacker)}
            onOpenChange={() => {
              setEditingBacker(null);
            }}
          />

          <BulkDeleteDialog
            type="contributor"
            title={bulkDeleteTitle}
            description={__(
              'Move the following Backers to trash? You can restore them anytime if needed.',
              'growfund',
            )}
            open={isDeleteDialogOpen}
            setOpen={setDeleteDialogOpen}
            deleteButtonText={__('Move to trash', 'growfund')}
            data={backersToDelete}
            onDelete={(closeDialog) => {
              backerBulkActionsMutation.mutate(
                {
                  ids: selectedBackerIds,
                  action: 'trash',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Backers moved to trash successfully.', 'growfund'));
                  },
                },
              );
            }}
            loading={backerBulkActionsMutation.isPending}
          />
        </>
      );
    },
  });
};

export default BackersTable;

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

import { DonorEmptyStateIcon, ErrorIcon } from '@/app/icons';
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
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import ManageDonorDialog from '@/features/donors/components/dialogs/manage-donor-dialog';
import { useDonorFilters } from '@/features/donors/hooks/use-donor-filters';
import { type Donor } from '@/features/donors/schemas/donor';
import { type DonorFilter, DonorFilterSchema } from '@/features/donors/schemas/donor-filter';
import {
    useDonorBulkActionsMutation,
    useDonorsQuery,
    useEmptyDonorsTrashMutation,
} from '@/features/donors/services/donor';
import { useCurrency } from '@/hooks/use-currency';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { type TableColumnDef } from '@/types';
import { createAcronym, emptyCell, isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const columnsHelper = createColumnHelper<Donor>();

const DonorsTable = () => {
  const { toCurrency } = useCurrency();
  const [editingDonor, setEditingDonor] = useState<Donor | null>(null);
  const [isDeleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [selectedDonorIds, setSelectedDonorIds] = useState<string[]>([]);
  const { openDialog } = useConsentDialog();
  const { isFundraiser, currentUser } = useCurrentUser();

  const donorBulkActionsMutation = useDonorBulkActionsMutation();
  const emptyDonorsTrashMutation = useEmptyDonorsTrashMutation();

  const form = useForm<DonorFilter>({
    resolver: zodResolver(DonorFilterSchema),
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
      search: params.search ?? undefined,
      campaign_id: params.campaign_id ?? undefined,
      status: params.status ?? undefined,
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search ?? null,
      status: formData.status ?? null,
      campaign_id: formData.campaign_id,
      ...(formData.date_range?.from &&
        formData.date_range.to && {
          start_date: format(formData.date_range.from, DATE_FORMATS.DATE),
          end_date: format(formData.date_range.to, DATE_FORMATS.DATE),
        }),
    }),
    watchFields: ['search', 'status', 'campaign_id', 'date_range'],
  });

  const { handleClearFilter, handleClearAllFilters, keyMap, valueMap } = useDonorFilters({
    form,
    syncQueryParams,
    params,
  });

  const handleDeletePermanently = useCallback(
    (selectedRows: string[]) => {
      openDialog({
        showCheckBox: true,
        checkBoxLabel: 'Delete all associated donations',
        title: __('Delete Donors Permanently', 'growfund'),
        content: __(
          'Are you sure you want to delete the selected donors permanently? This action cannot be undone.',
          'growfund',
        ),
        confirmText: __('Delete Permanently', 'growfund'),
        confirmButtonVariant: 'destructive',
        declineText: __('Keep', 'growfund'),
        onConfirm: async (closeDialog) => {
          await donorBulkActionsMutation.mutateAsync({
            is_permanent_delete: true,
            ids: selectedRows,
            action: 'delete',
          });
          closeDialog();
          toast.success(__('Donors deleted permanently.', 'growfund'));
        },
      });
    },
    [donorBulkActionsMutation, openDialog],
  );

  const donorsQuery = useDonorsQuery({
    page: params.pg,
    search: params.search ?? undefined,
    status: params.status ?? undefined,
    campaign_id: params.campaign_id ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
  });
  const isTrashDonors = params.status === 'trashed';

  const tableBulkActions = useMemo(() => {
    if (isTrashDonors) {
      return [
        { label: __('Delete', 'growfund'), value: 'delete' },
        { label: __('Restore', 'growfund'), value: 'restore' },
      ];
    }

    return [{ label: __('Move to trash', 'growfund'), value: 'trash' }];
  }, [isTrashDonors]);

  const handleMoveToTrash = useCallback((selectedRows: string[]) => {
    setSelectedDonorIds(selectedRows);
    setDeleteDialogOpen(true);
  }, []);
  const handleRestore = useCallback(
    (selectedRows: string[]) => {
      donorBulkActionsMutation.mutate(
        {
          ids: selectedRows,
          action: 'restore',
        },
        {
          onSuccess() {
            toast.success(__('Donors restored successfully.', 'growfund'));
          },
        },
      );
    },
    [donorBulkActionsMutation],
  );
  const handleEmptyTrash = useCallback(() => {
    openDialog({
      showCheckBox: true,
      checkBoxLabel: __('Delete all associated donations', 'growfund'),
      title: __('Empty Trashed Donors', 'growfund'),
      content: __(
        'Are you sure you want to empty the trash? This action will remove all the trashed donors',
        'growfund',
      ),
      confirmText: __('Empty Trash', 'growfund'),
      confirmButtonVariant: 'destructive',
      declineText: __('Keep', 'growfund'),
      onConfirm: async (closeDialog, isChecked) => {
        await emptyDonorsTrashMutation.mutateAsync({ delete_associated_donations: isChecked });
        closeDialog();
      },
    });
  }, [emptyDonorsTrashMutation, openDialog]);

  const getRowDropdownActions = useCallback(
    (donor: Donor) => {
      const hasEditPermission = isFundraiser ? donor.created_by === currentUser.id : true;
      if (isTrashDonors) {
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
    [currentUser.id, isFundraiser, isTrashDonors],
  );

  const handleActionSelect = useCallback(
    (action: string, rowDonor: Donor) => {
      if (action === 'edit') {
        setEditingDonor(rowDonor);
        return;
      }

      if (action === 'trash') {
        handleMoveToTrash([rowDonor.id]);
      }

      if (action === 'delete') {
        handleDeletePermanently([rowDonor.id]);
      }

      if (action === 'restore') {
        handleRestore([rowDonor.id]);
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
        header: __('Donor Details', 'growfund'),
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
                  to={RouteConfig.DonorsOverview.buildLink({ id: row.original.id })}
                  title={sprintf('%s %s', row.original.first_name, row.original.last_name)}
                  className="growfund-text-fg-primary growfund-typo-tiny growfund-font-medium group-hover/row:growfund-text-fg-emphasis hover/row:growfund-underline growfund-break-all growfund-line-clamp-1"
                >
                  {sprintf('%s %s', row.original.first_name, row.original.last_name)}
                </Link>
                <p className="growfund-text-fg-secondary">{row.original.email}</p>
              </div>
            </div>
          );
        },
        size: 270,
      }),
      columnsHelper.accessor('number_of_contributions', {
        header: __('Donations', 'growfund'),
        cell: ({ row }) => (
          <span className="growfund-text-right">{row.original.number_of_contributions}</span>
        ),
      }),
      columnsHelper.accessor('total_contributions', {
        header: __('Total Given', 'growfund'),
        cell: ({ row }) => (
          <span className="growfund-text-right">{toCurrency(row.original.total_contributions)}</span>
        ),
      }),
      columnsHelper.accessor('latest_donation_date', {
        header: __('Latest Donation', 'growfund'),
        cell: ({ row }) => (
          <span className="growfund-text-right">
            {row.original.latest_donation_date
              ? format(row.original.latest_donation_date, DATE_FORMATS.LOCALIZED_DATE)
              : emptyCell()}
          </span>
        ),
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
          const rowDonor = props.row.original;
          return (
            <ThreeDotsOptions
              options={getRowDropdownActions(rowDonor)}
              onOptionSelect={(action) => {
                handleActionSelect(action, rowDonor);
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
    ] as TableColumnDef<Donor>[];
  }, [handleActionSelect, getRowDropdownActions, toCurrency]);

  const donorsToDelete = useMemo(() => {
    if (!donorsQuery.data || selectedDonorIds.length === 0) {
      return [];
    }
    return donorsQuery.data.results
      .filter((donor) => {
        return selectedDonorIds.includes(donor.id);
      })
      .map((donor) => ({
        id: donor.id,
        image: donor.image?.url ?? null,
        email: donor.email,
        name: sprintf('%s %s', donor.first_name, donor.last_name),
      }));
  }, [donorsQuery.data, selectedDonorIds]);

  const bulkDeleteTitle = useMemo(() => {
    const count = donorsToDelete.length;
    return count === 1
      ? __('Move Donor to trash', 'growfund')
      : /* translator: %d: number of donors */
        sprintf(__('Move %d Donors to trash', 'growfund'), count);
  }, [donorsToDelete]);

  return matchPaginatedQueryStatus(donorsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState>
        <ErrorIcon />
        <ErrorStateDescription>{__('Error fetching donors', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <DonorEmptyStateIcon />
        <EmptyStateDescription>{__('No donors found.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    Success: (data) => {
      const donors = data.data.results;
      return (
        <>
          <DataTable
            columns={columns}
            data={donors}
            columnPinning={{ left: ['select'], right: ['actions'] }}
            loading={donorsQuery.isLoading}
            total={data.data.total}
            limit={data.data.per_page}
          >
            <DataTableWrapper>
              <DataTableWrapperHeader
                actions={tableBulkActions}
                onActionChange={(action, selectedRows) => {
                  if (action === 'trash') {
                    setDeleteDialogOpen(true);
                    setSelectedDonorIds(selectedRows);
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
                        {isTrashDonors && (
                          <Button variant="ghost" onClick={handleEmptyTrash}>
                            <Trash />
                            {__('Empty Trash', 'growfund')}
                          </Button>
                        )}
                      </div>

                      <div className="growfund-flex growfund-items-center growfund-gap-2">
                        <TextField
                          control={form.control}
                          type="search"
                          name="search"
                          placeholder={__('Search...', 'growfund')}
                        />
                        <DatePickerField
                          control={form.control}
                          name="date_range"
                          type="range"
                          placeholder={__('Date Range', 'growfund')}
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
          <ManageDonorDialog
            defaultValues={editingDonor ?? undefined}
            isOpen={isDefined(editingDonor)}
            onOpenChange={() => {
              setEditingDonor(null);
            }}
          />
          <BulkDeleteDialog
            type="contributor"
            title={bulkDeleteTitle}
            description={__(
              'Move the following donors to trash? You can restore them anytime if needed.',
              'growfund',
            )}
            open={isDeleteDialogOpen}
            setOpen={setDeleteDialogOpen}
            data={donorsToDelete}
            deleteButtonText={__('Move to trash', 'growfund')}
            onDelete={(closeDialog) => {
              donorBulkActionsMutation.mutate(
                {
                  ids: selectedDonorIds,
                  action: 'trash',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Donor moved to trash successfully.', 'growfund'));
                  },
                },
              );
            }}
            loading={donorBulkActionsMutation.isPending}
          />
        </>
      );
    },
  });
};

export default DonorsTable;

import { zodResolver } from '@hookform/resolvers/zod';
import { DotsVerticalIcon, ResetIcon } from '@radix-ui/react-icons';
import { createColumnHelper } from '@tanstack/react-table';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { FolderInput, ScanEye, Trash, Trash2Icon } from 'lucide-react';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router';
import { toast } from 'sonner';

import { DonationEmptyStateIcon, ErrorIcon } from '@/app/icons';
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
import { Badge, type BadgeProps } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Form } from '@/components/ui/form';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import ReassignFundDialog from '@/features/donations/components/dialogs/reassign-fund-dialog';
import { useDonationFilters } from '@/features/donations/hooks/use-donation-filters';
import {
  type Donation,
  type DonationStatus,
  DonationStatusSchema,
} from '@/features/donations/schemas/donation';
import {
  type DonationFiltersForm,
  DonationFiltersFormSchema,
} from '@/features/donations/schemas/donation-filter';
import {
  useDonationBulkActionsMutation,
  useDonationsQuery,
  useEmptyDonationsTrashMutation,
} from '@/features/donations/services/donations';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { useCurrency } from '@/hooks/use-currency';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { registry } from '@/lib/registry';
import { type TableColumnDef } from '@/types';
import { emptyCell } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const columnsHelper = createColumnHelper<Donation>();

const DonationsList = ({ donorId }: { donorId?: string }) => {
  const { appConfig } = useAppConfig();
  const navigate = useNavigate();
  const { toCurrency } = useCurrency();
  const [isOpen, setIsOpen] = useState(false);
  const [isDeleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [selectedDonationIds, setSelectedDonationIds] = useState<string[]>([]);
  const donationBulkActionsMutation = useDonationBulkActionsMutation();
  const emptyDonationsTrashMutation = useEmptyDonationsTrashMutation();
  const { openDialog } = useConsentDialog();

  const form = useForm<DonationFiltersForm>({
    resolver: zodResolver(DonationFiltersFormSchema),
  });

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      pg: parseAsInteger.withDefault(1),
      search: parseAsString,
      status: parseAsString,
      campaign_id: parseAsString,
      fund_id: parseAsString,
      start_date: parseAsString,
      end_date: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      search: params.search ?? undefined,
      status: DonationStatusSchema.safeParse(params.status).data ?? undefined,
      fund_id: params.fund_id ?? undefined,
      campaign_id: params.campaign_id ?? undefined,
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search,
      status: formData.status,
      campaign_id: formData.campaign_id,
      fund_id: formData.fund_id,
      ...(formData.date_range?.from &&
        formData.date_range.to && {
          start_date: format(formData.date_range.from, DATE_FORMATS.DATE),
          end_date: format(formData.date_range.to, DATE_FORMATS.DATE),
        }),
    }),
    watchFields: ['search', 'status', 'campaign_id', 'fund_id', 'date_range'],
  });

  const { handleClearAllFilters, handleClearFilter, keyMap, valueMap } = useDonationFilters({
    params,
    syncQueryParams,
    form,
  });

  const donationsQuery = useDonationsQuery({
    page: params.pg,
    search: params.search ?? undefined,
    status: DonationStatusSchema.safeParse(params.status).data ?? undefined,
    campaign_id: params.campaign_id ?? undefined,
    fund_id: params.fund_id ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
    user_id: donorId,
  });
  const isTrashDonations = params.status === 'trashed';

  const tableBulkActions = useMemo(() => {
    if (isTrashDonations) {
      return [
        { label: __('Delete', 'growfund'), value: 'delete' },
        { label: __('Restore', 'growfund'), value: 'restore' },
      ];
    }

    return [{ label: __('Move to trash', 'growfund'), value: 'trash' }];
  }, [isTrashDonations]);

  const handleRestore = useCallback(
    (selectedRows: string[]) => {
      donationBulkActionsMutation.mutate(
        {
          ids: selectedRows,
          action: 'restore',
        },
        {
          onSuccess() {
            toast.success(__('Donations restored successfully.', 'growfund'));
          },
        },
      );
    },
    [donationBulkActionsMutation],
  );

  const handleMoveToTrash = useCallback((selectedRows: string[]) => {
    setSelectedDonationIds(selectedRows);
    setDeleteDialogOpen(true);
  }, []);

  const handleEmptyTrash = useCallback(() => {
    openDialog({
      title: __('Empty Trashed Donations', 'growfund'),
      content: __(
        'Are you sure you want to empty the trash? This action will remove all the trashed donations',
        'growfund',
      ),
      confirmText: __('Empty Trash', 'growfund'),
      confirmButtonVariant: 'destructive',
      declineText: __('Keep', 'growfund'),
      onConfirm: async (closeDialog) => {
        await emptyDonationsTrashMutation.mutateAsync();
        closeDialog();
      },
    });
  }, [emptyDonationsTrashMutation, openDialog]);

  const handleDeletePermanently = useCallback(
    (selectedRows: string[]) => {
      openDialog({
        title: __('Delete backers permanently', 'growfund'),
        content: __(
          'Are you sure you want to delete the selected donations permanently? This action cannot be undone.',
          'growfund',
        ),
        confirmText: __('Delete Permanently', 'growfund'),
        confirmButtonVariant: 'destructive',
        declineText: __('Keep', 'growfund'),
        onConfirm: async (closeDialog) => {
          await donationBulkActionsMutation.mutateAsync({
            is_permanent_delete: true,
            ids: selectedRows,
            action: 'delete',
          });
          closeDialog();
          toast.success(__('Donations deleted permanently.', 'growfund'));
        },
      });
    },
    [donationBulkActionsMutation, openDialog],
  );

  const rowDropdownActions = useMemo(() => {
    if (isTrashDonations) {
      return [
        { label: __('Restore', 'growfund'), value: 'restore', icon: ResetIcon },
        {
          label: __('Delete', 'growfund'),
          value: 'delete',
          icon: Trash2Icon,
          is_critical: true,
        },
        { label: __('View', 'growfund'), value: 'view', icon: ScanEye },
      ];
    }

    return [
      { label: __('View', 'growfund'), value: 'view', icon: ScanEye },
      {
        label: __('Trash', 'growfund'),
        value: 'trash',
        icon: Trash2Icon,
        is_critical: true,
      },
    ];
  }, [isTrashDonations]);

  const handleActionSelect = useCallback(
    (action: string, donationId: string) => {
      if (action === 'view') {
        void navigate(RouteConfig.EditDonation.buildLink({ id: donationId }));
      }

      if (action === 'trash') {
        handleMoveToTrash([donationId]);
      }

      if (action === 'delete') {
        handleDeletePermanently([donationId]);
      }

      if (action === 'restore') {
        handleRestore([donationId]);
      }
    },
    [handleDeletePermanently, handleMoveToTrash, handleRestore, navigate],
  );

  const columns = useMemo(() => {
    return [
      columnsHelper.display({
        id: 'select',
        header: ({ table }) => {
          return (
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
          );
        },
        cell: ({ row }) => {
          return (
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
          );
        },
        size: 40,
        enableSorting: false,
      }),

      columnsHelper.accessor('id', {
        header: () => __('ID', 'growfund'),
        cell: (props) => {
          return (
            <Link to={RouteConfig.EditDonation.buildLink({ id: props.getValue() })}>
              {sprintf('#%s', props.getValue())}
            </Link>
          );
        },
        size: 62,
      }),
      columnsHelper.accessor('amount', {
        header: () => __('Amount', 'growfund'),
        cell: (props) => (
          <span className="growfund-font-medium growfund-text-fg-brand">
            {toCurrency(props.getValue())}
          </span>
        ),
        size: 98,
      }),

      columnsHelper.accessor('campaign.title', {
        header: 'Campaign',
        cell: (props) => {
          return (
            <Link
              to={RouteConfig.EditDonation.buildLink({ id: props.row.original.id })}
              className="growfund-truncate growfund-block"
              title={props.getValue()}
            >
              {props.getValue()}
            </Link>
          );
        },
        size: 160,
      }),
      columnsHelper.accessor('donor', {
        header: () => __('Donor Name', 'growfund'),
        cell: (props) => {
          const donor = props.getValue();
          return (
            <span>
              {donor.first_name} {donor.last_name}
            </span>
          );
        },
        size: 108,
      }),
      columnsHelper.display({
        id: 'donor-type',
        header: () => __('Donor Type', 'growfund'),
        cell: (props) => {
          const variants = new Map<'registered' | 'guest', BadgeProps['variant']>([
            ['registered', 'secondary'],
            ['guest', 'special'],
          ]);
          const statusTexts = new Map<'registered' | 'guest', string>([
            ['registered', __('Registered', 'growfund')],
            ['guest', __('Guest', 'growfund')],
          ]);

          const donorType =
            Number(props.row.original.donor.id) > 0
              ? 'registered'
              : 'guest';
          return (
            <Badge
              variant={variants.has(donorType) ? variants.get(donorType) : 'outline'}
              className="growfund-capitalize"
            >
              {statusTexts.has(donorType) ? statusTexts.get(donorType) : donorType}
            </Badge>
          );
        },
        size: 98,
      }),
      appConfig[AppConfigKeys.Campaign]?.allow_fund &&
        columnsHelper.accessor('fund.title', {
          header: () => __('Fund', 'growfund'),
          cell: (props) => {
            return <span>{props.getValue()}</span>;
          },
          size: 108,
        }),

      appConfig[AppConfigKeys.Campaign]?.allow_tribute &&
        columnsHelper.display({
          header: __('Tribute', 'growfund'),
          cell: ({ row }) => {
            const tribute = row.original.tribute_type;
            const tributeSalutation = row.original.tribute_salutation;
            const tributeTo = row.original.tribute_to;
            if (!tributeTo) {
              return <span>{emptyCell()}</span>;
            }
            return (
              <p>
                {tribute}
                {tributeTo && (
                  <span className="growfund-text-fg-special-3">
                    {' '}
                    {sprintf('%s %s', tributeSalutation, tributeTo)}
                  </span>
                )}
              </p>
            );
          },
          size: 160,
        }),

      columnsHelper.accessor('status', {
        header: () => __('Status', 'growfund'),
        cell: (props) => {
          const variants = new Map<DonationStatus, BadgeProps['variant']>([
            ['completed', 'primary'],
            ['pending', 'secondary'],
            ['failed', 'destructive'],
            ['cancelled', 'destructive'],
            ['refunded', 'special'],
          ]);
          const statusTexts = new Map<DonationStatus, string>([
            ['completed', __('Completed', 'growfund')],
            ['pending', __('Pending', 'growfund')],
            ['failed', __('Failed', 'growfund')],
            ['cancelled', __('Cancelled', 'growfund')],
            ['refunded', __('Refunded', 'growfund')],
          ]);
          const status = props.row.original.status;

          return (
            <Badge
              variant={variants.has(status) ? variants.get(status) : 'outline'}
              className="growfund-capitalize"
            >
              {statusTexts.has(status) ? statusTexts.get(status) : status}
            </Badge>
          );
        },
        size: 160,
      }),
      columnsHelper.display({
        id: 'actions',
        header: () => null,
        cell: (props) => {
          const donationId = props.row.original.id;
          const status = props.row.original.status;
          return (
            <div className="growfund-flex growfund-gap-2 growfund-opacity-0 group-hover/row:growfund-opacity-100">
              <ThreeDotsOptions
                options={rowDropdownActions.map((action) => ({
                  ...action,
                  disabled: action.value === 'trash' && status !== 'pending',
                }))}
                onOptionSelect={(action) => {
                  handleActionSelect(action, donationId);
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
            </div>
          );
        },
        size: 40,
      }),
      columnsHelper.accessor('created_at', {
        header: () => __('Date', 'growfund'),
        cell: (props) => {
          const date = new Date(props.getValue());
          return date.toLocaleDateString();
        },
        size: 108,
      }),
    ].filter(Boolean) as TableColumnDef<Donation>[];
  }, [toCurrency, handleActionSelect, rowDropdownActions, appConfig]);

  const donationsToDelete = useMemo(() => {
    if (!donationsQuery.data || selectedDonationIds.length === 0) {
      return [];
    }

    return donationsQuery.data.results
      .filter((donation) => selectedDonationIds.includes(donation.id))
      .map((donation) => {
        const first_name = donation.donor.first_name;
        const last_name = donation.donor.last_name;
        const donorName = `${first_name} ${last_name}`;

        return {
          id: donation.id,
          amount: donation.amount,
          name: donorName,
        };
      });
  }, [donationsQuery.data, selectedDonationIds]);

  const getFundsToReassign = useCallback(
    (selectedDonationIds: string[]) => {
      if (!donationsQuery.data || selectedDonationIds.length === 0) {
        return [];
      }
      return donationsQuery.data.results
        .filter((donation) => {
          return selectedDonationIds.includes(donation.id);
        })
        .map((donation) => ({
          id: donation.id,
        }));
    },
    [donationsQuery.data],
  );

  const bulkDeleteTitle = useMemo(() => {
    const count = donationsToDelete.length;
    return count === 1
      ? __('Move Donation to trash', 'growfund')
      : /* translators: %d: number of donations */
        sprintf(__('Move %d Donations to trash', 'growfund'), count);
  }, [donationsToDelete]);

  const FundSelectionField = registry.get('FundSelectionField');

  return matchPaginatedQueryStatus(donationsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState>
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading donations', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <DonationEmptyStateIcon />
        <EmptyStateDescription>{__('No donations made yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      return (
        <>
          <DataTable
            columns={columns}
            data={data.results}
            total={data.total}
            limit={data.per_page}
            columnPinning={{ left: ['select'], right: ['status', 'actions'] }}
          >
            <DataTableWrapper>
              <DataTableWrapperHeader
                actions={tableBulkActions}
                onActionChange={(action, selectedRows) => {
                  if (action === 'trash') {
                    handleMoveToTrash(selectedRows);
                    return;
                  }
                  if (action === 'restore') {
                    handleRestore(selectedRows);
                    return;
                  }

                  if (action === 'delete') {
                    handleDeletePermanently(selectedRows);
                  }
                }}
                secondaryActions={(selectedRows: string[]) => {
                  if (!appConfig[AppConfigKeys.Campaign]?.allow_fund) {
                    return null;
                  }

                  return (
                    <ReassignFundDialog
                      loading={donationBulkActionsMutation.isPending}
                      open={isOpen}
                      onOpenChange={setIsOpen}
                      data={getFundsToReassign(selectedRows)}
                      onReassign={(closeDialog, fundId) => {
                        if (!fundId || selectedRows.length === 0) {
                          return;
                        }

                        donationBulkActionsMutation.mutate(
                          {
                            ids: selectedRows,
                            action: 'reassign_fund',
                            fund_id: fundId,
                          },
                          {
                            onSuccess() {
                              closeDialog();
                              toast.success(
                                __('Reassigned the donations to the selected fund.', 'growfund'),
                              );
                            },
                          },
                        );
                      }}
                    >
                      <Button variant="outline">
                        <FolderInput />
                        {__('Reassign Fund', 'growfund')}
                      </Button>
                    </ReassignFundDialog>
                  );
                }}
              >
                <Form {...form}>
                  <div className="growfund-w-full growfund-space-y-4">
                    <div className="growfund-flex growfund-justify-between growfund-items-center growfund-w-full">
                      <div className="growfund-w-full growfund-flex growfund-items-center growfund-gap-2">
                        <div className="growfund-w-[8.1875rem]">
                          <SelectField
                            className="growfund-bg-background-white"
                            control={form.control}
                            name="status"
                            placeholder={__('All Statuses', 'growfund')}
                            options={[
                              { value: 'pending', label: __('Pending', 'growfund') },
                              { value: 'completed', label: __('Completed', 'growfund') },
                              { value: 'cancelled', label: __('Cancelled', 'growfund') },
                              { value: 'failed', label: __('Failed', 'growfund') },
                              { value: 'trashed', label: __('Trashed', 'growfund') },
                            ]}
                          />
                        </div>
                        <CampaignField control={form.control} name="campaign_id" />
                        {appConfig[AppConfigKeys.Campaign]?.allow_fund && FundSelectionField && (
                          <div className="growfund-w-[9.375rem]">
                            <FundSelectionField />
                          </div>
                        )}
                        {isTrashDonations && (
                          <Button variant="ghost" onClick={handleEmptyTrash}>
                            <Trash />
                            {__('Empty Trash', 'growfund')}
                          </Button>
                        )}
                      </div>

                      <div className="growfund-flex growfund-items-center growfund-gap-3">
                        <TextField
                          control={form.control}
                          type="search"
                          name="search"
                          placeholder={__('Search...', 'growfund')}
                          className="growfund-w-40"
                        />
                        {!isTrashDonations && (
                          <DatePickerField
                            control={form.control}
                            name="date_range"
                            type="range"
                            showRangePresets
                            clearable
                            placeholder={__('Date Range', 'growfund')}
                          />
                        )}
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
          <BulkDeleteDialog
            type="donation"
            title={bulkDeleteTitle}
            description={__(
              'Are you sure you want to move this donation to trash? You can restore it anytime from the trash.',
              'growfund',
            )}
            open={isDeleteDialogOpen}
            setOpen={setDeleteDialogOpen}
            deleteButtonText={__('Move to trash', 'growfund')}
            data={donationsToDelete}
            onDelete={(closeDialog) => {
              donationBulkActionsMutation.mutate(
                {
                  ids: selectedDonationIds,
                  action: 'trash',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Donation moved to trash successfully.', 'growfund'));
                  },
                },
              );
            }}
            loading={donationBulkActionsMutation.isPending}
          />
        </>
      );
    },
  });
};

export default DonationsList;

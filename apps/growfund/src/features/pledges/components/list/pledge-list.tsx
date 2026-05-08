import { zodResolver } from '@hookform/resolvers/zod';
import { DotsVerticalIcon, ResetIcon } from '@radix-ui/react-icons';
import { createColumnHelper } from '@tanstack/react-table';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { ScanEye, Trash, Trash2Icon } from 'lucide-react';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router';
import { toast } from 'sonner';

import { ErrorIcon, PledgeEmptyStateIcon } from '@/app/icons';
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
import { Image } from '@/components/ui/image';
import { RouteConfig } from '@/config/route-config';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { usePledgeFilters } from '@/features/pledges/hooks/use-pledge-filters';
import {
  type Pledge,
  type PledgeStatus,
  PledgeStatusSchema,
} from '@/features/pledges/schemas/pledge';
import { type PledgeFilter, PledgeFilterSchema } from '@/features/pledges/schemas/pledges-filter';
import {
  useEmptyPledgesTrashMutation,
  usePledgesBulkActionsMutation,
  usePledgesQuery,
} from '@/features/pledges/services/pledges';
import { useCurrency } from '@/hooks/use-currency';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { type TableColumnDef } from '@/types';
import { emptyCell } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const columnsHelper = createColumnHelper<Pledge>();

const PledgeList = ({ backerId }: { backerId?: string }) => {
  const [openMoveToTrashDialog, setOpenMoveToTrashDialog] = useState(false);
  const [selectedPledgeIds, setSelectedPledgeIds] = useState<string[]>([]);

  const navigate = useNavigate();
  const { toCurrency } = useCurrency();
  const { openDialog } = useConsentDialog();
  const { isBacker } = useCurrentUser();

  const form = useForm<PledgeFilter>({
    resolver: zodResolver(PledgeFilterSchema),
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
      search: params.search ?? undefined,
      status: PledgeStatusSchema.safeParse(params.status).data ?? undefined,
      campaign_id: params.campaign_id ?? undefined,
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search || null,
      status: formData.status || null,
      campaign_id: formData.campaign_id || null,
      ...(formData.date_range?.from &&
        formData.date_range.to && {
          start_date: format(formData.date_range.from, DATE_FORMATS.DATE),
          end_date: format(formData.date_range.to, DATE_FORMATS.DATE),
        }),
    }),
    watchFields: ['search', 'date_range', 'status', 'campaign_id'],
  });

  const { handleClearFilter, handleClearAllFilters, keyMap, valueMap } = usePledgeFilters({
    form,
    syncQueryParams,
    params,
  });

  const pledgesQuery = usePledgesQuery({
    page: params.pg,
    search: params.search ?? undefined,
    status: PledgeStatusSchema.safeParse(params.status).data ?? undefined,
    campaign_id: params.campaign_id ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
    user_id: backerId,
  });

  const emptyPledgeTrashMutation = useEmptyPledgesTrashMutation();
  const pledgeBulkActionMutation = usePledgesBulkActionsMutation();

  const handleMoveToTrash = useCallback((selectedRows: string[]) => {
    setSelectedPledgeIds(selectedRows);
    setOpenMoveToTrashDialog(true);
  }, []);

  const handleDeletePermanently = useCallback(
    (selectedRows: string[]) => {
      openDialog({
        title: __('Delete pledges permanently', 'growfund'),
        content: __(
          'Are you sure you want to delete the selected pledges permanently? This action cannot be undone.',
          'growfund',
        ),
        confirmText: __('Delete Permanently', 'growfund'),
        confirmButtonVariant: 'destructive',
        declineText: __('Keep', 'growfund'),
        onConfirm: async (closeDialog) => {
          await pledgeBulkActionMutation.mutateAsync({
            ids: selectedRows,
            action: 'delete',
          });
          closeDialog();
          toast.success(__('Pledges deleted permanently.', 'growfund'));
        },
      });
    },
    [pledgeBulkActionMutation, openDialog],
  );

  const handleRestore = useCallback(
    (selectedRows: string[]) => {
      pledgeBulkActionMutation.mutate(
        {
          ids: selectedRows,
          action: 'restore',
        },
        {
          onSuccess() {
            toast.success(__('Pledges restored successfully.', 'growfund'));
          },
        },
      );
    },
    [pledgeBulkActionMutation],
  );

  const handleEmptyTrash = useCallback(() => {
    openDialog({
      title: __('Empty Trashed Pledges', 'growfund'),
      content: __(
        'Are you sure you want to empty the trash? This action will remove all the trashed pledges.',
        'growfund',
      ),
      confirmText: __('Empty Trash', 'growfund'),
      confirmButtonVariant: 'destructive',
      declineText: __('Keep', 'growfund'),
      onConfirm: async (closeDialog) => {
        await emptyPledgeTrashMutation.mutateAsync();
        closeDialog();
      },
    });
  }, [emptyPledgeTrashMutation, openDialog]);

  const pledgesToDelete = useMemo(() => {
    if (!pledgesQuery.data || selectedPledgeIds.length === 0) {
      return [];
    }

    return pledgesQuery.data.results
      .filter((pledge) => {
        return selectedPledgeIds.includes(pledge.id);
      })
      .map((pledge) => ({
        id: pledge.id,
        image: pledge.campaign.images?.[0]?.url ?? null,
        name: sprintf('%s %s', pledge.backer.first_name, pledge.backer.last_name),
        amount: (pledge.payment.amount ?? 0) + (pledge.payment.bonus_support_amount ?? 0),
      }));
  }, [pledgesQuery.data, selectedPledgeIds]);

  const isTrashPledges = params.status === 'trashed';

  const tableBulkActions = useMemo(() => {
    if (isTrashPledges) {
      return [
        { label: __('Delete', 'growfund'), value: 'delete' },
        { label: __('Restore', 'growfund'), value: 'restore' },
      ];
    }

    return [{ label: __('Move to trash', 'growfund'), value: 'trash' }];
  }, [isTrashPledges]);

  const rowDropdownActions = useMemo(() => {
    if (isTrashPledges) {
      return [
        { label: __('Restore', 'growfund'), value: 'restore', icon: ResetIcon },
        {
          label: __('Delete', 'growfund'),
          value: 'delete',
          icon: Trash2Icon,
          is_critical: true,
        },
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
  }, [isTrashPledges]);

  const handleActionSelect = useCallback(
    (action: string, pledgeId: string) => {
      if (action === 'view') {
        void navigate(RouteConfig.EditPledge.buildLink({ id: pledgeId }));
        return;
      }

      if (action === 'trash') {
        handleMoveToTrash([pledgeId]);
      }

      if (action === 'delete') {
        handleDeletePermanently([pledgeId]);
      }

      if (action === 'restore') {
        handleRestore([pledgeId]);
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
          if (isBacker) {
            return sprintf('#%s', props.getValue());
          }

          return (
            <Link
              className="group-hover/row:growfund-text-fg-emphasis hover/row:growfund-underline"
              to={RouteConfig.EditPledge.buildLink({ id: props.getValue() })}
            >
              {sprintf('#%s', props.getValue())}
            </Link>
          );
        },
        size: 60,
      }),

      columnsHelper.accessor('campaign.title', {
        header: 'Campaign Name',
        cell: (props) => {
          if (isBacker) {
            return props.getValue();
          }

          return (
            <Link
              to={RouteConfig.EditPledge.buildLink({ id: props.row.original.id })}
              className="growfund-truncate-2-lines group-hover/row:growfund-text-fg-emphasis hover/row:growfund-underline"
              title={props.getValue()}
            >
              {props.getValue()}
            </Link>
          );
        },
        size: 180,
      }),

      columnsHelper.accessor('payment.amount', {
        header: () => __('Amount', 'growfund'),
        cell: (props) => {
          const amount = props.row.original.payment.total ?? 0;
          return <span className="growfund-font-medium">{toCurrency(Number(amount))}</span>;
        },
        size: 84,
      }),

      columnsHelper.accessor('backer', {
        header: __('Backer', 'growfund'),
        cell: (props) =>
          `${props.row.original.backer.first_name} ${props.row.original.backer.last_name}`,
        size: 130,
      }),

      columnsHelper.accessor('reward.image', {
        header: __('Reward', 'growfund'),
        cell: (props) => {
          return props.row.original.reward ? (
            <div className="growfund-flex growfund-gap-3">
              <Image
                src={props.row.original.reward.image?.url ?? null}
                aspectRatio="square"
                className="growfund-size-8 growfund-shrink-0"
              />
            </div>
          ) : (
            emptyCell()
          );
        },
        size: 60,
        enableSorting: false,
      }),

      columnsHelper.accessor('status', {
        header: () => __('Status', 'growfund'),
        cell: (props) => {
          const variants = new Map<PledgeStatus, BadgeProps['variant']>([
            ['pending', 'info'],
            ['completed', 'primary'],
            ['backed', 'primary'],
            ['failed', 'destructive'],
            ['cancelled', 'warning'],
            ['refunded', 'special'],
            ['trashed', 'destructive'],
            ['in-progress', 'outline'],
          ]);
          const statusTexts = new Map<PledgeStatus, string>([
            ['pending', __('Pending', 'growfund')],
            ['completed', __('Completed', 'growfund')],
            ['backed', __('Backed', 'growfund')],
            ['failed', __('Failed', 'growfund')],
            ['cancelled', __('Cancelled', 'growfund')],
            ['refunded', __('Refunded', 'growfund')],
            ['trashed', __('Trashed', 'growfund')],
            ['in-progress', __('In Progress', 'growfund')],
          ]);
          const id = props.row.original.id;
          const status = props.row.original.status;

          return (
            <div className="growfund-flex growfund-items-center growfund-justify-between">
              <Badge
                variant={variants.has(status) ? variants.get(status) : 'outline'}
                className="growfund-capitalize"
              >
                {statusTexts.has(status) ? statusTexts.get(status) : status}
              </Badge>
              <div className="growfund-flex growfund-gap-2 growfund-opacity-0 group-hover/row:growfund-opacity-100">
                <ThreeDotsOptions
                  options={rowDropdownActions.map((action) => ({
                    ...action,
                    disabled: action.value === 'trash' && status !== 'pending',
                  }))}
                  onOptionSelect={(action) => {
                    handleActionSelect(action, id);
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
            </div>
          );
        },
        size: 120,
      }),

      columnsHelper.accessor('created_at', {
        header: () => __('Date', 'growfund'),
        cell: (props) => {
          const date = new Date(props.getValue());
          return format(date, DATE_FORMATS.HUMAN_READABLE);
        },
        size: 80,
      }),
    ] as TableColumnDef<Pledge>[];
  }, [toCurrency, rowDropdownActions, handleActionSelect, isBacker]);

  return matchPaginatedQueryStatus(pledgesQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState>
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading pledges', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <PledgeEmptyStateIcon />
        <EmptyStateDescription>{__('No pledges made yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      return (
        <DataTable
          columns={columns}
          data={data.results}
          total={data.total}
          columnPinning={{ left: ['select'], right: ['status', 'actions'] }}
          limit={data.per_page}
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
            >
              <Form {...form}>
                <div className="growfund-w-full growfund-space-y-4">
                  <div className="growfund-flex growfund-items-center growfund-justify-between growfund-w-full growfund-gap-2">
                    <div className="growfund-flex growfund-items-center growfund-gap-3">
                      <div className="growfund-flex growfund-items-center growfund-gap-2">
                        <SelectField
                          control={form.control}
                          name="status"
                          placeholder={__('All', 'growfund')}
                          className="growfund-w-40"
                          options={[
                            { value: 'pending', label: __('Pending', 'growfund') },
                            { value: 'in-progress', label: __('In Progress', 'growfund') },
                            { value: 'completed', label: __('Completed', 'growfund') },
                            { value: 'backed', label: __('Backed', 'growfund') },
                            { value: 'failed', label: __('Failed', 'growfund') },
                            { value: 'cancelled', label: __('Cancelled', 'growfund') },
                            { value: 'trashed', label: __('Trashed', 'growfund') },
                          ]}
                        />
                      </div>
                      <CampaignField control={form.control} name="campaign_id" />
                      {isTrashPledges && (
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
                        className="growfund-w-40"
                        placeholder={__('Search...', 'growfund')}
                      />
                      <DatePickerField
                        control={form.control}
                        name="date_range"
                        type="range"
                        showRangePresets
                        placeholder={__('Date Range', 'growfund')}
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
          <BulkDeleteDialog
            type="pledge"
            title={__('Move pledges to trash', 'growfund')}
            description={__(
              'Move the following pledge to trash? You can restore them anytime from the trash.',
              'growfund',
            )}
            deleteButtonText={__('Move to trash', 'growfund')}
            open={openMoveToTrashDialog}
            setOpen={setOpenMoveToTrashDialog}
            data={pledgesToDelete}
            onDelete={(closeDialog) => {
              pledgeBulkActionMutation.mutate(
                {
                  ids: selectedPledgeIds,
                  action: 'trash',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Pledges moved to trash successfully.', 'growfund'));
                  },
                },
              );
            }}
            loading={pledgeBulkActionMutation.isPending}
          />
        </DataTable>
      );
    },
  });
};

export default PledgeList;

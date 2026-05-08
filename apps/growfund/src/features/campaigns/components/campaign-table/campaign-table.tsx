import { zodResolver } from '@hookform/resolvers/zod';
import { StarFilledIcon } from '@radix-ui/react-icons';
import { createColumnHelper } from '@tanstack/react-table';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import {
  Check,
  CheckCheck,
  CheckCircle,
  CircleX,
  Columns3Cog,
  EyeOff,
  FileEdit,
  Hourglass,
  MessageSquareText,
  PartyPopper,
  Star,
  Trash,
  Trophy,
  X,
} from 'lucide-react';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link } from 'react-router';
import { toast } from 'sonner';

import { CampaignDonationEmptyStateIcon, CampaignEmptyStateIcon, ErrorIcon } from '@/app/icons';
import ActiveFilters from '@/components/active-filters';
import BulkDeleteDialog from '@/components/dialogs/bulk-delete-dialog';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
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
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Form } from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import { Progress } from '@/components/ui/progress';
import { getGoalInfo } from '@/config/goal-info';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import CampaignTableDropdownMenu from '@/features/campaigns/components/campaign-table/campaign-table-dropdown-menu';
import DeclineCampaignDialog from '@/features/campaigns/components/dialogs/decline-campaign-dialog';
import DeclineReasonDisplayDialog from '@/features/campaigns/components/dialogs/decline-reason-display-dialog';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { useCampaignFilters } from '@/features/campaigns/hooks/use-campaign-filters';
import { type Campaign, type CampaignStatus } from '@/features/campaigns/schemas/campaign';
import {
  type CampaignFilter,
  CampaignFilterSchema,
} from '@/features/campaigns/schemas/campaign-filter';
import {
  useApproveCampaignMutation,
  useCampaignBulkActionsMutation,
  useCampaignsQuery,
  useEmptyCampaignsTrashMutation,
} from '@/features/campaigns/services/campaign';
import { hasCampaignSuperAccess } from '@/features/campaigns/utils/utils';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { useCurrency } from '@/hooks/use-currency';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { type IconComponent, type TableColumnDef } from '@/types';
import { emptyCell } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';
import { User } from '@/utils/user';

const columnsHelper = createColumnHelper<Campaign>();
const RIGHTMOST_COLUMN_WIDTH = 40;

const Status = ({ status, campaign }: { status: CampaignStatus; campaign: Campaign }) => {
  const { openDialog } = useConsentDialog();
  const campaignId = campaign.id;
  const approveCampaignMutation = useApproveCampaignMutation();

  const iconMap = new Map<
    CampaignStatus | 'hidden' | 'ended' | 'paused',
    {
      icon: IconComponent;
      color: string;
      label: string;
    }
  >([
    [
      'pending',
      {
        icon: Hourglass,
        color: 'growfund-text-icon-caution-hover',
        label: __('Pending', 'growfund'),
      },
    ],
    [
      'published',
      { icon: CheckCircle, color: 'growfund-text-icon-brand', label: __('Published', 'growfund') },
    ],
    [
      'draft',
      { icon: FileEdit, color: 'growfund-text-icon-secondary', label: __('Draft', 'growfund') },
    ],
    [
      'funded',
      {
        icon: PartyPopper,
        color: 'growfund-text-icon-success',
        label: __('Funded', 'growfund'),
      },
    ],
    [
      'completed',
      { icon: CheckCheck, color: 'growfund-text-icon-success', label: __('Completed', 'growfund') },
    ],
    [
      'hidden',
      { icon: EyeOff, color: 'growfund-text-icon-secondary', label: __('Hidden', 'growfund') },
    ],
    [
      'ended',
      { icon: CircleX, color: 'growfund-text-icon-critical', label: __('Ended', 'growfund') },
    ],
    [
      'paused',
      { icon: X, color: 'growfund-text-icon-caution-active', label: __('Paused', 'growfund') },
    ],
  ]);

  if (status === 'pending' && User.isAdmin()) {
    return (
      <div className="growfund-flex growfund-gap-2 growfund-items-center growfund-justify-start">
        <Button
          variant="primary-soft"
          size="icon"
          className="growfund-size-6"
          onClick={() => {
            openDialog({
              title: __('Approve campaign for publishing', 'growfund'),
              content: __(
                'Are you sure you want to publish this campaign? Once published, it will be live and visible to the audience.',
                'growfund',
              ),
              confirmText: __('Approve Campaign', 'growfund'),
              declineText: __('Cancel', 'growfund'),
              onConfirm: async (closeDialog) => {
                await approveCampaignMutation.mutateAsync(campaignId);
                closeDialog();
              },
            });
          }}
        >
          <Check />
        </Button>
        <DeclineCampaignDialog campaignId={campaignId} />
      </div>
    );
  }

  if (status === 'declined') {
    return (
      <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-justify-start">
        <DeclineReasonDisplayDialog
          reason={
            campaign.last_decline_reason ?? __('This campaign is declined by admin', 'growfund')
          }
        >
          <Button variant="destructive" size="icon" className="growfund-size-6">
            <MessageSquareText className="growfund-shrink-0" />
          </Button>
        </DeclineReasonDisplayDialog>
        <span className="growfund-typo-tiny growfund-text-fg-primary">
          {__('Declined', 'growfund')}
        </span>
      </div>
    );
  }

  let content = iconMap.get(status);

  if (status !== 'completed') {
    if (campaign.is_ended) {
      content = iconMap.get('ended');
    } else if (campaign.is_hidden) {
      content = iconMap.get('hidden');
    } else if (campaign.is_paused) {
      content = iconMap.get('paused');
    }
  }

  if (!content) {
    return null;
  }

  const StatusIcon = content.icon;

  return (
    <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-justify-start">
      <StatusIcon className={`growfund-size-4 growfund-shrink-0 ${content.color}`} />
      <span className="growfund-typo-tiny growfund-text-fg-primary">{content.label}</span>
    </div>
  );
};

const CampaignTable = ({ fundraiserId }: { fundraiserId?: string }) => {
  const { appConfig } = useAppConfig();
  const { isFundraiser, isCollaborator, currentUser } = useCurrentUser();
  const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
  const [selectedCampaignIds, setSelectedCampaignIds] = useState<string[]>([]);
  const { isDonationMode } = useAppConfig();

  const { toCurrency } = useCurrency();
  const form = useForm<CampaignFilter>({
    resolver: zodResolver(CampaignFilterSchema),
  });

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      pg: parseAsInteger.withDefault(1),
      search: parseAsString,
      start_date: parseAsString,
      end_date: parseAsString,
      status: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      page: params.pg,
      search: params.search,
      status: params.status as CampaignStatus,
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search || null,
      start_date: formData.date_range?.from
        ? format(formData.date_range.from, DATE_FORMATS.DATE)
        : null,
      end_date: formData.date_range?.to ? format(formData.date_range.to, DATE_FORMATS.DATE) : null,
      status: formData.status || null,
    }),
    watchFields: ['search', 'date_range', 'status'],
  });

  const { handleClearFilter, handleClearAllFilters, keyMap, valueMap } = useCampaignFilters({
    form,
    syncQueryParams,
    params,
  });

  const campaignsQuery = useCampaignsQuery({
    page: params.pg,
    search: params.search ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
    status: params.status ?? 'all',
    fundraiser_id: fundraiserId,
  });
  const campaignBulkActionsMutation = useCampaignBulkActionsMutation();
  const emptyCampaignsTrashMutation = useEmptyCampaignsTrashMutation();

  const { openDialog } = useConsentDialog();

  const isTrashCampaigns = params.status === 'trashed';

  const getSelectedCampaignIds = useCallback(
    (selectedRows: string[]) => {
      if (!campaignsQuery.data || selectedRows.length === 0) {
        return [];
      }

      const campaignIds = campaignsQuery.data.results
        .filter((campaign) => {
          return selectedRows.includes(campaign.id);
        })
        .filter((campaign) => {
          return hasCampaignSuperAccess(campaign, currentUser);
        })
        .map((campaign) => campaign.id);

      return campaignIds;
    },
    [campaignsQuery.data, currentUser],
  );

  const handleMoveToTrash = useCallback(
    (selectedRows: string[]) => {
      setSelectedCampaignIds(getSelectedCampaignIds(selectedRows));
      setOpenDeleteDialog(true);
    },
    [getSelectedCampaignIds],
  );

  const handleDeletePermanently = useCallback(
    (selectedRows: string[]) => {
      openDialog({
        title: __('Delete campaigns permanently', 'growfund'),
        content: __(
          'Are you sure you want to delete the selected campaigns permanently? This action cannot be undone.',
          'growfund',
        ),
        confirmText: __('Delete Permanently', 'growfund'),
        confirmButtonVariant: 'destructive',
        declineText: __('Keep', 'growfund'),
        onConfirm: async (closeDialog) => {
          await campaignBulkActionsMutation.mutateAsync({
            ids: getSelectedCampaignIds(selectedRows),
            action: 'delete',
          });
          closeDialog();
          toast.success(__('Campaigns deleted permanently.', 'growfund'));
        },
      });
    },
    [campaignBulkActionsMutation, getSelectedCampaignIds, openDialog],
  );

  const handleRestore = useCallback(
    (selectedRows: string[]) => {
      campaignBulkActionsMutation.mutate(
        {
          ids: getSelectedCampaignIds(selectedRows),
          action: 'restore',
        },
        {
          onSuccess() {
            toast.success(__('Campaigns restored successfully.', 'growfund'));
          },
        },
      );
    },
    [campaignBulkActionsMutation, getSelectedCampaignIds],
  );

  const handleEmptyTrash = useCallback(() => {
    openDialog({
      title: __('Empty Trashed Campaigns', 'growfund'),
      content: __(
        'Are you sure you want to empty the trash? This action will remove all the trashed campaigns.',
        'growfund',
      ),
      confirmText: __('Empty Trash', 'growfund'),
      confirmButtonVariant: 'destructive',
      declineText: __('Keep', 'growfund'),
      onConfirm: async (closeDialog) => {
        await emptyCampaignsTrashMutation.mutateAsync();
        closeDialog();
      },
    });
  }, [emptyCampaignsTrashMutation, openDialog]);

  const columns = useMemo(() => {
    return [
      columnsHelper.display({
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
      columnsHelper.accessor('id', {
        header: () => __('ID', 'growfund'),
        cell: (props) => (
          <Link
            to={RouteConfig.CampaignBuilder.buildLink({ id: props.row.original.id })}
            className="growfund-cursor-pointer group-hover/row:growfund-text-fg-emphasis hover/row:growfund-underline"
          >
            #{props.getValue()}
          </Link>
        ),
        size: 60,
      }),
      columnsHelper.accessor('is_featured', {
        header: () => __('Featured', 'growfund'),
        cell: (props) => {
          const isFeatured = props.row.original.is_featured;
          return (
            <div className="growfund-text-icon-caution-active">
              <Button
                variant="ghost"
                size="icon"
                className="growfund-size-6"
                disabled={!hasCampaignSuperAccess(props.row.original, currentUser)}
                onClick={() => {
                  campaignBulkActionsMutation.mutate({
                    ids: [props.row.original.id],
                    action: props.row.original.is_featured ? 'non-featured' : 'featured',
                  });
                }}
                title={
                  isFeatured
                    ? __('Remove from featured', 'growfund')
                    : __('Make featured', 'growfund')
                }
              >
                {isFeatured ? (
                  <StarFilledIcon className="!growfund-size-5 growfund-text-icon-caution-active" />
                ) : (
                  <Star className="growfund-size-4 growfund-text-icon-primary" strokeWidth={1} />
                )}
              </Button>
            </div>
          );
        },
        size: 90,
        enableSorting: false,
      }),
      columnsHelper.accessor('title', {
        header: () => __('Campaign Name', 'growfund'),
        cell: (props) => (
          <Link
            className="growfund-flex growfund-items-center growfund-gap-2 growfund-cursor-pointer group-hover/row:growfund-text-fg-emphasis hover/row:growfund-underline"
            to={RouteConfig.CampaignBuilder.buildLink({ id: props.row.original.id })}
          >
            <Image
              src={props.row.original.images?.[0]?.url ?? null}
              className="growfund-size-8 growfund-flex-shrink-0"
              alt={props.row.original.title}
            />
            <span className="growfund-truncate" title={props.getValue()}>
              {props.getValue()}
            </span>
          </Link>
        ),
        size: 240,
      }),
      columnsHelper.display({
        id: 'author',
        header: () => __('Creator', 'growfund'),
        cell: ({ row }) => row.original.author?.display_name || emptyCell(),
        enableHiding: true,
      }),
      columnsHelper.display({
        id: 'goal',
        header: () => __('Goal', 'growfund'),
        cell: ({ row }) => {
          const goalInfo = getGoalInfo(row.original, isDonationMode, toCurrency);
          if (!goalInfo || !row.original.has_goal) {
            return (
              <div className="growfund-typo-tiny growfund-text-fg-secondary">
                {__('-No Goal Set-', 'growfund')}
              </div>
            );
          }

          return (
            <div className="growfund-grid growfund-gap-1">
              <div className="growfund-flex growfund-items-center growfund-gap-1 growfund-typo-tiny growfund-text-fg-primary">
                {row.original.status === 'funded' && (
                  <Trophy className="growfund-size-3 growfund-text-icon-brand" />
                )}

                {/* translators: %s: goal progress */}
                {sprintf(__('%s funded', 'growfund'), `${goalInfo.progress_percentage}%`)}
              </div>
              <Progress size="sm" value={goalInfo.progress_percentage} />
              <div
                className="growfund-text-fg-secondary"
                dangerouslySetInnerHTML={{ __html: goalInfo.goal_label }}
              />
            </div>
          );
        },
        size: 200,
      }),
      columnsHelper.accessor('number_of_contributors', {
        header: () => (isDonationMode ? __('Donations', 'growfund') : __('Pledges', 'growfund')),
        cell: ({ row }) => {
          const value = row.original.number_of_contributors;
          return (
            <div className="growfund-typo-tiny growfund-text-fg-primary growfund-px-2">
              {value ?? emptyCell()}
            </div>
          );
        },
        size: 90,
      }),
      columnsHelper.display({
        id: 'status',
        header: () => <div className="growfund-w-full">{__('Status', 'growfund')}</div>,
        cell: ({ row }) => {
          const status = row.original.status;

          return <Status status={status} campaign={row.original} />;
        },
        size: 100,
      }),
      columnsHelper.display({
        id: 'actions',
        header: ({ table }) => {
          const columnsDisplayNames = new Map([
            ['id', __('ID', 'growfund')],
            ['title', __('Campaign Name', 'growfund')],
            ['author', __('Creator', 'growfund')],
            ['goal', __('Goal', 'growfund')],
            [
              'number_of_contributors',
              isDonationMode ? __('Donations', 'growfund') : __('Pledges', 'growfund'),
            ],
            ['status', __('Status', 'growfund')],
          ]);
          const columns = table
            .getAllColumns()
            .filter((column) => column.getCanHide() && columnsDisplayNames.has(column.id));

          return (
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-6 focus-visible:growfund-ring-0 focus-visible:growfund-ring-offset-0 [&[data-state=open]]:growfund-bg-background-fill-hover"
                >
                  <Columns3Cog className="!growfund-size-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                {columns.map((column) => {
                  return (
                    <DropdownMenuCheckboxItem
                      key={column.id}
                      className="growfund-capitalize"
                      checked={column.getIsVisible()}
                      onCheckedChange={(checked) => {
                        column.toggleVisibility(!!checked);
                      }}
                    >
                      {columnsDisplayNames.get(column.id)}
                    </DropdownMenuCheckboxItem>
                  );
                })}
              </DropdownMenuContent>
            </DropdownMenu>
          );
        },
        cell: ({ row }) => {
          return (
            <CampaignTableDropdownMenu
              className="growfund-opacity-0 group-hover/row:growfund-opacity-100"
              row={row}
              isTrashCampaigns={isTrashCampaigns}
              onMoveToTrash={() => {
                handleMoveToTrash([row.original.id]);
              }}
              onDeletePermanently={() => {
                handleDeletePermanently([row.original.id]);
              }}
              onRestore={() => {
                handleRestore([row.original.id]);
              }}
            />
          );
        },
        size: RIGHTMOST_COLUMN_WIDTH,
      }),
    ] as TableColumnDef<Campaign>[];
  }, [
    currentUser,
    campaignBulkActionsMutation,
    isDonationMode,
    toCurrency,
    isTrashCampaigns,
    handleMoveToTrash,
    handleDeletePermanently,
    handleRestore,
  ]);

  const campaignsToDelete = useMemo(() => {
    if (!campaignsQuery.data || selectedCampaignIds.length === 0) {
      return [];
    }

    return campaignsQuery.data.results
      .filter((campaign) => {
        return selectedCampaignIds.includes(campaign.id);
      })
      .filter((campaign) => {
        return hasCampaignSuperAccess(campaign, currentUser);
      })
      .map((campaign) => ({
        id: campaign.id,
        image: campaign.images?.[0]?.url ?? null,
        name: campaign.title,
      }));
  }, [campaignsQuery.data, currentUser, selectedCampaignIds]);

  const canDeletePermanently = useMemo(() => {
    return isFundraiser
      ? appConfig[AppConfigKeys.UserAndPermissions]?.fundraisers_can_delete_campaigns
      : !isCollaborator;
  }, [appConfig, isFundraiser, isCollaborator]);

  const actions = useMemo(() => {
    if (isCollaborator) {
      return [];
    }

    if (isTrashCampaigns) {
      if (!canDeletePermanently) {
        return [{ label: __('Restore', 'growfund'), value: 'restore' }];
      }

      return [
        { label: __('Restore', 'growfund'), value: 'restore' },
        { label: __('Delete', 'growfund'), value: 'delete', is_critical: true },
      ];
    }

    return [
      { label: __('Make featured', 'growfund'), value: 'featured' },
      {
        label: __('Remove from featured', 'growfund'),
        value: 'non-featured',
        has_separator_next: true,
      },
      { label: __('Move to trash', 'growfund'), value: 'trash', is_critical: true },
    ];
  }, [canDeletePermanently, isTrashCampaigns, isCollaborator]);

  return matchPaginatedQueryStatus(campaignsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState>
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading campaigns', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        {isDonationMode ? <CampaignDonationEmptyStateIcon /> : <CampaignEmptyStateIcon />}
        <EmptyStateDescription>
          {__(`You haven't added any campaign yet.`, 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (data) => {
      const campaigns = data.data;
      return (
        <DataTable
          data={campaigns.results}
          columns={columns}
          columnPinning={{ left: ['select'], right: ['status', 'actions'] }}
          total={campaigns.total}
          limit={campaigns.per_page}
          loading={campaignsQuery.isFetching}
        >
          <DataTableWrapper>
            <DataTableWrapperHeader
              actions={actions}
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
                  return;
                }

                if (['featured', 'non-featured'].includes(action)) {
                  campaignBulkActionsMutation.mutate({
                    ids: selectedRows,
                    action: action as 'featured' | 'non-featured',
                  });
                  return;
                }
              }}
            >
              <Form {...form}>
                <div className="growfund-w-full growfund-space-y-4">
                  <div className="growfund-grid growfund-grid-cols-[8rem_auto] growfund-items-center growfund-justify-between growfund-w-full">
                    <div className="growfund-flex growfund-items-center growfund-gap-2">
                      <SelectField
                        control={form.control}
                        name="status"
                        placeholder={__('All', 'growfund')}
                        options={[
                          { label: __('Published', 'growfund'), value: 'published' },
                          { label: __('Draft', 'growfund'), value: 'draft' },
                          { label: __('Pending', 'growfund'), value: 'pending' },
                          { label: __('Launched', 'growfund'), value: 'launched' },
                          { label: __('Funded', 'growfund'), value: 'funded' },
                          { label: __('Completed', 'growfund'), value: 'completed' },
                          { label: __('Declined', 'growfund'), value: 'declined' },
                          { label: __('Trashed', 'growfund'), value: 'trashed' },
                        ]}
                      />

                      {isTrashCampaigns && canDeletePermanently && (
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
                        clearable
                        showRangePresets
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
            type="campaign"
            title={__('Move campaigns to trash', 'growfund')}
            description={__(
              'Move the following campaign to trash? You can restore them anytime from the trash.',
              'growfund',
            )}
            deleteButtonText={__('Move to trash', 'growfund')}
            open={openDeleteDialog}
            setOpen={setOpenDeleteDialog}
            data={campaignsToDelete}
            onDelete={(closeDialog) => {
              campaignBulkActionsMutation.mutate(
                {
                  ids: selectedCampaignIds,
                  action: 'trash',
                },
                {
                  onSuccess() {
                    closeDialog();
                    toast.success(__('Campaigns moved to trash successfully.', 'growfund'));
                  },
                },
              );
            }}
            loading={campaignBulkActionsMutation.isPending}
          />
        </DataTable>
      );
    },
  });
};

export default CampaignTable;

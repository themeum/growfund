import { DotsVerticalIcon } from '@radix-ui/react-icons';
import { type Row } from '@tanstack/react-table';
import { __ } from '@wordpress/i18n';
import React, { forwardRef, useMemo, useState } from 'react';
import { useNavigate } from 'react-router';
import { toast } from 'sonner';

import BulkDeleteDialog from '@/components/dialogs/bulk-delete-dialog';
import FeatureGuard from '@/components/feature-guard';
import CampaignTableDropdownMenuItemFallback from '@/components/pro-fallbacks/campaign/campaign-table-dropdown-menu-item-fallback';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { CampaignsDropdownOptionsProvider } from '@/features/campaigns/contexts/campaigns-dropdown-options-context';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import {
    useCampaignBulkActionsMutation,
    useDeleteCampaignMutation,
} from '@/features/campaigns/services/campaign';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import useCurrentUser from '@/hooks/use-current-user';
import { registry } from '@/lib/registry';

interface CampaignTableDropdownMenuProps extends React.HTMLAttributes<HTMLDivElement> {
  row: Row<Campaign>;
  isTrashCampaigns?: boolean;
  onDeletePermanently?: () => void;
  onMoveToTrash?: () => void;
  onRestore?: () => void;
}

const CampaignTableDropdownMenu = forwardRef<HTMLDivElement, CampaignTableDropdownMenuProps>(
  (
    {
      row,
      isTrashCampaigns = false,
      onDeletePermanently,
      onMoveToTrash,
      onRestore,
      className,
      ...props
    }: CampaignTableDropdownMenuProps,
    ref,
  ) => {
    const { appConfig } = useAppConfig();
    const { isFundraiser } = useCurrentUser();
    const navigate = useNavigate();
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [openDeleteDialog, setOpenDeleteDialog] = useState(false);

    const deleteCampaignMutation = useDeleteCampaignMutation();
    const campaignBulkActionsMutation = useCampaignBulkActionsMutation();

    const canDeletePermanently = useMemo(() => {
      return isFundraiser
        ? appConfig[AppConfigKeys.UserAndPermissions]?.fundraisers_can_delete_campaigns
        : true;
    }, [appConfig, isFundraiser]);

    const CampaignTableDropdownPostUpdateItem = registry.get('CampaignTableDropdownPostUpdateItem');
    const CampaignTableDropdownMakeCopyItem = registry.get('CampaignTableDropdownMakeCopyItem');

    const campaign = {
      id: row.original.id,
      title: row.original.title,
      image: row.original.images?.[0]?.url ?? null,
      created_by: row.original.created_by,
    };

    return (
      <div ref={ref} className={className} {...props}>
        <DropdownMenu open={isDropdownOpen} onOpenChange={setIsDropdownOpen}>
          <DropdownMenuTrigger asChild>
            <Button variant="secondary" size="icon" className="growfund-size-6">
              <DotsVerticalIcon />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start">
            <FeatureGuard
              feature="campaign.post_an_update"
              fallback={
                <CampaignTableDropdownMenuItemFallback label={__('Post an update', 'growfund')} />
              }
            >
              {CampaignTableDropdownPostUpdateItem && (
                <CampaignsDropdownOptionsProvider
                  campaign={campaign}
                  setIsDropdownOpen={setIsDropdownOpen}
                >
                  <CampaignTableDropdownPostUpdateItem />
                </CampaignsDropdownOptionsProvider>
              )}
            </FeatureGuard>

            <DropdownMenuItem
              onClick={() => {
                void navigate(RouteConfig.CampaignDetails.buildLink({ id: row.original.id }));
              }}
            >
              {__('Overview', 'growfund')}
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => window.open(row.original.preview_url ?? '', '_blank')}>
              {__('Preview', 'growfund')}
            </DropdownMenuItem>
            <FeatureGuard
              feature="campaign.make_a_copy"
              fallback={
                <CampaignTableDropdownMenuItemFallback label={__('Make a copy', 'growfund')} />
              }
            >
              {CampaignTableDropdownMakeCopyItem && (
                <CampaignsDropdownOptionsProvider
                  campaign={campaign}
                  setIsDropdownOpen={setIsDropdownOpen}
                >
                  {' '}
                  <CampaignTableDropdownMakeCopyItem />
                </CampaignsDropdownOptionsProvider>
              )}
            </FeatureGuard>
            {isTrashCampaigns ? (
              <>
                <DropdownMenuItem onClick={onRestore}>{__('Restore', 'growfund')}</DropdownMenuItem>
                {canDeletePermanently && (
                  <>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      className="growfund-text-fg-critical hover:!growfund-text-fg-critical"
                      onClick={onDeletePermanently}
                    >
                      {__('Delete permanently', 'growfund')}
                    </DropdownMenuItem>
                  </>
                )}
              </>
            ) : (
              <>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  className="growfund-text-fg-critical hover:!growfund-text-fg-critical"
                  onClick={onMoveToTrash}
                >
                  {__('Move to trash', 'growfund')}
                </DropdownMenuItem>
              </>
            )}
          </DropdownMenuContent>
        </DropdownMenu>
        <BulkDeleteDialog
          type="campaign"
          title={__('Move to trash', 'growfund')}
          description={__(
            'Are you sure you want to move this campaign to trash? You can restore it anytime from the trash.',
            'growfund',
          )}
          open={openDeleteDialog}
          setOpen={setOpenDeleteDialog}
          data={[
            {
              id: row.original.id,
              name: row.original.title,
              image: row.original.images?.[0]?.url ?? null,
            },
          ]}
          onDelete={(closeDialog) => {
            campaignBulkActionsMutation.mutate(
              {
                ids: [row.original.id],
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
          loading={deleteCampaignMutation.isPending}
        />
      </div>
    );
  },
);

export default CampaignTableDropdownMenu;

import { __ } from '@wordpress/i18n';
import { Edit } from 'lucide-react';
import { useState } from 'react';
import { useNavigate } from 'react-router';

import { BackerEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { Container } from '@/components/layouts/container';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { growfundConfig } from '@/config/growfund';
import { RouteConfig } from '@/config/route-config';
import { MakeFundraiserDialogProvider } from '@/contexts/make-fundraiser-dialog-context';
import BackerDetailsTabs from '@/features/backers/components/backer-details-tabs';
import { BackerProvider } from '@/features/backers/contexts/backer';
import { type BackerInfo } from '@/features/backers/schemas/backer';
import { useGetBackerOverview } from '@/features/backers/services/backer';
import ManageBackerDialog from '@/features/pledges/components/dialogs/manage-backer-dialog';
import useCurrentUser from '@/hooks/use-current-user';
import { useRouteParams } from '@/hooks/use-route-params';
import { registry } from '@/lib/registry';
import { matchQueryStatus } from '@/utils/match-query-status';

const BackerDetailsLayout = () => {
  const { id } = useRouteParams(RouteConfig.BackerDetails);
  const { isFundraiser, currentUser, isAdmin } = useCurrentUser();
  const backerOverviewQuery = useGetBackerOverview(id);
  const [editingBacker, setEditingBacker] = useState<BackerInfo | null>(null);
  const [open, setOpen] = useState(false);
  const [isDialogOpen, setOpenDialog] = useState(false);
  const MakeFundraiserDialog = registry.get('MakeFundraiserDialog');

  const navigate = useNavigate();

  return matchQueryStatus(backerOverviewQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>
          <BackerEmptyStateIcon />
          <div>{__('Backer not found.', 'growfund')}</div>
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          <BackerEmptyStateIcon />
          <div>{__('Backer not found.', 'growfund')}</div>
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (response) => {
      const backer = response.data;
      const hasEditPermission = isFundraiser
        ? backer.backer_information.created_by === currentUser.id
        : true;
      const showMakeFundraiserButton = isAdmin && !backer.backer_information.is_fundraiser;
      return (
        <Page>
          <PageHeader
            name={`${backer.backer_information.first_name} ${backer.backer_information.last_name}`}
            action={
              hasEditPermission && (
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  {growfundConfig.has_growfund_pro && showMakeFundraiserButton && (
                    <>
                      <Button
                        variant="outline"
                        onClick={() => {
                          setOpenDialog(true);
                        }}
                      >
                        {__('Make Fundraiser', 'growfund')}
                      </Button>
                      {MakeFundraiserDialog && (
                        <MakeFundraiserDialogProvider
                          isOpen={isDialogOpen}
                          onOpenChange={(open) => {
                            setOpenDialog(open);
                          }}
                          user={{
                            id: backer.backer_information.id,
                            first_name: backer.backer_information.first_name,
                            last_name: backer.backer_information.last_name,
                            email: backer.backer_information.email,
                            image: backer.backer_information.image,
                          }}
                        >
                          <MakeFundraiserDialog />
                        </MakeFundraiserDialogProvider>
                      )}
                    </>
                  )}

                  <ManageBackerDialog
                    open={open}
                    onOpenChange={setOpen}
                    defaultValues={editingBacker ?? undefined}
                  >
                    <Button
                      variant="outline"
                      onClick={() => {
                        setOpen(true);
                        setEditingBacker(backer.backer_information);
                      }}
                    >
                      <Edit />
                      {__('Edit Profile', 'growfund')}
                    </Button>
                  </ManageBackerDialog>
                </div>
              )
            }
            onGoBack={() => navigate(RouteConfig.Backers.buildLink())}
          />
          <PageContent>
            <Container className="growfund-mt-8">
              <BackerProvider backerOverview={backer}>
                <BackerDetailsTabs />
              </BackerProvider>
            </Container>
          </PageContent>
        </Page>
      );
    },
  });
};

export default BackerDetailsLayout;

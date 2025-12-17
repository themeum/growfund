import { Pencil2Icon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useNavigate } from 'react-router';

import { DonorEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { Container } from '@/components/layouts/container';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { growfundConfig } from '@/config/growfund';
import { RouteConfig } from '@/config/route-config';
import { MakeFundraiserDialogProvider } from '@/contexts/make-fundraiser-dialog-context';
import ManageDonorDialog from '@/features/donors/components/dialogs/manage-donor-dialog';
import DonorDetailsTabs from '@/features/donors/components/donor-details/donor-details-tabs';
import { DonorProvider } from '@/features/donors/contexts/donor';
import { useGetDonorOverview } from '@/features/donors/services/donor';
import useCurrentUser from '@/hooks/use-current-user';
import { useRouteParams } from '@/hooks/use-route-params';
import { registry } from '@/lib/registry';
import { matchQueryStatus } from '@/utils/match-query-status';

const DonorDetailsLayout = () => {
  const { id } = useRouteParams(RouteConfig.DonorDetails);
  const donorOverviewQuery = useGetDonorOverview(id);
  const navigate = useNavigate();
  const [isOpenProfileEditor, setOpenProfileEditor] = useState(false);
  const { isFundraiser, isAdmin, currentUser } = useCurrentUser();
  const [isOpen, setOpen] = useState(false);

  const MakeFundraiserDialog = registry.get('MakeFundraiserDialog');

  return matchQueryStatus(donorOverviewQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>
          <DonorEmptyStateIcon />
          <div>{__('Donor not found.', 'growfund')}</div>
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          <DonorEmptyStateIcon />
          <div>{__('Donor not found.', 'growfund')}</div>
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (response) => {
      const donor = response.data;
      const hasEditPermission = isFundraiser ? donor.profile.created_by === currentUser.id : true;
      const showMakeFundraiserButton = isAdmin && !donor.profile.is_fundraiser;

      return (
        <Page>
          <PageHeader
            name={`${donor.profile.first_name} ${donor.profile.last_name}`}
            action={
              hasEditPermission && (
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  {growfundConfig.has_growfund_pro && showMakeFundraiserButton && (
                    <>
                      <Button
                        variant="outline"
                        onClick={() => {
                          setOpen(true);
                        }}
                      >
                        {__('Make Fundraiser', 'growfund')}
                      </Button>
                      {MakeFundraiserDialog && (
                        <MakeFundraiserDialogProvider
                          isOpen={isOpen}
                          onOpenChange={(open) => {
                            setOpen(open);
                          }}
                          user={{
                            id: donor.profile.id,
                            first_name: donor.profile.first_name,
                            last_name: donor.profile.last_name,
                            email: donor.profile.email,
                            image: donor.profile.image ?? undefined,
                          }}
                        >
                          <MakeFundraiserDialog />
                        </MakeFundraiserDialogProvider>
                      )}
                    </>
                  )}

                  <ManageDonorDialog
                    defaultValues={donor.profile}
                    isOpen={isOpenProfileEditor}
                    onOpenChange={setOpenProfileEditor}
                  >
                    <Button
                      variant="outline"
                      onClick={() => {
                        setOpenProfileEditor(true);
                      }}
                    >
                      <Pencil2Icon />
                      {__('Edit Profile', 'growfund')}
                    </Button>
                  </ManageDonorDialog>
                </div>
              )
            }
            onGoBack={() => navigate(RouteConfig.Donors.buildLink())}
          />
          <PageContent>
            <Container className="growfund-mt-8">
              <DonorProvider donorOverview={donor}>
                <DonorDetailsTabs />
              </DonorProvider>
            </Container>
          </PageContent>
        </Page>
      );
    },
  });
};

export default DonorDetailsLayout;

import { __ } from '@wordpress/i18n';
import { UserCog2 } from 'lucide-react';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';
import { useAppConfig } from '@/contexts/app-config';

const FundraiserEmailFieldsFallback = () => {
  const { isDonationMode } = useAppConfig();
  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <div className="growfund-flex growfund-items-center growfund-gap-2">
            <UserCog2 className="growfund-size-4 growfund-text-icon-primary growfund-shrink-0" />
            <CardTitle>
              {__('Fundraiser Emails', 'growfund')} <ProBadge />
            </CardTitle>
          </div>
        </div>
        <CardDescription>
          {__('Manage fundraiser email settings here.', 'growfund')}
        </CardDescription>
      </CardHeader>
      <CardContent className="growfund-space-y-2">
        <ProSwitchInput
          label={__('New User Registration', 'growfund')}
          description={__('Notifies when a user has registered on the site.', 'growfund')}
          className="growfund-p-2"
        />
        {isDonationMode ? (
          <>
            <ProSwitchInput
              label={__('New Donation', 'growfund')}
              description={__('Alerts the fundraiser when a new donation is made.', 'growfund')}
              className="growfund-p-2"
            />
            <ProSwitchInput
              label={__('Donation Charged', 'growfund')}
              description={__(
                'Alerts the fundraiser when a donation amount has been charged from a donor.',
                'growfund',
              )}
              className="growfund-p-2"
            />
            <ProSwitchInput
              label={__('Donation Cancelled', 'growfund')}
              description={__(
                'Notify the fundraiser when a donor cancels their donation.',
                'growfund',
              )}
              className="growfund-p-2"
            />
          </>
        ) : (
          <>
            <ProSwitchInput
              label={__('New Pledge', 'growfund')}
              description={__('Alerts the fundraiser when a new pledge is made.', 'growfund')}
              className="growfund-p-2"
            />
            <ProSwitchInput
              label={__('Pledge Charged', 'growfund')}
              description={__(
                'Alerts the fundraiser when a pledge amount has been charged from a backer.',
                'growfund',
              )}
              className="growfund-p-2"
            />
            <ProSwitchInput
              label={__('Pledge Cancelled', 'growfund')}
              description={__(
                'Notify the fundraiser when a backer cancels their pledge.',
                'growfund',
              )}
              className="growfund-p-2"
            />
          </>
        )}

        <ProSwitchInput
          label={__('Reset Password', 'growfund')}
          description={__('Receive an email when a password reset is requested.', 'growfund')}
          className="growfund-p-2"
        />
        <ProSwitchInput
          label={__('Campaign Approved', 'growfund')}
          description={__('Notify the fundraiser when their campaign is approved.', 'growfund')}
          className="growfund-p-2"
        />
        <ProSwitchInput
          label={__('Campaign Declined', 'growfund')}
          description={__('Notify the fundraiser when their campaign is declined.', 'growfund')}
          className="growfund-p-2"
        />
        <ProSwitchInput
          label={__('Campaign Update', 'growfund')}
          description={__(
            'Notify the fundraiser when an update is posted about the campaign.',
            'growfund',
          )}
          className="growfund-p-2"
        />
        <ProSwitchInput
          label={__('Campaign Funded', 'growfund')}
          description={__(
            'Notify the fundraiser when their campaign reaches its goal.',
            'growfund',
          )}
          className="growfund-p-2"
        />
        {isDonationMode ? (
          <ProSwitchInput
            label={__('New Offline Donation', 'growfund')}
            description={__('Notify the fundraiser about a new offline donation.', 'growfund')}
            className="growfund-p-2"
          />
        ) : (
          <>
            <ProSwitchInput
              label={__('New Offline Pledge', 'growfund')}
              description={__('Notify the fundraiser about a new offline pledge.', 'growfund')}
              className="growfund-p-2"
            />
            <ProSwitchInput
              label={__('Rewards Delivered', 'growfund')}
              description={__(
                'Notify the campaign creator when rewards have been delivered to the backer.',
                'growfund',
              )}
              className="growfund-p-2"
            />
          </>
        )}
      </CardContent>
    </Card>
  );
};

export default FundraiserEmailFieldsFallback;

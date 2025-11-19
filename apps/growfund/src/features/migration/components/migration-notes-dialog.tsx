import { __ } from '@wordpress/i18n';
import React, { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys } from '@/features/settings/context/settings-context';

const MigrationNotesDialog = ({ children }: React.PropsWithChildren) => {
  const [open, setOpen] = useState(false);
  const { appConfig } = useAppConfig();

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-bg-background-white growfund-gap-0">
        <DialogHeader className="growfund-border-0">
          <div className="growfund-typo-h4 growfund-text-fg-primary growfund-font-medium growfund-leading-none growfund-tracking-tight">
            {__('Migrate to Growfund', 'growfund')}
          </div>
        </DialogHeader>
        <div className="growfund-px-4 growfund-space-y-2">
          <p className="growfund-typo-tiny growfund-text-fg-secondary">
            {__(
              'Migrating to Growfund gives you a better fundraising experience. To ensure a smooth transition, back up your data before proceeding.',
              'growfund',
            )}
          </p>
          <div className="growfund-typo-h6 growfund-text-fg-primary">{__('How to Back Up', 'growfund')}</div>
          <p className="growfund-typo-tiny growfund-text-fg-secondary">
            {__(
              `Use a WordPress backup plugin or your hosting provider's backup tools. Store the backup securely before starting the migration.`,
              'growfund',
            )}
          </p>
          <ul className="growfund-list-disc growfund-ps-4 growfund-typo-tiny growfund-font-medium">
            <li className="growfund-leading-5">
              {__(
                `If you've deleted campaigns in WP Crowdfunding, their associated data will not be migrated; only data from existing campaigns will transfer.`,
                'growfund',
              )}
            </li>
            <li className="growfund-leading-5">
              {__(
                'You may need to reconnect your payment gateways (Stripe, PayPal, etc.) after migration is complete.',
                'growfund',
              )}
            </li>
          </ul>
          <div className="growfund-typo-tiny growfund-text-fg-secondary">
            {__('Need help? Contact our ', 'growfund')}
            <a
              className="growfund-text-fg-emphasis growfund-underline growfund-cursor-pointer"
              href={`mailto:${appConfig[AppConfigKeys.General]?.organization.contact_email ?? '#'}`}
            >
              {__('support', 'growfund')}
            </a>
            {__(' with any questions.', 'growfund')}
          </div>
        </div>
        <div className="growfund-p-4 growfund-text-fg-critical growfund-typo-tiny">
          {__(
            `By continuing, you confirm that you've backed up your data and understand how the migration works.`,
            'growfund',
          )}
        </div>
        <DialogFooter className="growfund-border-0">
          <Button
            variant="primary"
            onClick={() => {
              setOpen(false);
            }}
          >
            {__('Acknowledge & Continue', 'growfund')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default MigrationNotesDialog;

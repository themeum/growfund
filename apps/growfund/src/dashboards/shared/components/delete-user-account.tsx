import { __ } from '@wordpress/i18n';
import { Trash2, TriangleAlert } from 'lucide-react';

import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import DeleteUserDialog from '@/dashboards/shared/components/dialogs/delete-user-dialog';

const DeleteUserAccount = () => {
  return (
    <Box className="growfund-border-none growfund-shadow-none">
      <BoxContent>
        <div className="growfund-space-y-1">
          <div className="growfund-flex growfund-items-center growfund-gap-2">
            <TriangleAlert className="growfund-size-4 growfund-text-icon-primary" />
            <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
              {__('Danger Zone', 'growfund')}
            </h6>
          </div>
          <p className="growfund-typo-small growfund-text-fg-secondary">
            {__('Crucial actions are here proceed cautiously', 'growfund')}
          </p>
        </div>

        <div className="growfund-flex growfund-items-center growfund-justify-between growfund-mt-6">
          <div className="growfund-space-y-1">
            <p className="growfund-typo-small growfund-font-medium growfund-text-fg-critical">
              {__('Delete my account', 'growfund')}
            </p>
            <p className="growfund-typo-small growfund-text-fg-secondary">
              {__('Permanently delete the account.', 'growfund')}
            </p>
          </div>
          <DeleteUserDialog>
            <Button variant="destructive-soft" size="sm">
              <Trash2 />
              {__('Delete', 'growfund')}
            </Button>
          </DeleteUserDialog>
        </div>
      </BoxContent>
    </Box>
  );
};

export default DeleteUserAccount;

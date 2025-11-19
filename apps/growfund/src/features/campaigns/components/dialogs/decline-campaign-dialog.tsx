import { zodResolver } from '@hookform/resolvers/zod';
import { Cross2Icon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useForm } from 'react-hook-form';

import { TextareaField } from '@/components/form/textarea-field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Form } from '@/components/ui/form';
import {
    type DeclineCampaignForm,
    DeclineCampaignSchema,
} from '@/features/campaigns/schemas/decline-campaign';
import { useDeclineCampaignMutation } from '@/features/campaigns/services/campaign';
import { getDefaults } from '@/lib/zod';

const DeclineCampaignDialog = ({ campaignId }: { campaignId: string }) => {
  const [open, setOpen] = useState(false);
  const form = useForm<DeclineCampaignForm>({
    resolver: zodResolver(DeclineCampaignSchema),
    defaultValues: getDefaults(DeclineCampaignSchema),
  });
  const declineCampaignMutation = useDeclineCampaignMutation();

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="destructive-soft" size="icon" className="growfund-size-6">
          <Cross2Icon />
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{__('Decline campaign from publishing?', 'growfund')}</DialogTitle>
        </DialogHeader>
        <DialogCloseButton />
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(
              (values) => {
                declineCampaignMutation.mutate(
                  {
                    id: campaignId,
                    decline_reason: values.reason,
                  },
                  {
                    onSuccess() {
                      setOpen(false);
                    },
                  },
                );
              },
              (errors) => {
                console.error(errors);
              },
            )}
          >
            <div className="growfund-px-4 growfund-pb-4">
              <TextareaField
                control={form.control}
                name="reason"
                label={__('Reason', 'growfund')}
                placeholder={__(
                  'e.g., Need to refine the campaign messaging, waiting for additional assets, or need to adjust timing due to current events.',
                  'growfund',
                )}
                description={__(
                  'When you decline the campaign, it will be saved as draft. The campaign can be edited and published later.',
                  'growfund',
                )}
              />
            </div>

            <DialogFooter>
              <DialogClose asChild>
                <Button variant="outline" disabled={declineCampaignMutation.isPending}>
                  {__('Cancel', 'growfund')}
                </Button>
              </DialogClose>
              <Button
                type="submit"
                variant="destructive"
                loading={declineCampaignMutation.isPending}
                disabled={declineCampaignMutation.isPending}
              >
                {__('Decline Campaign', 'growfund')}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export default DeclineCampaignDialog;

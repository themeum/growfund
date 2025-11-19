import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router';

import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { RouteConfig } from '@/config/route-config';
import DonationForm from '@/features/donations/components/donation-form';
import { DonationFormSchema } from '@/features/donations/schemas/donation-form';
import { useCreateDonationMutation } from '@/features/donations/services/donations';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { getDefaults } from '@/lib/zod';

const CreateDonationPage = () => {
  const createDonationMutation = useCreateDonationMutation();

  const navigate = useNavigate();

  const form = useForm<DonationForm>({
    resolver: zodResolver(DonationFormSchema),
    defaultValues: getDefaults(DonationFormSchema._def.schema),
  });
  const { createErrorHandler } = useFormErrorHandler(form);

  const onSubmit = (values: DonationForm) => {
    if (Object.keys(form.formState.errors).length > 0) return;
    createDonationMutation.mutate(values, {
      onSuccess: () => {
        void navigate(RouteConfig.Donations.buildLink());
      },
      onError: createErrorHandler(),
    });
  };

  return (
    <Page>
      <Form {...form}>
        <PageHeader
          name={__('New Donation', 'growfund')}
          onGoBack={() => navigate(RouteConfig.Donations.buildLink())}
          action={
            <div className="growfund-flex growfund-items-center growfund-gap-2">
              <Button variant="outline" onClick={() => navigate(RouteConfig.Donations.buildLink())}>
                {__('Discard', 'growfund')}
              </Button>

              <Button
                onClick={form.handleSubmit(
                  (values) => {
                    onSubmit(values);
                  },
                  (errors) => {
                    console.error(errors);
                  },
                )}
              >
                {__('Save', 'growfund')}
              </Button>
            </div>
          }
        />
        <PageContent>
          <DonationForm />
        </PageContent>
      </Form>
    </Page>
  );
};

export default CreateDonationPage;

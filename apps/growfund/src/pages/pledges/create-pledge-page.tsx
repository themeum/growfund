import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router';

import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { RouteConfig } from '@/config/route-config';
import PledgeForm from '@/features/pledges/components/pledge-form';
import {
    PledgeFormSchema,
    type PledgeForm as PledgeFormType,
    type PledgePayload,
} from '@/features/pledges/schemas/pledge-form';
import { useCreateNewPledgeMutation } from '@/features/pledges/services/pledges';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { getDefaults } from '@/lib/zod';

const CreatePledgePage = () => {
  const navigate = useNavigate();

  const form = useForm<PledgeFormType>({
    resolver: zodResolver(PledgeFormSchema),
    defaultValues: {
      ...getDefaults(PledgeFormSchema._def.schema),
      is_manual: true,
    },
  });

  const createNewPledgeMutation = useCreateNewPledgeMutation();

  const { createErrorHandler } = useFormErrorHandler(form);

  const handleFormSubmit = (data: PledgePayload) => {
    if (Object.keys(form.formState.errors).length > 0) return;

    createNewPledgeMutation.mutate(data, {
      onSuccess: () => {
        void navigate(RouteConfig.Pledges.buildLink());
      },
      onError: createErrorHandler(),
    });
  };

  return (
    <Page>
      <Form {...form}>
        <PageHeader
          name={__('New Pledge', 'growfund')}
          onGoBack={() => navigate(RouteConfig.Pledges.buildLink())}
          action={
            <div className="growfund-flex growfund-items-center growfund-gap-2">
              <Button variant="outline" onClick={() => navigate(RouteConfig.Pledges.buildLink())}>
                {__('Discard', 'growfund')}
              </Button>
              <Button
                loading={createNewPledgeMutation.isPending}
                disabled={createNewPledgeMutation.isPending}
                onClick={form.handleSubmit(handleFormSubmit, (errors) => {
                  console.error(errors);
                })}
              >
                {__('Save', 'growfund')}
              </Button>
            </div>
          }
        />
        <PageContent>
          <PledgeForm />
        </PageContent>
      </Form>
    </Page>
  );
};

export default CreatePledgePage;

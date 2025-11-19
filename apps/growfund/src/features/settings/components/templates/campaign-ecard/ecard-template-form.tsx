import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { Container } from '@/components/layouts/container';
import {
    TemplateFormColorSection,
    TemplateFormContentSection,
    TemplateFormImageSection,
} from '@/components/template-form/template-form';
import ECardContentForm from '@/features/settings/components/templates/campaign-ecard/ecard-content-form';
import EcardPreview from '@/features/settings/components/templates/campaign-ecard/ecard-preview';
import { type EcardTemplateForm } from '@/features/settings/schemas/ecard';

const EcardTemplateForm = ({ shortCodes }: { shortCodes?: { value: string; label: string }[] }) => {
  const form = useFormContext<EcardTemplateForm>();

  return (
    <Container className="growfund-mt-6">
      <div className="growfund-flex growfund-justify-center growfund-gap-6">
        <div className="growfund-flex-col">
          <div className="growfund-w-[29rem] growfund-space-y-4">
            <TemplateFormImageSection
              namePrefix="media"
              control={form.control}
              header={__('eCard Image', 'growfund')}
              minRangeHeight={12}
              maxRangeHeight={480}
            />
            <TemplateFormContentSection
              header={__('Contents', 'growfund')}
              description={__('Manage the pdf contents from here', 'growfund')}
            >
              <ECardContentForm shortCodes={shortCodes} />
            </TemplateFormContentSection>
            <TemplateFormColorSection
              control={form.control}
              fields={[
                {
                  name: 'colors.background',
                  label: __('Background', 'growfund'),
                },
                {
                  name: 'colors.greetings',
                  label: __('Greetings', 'growfund'),
                },
                {
                  name: 'colors.text_color',
                  label: __('Text Color', 'growfund'),
                },
                {
                  name: 'colors.secondary_text_color',
                  label: __('Secondary Text Color', 'growfund'),
                },
              ]}
              header={__('Colors', 'growfund')}
              description={__('Style how the emails look like', 'growfund')}
            />
          </div>
        </div>
        <div className="growfund-flex-col growfund-sticky growfund-top-[calc(var(--growfund-topbar-height)+var(--growfund-spacing)_*_1.5)] growfund-h-full">
          <EcardPreview />
        </div>
      </div>
    </Container>
  );
};

export default EcardTemplateForm;

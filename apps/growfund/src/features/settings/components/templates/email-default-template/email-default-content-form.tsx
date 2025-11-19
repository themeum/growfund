import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { EditorField } from '@/components/form/editor-field';
import { type EmailTemplateForm } from '@/features/settings/schemas/email-default-template';

const EmailDefaultContentForm = () => {
  const form = useFormContext<EmailTemplateForm>();
  return (
    <div className="growfund-w-full growfund-space-y-4">
      <EditorField
        control={form.control}
        name="content.additional"
        label={__('Additional Description', 'growfund')}
        description={__('This will be displayed after the message.', 'growfund')}
        placeholder={__('Thank You For Your Donation!', 'growfund')}
        rows={2}
        shortCodes={[
          {
            label: __('Site Name', 'growfund'),
            value: '{site_name}',
          },
          {
            label: __('Site URL', 'growfund'),
            value: '{site_url}',
          },
          {
            label: __('Support Email', 'growfund'),
            value: '{support_email}',
          },
          {
            label: __('Organization Name', 'growfund'),
            value: '{organization_name}',
          },
        ]}
      />

      <EditorField
        control={form.control}
        name="content.footer"
        label={__('Footer', 'growfund')}
        rows={4}
        shortCodes={[
          {
            label: __('Year', 'growfund'),
            value: '{year}',
          },
          {
            label: __('Site Name', 'growfund'),
            value: '{site_name}',
          },
          {
            label: __('Site URL', 'growfund'),
            value: '{site_url}',
          },
          {
            label: __('Support Email', 'growfund'),
            value: '{support_email}',
          },
          {
            label: __('Terms and Conditions', 'growfund'),
            value: '{terms_and_conditions}',
          },
          {
            label: __('Privacy Policy', 'growfund'),
            value: '{privacy_policy}',
          },
          {
            label: __('Organization Name', 'growfund'),
            value: '{organization_name}',
          },
          {
            label: __('Organization Location', 'growfund'),
            value: '{organization_location}',
          },
        ]}
      />
    </div>
  );
};

export default EmailDefaultContentForm;

import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { EditorField } from '@/components/form/editor-field';
import { TextField } from '@/components/form/text-field';
import { type EcardTemplateForm } from '@/features/settings/schemas/ecard';

const ECardContentForm = ({ shortCodes }: { shortCodes?: { value: string; label: string }[] }) => {
  const form = useFormContext<EcardTemplateForm>();
  return (
    <div className="growfund-w-full growfund-space-y-4">
      <TextField
        control={form.control}
        name="content.greetings"
        label={__('Greetings', 'growfund')}
        placeholder={`${__('Dear', 'growfund')} {tribute_notification_receiver}`}
        shortCodes={[
          {
            label: 'Notification Recipient',
            value: '{tribute_notification_receiver}',
          },
        ]}
      />
      <EditorField
        control={form.control}
        name="content.description"
        label={__('Description', 'growfund')}
        shortCodes={shortCodes}
        rows={4}
      />
    </div>
  );
};

export default ECardContentForm;

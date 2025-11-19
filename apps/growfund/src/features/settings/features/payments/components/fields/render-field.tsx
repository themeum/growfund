import { useFormContext } from 'react-hook-form';

import { MediaField } from '@/components/form/media-field';
import { SelectField } from '@/components/form/select-field';
import { SwitchField } from '@/components/form/switch-field';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { type GatewayField } from '@/features/settings/features/payments/schemas/payment';

const RenderField = ({ field }: { field: GatewayField }) => {
  const form = useFormContext();
  switch (field.type) {
    case 'text':
    case 'password':
      return (
        <TextField
          type={field.type === 'password' ? 'password' : 'text'}
          control={form.control}
          name={field.name}
          label={field.label}
          placeholder={field.placeholder}
        />
      );
    case 'textarea':
      return (
        <TextareaField
          control={form.control}
          name={field.name}
          label={field.label}
          placeholder={field.placeholder}
          rows={6}
        />
      );
    case 'select':
      return (
        <SelectField
          control={form.control}
          name={field.name}
          label={field.label}
          options={field.options ?? []}
        />
      );
    case 'switch':
      return <SwitchField control={form.control} name={field.name} label={field.label} />;
    case 'media':
      return <MediaField control={form.control} name={field.name} label={field.label} />;
    default:
      return null;
  }
};

export default RenderField;

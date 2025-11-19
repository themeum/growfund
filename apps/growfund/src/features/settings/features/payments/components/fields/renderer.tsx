import RenderField from '@/features/settings/features/payments/components/fields/render-field';
import { type GatewayField } from '@/features/settings/features/payments/schemas/payment';

const Renderer = ({ fields }: { fields: GatewayField[] }) => {
  return (
    <div className="growfund-space-y-4">
      {fields.map((field, index) => {
        return <RenderField key={index} field={field} />;
      })}
    </div>
  );
};

export default Renderer;

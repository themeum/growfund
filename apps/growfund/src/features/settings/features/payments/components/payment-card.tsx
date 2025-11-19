import { __ } from '@wordpress/i18n';
import { CheckCircle } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Image } from '@/components/ui/image';
import { type Payment } from '@/features/settings/features/payments/schemas/payment';
import {
    useInstallPaymentGateway,
    useStorePaymentGateway,
} from '@/features/settings/features/payments/services/payment';
import { isMediaObject } from '@/utils/media';

interface PaymentCardProps {
  payment: Payment;
}

export const PaymentCard = ({ payment }: PaymentCardProps) => {
  const installPaymentGatewayMutation = useInstallPaymentGateway();
  const storePaymentGatewayMutation = useStorePaymentGateway();

  const logoSrc = isMediaObject(payment.config.logo)
    ? payment.config.logo.url
    : payment.config.logo;

  return (
    <Card className="growfund-cursor-pointer hover:growfund-bg-background-surface-alt">
      <div className="growfund-flex growfund-justify-between growfund-items-center growfund-p-4">
        <div className="growfund-flex growfund-gap-2">
          <Image
            src={logoSrc}
            alt={payment.config.label}
            className="growfund-size-5 growfund-border-none growfund-bg-transparent"
            fit="contain"
            aspectRatio="square"
          />
          <span className="growfund-font-medium growfund-text-base">{payment.config.label}</span>
        </div>
        {payment.is_installed ? (
          <div className="growfund-flex growfund-gap-2 growfund-h-9 growfund-items-center">
            <CheckCircle className="growfund-text-fg-success growfund-size-4" />
            <span className="growfund-text-fg-success growfund-font-medium growfund-typo-small">
              {__('Installed', 'growfund')}
            </span>
          </div>
        ) : (
          <Button
            variant="secondary"
            onClick={async () => {
              await installPaymentGatewayMutation.mutateAsync(payment.download_url);
              await storePaymentGatewayMutation.mutateAsync({
                name: payment.name,
                payload: payment,
              });
            }}
            loading={
              installPaymentGatewayMutation.isPending || storePaymentGatewayMutation.isPending
            }
            disabled={
              installPaymentGatewayMutation.isPending || storePaymentGatewayMutation.isPending
            }
          >
            {__('Install', 'growfund')}
          </Button>
        )}
      </div>
    </Card>
  );
};

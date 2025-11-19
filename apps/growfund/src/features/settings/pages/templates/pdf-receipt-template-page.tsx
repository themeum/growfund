import { useAppConfig } from '@/contexts/app-config';
import PdfDonationReceiptTemplate from '@/features/settings/components/templates/pdf-donation-receipt-template';
import PdfPledgeReceiptTemplate from '@/features/settings/components/templates/pdf-pledge-receipt-template';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';

const PdfReceiptTemplate = () => {
  const { isDonationMode } = useAppConfig();

  if (isDonationMode) {
    return <PdfDonationReceiptTemplate />;
  }

  return <PdfPledgeReceiptTemplate />;
};

export default PdfReceiptTemplate;

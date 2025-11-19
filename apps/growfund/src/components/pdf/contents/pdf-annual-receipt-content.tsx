import { Text, View } from '@react-pdf/renderer';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';

import { usePdf } from '@/contexts/pdf-context';
import { type DonorAnnualReceiptDetail } from '@/dashboards/donors/features/annual-receipts/schema/donor-annual-receipt';
import { DATE_FORMATS } from '@/lib/date';

const PdfAnnualReceiptContent = ({
  annualReceipt,
}: {
  annualReceipt: DonorAnnualReceiptDetail;
}) => {
  const { pdfReceiptTemplate, toCurrency } = usePdf();
  const secondaryTextColor = pdfReceiptTemplate?.colors?.secondary_text ?? '#636363';
  return (
    <View style={{ paddingTop: 12, paddingBottom: 8 }}>
      <View style={{ flexDirection: 'row' }}>
        <View style={{ marginBottom: 4, width: 150 }}>
          <Text style={{ color: secondaryTextColor }}>{__('Date', 'growfund')}</Text>
        </View>
        <View style={{ marginBottom: 4, marginLeft: 8, width: 100 }}>
          <Text style={{ color: secondaryTextColor }}>{__('Amount', 'growfund')}</Text>
        </View>
        <View style={{ marginBottom: 4, marginLeft: 8, width: 120 }}>
          <Text style={{ color: secondaryTextColor }}>{__('Donation ID', 'growfund')}</Text>
        </View>
        <View style={{ marginBottom: 4, marginLeft: 8 }}>
          <Text style={{ color: secondaryTextColor }}>{__('Payment Method', 'growfund')}</Text>
        </View>
      </View>
      {annualReceipt.donations.map((donation, index) => {
        return (
          <View key={index} style={{ flexDirection: 'row', marginTop: 12 }}>
            <View style={{ marginBottom: 4, width: 150 }}>
              <Text>{format(donation.created_at, DATE_FORMATS.HUMAN_READABLE)}</Text>
            </View>
            <View style={{ marginBottom: 4, marginLeft: 8, width: 100 }}>
              <Text>{toCurrency(donation.amount, false)}</Text>
            </View>
            <View style={{ marginBottom: 4, marginLeft: 8, width: 120 }}>
              <Text>{donation.id}</Text>
            </View>
            <View style={{ marginBottom: 4, marginLeft: 8 }}>
              <Text>{donation.payment_method.label}</Text>
            </View>
          </View>
        );
      })}
      <Text style={{ marginTop: 48 }}>
        {__('No goods or services were exchanged for these contributions.', 'growfund')}
      </Text>
    </View>
  );
};

export default PdfAnnualReceiptContent;

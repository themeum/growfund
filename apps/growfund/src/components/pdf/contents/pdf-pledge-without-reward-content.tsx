import { Text, View } from '@react-pdf/renderer';
import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';

import PdfPledgeDetailsContent from '@/components/pdf/contents/pdf-pledge-details-content';
import { usePdf } from '@/contexts/pdf-context';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { DATE_FORMATS } from '@/lib/date';

const PdfPledgeWithoutRewardContent = ({ pledge }: { pledge: Pledge }) => {
  const { pdfReceiptTemplate, toCurrency } = usePdf();
  const secondaryTextColor = pdfReceiptTemplate?.colors?.secondary_text ?? '#636363';

  return (
    <>
      <View
        style={{
          padding: 16,
          paddingTop: 12,
          paddingBottom: 12,
          backgroundColor: '#F7F7F7',
          borderRadius: 6,
        }}
      >
        <View
          style={{
            width: '100%',
            flexDirection: 'row',
            alignItems: 'flex-end',
            justifyContent: 'space-between',
          }}
        >
          <View style={{ flexDirection: 'column', alignItems: 'flex-start' }}>
            <Text
              style={{
                width: '100%',
                color: secondaryTextColor,
              }}
            >
              {__('Pledge Amount', 'growfund')}
            </Text>
            <Text style={{ fontWeight: 'bold', fontSize: 20, marginTop: 8 }}>
              {toCurrency(pledge.payment.amount ?? 0, false)}
            </Text>
          </View>
          <View style={{ flexDirection: 'column', alignItems: 'flex-end' }}>
            <Text
              style={{
                color: secondaryTextColor,
              }}
            >
              {__('Date & Time', 'growfund')}
            </Text>
            <Text
              style={{
                marginTop: 8,
              }}
            >
              {format(pledge.created_at, DATE_FORMATS.HUMAN_READABLE_FULL_DATE_TIME)}
            </Text>
          </View>
        </View>
      </View>

      <PdfPledgeDetailsContent pledge={pledge} />
    </>
  );
};

export default PdfPledgeWithoutRewardContent;

import { Text, View } from '@react-pdf/renderer';
import { __, sprintf } from '@wordpress/i18n';

import { usePdf } from '@/contexts/pdf-context';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { displayAddress, emptyCell } from '@/utils';

const PdfPledgeDetailsContent = ({ pledge }: { pledge: Pledge }) => {
  const { pdfReceiptTemplate } = usePdf();
  const secondaryTextColor = pdfReceiptTemplate?.colors?.secondary_text ?? '#636363';
  return (
    <View style={{ paddingTop: 24, paddingBottom: 8 }}>
      <View style={{ flexDirection: 'row' }}>
        <View style={{ width: 241 }}>
          <View style={{ marginBottom: 4 }}>
            <Text style={{ color: secondaryTextColor }}>{__('Backer Name', 'growfund')}</Text>
            <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
              {sprintf('%s %s', pledge.backer.first_name, pledge.backer.last_name)}
            </Text>
          </View>
        </View>
        <View style={{ marginLeft: 8 }}>
          <View style={{ marginBottom: 4 }}>
            <Text style={{ color: secondaryTextColor }}>{__('Backer Address', 'growfund')}</Text>
            <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
              {displayAddress(pledge.backer.shipping_address)}
            </Text>
          </View>
        </View>
      </View>
      <View style={{ flexDirection: 'row', marginTop: 8 }}>
        <View style={{ width: 241 }}>
          <View style={{ marginBottom: 4 }}>
            <Text style={{ color: secondaryTextColor }}>{__('Pledge To', 'growfund')}</Text>
            <Text style={{ marginTop: 2, fontWeight: 'bold' }}>{pledge.campaign.title}</Text>
          </View>
        </View>
      </View>
      <View style={{ flexDirection: 'row', marginTop: '8px' }}>
        <View style={{ width: 241 }}>
          <View style={{ marginBottom: 4 }}>
            <Text style={{ color: secondaryTextColor }}>{__('Payment Method', 'growfund')}</Text>
            <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
              {pledge.payment.payment_method.label}
            </Text>
          </View>
        </View>
        <View style={{ marginLeft: '8px' }}>
          <View style={{ marginBottom: 4 }}>
            <Text style={{ color: secondaryTextColor }}>{__('Transaction ID', 'growfund')}</Text>
            <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
              {pledge.payment.transaction_id ?? emptyCell(2)}
            </Text>
          </View>
        </View>
      </View>
    </View>
  );
};

export default PdfPledgeDetailsContent;

import { Image, Text, View } from '@react-pdf/renderer';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';

import placeholder from '@/assets/images/placeholder.svg';
import PdfPledgeDetailsContent from '@/components/pdf/contents/pdf-pledge-details-content';
import { usePdf } from '@/contexts/pdf-context';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { DATE_FORMATS } from '@/lib/date';

const PDFPledgeWithRewardContent = ({ pledge }: { pledge: Pledge }) => {
  const { pdfReceiptTemplate, toCurrency } = usePdf();
  const secondaryTextColor = pdfReceiptTemplate?.colors?.secondary_text ?? '#636363';

  return (
    <>
      <View style={{ padding: 16, paddingBottom: 12, backgroundColor: '#F7F7F7', borderRadius: 6 }}>
        {/* Campaign card */}
        <View style={{ flexDirection: 'row', alignItems: 'center', width: '100%' }}>
          <View style={{ marginRight: 16 }}>
            <Image
              style={{ width: 72, height: 72, borderRadius: 6 }}
              src={pledge.campaign.images?.[0]?.url ?? placeholder}
            />
          </View>
          <View style={{ flexDirection: 'column', marginLeft: 8, width: '100%' }}>
            <Text style={{ width: '100%', fontWeight: 'bold' }}>{pledge.campaign.title}</Text>
            <Text
              style={{
                width: '100%',
                color: secondaryTextColor,
                marginTop: 8,
              }}
            >
              {/* translator: %s: total number of reward items */}
              {sprintf(__('%s items included', 'growfund'), pledge.reward?.items.length)}
            </Text>
            <View
              style={{
                width: '100%',
                flexDirection: 'row',
                alignItems: 'flex-end',
                justifyContent: 'space-between',
              }}
            >
              <Text style={{ fontWeight: 'bold', color: secondaryTextColor }}>
                {toCurrency(pledge.payment.amount ?? 0, false)}
              </Text>
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
                    marginTop: 4,
                  }}
                >
                  {format(pledge.created_at, DATE_FORMATS.HUMAN_READABLE_FULL_DATE_TIME)}
                </Text>
              </View>
            </View>
          </View>
        </View>

        {/* Separator */}
        <View style={{ paddingTop: 8 }}>
          <View style={{ width: '100%', height: 1, backgroundColor: '#E6E6E6' }}></View>
        </View>

        <View>
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              paddingTop: 8,
            }}
          >
            <Text>{__('Pledge with a reward', 'growfund')}</Text>
            <Text>{toCurrency(pledge.payment.amount ?? 0, false)}</Text>
          </View>
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              paddingTop: 8,
            }}
          >
            <Text>{__('Bonus', 'growfund')}</Text>
            <Text>{toCurrency(pledge.payment.bonus_support_amount ?? 0, false)}</Text>
          </View>
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'space-between',
              paddingTop: 8,
            }}
          >
            <Text>{__('Shipping', 'growfund')}</Text>
            <Text>{toCurrency(pledge.payment.shipping_cost ?? 0, false)}</Text>
          </View>
          <View style={{ flexDirection: 'row', justifyContent: 'space-between', paddingTop: 8 }}>
            <Text style={{ fontWeight: 'bold' }}>{__('Total', 'growfund')}</Text>
            <Text style={{ fontWeight: 'bold' }}>
              {toCurrency(pledge.payment.total ?? 0, false)}
            </Text>
          </View>
        </View>
      </View>

      {/* pledge details */}
      <PdfPledgeDetailsContent pledge={pledge} />
    </>
  );
};

export default PDFPledgeWithRewardContent;

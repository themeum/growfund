import { Text, View } from '@react-pdf/renderer';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';

import { usePdf } from '@/contexts/pdf-context';
import { type Donation } from '@/features/donations/schemas/donation';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { DATE_FORMATS } from '@/lib/date';
import { displayAddress, emptyCell } from '@/utils';

const PdfDonationContent = ({ donation }: { donation: Donation }) => {
  const {
    pdfReceiptTemplate,
    toCurrency,
    appConfig: { [AppConfigKeys.Campaign]: campaignSettings },
  } = usePdf();
  const secondaryTextColor = pdfReceiptTemplate?.colors?.secondary_text ?? '#636363';
  const notificationType = new Map<Donation['tribute_notification_type'], string>([
    ['send-ecard', __('e-Card', 'growfund')],
    ['send-post-mail', __('Post Mail', 'growfund')],
    ['send-ecard-and-post-mail', __('e-Card and Post Mail', 'growfund')],
  ]);

  return (
    <View>
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
          <View style={{ flexDirection: 'column', alignItems: 'flex-end' }}>
            <Text
              style={{
                width: '100%',
                color: secondaryTextColor,
              }}
            >
              {__('Donation Amount', 'growfund')}
            </Text>
            <Text style={{ fontWeight: 'bold', fontSize: 20, marginTop: 8 }}>
              {toCurrency(donation.amount, false)}
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
              {format(donation.created_at, DATE_FORMATS.HUMAN_READABLE_FULL_DATE_TIME)}
            </Text>
          </View>
        </View>
      </View>
      <View style={{ paddingTop: 24, paddingBottom: 8 }}>
        <View style={{ flexDirection: 'row' }}>
          <View style={{ width: 241 }}>
            <View style={{ marginBottom: 4 }}>
              <Text style={{ color: secondaryTextColor }}>{__('Donor Name', 'growfund')}</Text>
              <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                {sprintf('%s %s', donation.donor.first_name, donation.donor.last_name)}
              </Text>
            </View>
          </View>
          <View style={{ marginLeft: 8 }}>
            <View style={{ marginBottom: 4 }}>
              <Text style={{ color: secondaryTextColor }}>{__('Payment Method', 'growfund')}</Text>
              <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                {donation.payment_method.label}
              </Text>
            </View>
          </View>
        </View>
        <View style={{ flexDirection: 'row', marginTop: 8 }}>
          <View style={{ width: 241 }}>
            <View style={{ marginBottom: 4 }}>
              <Text style={{ color: secondaryTextColor }}>{__('Transaction ID', 'growfund')}</Text>
              <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                {donation.transaction_id ?? emptyCell(2)}
              </Text>
            </View>
          </View>
        </View>
        <View style={{ flexDirection: 'row', marginTop: '8px' }}>
          <View style={{ width: 241 }}>
            <View style={{ marginBottom: 4 }}>
              <Text style={{ color: secondaryTextColor }}>{__('Donation To', 'growfund')}</Text>
              <Text style={{ marginTop: 2, fontWeight: 'bold' }}>{donation.campaign.title}</Text>
            </View>
          </View>
          {campaignSettings?.allow_tribute && (
            <View style={{ marginLeft: '8px' }}>
              <View style={{ marginBottom: 4 }}>
                <Text style={{ color: secondaryTextColor }}>{__('Tribute To', 'growfund')}</Text>
                <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                  {donation.tribute_to ?? emptyCell(2)}
                </Text>
              </View>
            </View>
          )}
        </View>
        {campaignSettings?.allow_tribute && (
          <View>
            <View style={{ flexDirection: 'row', marginTop: '8px' }}>
              <View style={{ width: 241 }}>
                <View style={{ marginBottom: 4 }}>
                  <Text style={{ color: secondaryTextColor }}>
                    {__('Send Notification To', 'growfund')}
                  </Text>
                  <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                    {donation.tribute_notification_recipient_name ?? emptyCell(2)}
                  </Text>
                </View>
              </View>
              <View style={{ marginLeft: '8px' }}>
                <View style={{ marginBottom: 4 }}>
                  <Text style={{ color: secondaryTextColor }}>
                    {__('Notification Method', 'growfund')}
                  </Text>
                  <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                    {notificationType.has(donation.tribute_notification_type)
                      ? notificationType.get(donation.tribute_notification_type)
                      : emptyCell(2)}
                  </Text>
                </View>
              </View>
            </View>
            <View style={{ flexDirection: 'row', marginTop: '8px' }}>
              <View style={{ width: 241 }}>
                <View style={{ marginBottom: 4 }}>
                  <Text style={{ color: secondaryTextColor }}>
                    {__('Recipient Email', 'growfund')}
                  </Text>
                  <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                    {donation.tribute_notification_recipient_email ?? emptyCell(2)}
                  </Text>
                </View>
              </View>
              <View style={{ marginLeft: '8px' }}>
                <View style={{ marginBottom: 4 }}>
                  <Text style={{ color: secondaryTextColor }}>
                    {__('Recipient Address', 'growfund')}
                  </Text>
                  <Text style={{ marginTop: 2, fontWeight: 'bold' }}>
                    {displayAddress(donation.tribute_notification_recipient_address) ||
                      emptyCell(2)}
                  </Text>
                </View>
              </View>
            </View>
          </View>
        )}
      </View>
    </View>
  );
};

export default PdfDonationContent;

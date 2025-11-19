import { Document, Image, Page, Text, View } from '@react-pdf/renderer';
import { sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useMemo } from 'react';
import Html from 'react-pdf-html';

import { growfundConfig } from '@/config/growfund';
import { pdfECardShortcodes } from '@/config/pdf';
import { usePdf } from '@/contexts/pdf-context';
import { type Donation } from '@/features/donations/schemas/donation';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type EcardTemplate } from '@/features/settings/schemas/ecard';
import { DATE_FORMATS } from '@/lib/date';
import { shortcodeReplacement } from '@/lib/utils';
import { isDefined } from '@/utils';
import { toPdfCompatibleImage } from '@/utils/media';

interface Props {
  recipientName?: string;
  template: EcardTemplate;
  donation: Donation;
}

const EcardPDF = ({ template, donation }: Props) => {
  const {
    appConfig: {
      [AppConfigKeys.General]: generalSettings,
      [AppConfigKeys.Branding]: brandingSettings,
    },
    toCurrency,
  } = usePdf();
  const replacement = useMemo(() => {
    return {
      tribute_notification_receiver: donation.tribute_notification_recipient_name ?? '',
      tribute_type: donation.tribute_type ?? '',
      tribute_to: donation.tribute_to ?? '',
      donor_name: sprintf('%s %s', donation.donor.first_name, donation.donor.last_name),
      donation_amount: toCurrency(donation.amount, false),
      donation_date: format(donation.created_at, DATE_FORMATS.HUMAN_READABLE),
      campaign_name: donation.campaign.title,
      organization_name: generalSettings?.organization.name ?? '',
    };
  }, [
    donation.amount,
    donation.campaign.title,
    donation.created_at,
    donation.donor.first_name,
    donation.donor.last_name,
    donation.tribute_notification_recipient_name,
    donation.tribute_to,
    donation.tribute_type,
    generalSettings?.organization.name,
    toCurrency,
  ]);

  const greetings = useMemo(() => {
    return shortcodeReplacement(pdfECardShortcodes, template.content.greetings, replacement);
  }, [template.content.greetings, replacement]);

  const description = useMemo(() => {
    return shortcodeReplacement(pdfECardShortcodes, template.content.description, replacement);
  }, [template.content.description, replacement]);

  const mediaImage = template.media.image;
  const imageHeight = template.media.height;
  const imagePosition = template.media.position;
  const greetingsColor = template.colors.greetings ?? '#000000';
  const backgroundColor = template.colors.background ?? '#F3E6D6';
  const textColor = template.colors.text_color ?? '#333333';
  const secondaryTextColor = template.colors.secondary_text_color ?? '#636363';

  return (
    <Document>
      <Page
        size="A4"
        style={{
          margin: 0,
          fontFamily: 'Helvetica',
          fontSize: 12,
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
          alignItems: 'center',
          height: '100%',
        }}
      >
        <View
          style={{
            width: 384,
            backgroundColor: backgroundColor,
            border: '0.6px',
            borderStyle: 'solid',
            borderColor: '#E6E6E6',
          }}
        >
          <View
            style={{
              width: '100%',
              display: 'flex',
              justifyContent:
                imagePosition === 'left'
                  ? 'flex-start'
                  : imagePosition === 'right'
                  ? 'flex-end'
                  : 'center',
              height: imageHeight ?? 'auto',
            }}
          >
            <Image
              src={toPdfCompatibleImage(
                mediaImage?.url ?? growfundConfig.assets_url + '/images/ecard.webp',
              )}
              style={{ width: '100%', objectFit: 'contain' }}
            />
          </View>

          <View style={{ paddingTop: 16, paddingBottom: 24, paddingLeft: 32, paddingRight: 32 }}>
            <Text style={{ fontSize: 14, fontWeight: 'semibold', color: greetingsColor }}>
              {greetings ? greetings.replace('{tribute_notification_receiver}', '') : ''}
            </Text>

            <Text style={{ marginTop: 16, lineHeight: 1.4, color: textColor }}>
              <Html
                resetStyles={false}
                stylesheet={{
                  p: {
                    marginBottom: 1,
                    marginTop: 2,
                    fontSize: 14,
                    lineHeight: 1.4,
                    width: '100%',
                  },
                }}
              >
                {description}
              </Html>
            </Text>

            <View style={{ marginTop: 20, flexDirection: 'column', gap: 12 }}>
              <Text style={{ fontSize: 12, color: secondaryTextColor }}>
                {format(donation.created_at, DATE_FORMATS.HUMAN_READABLE)}
              </Text>
              {isDefined(brandingSettings?.logo?.url) && (
                <Image
                  src={toPdfCompatibleImage(brandingSettings.logo.url)}
                  style={{ height: 24, maxWidth: 100 }}
                />
              )}
            </View>
          </View>
        </View>
      </Page>
    </Document>
  );
};

export default EcardPDF;

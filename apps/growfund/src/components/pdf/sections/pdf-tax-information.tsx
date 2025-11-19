import { Text, View } from '@react-pdf/renderer';
import { __ } from '@wordpress/i18n';
import Html from 'react-pdf-html';

import { usePdf } from '@/contexts/pdf-context';
import { isDefined } from '@/utils';

const PdfTaxInformation = () => {
  const { pdfReceiptTemplate } = usePdf();

  const secondaryTextColor = pdfReceiptTemplate?.colors?.secondary_text ?? '#636363';

  if (!isDefined(pdfReceiptTemplate) || !pdfReceiptTemplate.content.tax_information?.is_available)
    return <View />;

  return (
    <View
      style={{
        backgroundColor: '#F9FAFB',
        borderLeft: 4,
        borderLeftColor: '#338C58',
        borderLeftStyle: 'solid',
        marginTop: 8,
        marginBottom: 8,
        paddingTop: 12,
        paddingBottom: 12,
        paddingLeft: 20,
        paddingRight: 12,
        borderRadius: 4,
      }}
    >
      <Text
        style={{
          fontWeight: 'bold',
          color: secondaryTextColor,
        }}
      >
        {__('Tax Information:', 'growfund')}
      </Text>
      <View style={{ marginTop: 8 }}>
        <Html
          resetStyles={false}
          stylesheet={{
            p: { marginBottom: 1, marginTop: 2, fontSize: 12, width: '100%' },
          }}
        >
          {pdfReceiptTemplate.content.tax_information.details ?? ''}
        </Html>
      </View>
    </View>
  );
};

export default PdfTaxInformation;

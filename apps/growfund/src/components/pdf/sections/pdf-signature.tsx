import { Image, Text, View } from '@react-pdf/renderer';
import Html from 'react-pdf-html';

import { usePdf } from '@/contexts/pdf-context';
import { isDefined } from '@/utils';
import { toPdfCompatibleImage } from '@/utils/media';

const PdfSignature = () => {
  const { pdfReceiptTemplate } = usePdf();

  if (!isDefined(pdfReceiptTemplate) || !pdfReceiptTemplate.content.signature?.is_available)
    return <View />;

  return (
    <View style={{ marginTop: 48 }}>
      <Text
        style={{
          marginBottom: 12,
          marginTop: 0,
        }}
      >
        Sincerely,
      </Text>
      {isDefined(pdfReceiptTemplate.content.signature.image) && (
        <View style={{ marginBottom: 12 }}>
          <Image
            style={{ height: 32, maxWidth: 64, borderRadius: 6 }}
            src={toPdfCompatibleImage(pdfReceiptTemplate.content.signature.image.url)}
          />
        </View>
      )}
      {pdfReceiptTemplate.content.signature.details && (
        <View>
          <Html
            resetStyles={false}
            stylesheet={{
              p: { marginBottom: 1, marginTop: 2, fontSize: 12, width: '100%' },
            }}
          >
            {pdfReceiptTemplate.content.signature.details ?? ''}
          </Html>
        </View>
      )}
    </View>
  );
};

export default PdfSignature;

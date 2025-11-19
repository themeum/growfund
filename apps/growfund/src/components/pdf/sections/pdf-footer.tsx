import { View } from '@react-pdf/renderer';
import Html from 'react-pdf-html';

import { usePdf } from '@/contexts/pdf-context';
import { isDefined } from '@/utils';

const PdfFooter = () => {
  const { pdfReceiptTemplate } = usePdf();

  if (!isDefined(pdfReceiptTemplate?.content.footer)) return <View />;

  return (
    <View
      style={{
        marginTop: 12,
        borderTop: 1,
        borderTopStyle: 'solid',
        borderTopColor: '#E6E6E6',
        paddingTop: 12,
      }}
    >
      <Html
        resetStyles={false}
        stylesheet={{
          p: { marginBottom: 1, marginTop: 2, fontSize: 12 },
        }}
      >
        {pdfReceiptTemplate.content.footer}
      </Html>
    </View>
  );
};

export default PdfFooter;

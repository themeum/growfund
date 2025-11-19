import { View } from '@react-pdf/renderer';
import Html from 'react-pdf-html';

import { usePdf } from '@/contexts/pdf-context';
import { isDefined } from '@/utils';

const PdfReceiptGreetings = () => {
  const { pdfReceiptTemplate } = usePdf();

  if (!isDefined(pdfReceiptTemplate?.content.greetings)) return <View />;

  return (
    <View style={{ paddingTop: 20, paddingBottom: 20 }}>
      <Html
        resetStyles={false}
        stylesheet={{
          p: { marginBottom: 1, marginTop: 2, fontSize: 12, width: '100%' },
        }}
      >
        {pdfReceiptTemplate.content.greetings}
      </Html>
    </View>
  );
};

export default PdfReceiptGreetings;

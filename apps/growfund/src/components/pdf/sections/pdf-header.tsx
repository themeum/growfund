import { Image, Text, View } from '@react-pdf/renderer';
import { useMemo } from 'react';

import { usePdf } from '@/contexts/pdf-context';
import { isDefined } from '@/utils';
import { toPdfCompatibleImage } from '@/utils/media';

const PdfHeader = ({ heading }: { heading: string }) => {
  const { pdfReceiptTemplate } = usePdf();

  const imagePosition = useMemo(() => {
    if (!isDefined(pdfReceiptTemplate?.media.position)) {
      return 'center';
    }

    if (pdfReceiptTemplate.media.position === 'left') {
      return 'flex-start';
    }

    if (pdfReceiptTemplate.media.position === 'right') {
      return 'flex-end';
    }

    return 'center';
  }, [pdfReceiptTemplate?.media.position]);
  return (
    <View
      style={{
        borderBottom: 1,
        borderBottomColor: '#E6E6E6',
        borderBottomStyle: 'solid',
        paddingTop: 12,
        paddingBottom: 12,
      }}
    >
      {isDefined(pdfReceiptTemplate?.media.image) && (
        <View style={{ flexDirection: 'row', justifyContent: imagePosition }}>
          <Image
            style={{
              height: pdfReceiptTemplate.media.height ?? 24,
              borderRadius: 6,
            }}
            src={toPdfCompatibleImage(pdfReceiptTemplate.media.image.url)}
          />
        </View>
      )}
      <Text
        style={{
          fontSize: 14,
          fontWeight: 'bold',
          textAlign: 'center',
          color: '#333333',
          marginTop: 16,
        }}
      >
        {heading}
      </Text>
    </View>
  );
};

export default PdfHeader;

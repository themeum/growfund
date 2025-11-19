import { __ } from '@wordpress/i18n';
import { useFormContext, useWatch } from 'react-hook-form';

import { BrandIcon } from '@/app/icons';
import { Box } from '@/components/ui/box';
import { Image } from '@/components/ui/image';
import { type EcardTemplateForm } from '@/features/settings/schemas/ecard';

const EcardPreview = () => {
  const form = useFormContext<EcardTemplateForm>();

  const greetings = useWatch({ control: form.control, name: 'content.greetings' });
  const description = useWatch({ control: form.control, name: 'content.description' });

  const mediaImage = useWatch({ control: form.control, name: 'media.image' });
  const imageHeight = useWatch({ control: form.control, name: 'media.height' });
  const imagePosition = useWatch({ control: form.control, name: 'media.position' });
  const greetingsColor = useWatch({ control: form.control, name: 'colors.greetings' });
  const backgroundColor = useWatch({ control: form.control, name: 'colors.background' });
  const textColor = useWatch({ control: form.control, name: 'colors.text_color' });
  const secondaryTextColor = useWatch({
    control: form.control,
    name: 'colors.secondary_text_color',
  });

  return (
    <Box
      className="growfund-overflow-hidden growfund-text-black growfund-w-[24rem]"
      style={{ backgroundColor: backgroundColor ?? '' }}
    >
      <div
        className="growfund-w-full growfund-flex"
        style={{ justifyContent: imagePosition, height: imageHeight ? `${imageHeight}px` : '' }}
      >
        <Image
          src={mediaImage?.url ?? `/images/ecard.webp`}
          alt="e-card image"
          className="growfund-p-0 growfund-m-0 growfund-rounded-none"
          fit="contain"
          style={{ backgroundColor: backgroundColor ?? '' }}
        />
      </div>
      <div className="growfund-pt-4 growfund-pb-6 growfund-px-8">
        <div className="growfund-typo-small growfund-font-semibold" style={{ color: greetingsColor ?? '' }}>
          {greetings && greetings}
        </div>
        <div className="growfund-mt-2" style={{ color: textColor ?? '' }}>
          <div dangerouslySetInnerHTML={{ __html: description }} />
        </div>
        <div className="growfund-flex growfund-flex-col growfund-gap-2 growfund-p-0 growfund-m-0 growfund-mt-5">
          <div className="growfund-typo-tiny" style={{ color: secondaryTextColor ?? '' }}>
            {__('March 26, 2026', 'growfund')}
          </div>
          <BrandIcon className="growfund-h-5" viewBox="0 0 210 24" />
        </div>
      </div>
    </Box>
  );
};

export default EcardPreview;

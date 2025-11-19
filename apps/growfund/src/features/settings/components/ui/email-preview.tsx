import { __ } from '@wordpress/i18n';
import { Link } from 'react-router';

import { BrandIcon } from '@/app/icons';
import placeholder from '@/assets/images/placeholder.svg';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Image } from '@/components/ui/image';
import { Separator } from '@/components/ui/separator';
import {
    EmailPreviewProvider,
    useEmailPreviewContext,
} from '@/features/settings/context/email-preview-context';
import { type EmailTemplate } from '@/features/settings/schemas/email-default-template';
import { cn } from '@/lib/utils';

const EmailCard = ({
  emailTemplate,
  children,
  className,
}: {
  emailTemplate?: EmailTemplate | null;
  children?: React.ReactNode;
  className?: string;
}) => {
  return (
    <EmailPreviewProvider emailTemplate={emailTemplate}>
      <Card
        className={className}
        style={{
          backgroundColor: emailTemplate?.colors?.background ?? '',
          color: emailTemplate?.colors?.text ?? '',
        }}
      >
        {children}
      </Card>
    </EmailPreviewProvider>
  );
};

const EmailContent = ({
  className,
  children,
}: {
  className?: string;
  children?: React.ReactNode;
}) => {
  return (
    <CardContent className={cn('growfund-py-4 growfund-px-16 growfund-flex growfund-flex-col growfund-gap-5', className)}>
      {children}
    </CardContent>
  );
};

const EmailHeader = ({ className, heading }: { className?: string; heading: string }) => {
  const { emailTemplate } = useEmailPreviewContext();

  return (
    <CardTitle
      className={cn('growfund-pt-6', className)}
      style={{ color: emailTemplate?.colors?.label ?? '' }}
    >
      {heading}
    </CardTitle>
  );
};

const EmailMessage = ({
  children,
  className,
}: {
  children?: React.ReactNode;
  className?: string;
}) => {
  const { emailTemplate } = useEmailPreviewContext();

  if (typeof children === 'string') {
    return (
      <CardDescription
        className={className}
        style={{
          color: emailTemplate?.colors?.text ?? '',
        }}
        dangerouslySetInnerHTML={{ __html: children }}
      />
    );
  }

  return (
    <CardDescription
      className={className}
      style={{
        color: emailTemplate?.colors?.text ?? '',
      }}
    >
      {children}
    </CardDescription>
  );
};

const EmailAdditionalContent = ({ className }: { className?: string }) => {
  const { emailTemplate } = useEmailPreviewContext();

  return (
    <>
      {emailTemplate?.content?.additional && (
        <div
          className={cn('growfund-mt-4', className)}
          dangerouslySetInnerHTML={{ __html: emailTemplate.content.additional }}
        ></div>
      )}
    </>
  );
};

const EmailLogo = () => {
  const { emailTemplate } = useEmailPreviewContext();

  return (
    <>
      <CardHeader className="growfund-py-6 growfund-px-16">
        {emailTemplate?.media?.image?.url ? (
          <div
            className="growfund-flex growfund-gap-2 growfund-items-center"
            style={{
              justifyContent: emailTemplate.media.position,
            }}
          >
            <Image
              src={emailTemplate.media.image.url}
              alt="Logo"
              rounded="none"
              style={{
                height: emailTemplate.media.height ? `${emailTemplate.media.height}px` : '',
              }}
              className="growfund-bg-transparent growfund-border-none"
            />
          </div>
        ) : (
          <div className="growfund-flex growfund-gap-2 growfund-items-center">
            <BrandIcon className="growfund-h-5" />
          </div>
        )}
      </CardHeader>
      <Separator />
    </>
  );
};

const EmailFooter = () => {
  const { emailTemplate } = useEmailPreviewContext();
  const hasCustomFooter = !!emailTemplate?.content?.footer;

  return (
    <>
      <Separator />
      <CardFooter className="growfund-py-6 growfund-px-16 growfund-justify-center">
        {hasCustomFooter ? (
          <div dangerouslySetInnerHTML={{ __html: emailTemplate.content?.footer ?? '' }} />
        ) : (
          <div className="growfund-text-center growfund-text-base">
            <div className="growfund-mb-1 growfund-text-fg-subdued growfund-typo-tiny">
              {__('Add footer from default email template.', 'growfund')}
            </div>
          </div>
        )}
      </CardFooter>
    </>
  );
};

const EmailDefaultContent = ({ className }: { className?: string }) => {
  const { emailTemplate } = useEmailPreviewContext();

  return (
    <Card className={cn('growfund-bg-background-surface-alt growfund-rounded-lg', className)}>
      <CardContent
        className="growfund-mt-2 growfund-space-y-2"
        style={{ color: emailTemplate?.colors?.text ?? '' }}
      >
        <p className="growfund-typo-small growfund-font-bold">
          {__('February 24th, 2025 by', 'growfund')}{' '}
          <span className="growfund-font-normal">Rick Horan</span>
        </p>
        <Image src={placeholder} alt="image" className="growfund-h-[190px]" />
        <p className="growfund-typo-small ">
          {__(
            'Once upon a time in a small town, there lived a brave little girl named Lily. At just eight years old, she faced the biggest challenge of her life: cancer. With her bright smile and unyielding spirit, she fought through countless treatments...',
            'growfund',
          )}
        </p>
        <EmailButton label={__('See Update', 'growfund')} />
      </CardContent>
    </Card>
  );
};

const EmailButton = ({ label, className }: { label: string; className?: string }) => {
  const { emailTemplate } = useEmailPreviewContext();

  return (
    <Button
      className={cn('growfund-w-full', className)}
      variant="primary"
      style={{
        color: emailTemplate?.colors?.button ?? '',
        backgroundColor: emailTemplate?.colors?.button_background ?? '',
      }}
    >
      {label}
    </Button>
  );
};

const EmailLink = ({ to, text, className }: { to: string; text: string; className?: string }) => {
  const { emailTemplate } = useEmailPreviewContext();

  return (
    <Link to={to} className={className} style={{ color: emailTemplate?.colors?.link ?? '' }}>
      {text}
    </Link>
  );
};

export {
    EmailAdditionalContent,
    EmailButton,
    EmailCard,
    EmailContent,
    EmailDefaultContent,
    EmailFooter,
    EmailHeader,
    EmailLink,
    EmailLogo,
    EmailMessage
};


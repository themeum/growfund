import { Toaster as Sonner } from 'sonner';

type ToasterProps = React.ComponentProps<typeof Sonner>;

const Toaster = ({ ...props }: ToasterProps) => {
  return (
    <Sonner
      theme={'system'}
      className="growfund-toaster growfund-group"
      toastOptions={{
        classNames: {
          toast:
            'growfund-group growfund-toast group-[.toaster]:growfund-bg-background group-[.toaster]:growfund-text-foreground group-[.toaster]:growfund-border-border group-[.toaster]:growfund-shadow-lg',
          description: 'group-[.toast]:growfund-text-muted-foreground',
          actionButton: 'group-[.toast]:growfund-bg-primary group-[.toast]:growfund-text-primary-foreground',
          cancelButton: 'group-[.toast]:growfund-bg-muted group-[.toast]:growfund-text-muted-foreground',
          success: '[&_svg]:growfund-text-icon-success',
          error: '[&_svg]:growfund-text-icon-critical',
          info: '[&_svg]:growfund-text-icon-info',
          warning: '[&_svg]:growfund-text-icon-warning',
        },
      }}
      {...props}
    />
  );
};

export { Toaster };

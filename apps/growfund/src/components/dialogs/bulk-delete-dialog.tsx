import { __, sprintf } from '@wordpress/i18n';
import React from 'react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Box } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Image } from '@/components/ui/image';
import { useCurrency } from '@/hooks/use-currency';

export type BulkDeleteItemType =
  | 'fund'
  | 'campaign'
  | 'donation'
  | 'pledge'
  | 'contributor'
  | 'category'
  | 'tag';

interface Data {
  id?: string;
  image?: string | null;
  name: string;
  email?: string;
  amount?: number;
}

interface BulkDeleteDialogProps {
  title: string | React.ReactNode;
  description: string | React.ReactNode;
  open: boolean;
  setOpen: (open: boolean) => void;
  deleteButtonText?: string;
  cancelButtonText?: string;
  data: Data[];
  onDelete: (closeDialog: () => void) => void;
  isAvatar?: boolean;
  showImage?: boolean;
  loading?: boolean;
  type: BulkDeleteItemType;
}

const BulkDeleteDialog = ({
  title,
  description,
  open,
  setOpen,
  deleteButtonText = __('Delete', 'growfund'),
  cancelButtonText = __('Cancel', 'growfund'),
  data,
  isAvatar = false,
  showImage = true,
  onDelete,
  type,
  loading = false,
}: React.PropsWithChildren<BulkDeleteDialogProps>) => {
  const { toCurrency } = useCurrency();

  const renderItem = (item: Data, type: BulkDeleteItemType) => {
    switch (type) {
      case 'fund':
        return (
          <>
            <div className="growfund-shrink-0 growfund-max-w-60 growfund-m-3">
              <span className="growfund-truncate">{item.name}</span>
            </div>
            <div>
              {item.amount && <span className="growfund-font-medium">{toCurrency(item.amount)}</span>}
            </div>
          </>
        );

      case 'campaign':
        return (
          <>
            {/* translators: %s: campaign ID */}
            <div className="growfund-shrink-0">{sprintf(__('ID #%s', 'growfund'), item.id)}</div>
            <div className="growfund-shrink-0">
              {showImage &&
                (isAvatar ? (
                  <Avatar className="growfund-size-8">
                    <AvatarImage src={item.image ?? undefined} alt={item.name} />
                    <AvatarFallback>{item.name.charAt(0)}</AvatarFallback>
                  </Avatar>
                ) : (
                  <Image
                    src={item.image ?? null}
                    alt={item.name}
                    className="growfund-size-8"
                    fit="cover"
                    aspectRatio="square"
                  />
                ))}
            </div>
            <div className="growfund-max-w-60 growfund-flex growfund-flex-col growfund-gap-1" title={item.name}>
              <span className="growfund-truncate">{item.name}</span>
            </div>
          </>
        );
      case 'contributor':
        return (
          <div className="growfund-flex growfund-gap-4 growfund-items-center growfund-w-full">
            <div className="growfund-shrink-0 growfund-min-w-[100px]">
              {/* translators: %s: contributor ID (backer ID or donor ID) */}
              {sprintf(__('ID #%s', 'growfund'), item.id)}
            </div>
            <div className="growfund-shrink-0">
              {showImage &&
                (isAvatar ? (
                  <Avatar className="growfund-size-8">
                    <AvatarImage src={item.image ?? undefined} alt={item.name} />
                    <AvatarFallback>{item.name.charAt(0)}</AvatarFallback>
                  </Avatar>
                ) : (
                  <Image
                    src={item.image ?? null}
                    alt={item.name}
                    className="growfund-size-8 growfund-rounded-full"
                    fit="cover"
                    aspectRatio="square"
                  />
                ))}
            </div>
            <div className="growfund-flex growfund-flex-col growfund-gap-1 growfund-max-w-60" title={item.name}>
              <span className="growfund-truncate">{item.name}</span>
              {item.email && <span className="growfund-truncate">{item.email}</span>}
            </div>
          </div>
        );
      case 'category':
        return (
          <div className="growfund-flex growfund-gap-4 growfund-items-center growfund-w-full">
            <div className="growfund-shrink-0">
              {showImage &&
                (isAvatar ? (
                  <Avatar className="growfund-size-8">
                    <AvatarImage src={item.image ?? undefined} alt={item.name} />
                    <AvatarFallback>{item.name.charAt(0)}</AvatarFallback>
                  </Avatar>
                ) : (
                  <Image
                    src={item.image ?? null}
                    alt={item.name}
                    className="growfund-size-8"
                    fit="cover"
                    aspectRatio="square"
                  />
                ))}
            </div>

            <div className="growfund-flex growfund-flex-col growfund-gap-1 growfund-max-w-60" title={item.name}>
              <span className="growfund-truncate">{item.name}</span>
            </div>
          </div>
        );
      case 'tag':
        return (
          <div className="growfund-m-2 growfund-max-w-60">
            <span className="growfund-truncate">{item.name}</span>
          </div>
        );

      case 'pledge':
      case 'donation':
        return (
          <>
            {/* translators: %s: contribution ID (pledge ID or donation ID) */}
            <div className="growfund-shrink-0 growfund-my-3">{sprintf(__('ID #%s', 'growfund'), item.id)}</div>
            {item.amount && (
              <div className="growfund-shrink-0 growfund-font-medium">{toCurrency(item.amount)}</div>
            )}
            <div className="growfund-truncate growfund-text-fg-secondary growfund-max-w-60">
              {/* translators: %s: contributor name (backer name or donor name) */}
              {sprintf(__('by %s', 'growfund'), item.name)}
            </div>
          </>
        );
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogCloseButton />
        </DialogHeader>

        <div className="growfund-px-4 growfund-py-0 growfund-space-y-3">
          <p className="growfund-typo-sm growfund-text-fg-secondary">{description}</p>
          <Box className="growfund-max-h-96 growfund-overflow-y-auto growfund-border-border-tertiary growfund-shadow-none">
            {data.map((item) => {
              return (
                <div
                  key={item.id}
                  className="growfund-grid growfund-grid-cols-[1fr_1fr_6fr] growfund-items-center growfund-gap-4 growfund-space-x-3 [&:not(:last-of-type)]:growfund-border-b [&:not(:last-of-type)]:growfund-border-b-border growfund-px-6 growfund-py-2 growfund-typo-tiny growfund-text-fg-primary"
                >
                  {renderItem(item, type)}
                </div>
              );
            })}
          </Box>
        </div>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline" disabled={loading}>
              {cancelButtonText}
            </Button>
          </DialogClose>
          <Button
            variant="destructive"
            loading={loading}
            disabled={loading}
            onClick={() => {
              onDelete(() => {
                setOpen(false);
              });
            }}
          >
            {deleteButtonText}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default BulkDeleteDialog;

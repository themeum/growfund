import { __ } from '@wordpress/i18n';
import { type RefObject } from 'react';

import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { DialogTitle } from '@/components/ui/dialog';
import { Drawer, DrawerContent, DrawerTrigger } from '@/components/ui/drawer';
import {
    Popover,
    PopoverAnchor,
    PopoverContent,
    PopoverPortal,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useIsMobile } from '@/hooks/use-mobile';

interface DialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  allShortcodes: { label: string; value: string }[];
  onSelect: (shortcode: string) => void;
  popoverRef: RefObject<HTMLDivElement | null>;
  popoverContainer: Element | null;
}

export function ManageEditorShortCodes({
  open,
  onOpenChange,
  allShortcodes,
  onSelect,
  popoverRef,
  popoverContainer,
}: DialogProps) {
  const isMobile = useIsMobile();

  if (!isMobile) {
    return (
      <Popover open={open} onOpenChange={onOpenChange}>
        <PopoverTrigger asChild>
          <Button variant="ghost" className="growfund-hidden" />
        </PopoverTrigger>
        <PopoverAnchor asChild>
          <div ref={popoverRef} />
        </PopoverAnchor>
        <PopoverPortal container={popoverContainer}>
          <PopoverContent className="growfund-min-w-[12rem] growfund-w-0 growfund-p-0" align="end">
            <ShortcodeList
              onOpenChange={onOpenChange}
              setSelectedShortcode={onSelect}
              allShortcodes={allShortcodes}
            />
          </PopoverContent>
        </PopoverPortal>
      </Popover>
    );
  }

  return (
    <Drawer open={open} onOpenChange={onOpenChange}>
      <DrawerTrigger asChild>
        <Button variant="ghost" className="growfund-hidden" />
      </DrawerTrigger>
      <DrawerContent className="growfund-p-4">
        <DialogTitle>{__('Shortcode Selector', 'growfund')}</DialogTitle>
        <div className="growfund-mt-4 growfund-border-t">
          <ShortcodeList
            onOpenChange={onOpenChange}
            setSelectedShortcode={onSelect}
            allShortcodes={allShortcodes}
          />
        </div>
      </DrawerContent>
    </Drawer>
  );
}

function ShortcodeList({
  onOpenChange,
  setSelectedShortcode,
  allShortcodes,
}: {
  onOpenChange: (open: boolean) => void;
  setSelectedShortcode: (shortcode: string) => void;
  allShortcodes: { label: string; value: string }[];
}) {
  return (
    <Command>
      <CommandInput placeholder={__('Search shortcodes', 'growfund')} />
      <CommandList>
        <CommandEmpty>{__('No shortcode found.', 'growfund')}</CommandEmpty>
        <CommandGroup>
          {allShortcodes.map((shortcode, index) => {
            return (
              <CommandItem
                key={index}
                value={shortcode.value}
                onSelect={(value) => {
                  setSelectedShortcode(
                    allShortcodes.find((priority) => priority.value === value)?.value ?? '',
                  );
                  onOpenChange(false);
                }}
              >
                {shortcode.label}
              </CommandItem>
            );
          })}
        </CommandGroup>
      </CommandList>
    </Command>
  );
}

export default ManageEditorShortCodes;

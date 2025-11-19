import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { Edit3, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import z from 'zod';

import { TextareaField } from '@/components/form/textarea-field';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { isDefined } from '@/utils';

interface AddNotesProps {
  value?: string | null;
  onChange: (notes: string | null) => void;
}

const NotesSchema = z.object({
  notes: z.string().min(1, { message: __('Notes are required', 'growfund') }),
});

type NotesFormFields = z.infer<typeof NotesSchema>;

const DonationNotesCard = ({ value, onChange }: AddNotesProps) => {
  const form = useForm<NotesFormFields>({
    resolver: zodResolver(NotesSchema),
    defaultValues: { notes: value ?? '' },
  });
  const [openForm, setOpenForm] = useState(false);
  const hasNoteContent = isDefined(value) && value.length > 0;
  const onSubmit = (values: NotesFormFields) => {
    onChange(values.notes);
    setOpenForm(false);
  };

  return (
    <Box className="growfund-group/notes">
      <BoxContent>
        <Form {...form}>
          <div className="growfund-flex growfund-items-center growfund-justify-between growfund-min-h-9">
            <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary">
              {__('Notes', 'growfund')}
            </h6>

            {hasNoteContent && !openForm && (
              <div className="growfund-flex growfund-items-center growfund-opacity-0 group-hover/notes:growfund-opacity-100 growfund-transition-opacity">
                <Button
                  variant="ghost"
                  size="icon"
                  className="hover:growfund-text-icon-critical"
                  onClick={() => {
                    onChange(null);
                  }}
                >
                  <Trash2 />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={() => {
                    setOpenForm(true);
                  }}
                >
                  <Edit3 />
                </Button>
              </div>
            )}
          </div>
          {hasNoteContent && !openForm && (
            <span className="growfund-mt-3 growfund-typo-small growfund-font-medium growfund-text-fg-secondary">
              {value}
            </span>
          )}

          {!hasNoteContent && !openForm && (
            <Button
              variant="secondary"
              onClick={() => {
                setOpenForm(true);
              }}
              className="growfund-w-full"
            >
              <Plus />
              {__('Add Notes', 'growfund')}
            </Button>
          )}

          {openForm && (
            <div className="growfund-flex-col growfund-flex growfund-gap-3 growfund-mt-2">
              <TextareaField
                control={form.control}
                name="notes"
                placeholder={__('Add notes', 'growfund')}
                autoFocus
              />
              <div className="growfund-flex growfund-justify-end growfund-gap-2">
                <Button
                  onClick={() => {
                    form.reset({ notes: value ?? '' });
                    setOpenForm(false);
                  }}
                  variant="outline"
                >
                  {__('Cancel', 'growfund')}
                </Button>
                <Button
                  onClick={form.handleSubmit(onSubmit, (errors) => {
                    console.error(errors);
                  })}
                >
                  {__('Save', 'growfund')}
                </Button>
              </div>
            </div>
          )}
        </Form>
      </BoxContent>
    </Box>
  );
};

export default DonationNotesCard;

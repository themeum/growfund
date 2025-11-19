import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import { useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import { MessagesEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { MessageDisplay } from '@/features/campaigns/components/giving-thanks/message-display';
import { MessageForm } from '@/features/campaigns/components/giving-thanks/message-form';
import { type CampaignForm } from '@/features/campaigns/schemas/campaign';
import { cn } from '@/lib/utils';

export const GivingThanks = () => {
  const form = useFormContext<CampaignForm>();
  const messages = useWatch({ control: form.control, name: 'giving_thanks' }) ?? [];
  const [isCreateNew, setIsCreateNew] = useState(false);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);

  const { remove, append, update } = useFieldArray({
    control: form.control,
    name: 'giving_thanks',
  });

  const appreciationType = useWatch({
    control: form.control,
    name: 'appreciation_type',
  });

  if (appreciationType !== 'giving-thanks') {
    return null;
  }

  const error = form.getFieldState('giving_thanks').error;

  return (
    <div className="growfund-space-y-2">
      <div className={cn('growfund-space-y-3 growfund-bg-background-surface growfund-rounded-lg growfund-p-4')}>
        <span className={cn('growfund-font-medium growfund-typo-small growfund-text-fg-primary')}>
          {__('Thanks-giving Messages', 'growfund')}
        </span>

        {!!error && (
          <Alert variant="destructive">
            {__(
              'Please add at least one thank-you message before publishing your campaign.',
              'growfund',
            )}
          </Alert>
        )}

        {messages.length === 0 && !isCreateNew && (
          <EmptyState className="growfund-bg-background-surface-secondary growfund-shadow-sm growfund-border-none">
            <MessagesEmptyStateIcon />
            <EmptyStateDescription>
              {__('No messages added yet. Add at least one message to publish.', 'growfund')}
            </EmptyStateDescription>
          </EmptyState>
        )}

        {messages.map((message, index) => {
          if (editingIndex === index) {
            return (
              <div
                key={index}
                className="growfund-mt-2 growfund-space-y-2 growfund-bg-background-surface growfund-px-4 growfund-py-3 growfund-border growfund-border-border growfund-rounded-md"
              >
                <MessageForm
                  key={index}
                  defaultData={{
                    from: message.pledge_from,
                    to: message.pledge_to,
                    message: message.appreciation_message,
                  }}
                  onSave={(data) => {
                    update(index, {
                      pledge_from: data.from,
                      pledge_to: data.to,
                      appreciation_message: data.message,
                    });
                    setIsCreateNew(false);
                    setEditingIndex(null);
                  }}
                  onRemove={() => {
                    remove(index);
                    setIsCreateNew(false);
                    setEditingIndex(null);
                  }}
                  onCancel={() => {
                    setIsCreateNew(false);
                    setEditingIndex(null);
                  }}
                />
              </div>
            );
          }
          return (
            <div
              key={index}
              className="growfund-mt-2 growfund-space-y-2 growfund-bg-background-surface growfund-px-4 growfund-py-3 growfund-border growfund-border-border growfund-rounded-md"
            >
              <MessageDisplay
                key={index}
                formatData={message}
                onEdit={() => {
                  setEditingIndex(index);
                }}
                onRemove={() => {
                  setEditingIndex(null);
                  remove(index);
                }}
                disabled={isCreateNew || editingIndex !== null}
              />
            </div>
          );
        })}

        {editingIndex === null && !isCreateNew && (
          <Button
            variant="secondary"
            className="growfund-w-full growfund-mt-3"
            onClick={() => {
              setIsCreateNew(true);
            }}
          >
            <Plus />
            {__('Add Message', 'growfund')}
          </Button>
        )}

        {isCreateNew && (
          <div className="growfund-mt-2 growfund-space-y-2 growfund-bg-background-surface growfund-px-4 growfund-py-3 growfund-border growfund-border-border growfund-rounded-md">
            <MessageForm
              onSave={(data) => {
                append({
                  pledge_from: data.from,
                  pledge_to: data.to,
                  appreciation_message: data.message,
                });
                setIsCreateNew(false);
              }}
              onCancel={() => {
                setIsCreateNew(false);
              }}
            />
          </div>
        )}
      </div>
    </div>
  );
};

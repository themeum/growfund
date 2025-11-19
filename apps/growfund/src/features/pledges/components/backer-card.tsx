import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { UserSearchOrAddField } from '@/components/form/user-search-or-add';
import { Box, BoxContent } from '@/components/ui/box';
import UserPreviewCard from '@/components/users/user-preview-card';
import { LIST_LIMIT } from '@/constants/list-limits';
import { type Backer, type BackerInfo } from '@/features/backers/schemas/backer';
import { useBackersQuery } from '@/features/backers/services/backer';
import ManageBackerDialog from '@/features/pledges/components/dialogs/manage-backer-dialog';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';
import { useDebounce } from '@/hooks/use-debounce';
import { isDefined } from '@/utils';

const BackerCard = ({
  selectedBacker,
  setBacker,
}: {
  selectedBacker?: BackerInfo | null;
  setBacker?: (backer: BackerInfo | null) => void;
}) => {
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');
  const form = useFormContext<PledgeForm>();

  const debouncedSearch = useDebounce(search);

  const backersQuery = useBackersQuery({
    per_page: LIST_LIMIT.BACKER_FIELD,
    search: debouncedSearch,
  });

  const backerId = useWatch({ control: form.control, name: 'user_id' });

  return (
    <>
      <ManageBackerDialog
        open={open}
        onOpenChange={setOpen}
        defaultValues={selectedBacker ?? undefined}
        onSaveChanges={(backer) => {
          form.setValue('user_id', backer.id);
          setBacker?.(backer);
        }}
      />
      <Box className="growfund-group/search">
        {!isDefined(selectedBacker) && (
          <BoxContent>
            <UserSearchOrAddField
              control={form.control}
              name="user_id"
              label={__('Backer', 'growfund')}
              placeholder={__('Select or add a backer', 'growfund')}
              options={backersQuery.data?.results ?? []}
              onSearchChange={setSearch}
              searchValue={search}
              selectedValue={backerId}
              onSelectChange={(id) => {
                if (id) {
                  form.setValue('user_id', id);
                  const backer = backersQuery.data?.results.find((backer) => backer.id === id);
                  setBacker?.(backer ?? null);
                }
              }}
              onClickAddButton={(event) => {
                event.preventDefault();
                setOpen(true);
              }}
              loading={backersQuery.isFetching || backersQuery.isLoading}
            />
          </BoxContent>
        )}
        {isDefined(selectedBacker) && (
          <UserPreviewCard
            user={selectedBacker as Backer}
            title={__('Backer', 'growfund')}
            onRemove={() => {
              form.setValue('user_id', null);
              setBacker?.(null);
            }}
            onEdit={() => {
              setOpen(true);
            }}
          />
        )}
      </Box>
    </>
  );
};

export default BackerCard;

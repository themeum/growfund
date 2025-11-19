import { __, sprintf } from '@wordpress/i18n';
import { useMemo, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { UserSearchOrAddField } from '@/components/form/user-search-or-add';
import { Box, BoxContent } from '@/components/ui/box';
import UserPreviewCard from '@/components/users/user-preview-card';
import { LIST_LIMIT } from '@/constants/list-limits';
import { type DonationForm } from '@/features/donations/schemas/donation-form';
import ManageDonorDialog from '@/features/donors/components/dialogs/manage-donor-dialog';
import { useDonorsQuery } from '@/features/donors/services/donor';
import { useDebounce } from '@/hooks/use-debounce';
import { isDefined } from '@/utils';

const DonorCard = () => {
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');
  const form = useFormContext<DonationForm>();
  const debouncedSearch = useDebounce(search);

  const donorsQuery = useDonorsQuery({
    per_page: LIST_LIMIT.DONOR_FIELD,
    search: debouncedSearch,
  });

  const donorId = useWatch({ control: form.control, name: 'user_id' });

  const donor = useMemo(() => {
    return donorsQuery.data?.results.find((user) => user.id === donorId);
  }, [donorId, donorsQuery.data?.results]);

  return (
    <>
      <ManageDonorDialog isOpen={open} onOpenChange={setOpen} defaultValues={donor} />

      <Box className="growfund-group/search">
        {!isDefined(donor) && (
          <BoxContent>
            <UserSearchOrAddField
              control={form.control}
              name="user_id"
              label={__('Donor', 'growfund')}
              placeholder={__('Select or Add a donor', 'growfund')}
              options={
                donorsQuery.data?.results
                  ? donorsQuery.data.results.map((user) => ({
                      id: user.id,
                      name: sprintf('%s %s', user.first_name, user.last_name),
                      email: user.email,
                      image: user.image,
                      first_name: user.first_name,
                      last_name: user.last_name,
                    }))
                  : []
              }
              onSearchChange={setSearch}
              searchValue={search}
              selectedValue={donorId}
              onSelectChange={(id) => {
                if (id) {
                  form.setValue('user_id', id);
                }
              }}
              onClickAddButton={(event) => {
                event.preventDefault();
                setOpen(true);
              }}
            />
          </BoxContent>
        )}
        {isDefined(donor) && (
          <UserPreviewCard
            user={donor}
            title={__('Donor', 'growfund')}
            onRemove={() => {
              form.setValue('user_id', '');
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

export default DonorCard;

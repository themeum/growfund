import { __, sprintf } from '@wordpress/i18n';
import { Bookmark } from 'lucide-react';
import { parseAsString } from 'nuqs';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';

import { TextField } from '@/components/form/text-field';
import { Container } from '@/components/layouts/container';
import { Form } from '@/components/ui/form';
import BookmarkedCampaigns from '@/dashboards/shared/components/bookmarked-campaigns';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import useCurrentUser from '@/hooks/use-current-user';
import { useQueryParamsStates } from '@/hooks/use-query-params-states';

const UserBookmarksPage = () => {
  const { setTopbar } = useDashboardLayoutContext();
  const { currentUser: user } = useCurrentUser();
  const [queryMeta, setQueryMeta] = useState({ overall: 0, total: 0 });
  const { params } = useQueryParamsStates({
    search: parseAsString,
  });

  const form = useForm<{ search: string }>({
    defaultValues: {
      search: params.search ?? undefined,
    },
  });

  useEffect(() => {
    setTopbar({
      title: __('Bookmarks', 'growfund'),
      icon: Bookmark,
    });
  }, [setTopbar]);

  return (
    <Container className="growfund-mt-10 growfund-space-y-3" size="sm">
      <Form {...form}>
        {queryMeta.overall > 0 && (
          <div className="growfund-flex growfund-items-center growfund-justify-between">
            <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-text-fg-primary">
              <h5 className="growfund-typo-h5 growfund-font-semibold">{__('Bookmarks', 'growfund')}</h5>
              <h6 className="growfund-typo-h6 growfund-font-regular">{sprintf('(%s)', queryMeta.total)}</h6>
            </div>
            <div className="growfund-max-w-[25rem] growfund-flex growfund-items-center growfund-gap-2">
              <TextField
                control={form.control}
                name="search"
                type="search"
                placeholder={__('Search...', 'growfund')}
              />
            </div>
          </div>
        )}
        <BookmarkedCampaigns onLoadData={setQueryMeta} userId={user.id} />
      </Form>
    </Container>
  );
};

export default UserBookmarksPage;

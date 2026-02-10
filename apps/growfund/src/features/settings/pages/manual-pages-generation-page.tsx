import { zodResolver } from '@hookform/resolvers/zod';
import { createColumnHelper } from '@tanstack/react-table';
import { __ } from '@wordpress/i18n';
import { AlertTriangle, ExternalLink, HammerIcon } from 'lucide-react';
import { useCallback, useEffect, useMemo } from 'react';
import { type Path, useForm } from 'react-hook-form';

import { BrandIcon, ErrorIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { SelectField } from '@/components/form/select-field';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { DataTable, DataTableContent, DataTableWrapper } from '@/components/table/data-table';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { InfoTooltip } from '@/components/ui/tooltip';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  PageSettingsSchema,
  type PageSettingsSchemaForm,
} from '@/features/settings/schemas/settings';
import { useWordPressPagesQuery } from '@/features/settings/services/general';
import {
  useGetManualPagesQuery,
  useRegeneratePagesMutation,
} from '@/features/settings/services/manual-pages';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { type WPPage } from '@/schemas/wp-page';
import { type TableColumnDef } from '@/types';
import { matchQueryStatus } from '@/utils/match-query-status';

const columnHelper = createColumnHelper<WPPage>();

const TITLE_MAP: Record<string, string> = {
  login_page: __('Login', 'growfund'),
  registration_page: __('Registration', 'growfund'),
  fundraiser_registration_page: __('Fundraiser Registration', 'growfund'),
  campaigns_page: __('Campaigns', 'growfund'),
  checkout_page: __('Checkout', 'growfund'),
  privacy_policy_page: __('Privacy Policy', 'growfund'),
  terms_and_conditions_page: __('Terms and Conditions', 'growfund'),
};

const STATUS_MAP: Record<string, string> = {
  published: __('Active', 'growfund'),
  'not-found': __('Not Found', 'growfund'),
  draft: __('Inaccessible', 'growfund'),
};

const TOOLTIP_BACKGROUND_MAP: Record<string, string> = {
  published: 'growfund-bg-background-fill-brand',
  'not-found': 'growfund-bg-background-fill-critical',
  draft: 'growfund-bg-background-fill-critical',
};

const TOOLTIP_TEXT_COLOR_MAP: Record<string, string> = {
  published: 'growfund-text-fg-brand',
  'not-found': 'growfund-text-fg-critical',
  draft: 'growfund-text-fg-critical',
};

const ManualPagesGenerationSettingspage = () => {
  const { appConfig } = useAppConfig();

  const form = useForm<PageSettingsSchemaForm>({
    resolver: zodResolver(PageSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, appConfig[AppConfigKeys.Page] ?? {});
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<PageSettingsSchemaForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Page, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const wordpressPagesQuery = useWordPressPagesQuery();

  const wordpressPages = useMemo(() => {
    if (!wordpressPagesQuery.data) {
      return [];
    }
    return wordpressPagesQuery.data;
  }, [wordpressPagesQuery.data]);

  const pagesOptions = useMemo(() => {
    return wordpressPages.map((page) => ({ label: page.name, value: page.id }));
  }, [wordpressPages]);

  const growfundPagesQuery = useGetManualPagesQuery();

  const formValues = form.watch();

  const growfundPages = useMemo(() => {
    if (!growfundPagesQuery.data) {
      return [];
    }
    return growfundPagesQuery.data.map((page) => {
      const pageKey = page.page_key as Path<PageSettingsSchemaForm>;
      const id = formValues[pageKey];
      if (id !== page.id) {
        const newPage = wordpressPages.find((wpPage) => wpPage.id === id) ?? page;
        return { ...newPage, page_key: pageKey };
      }
      return page;
    });
  }, [growfundPagesQuery.data, formValues, wordpressPages]);

  const generateManualPagesMutation = useRegeneratePagesMutation();

  const handleRegenerate = useCallback(() => {
    generateManualPagesMutation.mutate();
  }, [generateManualPagesMutation]);

  const isNotAllPublished = useMemo(() => {
    if (!growfundPagesQuery.data) return true;
    return growfundPagesQuery.data.some((page) => page.status !== 'published');
  }, [growfundPagesQuery.data]);

  const columns = useMemo(() => {
    return [
      columnHelper.accessor('id', {
        header: () => __('ID', 'growfund'),
        cell: (props) => props.getValue() || '#',
        size: 80,
      }),
      columnHelper.accessor('slug', {
        header: () => __('Title', 'growfund'),
        cell: (props) => {
          const { slug, url, page_key } = props.row.original;
          const displayTitle = TITLE_MAP[page_key ?? ''] || slug || '--';
          return (
            <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-group growfund-w-[250px] growfund-overflow-hidden">
              <span className="growfund-truncate growfund-transition-opacity growfund-duration-200 group-hover:growfund-hidden">
                {displayTitle}
              </span>

              <span className="growfund-hidden group-hover:growfund-flex growfund-gap-2">
                <span className="growfund-text-fg-secondary growfund-truncate">
                  {slug ? slug : '--'}
                </span>

                {url && (
                  <a
                    href={url}
                    target="_blank"
                    rel="noreferrer"
                    className="growfund-transition growfund-duration-200"
                  >
                    <ExternalLink size={14} />
                  </a>
                )}
              </span>
            </div>
          );
        },
        size: 300,
      }),
      columnHelper.accessor('name', {
        header: () => __('Page', 'growfund'),
        size: 200,
        cell: (props) => {
          const row = props.row.original;
          if (!row.page_key) {
            return <span className="growfund-text-critical">{__('No page key', 'growfund')}</span>;
          }
          const fieldKey = row.page_key as Path<PageSettingsSchemaForm>;
          const currentValue = form.getValues(fieldKey);
          const selectedOption = pagesOptions.find(
            (option) => String(option.value) === String(currentValue || row.id),
          );

          const displayValue = selectedOption ? selectedOption.label : row.name || '--';

          return (
            <div className="growfund-group growfund-relative growfund-h-[36px] growfund-flex growfund-items-center growfund-w-full">
              <div className="growfund-w-full growfund-h-full growfund-leading-9 growfund-truncate growfund-cursor-pointer group-hover:growfund-invisible growfund-absolute growfund-left-0 growfund-top-0">
                {displayValue}
              </div>
              <div className="growfund-absolute growfund-left-[-12px] growfund-top-0 growfund-invisible group-hover:growfund-visible growfund-max-w-48 growfund-w-full growfund-z-20 group-hover:growfund-opacity-100 focus-within:growfund-opacity-100">
                <SelectField
                  control={form.control}
                  name={fieldKey}
                  options={pagesOptions}
                  placeholder={__('Change page', 'growfund')}
                />
              </div>
            </div>
          );
        },
      }),

      columnHelper.accessor('status', {
        header: () => __('Status', 'growfund'),
        size: 150,
        cell: (props) => {
          const status = props.getValue();
          const bgClass = TOOLTIP_BACKGROUND_MAP[status] || 'growfund-bg-gray-500';
          const textClass = TOOLTIP_TEXT_COLOR_MAP[status] || 'growfund-text-gray-500';

          return (
            <div className="growfund-relative growfund-flex growfund-items-center growfund-pl-4">
              <span
                className={`growfund-w-1 growfund-h-5 growfund-rounded-full growfund-absolute growfund-left-0 growfund-top-1/2 -growfund-translate-y-1/2 ${bgClass}`}
              />
              <span className={`growfund-mr-2 ${textClass}`}>{STATUS_MAP[status]}</span>
              {status !== 'published' && (
                <InfoTooltip iconClassName="growfund-text-icon-critical">
                  {status === 'draft'
                    ? __('This page is not yet published. Run the fix to resolve.', 'growfund')
                    : __('Page not found. Run the fix to regenerate.', 'growfund')}
                </InfoTooltip>
              )}
            </div>
          );
        },
      }),
    ] as TableColumnDef<WPPage>[];
  }, [form, pagesOptions]);

  return (
    <div className="growfund-space-y-6">
      <Form {...form}>
        <Card>
          <CardHeader>
            <CardTitle className="growfund-flex growfund-items-center growfund-justify-between">
              <BrandIcon className="growfund-w-[100px] growfund-h-5 growfund-flex-shrink-0" />

              <Button
                variant="primary"
                disabled={generateManualPagesMutation.isPending || !isNotAllPublished}
                loading={generateManualPagesMutation.isPending}
                className="disabled:growfund-opacity-50"
                onClick={handleRegenerate}
              >
                <HammerIcon className="growfund-size-4" />
                {__('Run Fix', 'growfund')}
              </Button>
            </CardTitle>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            {matchQueryStatus(growfundPagesQuery, {
              Loading: <LoadingSpinnerOverlay />,

              Error: (
                <ErrorState className="growfund-mt-10">
                  <ErrorIcon />
                  <ErrorStateDescription>
                    {__('Error fetching manual pages', 'growfund')}
                  </ErrorStateDescription>
                </ErrorState>
              ),

              Empty: (
                <EmptyState>
                  <ErrorIcon />
                  <EmptyStateDescription>
                    {__(
                      'No manual pages found. Click "Re-Generate Pages" to create them.',
                      'growfund',
                    )}
                  </EmptyStateDescription>
                </EmptyState>
              ),

              Success: () => {
                const hasIssues = growfundPages.some((page) => page.status !== 'published');

                return (
                  <div className="growfund-space-y-4">
                    {hasIssues && (
                      <Alert variant="warning">
                        <div className="growfund-flex growfund-items-center growfund-gap-2">
                          <AlertTriangle className="growfund-size-5 growfund-text-warning" />
                          <AlertTitle className="growfund-mb-0">
                            {__('Missing/Inaccessible Pages Found', 'growfund')}
                          </AlertTitle>
                        </div>
                        <AlertDescription>
                          {__(
                            'Some pages are currently unavailable. Click Run Fix to automatically restore missing pages or resolve status issues.',
                            'growfund',
                          )}
                        </AlertDescription>
                      </Alert>
                    )}

                    <DataTable data={growfundPages} columns={columns}>
                      <DataTableWrapper>
                        <DataTableContent />
                      </DataTableWrapper>
                    </DataTable>
                  </div>
                );
              },
            })}
          </CardContent>
        </Card>
      </Form>
    </div>
  );
};

export default ManualPagesGenerationSettingspage;

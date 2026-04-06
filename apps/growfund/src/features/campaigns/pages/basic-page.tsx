import { DashIcon, StarFilledIcon } from '@radix-ui/react-icons';
import { __, sprintf } from '@wordpress/i18n';
import { Star } from 'lucide-react';
import { useEffect, useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { toast } from 'sonner';

import ElementWrapper from '@/components/element-wrapper';
import { ComboBoxField } from '@/components/form/combobox-field';
import { DatePickerField } from '@/components/form/date-picker-field';
import { EditorField } from '@/components/form/editor-field';
import { GalleryField } from '@/components/form/gallery-field/gallery-field';
import { SlugField } from '@/components/form/slug-field';
import { SwitchField } from '@/components/form/switch-field';
import { TagsField } from '@/components/form/tags-field';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import VideoField from '@/components/form/video-field';
import { Container } from '@/components/layouts/container';
import CampaignLaunchDateFallback from '@/components/pro-fallbacks/campaign/campaign-launch-date-fallback';
import CollaboratorsFallback from '@/components/pro-fallbacks/campaign/collaborators-fallback';
import ShowCollaboratorsCheckboxFallback from '@/components/pro-fallbacks/campaign/show-collaborators-checkbox-fallback';
import { useAppConfig } from '@/contexts/app-config';
import CampaignAction from '@/features/campaigns/components/campaign-action/campaign-action';
import StepNavigation from '@/features/campaigns/components/step-navigation';
import { type CampaignBuilderForm } from '@/features/campaigns/schemas/campaign';
import {
  useCreateCategoryMutation,
  useGetSubCategoriesQuery,
  useGetTopLevelCategoriesQuery,
} from '@/features/categories/services/category';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import useCurrentUser from '@/hooks/use-current-user';
import { useQueryToOption } from '@/hooks/use-query-to-option';
import { registry } from '@/lib/registry';
import { isDefined, trim } from '@/utils';
import { locationsAsOptions } from '@/utils/countries';
import { MediaType } from '@/utils/media';

const BasicStep = () => {
  const { appConfig } = useAppConfig();
  const { isAdmin } = useCurrentUser();
  const form = useFormContext<CampaignBuilderForm>();

  const topLevelCategoriesQuery = useGetTopLevelCategoriesQuery();
  const categoryOptions = useQueryToOption(topLevelCategoriesQuery, 'id', 'name');

  const categoryId = useWatch({ control: form.control, name: 'category' });

  const subCategoriesQuery = useGetSubCategoriesQuery(categoryId ?? undefined);
  const subCategoryOptions = useQueryToOption(subCategoriesQuery, 'id', 'name');

  const subCategoryId = useWatch({
    control: form.control,
    name: 'sub_category',
  });
  const isFeatured = useWatch({ control: form.control, name: 'is_featured' });
  const startDate = useWatch({ control: form.control, name: 'start_date' });

  const permalinkUrl = useMemo(() => {
    const baseUrl = window.location.origin;
    if (!appConfig[AppConfigKeys.Advanced]?.campaign_permalink) return baseUrl;

    return sprintf(
      '%s/%s',
      baseUrl,
      trim(appConfig[AppConfigKeys.Advanced].campaign_permalink, '/'),
    );
  }, [appConfig]);

  const createCategoryMutation = useCreateCategoryMutation();

  useEffect(() => {
    if (categoryId && !subCategoryOptions.find((option) => option.value === subCategoryId)) {
      form.resetField('sub_category');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [categoryId, subCategoryOptions]);

  const handleCategoryCreate = async (name: string, parentId?: string | null) => {
    try {
      const response = await createCategoryMutation.mutateAsync({
        name,
        parent_id: parentId,
      });
      const newCategoryId = (response.data as { id: string }).id;

      if (isDefined(parentId)) {
        form.clearErrors('sub_category');
        form.setValue('sub_category', newCategoryId);

        return true;
      }

      form.clearErrors('category');
      form.setValue('category', newCategoryId);

      return true;
    } catch {
      toast.error(__('Failed to create category.', 'growfund'));
      return false;
    }
  };

  const CampaignCollaborators = registry.get('CampaignCollaborators');
  const CampaignLaunchDate = registry.get('CampaignLaunchDate');
  const ShowCampaignCollaboratorsCheckbox = registry.get('ShowCampaignCollaboratorsCheckbox');

  return (
    <Container>
      <div className="growfund-grid growfund-grid-cols-[auto_25rem] growfund-gap-6">
        <div className="growfund-h-fit growfund-flex growfund-flex-col growfund-gap-4">
          <div className="growfund-bg-background-surface growfund-p-4 growfund-rounded-md growfund-shadow-sm growfund-space-y-4">
            <div>
              <TextField
                control={form.control}
                name="title"
                label={__('Title', 'growfund')}
                placeholder={__('e.g. Save the blooms', 'growfund')}
              />
              <SlugField
                control={form.control}
                name="slug"
                placeholder={__('e.g. save-the-blooms', 'growfund')}
                className="growfund-w-52 growfund-h-8 growfund-rounded-md growfund-py-2 growfund-px-3"
                permalinkUrl={permalinkUrl}
              />
            </div>
            <TextareaField
              control={form.control}
              name="description"
              label={__('Description', 'growfund')}
              placeholder={__(
                'e.g. Help us protect rare flowers by building community gardens, educating communities, and promoting sustainable growing practices.',
                'growfund',
              )}
              rows={6}
            />
            <EditorField
              control={form.control}
              name="story"
              label={__('Story', 'growfund')}
              placeholder={__('Write down the story of your funding.', 'growfund')}
            />
            <GalleryField
              control={form.control}
              name="images"
              label={__('Images', 'growfund')}
              uploadButtonLabel={__('Upload images', 'growfund')}
              dropzoneLabel={__('Drag and drop, or upload images', 'growfund')}
              accept={[MediaType.IMAGES]}
            />
          </div>
          <div className="growfund-self-end">
            <StepNavigation />
          </div>
        </div>
        <div className="growfund-space-y-4">
          <CampaignAction />
          <div className="growfund-bg-background-surface growfund-p-4 growfund-rounded-md growfund-shadow-sm growfund-space-y-4 growfund-h-fit">
            <VideoField
              control={form.control}
              name="video"
              label={__('Video', 'growfund')}
              uploadButtonLabel={__('Upload video', 'growfund')}
              dropzoneLabel={__('Drag and drop, or upload video', 'growfund')}
            />
            <div className="growfund-flex growfund-items-start growfund-gap-2 growfund-border growfund-border-border growfund-rounded-md growfund-py-2 growfund-px-3">
              {isFeatured ? (
                <StarFilledIcon className="growfund-size-4 growfund-text-icon-caution-active" />
              ) : (
                <Star className="growfund-size-4 growfund-text-icon-primary" />
              )}

              <SwitchField
                control={form.control}
                name="is_featured"
                label={__('Feature this campaign', 'growfund')}
                description={__('Appears prominently on lists & pages.', 'growfund')}
              />
            </div>
            <ComboBoxField
              control={form.control}
              name="category"
              label={__('Category', 'growfund')}
              options={categoryOptions}
              showAddItemButton={isAdmin}
              onAddNewItem={async (value) => {
                return await handleCategoryCreate(value);
              }}
            />
            <ComboBoxField
              control={form.control}
              name="sub_category"
              label={__('Sub-Category', 'growfund')}
              options={subCategoryOptions}
              showAddItemButton={!!categoryId}
              onAddNewItem={(value) => handleCategoryCreate(value, categoryId)}
            />
            <div className="growfund-flex growfund-items-start growfund-gap-2">
              <ElementWrapper fallback={<CampaignLaunchDateFallback defaultValue={startDate} />}>
                {CampaignLaunchDate && <CampaignLaunchDate />}
              </ElementWrapper>
              <DashIcon className="growfund-text-icon-primary growfund-mt-10 growfund-flex-shrink-0 " />
              <DatePickerField
                control={form.control}
                name="end_date"
                label={__('End Date', 'growfund')}
                placeholder={__('Pick a date', 'growfund')}
                clearable
              />
            </div>

            <ComboBoxField
              control={form.control}
              name="location"
              label={__('Location', 'growfund')}
              options={locationsAsOptions()}
            />
            <TagsField control={form.control} name="tags" label={__('Tags', 'growfund')} />
            <ElementWrapper fallback={<CollaboratorsFallback />}>
              {CampaignCollaborators && <CampaignCollaborators />}
            </ElementWrapper>
            <ElementWrapper fallback={<ShowCollaboratorsCheckboxFallback />}>
              {ShowCampaignCollaboratorsCheckbox && <ShowCampaignCollaboratorsCheckbox />}
            </ElementWrapper>
          </div>
        </div>
      </div>
    </Container>
  );
};

export default BasicStep;

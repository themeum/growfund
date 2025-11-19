import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import FeatureGuard from '@/components/feature-guard';
import { MediaField } from '@/components/form/media-field';
import RangeSliderField from '@/components/form/range-slider-field';
import BrandingColorSelectionFallback from '@/components/pro-fallbacks/settings/branding/color-selection-fallback';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
    BrandingSettingsSchema,
    type BrandingSettingsForm,
} from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';
import { MediaType } from '@/utils/media';

const BrandingSettingsPage = () => {
  const { appConfig } = useAppConfig();
  const form = useForm<BrandingSettingsForm>({
    resolver: zodResolver(BrandingSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, {
      ...getDefaults(BrandingSettingsSchema),
      ...(appConfig[AppConfigKeys.Branding] ?? {}),
    });
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<BrandingSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Branding, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const logoHeight = useWatch({ control: form.control, name: 'logo_height' });

  const BrandingColorSelection = registry.get('BrandingColorSelection');

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader>
            <CardTitle>{__('Branding', 'growfund')}</CardTitle>
            <CardDescription>
              {__("Customize your brand's visual elements.", 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-grid growfund-grid-cols-2 growfund-gap-6">
            <div className="growfund-space-y-4">
              <MediaField
                control={form.control}
                name="logo"
                label={__('Logo', 'growfund')}
                uploadButtonLabel={__('Upload Image', 'growfund')}
                dropzoneLabel={__('Drag and drop, or upload a logo', 'growfund')}
                accept={[MediaType.IMAGES]}
                style={{
                  height: `${logoHeight || 28}px`,
                }}
              />
              <RangeSliderField
                control={form.control}
                name="logo_height"
                label={__('Height', 'growfund')}
                min={20}
                max={60}
              />
            </div>
            <FeatureGuard
              feature="settings.branding.colors"
              fallback={<BrandingColorSelectionFallback />}
            >
              {BrandingColorSelection && <BrandingColorSelection />}
            </FeatureGuard>
          </CardContent>
        </Card>
      </div>
    </Form>
  );
};

export default BrandingSettingsPage;

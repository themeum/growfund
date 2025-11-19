import { __ } from '@wordpress/i18n';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const IntegrationsSettingsPage = () => {
  return (
    <div className="growfund-grid growfund-gap-4">
      <Card>
        <CardHeader>
          <CardTitle>{__('Integrations', 'growfund')}</CardTitle>
          <CardDescription>{__('Manage your integrations.', 'growfund')}</CardDescription>
        </CardHeader>
        <CardContent className="growfund-text-fg-special-3">
          {__('This settings will be implemented later.', 'growfund')}
        </CardContent>
      </Card>
    </div>
  );
};

export default IntegrationsSettingsPage;

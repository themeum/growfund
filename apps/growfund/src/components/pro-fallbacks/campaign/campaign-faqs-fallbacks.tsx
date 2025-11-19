import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';
import { Separator } from '@/components/ui/separator';

const CampaignFaqsFallbacks = () => {
  return (
    <Card>
      <CardHeader>
        <CardTitle>
          {__('Frequently Asked Questions', 'growfund')} <ProBadge />
        </CardTitle>
      </CardHeader>
      <Separator />
      <CardContent className="growfund-mt-4 growfund-space-y-3">
        <Button variant="secondary" className="growfund-w-full" disabled>
          <Plus />
          {__('Add FAQ', 'growfund')}
        </Button>
      </CardContent>
    </Card>
  );
};

export default CampaignFaqsFallbacks;

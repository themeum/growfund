import { __ } from '@wordpress/i18n';
import { FileText } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useCurrency } from '@/hooks/use-currency';

const backers = [
  {
    name: 'Oliver',
    amount: 500,
    image: 'https://api.dicebear.com/7.x/adventurer/svg?seed=Oliver',
  },
  {
    name: 'Emma',
    amount: 400,
    image: 'https://api.dicebear.com/7.x/adventurer/svg?seed=Emma',
  },
  {
    name: 'James',
    amount: 300,
    image: 'https://api.dicebear.com/7.x/adventurer/svg?seed=James',
  },
  {
    name: 'Sophia',
    amount: 200,
    image: 'https://api.dicebear.com/7.x/adventurer/svg?seed=Sophia',
  },
  {
    name: 'William',
    amount: 100,
    image: 'https://api.dicebear.com/7.x/adventurer/svg?seed=William',
  },
  {
    name: 'Anonymous',
    amount: 100,
    image: 'https://api.dicebear.com/7.x/adventurer/svg?seed=Anonymous',
  },
];

const variants = ['primary', 'secondary', 'destructive', 'warning', 'info', 'special'] as const;

const TopBackers = () => {
  const { toCurrency } = useCurrency();
  return (
    <Card className="growfund-group/top-backers">
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <CardTitle>{__('Top Backers', 'growfund')}</CardTitle>
          <Button
            variant="ghost"
            size="sm"
            className="growfund-opacity-0 group-hover/top-backers:growfund-opacity-100"
            onClick={() => {
              alert('Will be implemented in the future');
            }}
          >
            <FileText className="growfund-size-4" />
            {__('See All Backers', 'growfund')}
          </Button>
        </div>
      </CardHeader>
      <CardContent className="growfund-space-y-4">
        {backers.map((backer) => (
          <div key={backer.name} className="growfund-flex growfund-items-center growfund-justify-between">
            <div className="growfund-flex growfund-items-center growfund-gap-2">
              <Avatar className="growfund-size-8">
                <AvatarImage src={backer.image} />
                <AvatarFallback>{backer.name.charAt(0)}</AvatarFallback>
              </Avatar>
              <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{backer.name}</p>
            </div>
            <Badge variant={variants[Math.floor(Math.random() * variants.length)]}>
              {toCurrency(backer.amount)}
            </Badge>
          </div>
        ))}
      </CardContent>
    </Card>
  );
};

export default TopBackers;

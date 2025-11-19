import { __, sprintf } from '@wordpress/i18n';
import { FileText, Users } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DotSeparator } from '@/components/ui/dot-separator';
import { Image } from '@/components/ui/image';
import { Progress } from '@/components/ui/progress';
import { formatCurrency } from '@/utils/currency';

const { toCurrency } = formatCurrency({ fractions: 0 });

const topDonations = [
  {
    id: 1,
    title: 'Save the Rainforest',
    raised: 15000,
    goal: 25000,
    total_backers: 342,
    image:
      'https://images.unsplash.com/photo-1460904577954-8fadb262612c?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 2,
    title: 'Clean Ocean Initiative',
    raised: 8500,
    goal: 10000,
    total_backers: 156,
    image:
      'https://images.unsplash.com/photo-1559494007-9f5847c49d94?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 3,
    title: 'Local Food Bank Support',
    raised: 5600,
    goal: 7500,
    total_backers: 89,
    image:
      'https://images.unsplash.com/photo-1593113598332-cd288d649433?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 4,
    title: "Children's Hospital Equipment",
    raised: 45000,
    goal: 50000,
    total_backers: 678,
    image:
      'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 5,
    title: 'Animal Shelter Renovation',
    raised: 12300,
    goal: 15000,
    total_backers: 245,
    image:
      'https://images.unsplash.com/photo-1548681528-6a5c45b66b42?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 6,
    title: 'Community Art Center',
    raised: 9800,
    goal: 20000,
    total_backers: 167,
    image:
      'https://images.unsplash.com/photo-1513364776144-60967b0f800f?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 7,
    title: 'Youth Sports Program',
    raised: 15600,
    goal: 30000,
    total_backers: 234,
    image:
      'https://images.unsplash.com/photo-1517466787929-bc90951d0974?q=80&w=500&auto=format&fit=crop',
  },
  {
    id: 8,
    title: 'Senior Center Expansion',
    raised: 28900,
    goal: 40000,
    total_backers: 412,
    image:
      'https://images.unsplash.com/photo-1544654803-b69140b285a1?q=80&w=500&auto=format&fit=crop',
  },
];

const TopDonations = () => {
  return (
    <Card className="growfund-group/top-donations">
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <CardTitle>{__('Top Donations', 'growfund')}</CardTitle>
          <Button
            variant="ghost"
            size="sm"
            className="growfund-opacity-0 group-hover/top-donations:growfund-opacity-100"
            onClick={() => {
              alert('Will be implemented in the future');
            }}
          >
            <FileText className="growfund-size-4" />
            {__('See All Campaigns', 'growfund')}
          </Button>
        </div>
      </CardHeader>
      <CardContent className="growfund-space-y-4">
        {topDonations.map((donation) => (
          <div
            className="growfund-grid growfund-grid-cols-[56px_auto] growfund-gap-3 growfund-min-h-[5rem] growfund-items-center"
            key={donation.id}
          >
            <Image src={donation.image} alt={donation.title} fit="cover" aspectRatio="square" />
            <div className="growfund-space-y-2">
              <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{donation.title}</p>
              <Progress value={(donation.raised / donation.goal) * 100} size="sm" />
              <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-small growfund-font-medium growfund-text-fg-subdued">
                <div>
                  <span className="growfund-text-fg-primary">{toCurrency(donation.raised)}</span>
                  <span>
                    {/* translator: %s: goal */}
                    {sprintf(__(' collected of %s goal', 'growfund'), toCurrency(donation.goal))}
                  </span>
                </div>
                <DotSeparator />
                <Users className="growfund-size-3" />
                <span className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                  {/* translator: %s: total backers */}
                  {sprintf(__('%s Backers', 'growfund'), donation.total_backers)}
                </span>
              </div>
            </div>
          </div>
        ))}
      </CardContent>
    </Card>
  );
};

export default TopDonations;

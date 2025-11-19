import PledgeItem from '@/dashboards/backers/features/pledges/components/pledge-item';
import { type Pledge } from '@/features/pledges/schemas/pledge';

interface PledgeListProps {
  pledges: Pledge[];
}

const PledgeList = ({ pledges }: PledgeListProps) => {
  return (
    <div className="growfund-space-y-3 growfund-mb-10">
      {pledges.map((pledge) => {
        return <PledgeItem pledge={pledge} key={pledge.id} />;
      })}
    </div>
  );
};

export default PledgeList;
